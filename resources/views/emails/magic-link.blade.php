<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Votre lien de connexion — Dlo Azur Piscines</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Inter', sans-serif; background: #fdfcf9; color: #1a2c40; padding: 24px; margin: 0;">
    <div style="max-width: 480px; margin: 0 auto; background: white; border-radius: 16px; padding: 32px; box-shadow: 0 2px 8px rgba(26,44,64,0.06);">

        <!-- Header -->
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 4C16 4 6 13 6 20C6 25.5228 10.4772 30 16 30C21.5228 30 26 25.5228 26 20C26 13 16 4 16 4Z" fill="#0080ff"/>
            </svg>
            <span style="font-size: 18px; font-weight: 700; color: #154c79;">Dlo Azur Piscines</span>
        </div>

        <!-- Salutation -->
        <p style="font-size: 16px; margin: 0 0 16px;">Bonjour {{ $clientName }},</p>

        <!-- Corps -->
        <p style="font-size: 15px; color: #4a5568; line-height: 1.6; margin: 0 0 24px;">
            Voici votre lien de connexion à votre espace personnel Dlo Azur Piscines.
            Vous y retrouverez l'historique de vos passages d'entretien.
        </p>

        <!-- CTA -->
        <div style="text-align: center; margin: 24px 0;">
            <a href="{{ $magicUrl }}"
               style="display: inline-block; background: #0080ff; color: white; padding: 14px 32px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 16px;">
                Accéder à mon espace
            </a>
        </div>

        <!-- Note sécurité -->
        <p style="font-size: 13px; color: #718096; line-height: 1.5; margin: 24px 0 0; padding: 12px 16px; background: #f7fafc; border-radius: 8px; border-left: 3px solid #e2e8f0;">
            Ce lien est valable <strong>48 heures</strong> et utilisable jusqu'à 3 fois.<br>
            Si vous n'avez pas demandé cet email, ignorez-le.
        </p>

        <!-- Signature -->
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
            <p style="font-size: 14px; color: #4a5568; margin: 0;">
                À bientôt sur le bassin,<br>
                <strong>Pierre ADAM</strong> · Dlo Azur Piscines · 0696 94 00 54
            </p>
        </div>

        <!-- Footer RGPD -->
        <p style="font-size: 11px; color: #a0aec0; text-align: center; margin: 24px 0 0;">
            Données hébergées en Europe · Confidentialité
        </p>
    </div>
</body>
</html>
