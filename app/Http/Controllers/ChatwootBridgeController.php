<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;

use Webkul\User\Repositories\UserRepository;

class ChatwootBridgeController extends Controller
{
    public function __construct(
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository,
        protected UserRepository $userRepository
    ) {
    }

    public function index(Request $request)
    {
        Log::info('--- CORAÇÃO DA PONTE CHATWOOT ATIVO ---');

        try {
            $token = $request->query('token');
            $secret = env('CHATWOOT_BRIDGE_SECRET');
            $agentEmail = $request->query('agent_email');

            Log::info('Acesso à Ponte Chatwoot', [
                'email' => $request->query('email'),
                'agente' => $agentEmail,
                'token_valido' => ($token === $secret)
            ]);

            if (!$secret || $token !== $secret) {
                return $this->safeView(['error' => 'Acesso negado: Token inválido.'], 403);
            }

            $email = $request->query('email');
            $name = $request->query('name');
            $phone = $request->query('phone');
            $jobTitle = $request->query('job_title');

            // Check if variable substitution failed in Chatwoot
            if (!$email || $email === '{{contact.email}}' || $email === '{{ email }}') {
                return $this->safeView([
                    'error' => 'E-mail não detectado pelo Chatwoot.',
                    'show_dashboard_link' => true
                ], 200);
            }

            // identify CRM User for ACL
            $crmUser = null;
            if ($agentEmail) {
                $crmUser = $this->userRepository->findOneByField('email', $agentEmail);
            }

            // Krayin stores emails in a JSON column 'emails'. 
            $person = $this->personRepository->scopeQuery(function ($query) use ($email) {
                return $query->whereJsonContains('emails', ['value' => $email]);
            })->first();

            $leads = collect();
            if ($person) {
                // Determine user context: Priority to active session, fallback to mapped agent email
                $currentUser = auth()->user() ?? $crmUser;

                if ($currentUser) {
                    // Logic to load leads according to user permissions
                    // We can either filter by own leads or use Krayin's permission logic
                    $leads = $this->leadRepository->findWhere([
                        'user_id' => $currentUser->id,
                        'person_id' => $person->id
                    ]);

                    // Fallback: If no leads found for specific user but person exists, 
                    // check if user has permission to see all leads.
                    if ($leads->isEmpty() && $currentUser->role->permission_type === 'all') {
                        $leads = $this->leadRepository->findWhere(['person_id' => $person->id]);
                    }
                } else {
                    // No user identified, show all associated leads (Bridge token is the security layer)
                    $leads = $this->leadRepository->findWhere(['person_id' => $person->id]);
                }
            }

            return $this->safeView([
                'person' => $person,
                'leads' => $leads,
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
                'job_title' => $jobTitle,
                'agent' => $crmUser,
                'current_user' => auth()->user()
            ]);

        } catch (\Exception $e) {
            Log::error('FALHA CRÍTICA NA PONTE: ' . $e->getMessage(), [
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine()
            ]);

            return $this->safeView([
                'error' => 'ERRO CRÍTICO: ' . $e->getMessage()
            ], 200);
        }
    }

    /**
     * Safety wrapper for view to avoid 500 if view file is missing from Git
     */
    private function safeView($data, $status = 200)
    {
        if (view()->exists('chatwoot.bridge')) {
            return response()->view('chatwoot.bridge', $data, $status);
        }

        return response()->json([
            'status' => 'error_missing_view',
            'message' => 'O arquivo da view chatwoot.bridge não foi encontrado no servidor. Verifique se deu "git add" no arquivo.',
            'debug_data' => $data
        ], $status);
    }
}
