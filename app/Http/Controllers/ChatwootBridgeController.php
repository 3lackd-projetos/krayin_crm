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
                return response()->view('chatwoot.bridge', ['error' => 'Acesso negado: Token inválido.'], 403);
            }

            $email = $request->query('email');
            if (!$email) {
                return response()->view('chatwoot.bridge', ['error' => 'E-mail não fornecido.'], 400);
            }

            $person = $this->personRepository->findOneByField('email', $email);

            $leads = collect();
            if ($person) {
                // Simplified lead fetch
                $leads = $this->leadRepository->findWhere(['person_id' => $person->id]);
            }

            return view('chatwoot.bridge', [
                'person' => $person,
                'leads' => $leads,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Chatwoot Bridge CRASH: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Return 200 but with error content for debugging
            return response()->view('chatwoot.bridge', [
                'error' => 'ERRO CRÍTICO: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine()
            ], 200);
        }
    }
}
