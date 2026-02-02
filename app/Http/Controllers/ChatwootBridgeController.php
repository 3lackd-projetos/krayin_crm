<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;

class ChatwootBridgeController extends Controller
{
    public function __construct(
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository
    ) {
    }

    public function index(Request $request)
    {
        Log::info('--- Chatwoot Bridge HEARTBEAT ---');

        try {
            $token = $request->query('token');
            $secret = env('CHATWOOT_BRIDGE_SECRET');

            Log::info('Chatwoot Bridge Access', [
                'email' => $request->query('email'),
                'token_match' => ($token === $secret)
            ]);

            if (!$secret || $token !== $secret) {
                return $this->safeView(['error' => 'Acesso negado: Token inválido.'], 403);
            }

            $email = $request->query('email');

            // Check if variable substitution failed in Chatwoot
            if (!$email || $email === '{{contact.email}}' || $email === '{{ email }}') {
                return $this->safeView(['error' => 'E-mail não detectado pelo Chatwoot. Verifique as chaves {{ }} no App Dashboard.'], 400);
            }

            // Krayin stores emails in a JSON column 'emails'. 
            // format: [{"value": "email@example.com", "label": "work"}]
            $person = $this->personRepository->scopeQuery(function ($query) use ($email) {
                return $query->whereJsonContains('emails', ['value' => $email]);
            })->first();

            $leads = collect();
            if ($person) {
                // If the CRM user is logged in, use Krayin's internal permissions
                // Otherwise, show the leads (protected by the Bridge Token)
                if (auth()->check()) {
                    $userIds = bouncer()->getAuthorizedUserIds();
                    $leads = $this->leadRepository->findWhereIn('user_id', $userIds)
                        ->where('person_id', $person->id);
                } else {
                    $leads = $this->leadRepository->findWhere(['person_id' => $person->id]);
                }
            }

            return $this->safeView([
                'person' => $person,
                'leads' => $leads,
                'email' => $email,
                'current_user' => auth()->user()
            ]);

        } catch (\Exception $e) {
            Log::error('Chatwoot Bridge CRASH: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
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
