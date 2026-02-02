<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Krayin CRM Bridge</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 10px;
            line-height: 1.4;
        }

        .card {
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            padding: 12px;
            background: #fff;
            margin-bottom: 10px;
        }

        .header {
            font-weight: bold;
            margin-bottom: 8px;
            color: #0E90D9;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .label {
            color: #888;
            font-size: 11px;
            text-transform: uppercase;
        }

        .value {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .lead-item {
            border-left: 3px solid #0E90D9;
            padding-left: 8px;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .btn {
            display: inline-block;
            background: #0E90D9;
            color: #fff;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 11px;
            margin-top: 5px;
        }

        .error {
            color: #d93025;
            background: #fce8e6;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #f28b82;
        }
    </style>
</head>

<body>
    @if(isset($error))
        <div class="error">{{ $error }}</div>
    @elseif(!$person)
        <div class="card">
            <div class="header">Krayin CRM</div>
            <p>Cliente não encontrado: <strong>{{ $email }}</strong></p>
            <a href="{{ config('app.url') }}/admin/contacts/persons/create?email={{ $email }}" target="_blank" class="btn">+
                Criar no CRM</a>
        </div>
    @else
        <div class="card">
            <div class="header">Dados do Contato</div>
            <div class="label">Nome</div>
            <div class="value">{{ $person->name }}</div>

            <div class="label">E-mail</div>
            <div class="value">{{ $person->email }}</div>

            @if($person->contact_numbers && count($person->contact_numbers) > 0)
                <div class="label">Telefone</div>
                <div class="value">{{ $person->contact_numbers[0]['value'] }}</div>
            @endif

            <a href="{{ config('app.url') }}/admin/contacts/persons/view/{{ $person->id }}" target="_blank"
                class="btn">Abrir Perfil Completo</a>
        </div>

        @if(count($leads) > 0)
            <div class="card">
                <div class="header">Leads Ativos ({{ count($leads) }})</div>
                @foreach($leads as $lead)
                    <div class="lead-item">
                        <strong>{{ $lead->title }}</strong><br>
                        @php $stage = $lead->stage; @endphp
                        Etapa: <span style="color: #0E90D9; font-weight: bold;">{{ $stage->name ?? 'N/A' }}</span><br>
                        @if(function_exists('core'))
                            Valor: {{ core()->formatBasePrice($lead->lead_value) }}
                        @else
                            Valor: {{ $lead->lead_value }}
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="card">
                <div class="header">Leads</div>
                <p>Nenhum lead encontrado para este contato.</p>
                <a href="{{ config('app.url') }}/admin/leads/create?person_id={{ $person->id }}" target="_blank" class="btn">+
                    Novo Lead</a>
            </div>
        @endif
    @endif
</body>

</html>