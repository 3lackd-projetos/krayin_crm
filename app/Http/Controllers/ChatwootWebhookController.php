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
            'contact_updated',
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
        // 1. Try multiple ways to find the contact info (aggressive extraction)
        $contact = null;

        if (isset($payload['sender'])) {
            $contact = $payload['sender'];
        } elseif (isset($payload['contact'])) {
            $contact = $payload['contact'];
        } elseif (isset($payload['data']) && is_array($payload['data'])) {
            $contact = $payload['data']; // contact_updated often puts data in 'data'
        } elseif (isset($payload['meta']['sender'])) {
            $contact = $payload['meta']['sender']; // conversation_created
        } elseif (isset($payload['email'])) {
            $contact = $payload; // payload itself IS the contact
        }

        if (!$contact || empty($contact['email'])) {
            Log::info('Chatwoot Webhook: Skipped - No email found in payload structure.', [
                'keys' => array_keys($payload),
                'event' => $payload['event'] ?? 'unknown'
            ]);
            return response()->json(['status' => 'no_email_skipped']);
        }

        $email = $contact['email'];
        $name = $contact['name'] ?? 'Chatwoot User';
        $phone = $contact['phone_number'] ?? ($contact['phone'] ?? '');

        // 2. Find or Create Person (using JSON aware search)
        $person = $this->personRepository->scopeQuery(function ($query) use ($email) {
            return $query->whereJsonContains('emails', ['value' => $email]);
        })->first();

        $personData = [
            'name' => $name,
            'emails' => [['value' => $email, 'label' => 'work']],
            'entity_type' => 'persons',
        ];

        if ($phone) {
            $personData['contact_numbers'] = [['value' => $phone, 'label' => 'work']];
        }

        if (!$person) {
            $personData['user_id'] = 1; // Default Admin
            $person = $this->personRepository->create($personData);
            Log::info('Chatwoot Webhook: Created Person ' . $email);
        } else {
            // Update existing person with new info from Chatwoot
            $this->personRepository->update($personData, $person->id);
            Log::info('Chatwoot Webhook: Updated Person ' . $email);
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
            'user_id' => $person->user_id ?? 1,
            'person_id' => $person->id,
            'lead_source_id' => 1,
            'lead_type_id' => 1,
            'lead_pipeline_id' => 1,
            'lead_pipeline_stage_id' => 1,
            'chatwoot_conversation_id' => $conversationId,
            'chatwoot_account_id' => $accountId,
        ];

        if (!$existingLead) {
            $leadData['description'] = 'Lead criado automaticamente via Chatwoot Webhook.';
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
