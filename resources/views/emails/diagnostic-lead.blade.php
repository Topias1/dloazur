<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Nouveau diagnostic — Dlo Azur Piscines</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6; margin: 0; padding: 0; background: #f5f5f5; }
        .wrapper { max-width: 600px; margin: 24px auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #0080ff; color: #fff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; }
        .header p { margin: 4px 0 0; font-size: 13px; opacity: 0.85; }
        .body { padding: 32px; }
        .field { margin-bottom: 20px; }
        .label { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #888; margin-bottom: 4px; }
        .value { font-size: 14px; color: #222; }
        .message-box { background: #f9f9f9; border-left: 3px solid #0080ff; padding: 12px 16px; border-radius: 4px; white-space: pre-wrap; }
        .footer { background: #f0f0f0; padding: 16px 32px; font-size: 12px; color: #999; text-align: center; }
        .divider { border: 0; border-top: 1px solid #eee; margin: 0; }
        .tag { display: inline-block; background: #e8f4ff; color: #0060cc; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-right: 4px; margin-bottom: 4px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Nouveau diagnostic reçu</h1>
        <p>Outil diagnostic piscine — Dlo Azur Piscines</p>
    </div>
    <div class="body">

        {{-- Coordonnées --}}
        <div class="field">
            <div class="label">Prénom</div>
            <div class="value">{{ $prenom }}</div>
        </div>

        <div class="field">
            <div class="label">Commune</div>
            <div class="value">{{ $commune }}</div>
        </div>

        @if ($email)
        <div class="field">
            <div class="label">E-mail</div>
            <div class="value"><a href="mailto:{{ $email }}">{{ $email }}</a></div>
        </div>
        @endif

        @if ($siteWeb)
        <div class="field">
            <div class="label">Site web</div>
            <div class="value"><a href="{{ $siteWeb }}">{{ $siteWeb }}</a></div>
        </div>
        @endif

        {{-- Mesures --}}
        @if (!empty($mesures))
        <div class="field">
            <div class="label">Mesures</div>
            <div class="value">
                @if (isset($mesures['ph'])) pH : {{ $mesures['ph'] }}<br>@endif
                @if (isset($mesures['chlore'])) Chlore libre : {{ $mesures['chlore'] }} mg/L<br>@endif
                @if (isset($mesures['alcalinite'])) TAC : {{ $mesures['alcalinite'] }} mg/L<br>@endif
                @if (isset($mesures['stabilisant'])) Stabilisant : {{ $mesures['stabilisant'] }} mg/L<br>@endif
                @if (!empty($mesures['sel']) && isset($mesures['selPpm'])) Sel : {{ $mesures['selPpm'] }} ppm<br>@endif
                @if (isset($mesures['th'])) TH : {{ $mesures['th'] }} mg/L<br>@endif
            </div>
        </div>
        @endif

        {{-- Actions tentées --}}
        @if (!empty($triedActions))
        <div class="field">
            <div class="label">Déjà tenté sans succès</div>
            <div class="value">
                @foreach ($triedActions as $action)
                    <span class="tag">{{ $action }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Résumé diagnostic --}}
        <div class="field">
            <div class="label">Résumé du diagnostic</div>
            <div class="value message-box">{{ $summary }}</div>
        </div>

        @if ($diagId)
        <div class="field">
            <div class="label">ID Diagnostic</div>
            <div class="value" style="color: #888; font-size: 12px;">#{{ $diagId }}</div>
        </div>
        @endif

    </div>
    <hr class="divider">
    <div class="footer">
        @if ($email)
            Répondre directement à cet email pour contacter {{ $prenom }}.
        @else
            Aucun email fourni — contacter par WhatsApp.
        @endif
    </div>
</div>
</body>
</html>
