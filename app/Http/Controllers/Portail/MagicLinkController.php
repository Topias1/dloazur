<?php

namespace App\Http\Controllers\Portail;

use App\Http\Controllers\Controller;
use App\Http\Requests\MagicLinkRequest;
use App\Mail\MagicLinkMail;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;

class MagicLinkController extends Controller
{
    /**
     * GET /auth/magic — formulaire de demande de lien.
     */
    public function requestForm(): View
    {
        return view('portail.magic-link-request');
    }

    /**
     * POST /auth/magic — crée et envoie le magic link.
     *
     * Anti-énumération (D-52) : message générique identique que l'email existe ou non.
     * Sleep aléatoire 1-3s pour uniformiser le temps de réponse (OWASP Forgot Password Cheatsheet).
     */
    public function send(MagicLinkRequest $request): RedirectResponse
    {
        // D-52 : sleep aléatoire pour uniformiser le timing de réponse
        usleep(random_int(1_000_000, 3_000_000));

        $client = Client::where('email', $request->validated('email'))->first();

        if ($client) {
            try {
                // D-54 : lifetime=48h (2880 min), numMaxVisits=3
                $action = (new LoginAction($client))->guard('clients');
                $magicLink = MagicLink::create($action, 2880, 3);

                // D-50 : URL custom vers NOTRE page de confirmation intermédiaire
                // (et NON vers /magiclink/{id}:{token} auto-généré par le package)
                // Le token complet pour getValidMagicLinkByToken() est "{id}:{secret}"
                $mlToken = $magicLink->id . ':' . $magicLink->token;
                $customUrl = route('portail.magic-link.confirm-view', ['ml' => $mlToken]);

                Mail::to($client->email)->send(new MagicLinkMail(
                    magicUrl: $customUrl,
                    clientName: $client->name,
                ));
            } catch (\Throwable $e) {
                // On NE révèle PAS l'échec — message générique (anti-énumération D-52)
                Log::error('Magic link send failed', [
                    'client_id' => $client->id,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        // Message générique identique pour email existant ET inexistant (D-52)
        return back()->with(
            'status',
            'Si cet email correspond à un compte, un lien de connexion a été envoyé. Vérifiez votre boîte de réception.'
        );
    }

    /**
     * GET /auth/confirm?ml={token} — page de confirmation intermédiaire.
     *
     * D-50 CRITIQUE : AUCUNE validation, AUCUN side-effect ici.
     * Microsoft 365 SafeLinks fait un GET pré-scan avant de rediriger l'utilisateur.
     * Si on consommait le token ici, le client arriverait sur "lien expiré".
     * La page retourne UNIQUEMENT du HTML statique avec un <form method="POST">.
     */
    public function confirmView(Request $request): View
    {
        $token = (string) $request->query('ml', '');

        // Aucune validation du token — la page est purement présentationnelle.
        return view('portail.confirm', ['token' => $token]);
    }

    /**
     * POST /auth/confirm — consomme le token et connecte le client.
     *
     * Seul ce POST consomme le token (D-50).
     */
    public function confirm(Request $request): RedirectResponse
    {
        $token = (string) $request->input('ml', '');

        if ($token === '') {
            return redirect()->route('portail.magic-link.request')
                ->withErrors(['ml' => 'Lien de connexion manquant. Demandez un nouveau lien.']);
        }

        // getValidMagicLinkByToken vérifie expiration + numMaxVisits
        $magicLink = MagicLink::getValidMagicLinkByToken($token);

        if (! $magicLink) {
            return redirect()->route('portail.magic-link.request')
                ->withErrors(['ml' => "Ce lien de connexion n'est plus valide. Il est peut-être expiré ou déjà utilisé. Demandez un nouveau lien."]);
        }

        try {
            // L'action LoginAction::run() appelle loginUsingId() sur le guard 'clients'.
            // Le retour de run() est une RedirectResponse (celle configurée dans LoginAction).
            // On l'ignore — on fait notre propre redirect vers portail.passages.
            $magicLink->action->run();
        } catch (\Throwable $e) {
            Log::error('Magic link consumption failed', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('portail.magic-link.request')
                ->withErrors(['ml' => 'Une erreur est survenue. Réessayez.']);
        }

        // Incrémenter num_visits manuellement.
        // Le package incrémente normalement via son middleware sur /magiclink/{id}:{token}.
        // Comme on bypass cette route, on utilise la méthode visited() du modèle.
        $magicLink->visited();

        // Régénérer la session pour la sécurité post-authentification
        $request->session()->regenerate();

        return redirect()->route('portail.passages');
    }

    /**
     * POST /portail/logout — déconnexion du client.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('clients')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portail.magic-link.request');
    }
}
