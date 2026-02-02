<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Attribute\Repositories\AttributeValueRepository;

class ChatwootWebhookController extends Controller
{
    public function __construct(
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository
    ) {
    }

    public function handle(Request $request)
    {
        $token = $request->query('token');
        $secret = env('CHATWOOT_BRIDGE_SECRET');

        if (!$secret || $token !== $secret) {
            Log::warning('Chatwoot Webhook: Unauthorized access attempt.');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? '';

        Log::info('Chatwoot Webhook Received: ' . $event);

        $allowedEvents = [
            'contact_created',
            'conversation_created',
            'conversation_status_changed',
            'message_created'
        ];

        if (in_array($event, $allowedEvents)) {
            return $this->processEngagement($payload);
        }

        return response()->json(['status' => 'ignored']);
    }

    protected function processEngagement($payload)
    {
        // 1. Try different ways to find the contact info
        $contact = $payload['sender'] ?? ($payload['contact'] ?? null);

        // In conversation_created/updated, it's often in meta
        if (!$contact && isset($payload['meta']['sender'])) {
            $contact = $payload['meta']['sender'];
        }

        if (!$contact || empty($contact['email'])) {
            return response()->json(['status' => 'no_email_skipped']);
        }

        $email = $contact['email'];
        $name = $contact['name'] ?? 'Chatwoot User';
        $phone = $contact['phone_number'] ?? '';

        // 2. Find or Create Person
        $person = $this->personRepository->findOneByField('email', $email);

        if (!$person) {
            $personData = [
                'name' => $name,
                'emails' => [['value' => $email, 'label' => 'work']],
                'entity_type' => 'persons',
                'user_id' => 1,
            ];

            if ($phone) {
                $personData['contact_numbers'] = [['value' => $phone, 'label' => 'work']];
            }

            $person = $this->personRepository->create($personData);
            Log::info('Chatwoot Webhook: Created Person ' . $email);
        }

        // 3. Extract IDs for Reverse Sync
        $conversationId = $payload['conversation']['id'] ?? ($payload['id'] ?? null);
        $accountId = $payload['account']['id'] ?? ($payload['account_id'] ?? null);

        // 4. Manage Lead
        $existingLead = $this->leadRepository->findOneWhere([
            'person_id' => $person->id,
            'lead_pipeline_stage_id' => 1,
        ]);

        $leadData = [
            'title' => 'Lead Chatwoot - ' . $name,
            'user_id' => 1,
            'person_id' => $person->id,
            'lead_source_id' => 1,
            'lead_type_id' => 1,
            'lead_pipeline_id' => 1,
            'lead_pipeline_stage_id' => 1,
            'chatwoot_conversation_id' => $conversationId,
            'chatwoot_account_id' => $accountId,
        ];

        if (!$existingLead) {
            $leadData['description'] = 'Lead criado automaticamente via Chatwoot.';
            $this->leadRepository->create($leadData);
            Log::info('Chatwoot Webhook: Created Lead for ' . $email);
        } else {
            // Update conversation mapping on existing lead
            $this->leadRepository->update($leadData, $existingLead->id);
            Log::info('Chatwoot Webhook: Updated Mapping for Lead ' . $existingLead->id);
        }

        return response()->json(['status' => 'success']);
    }
}
