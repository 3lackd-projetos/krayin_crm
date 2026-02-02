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
        protected LeadRepository $leadRepository,
        protected AttributeValueRepository $attributeValueRepository
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

        if ($event === 'contact_created' || $event === 'message_created') {
            return $this->processEngagement($payload);
        }

        return response()->json(['status' => 'ignored']);
    }

    protected function processEngagement($payload)
    {
        $contact = $payload['contact'] ?? ($payload['sender'] ?? null);

        if (!$contact || empty($contact['email'])) {
            return response()->json(['status' => 'no_email_skipped']);
        }

        $email = $contact['email'];
        $name = $contact['name'] ?? 'Chatwoot User';
        $phone = $contact['phone_number'] ?? '';

        // 1. Find or Create Person
        $person = $this->personRepository->findOneByField('email', $email);

        if (!$person) {
            $personData = [
                'name' => $name,
                'emails' => [['value' => $email, 'label' => 'work']],
                'entity_type' => 'persons',
                'user_id' => 1, // Default to admin
            ];

            if ($phone) {
                $personData['contact_numbers'] = [['value' => $phone, 'label' => 'work']];
            }

            $person = $this->personRepository->create($personData);
            Log::info('Chatwoot Webhook: Created new Person - ' . $email);
        }

        // 2. Extract Chatwoot IDs
        $conversationId = $payload['conversation']['id'] ?? null;
        $accountId = $payload['account']['id'] ?? null;

        // 3. Check for active leads to avoid duplicates or update details
        $existingLead = $this->leadRepository->findOneWhere([
            'person_id' => $person->id,
            'lead_pipeline_stage_id' => 1,
        ]);

        $leadData = [
            'title' => 'Novo Lead do Chatwoot - ' . $name,
            'description' => 'Lead criado automaticamente via integração direta com Chatwoot.',
            'lead_value' => 0,
            'user_id' => 1, // Default to admin
            'person_id' => $person->id,
            'lead_source_id' => 1,
            'lead_type_id' => 1,
            'lead_pipeline_id' => 1,
            'lead_pipeline_stage_id' => 1,
            'chatwoot_conversation_id' => $conversationId,
            'chatwoot_account_id' => $accountId,
        ];

        if (!$existingLead) {
            $this->leadRepository->create($leadData);
            Log::info('Chatwoot Webhook: Created new Lead for ' . $email . ' (Conv: ' . $conversationId . ')');
        } else {
            // Update existing lead with IDs if missing
            $this->leadRepository->update($leadData, $existingLead->id);
            Log::info('Chatwoot Webhook: Updated IDs for Lead ' . $existingLead->id);
        }

        return response()->json(['status' => 'success']);
    }
}
