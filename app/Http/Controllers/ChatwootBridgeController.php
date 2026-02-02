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
        \Illuminate\Support\Facades\Log::info('Chatwoot Bridge Access Attempt', [
            'email' => $request->query('email'),
            'ip' => $request->ip()
        ]);

        $token = $request->query('token');
        $secret = env('CHATWOOT_BRIDGE_SECRET');

        if (!$secret || $token !== $secret) {
            return response()->view('chatwoot.bridge', ['error' => 'Acesso negado: Token inválido ou não configurado.'], 403);
        }

        $email = $request->query('email');
        if (!$email) {
            return response()->view('chatwoot.bridge', ['error' => 'E-mail não fornecido.'], 400);
        }

        $person = $this->personRepository->findOneByField('email', $email);

        $leads = [];
        if ($person) {
            $leads = $this->leadRepository->with(['stage'])->findWhere(['person_id' => $person->id]);
        }

        return view('chatwoot.bridge', [
            'person' => $person,
            'leads' => $leads,
            'email' => $email
        ]);
    }
}
