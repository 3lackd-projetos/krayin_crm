<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Contact\Repositories\OrganizationRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Attribute\Repositories\AttributeValueRepository;
use Webkul\User\Repositories\UserRepository;

class ChatwootWebhookController extends Controller
{
    public function __construct(
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository,
        protected OrganizationRepository $organizationRepository,
        protected UserRepository $userRepository
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
            $contact = $payload['data'];
        } elseif (isset($payload['meta']['sender'])) {
            $contact = $payload['meta']['sender'];
        } elseif (isset($payload['email'])) {
            $contact = $payload;
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
        $companyName = $contact['company_name'] ?? ($contact['additional_attributes']['company_name'] ?? null);

        // 1.5 Map Assignee (Salesperson) from Chatwoot
        $assigneeEmail = $payload['meta']['assignee']['email'] ?? null;
        $crmOwnerId = 1; // Default Admin
        if ($assigneeEmail) {
            $owner = $this->userRepository->findOneByField('email', $assigneeEmail);
            if ($owner) {
                $crmOwnerId = $owner->id;
                Log::info('Chatwoot Webhook: Mapped to Owner ' . $assigneeEmail);
            }
        }

        // 2. Manage Organization
        $organizationId = null;
        if ($companyName) {
            $organization = $this->organizationRepository->findOneByField('name', $companyName);
            if (!$organization) {
                $organization = $this->organizationRepository->create([
                    'name' => $companyName,
                    'entity_type' => 'organizations',
                    'user_id' => $crmOwnerId,
                ]);
                Log::info('Chatwoot Webhook: Created Organization ' . $companyName);
            }
            $organizationId = $organization->id;
        }

        // 3. Find or Create Person (using JSON aware search)
        $person = $this->personRepository->scopeQuery(function ($query) use ($email) {
            return $query->whereJsonContains('emails', ['value' => $email]);
        })->first();

        $personData = [
            'name' => $name,
            'emails' => [['value' => $email, 'label' => 'work']],
            'entity_type' => 'persons',
            'organization_id' => $organizationId,
            'user_id' => $crmOwnerId,
        ];

        if ($phone) {
            $personData['contact_numbers'] = [['value' => $phone, 'label' => 'work']];
        }

        if (!$person) {
            $person = $this->personRepository->create($personData);
            Log::info('Chatwoot Webhook: Created Person ' . $email . ' owned by ' . $crmOwnerId);
        } else {
            // Update existing person with new info from Chatwoot
            $this->personRepository->update($personData, $person->id);
            Log::info('Chatwoot Webhook: Updated Person ' . $email);
        }

        // 4. Extract IDs for Reverse Sync
        $conversationId = $payload['conversation']['id'] ?? ($payload['id'] ?? null);
        $accountId = $payload['account']['id'] ?? ($payload['account_id'] ?? null);

        // 5. Manage Lead
        $existingLead = $this->leadRepository->findOneWhere([
            'person_id' => $person->id,
            'lead_pipeline_stage_id' => 1,
        ]);

        $leadData = [
            'entity_type' => 'leads',
            'title' => 'Lead Chatwoot - ' . $name,
            'user_id' => $crmOwnerId,
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
