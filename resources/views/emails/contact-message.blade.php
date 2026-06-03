<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Nouveau message — Dlo Azur Piscines</title>
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
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Nouveau message reçu</h1>
        <p>Formulaire de contact — Dlo Azur Piscines</p>
    </div>
    <div class="body">
        <div class="field">
            <div class="label">Prénom</div>
            <div class="value">{{ $firstname }}</div>
        </div>
        <div class="field">
            <div class="label">Nom</div>
            <div class="value">{{ $lastname }}</div>
        </div>
        <div class="field">
            <div class="label">Téléphone</div>
            <div class="value"><a href="tel:{{ $phone }}">{{ $phone }}</a></div>
        </div>
        <div class="field">
            <div class="label">E-mail</div>
            <div class="value"><a href="mailto:{{ $email }}">{{ $email }}</a></div>
        </div>
        <div class="field">
            <div class="label">Message</div>
            <div class="value message-box">{{ $message }}</div>
        </div>
    </div>
    <hr class="divider">
    <div class="footer">
        Répondre directement à cet email pour contacter {{ trim($firstname.' '.$lastname) }}.
    </div>
</div>
</body>
</html>
