<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;

class ChatwootBridgeController extends Controller
{
    public function __construct(
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository
    ) {
    }

    public function index(Request $request)
    {
        try {
            $token = $request->query('token');
            $secret = env('CHATWOOT_BRIDGE_SECRET');

            \Illuminate\Support\Facades\Log::info('Chatwoot Bridge Access', [
                'email' => $request->query('email'),
                'token_match' => ($token === $secret)
            ]);

            if (!$secret || $token !== $secret) {
                return response()->view('chatwoot.bridge', ['error' => 'Acesso negado: Token inválido.'], 403);
            }

            $email = $request->query('email');
            if (!$email) {
                return response()->view('chatwoot.bridge', ['error' => 'E-mail não fornecido.'], 400);
            }

            $person = $this->personRepository->findOneByField('email', $email);

            $leads = collect();
            if ($person) {
                // Fetch leads without eager loading first to see if it works
                $leads = $this->leadRepository->findWhere(['person_id' => $person->id]);
            }

            return view('chatwoot.bridge', [
                'person' => $person,
                'leads' => $leads,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Chatwoot Bridge Crash: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->view('chatwoot.bridge', [
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }
}
