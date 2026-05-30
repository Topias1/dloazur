<?php

namespace App\Http\Controllers\Portail;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Passage;
use App\Models\Piscine;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * DemoLoginController — connexion démo DEV-ONLY pour /auth/magic.
 *
 * Gardée UNIQUEMENT par le flag config('app.demo_login') (env DEMO_LOGIN_ENABLED).
 * Sur Laravel Cloud chaque environnement tourne en APP_ENV=production : impossible
 * de garder par nom d'environnement. La vraie production ne définit jamais le flag,
 * donc abort_unless(...404) est la première ligne de chaque action publique.
 *
 * Les comptes démo sont provisionnés paresseusement et de façon idempotente
 * (firstOrCreate sur l'email + garde piscines()->doesntExist()) — aucun seeder ajouté.
 */
class DemoLoginController extends Controller
{
    /**
     * POST /auth/demo/client — provisionne + connecte le client démo (guard 'clients').
     */
    public function client(Request $request): RedirectResponse
    {
        abort_unless((bool) config('app.demo_login'), 404);

        $client = Client::firstOrCreate(
            ['email' => 'demo-client@dloazur.test'],
            [
                'uuid'    => (string) Str::uuid(),
                'name'    => 'Démo Client',
                'phone'   => '0696 00 00 00',
                'address' => 'Lagon démo, Martinique',
            ]
        );

        // Provisionnement exactement une fois : la garde doesntExist() empêche les doublons.
        if ($client->piscines()->doesntExist()) {
            $this->seedDemoData($client);
        }

        Auth::guard('clients')->login($client);
        $request->session()->regenerate();

        return redirect()->route('portail.passages');
    }

    /**
     * POST /auth/demo/admin — provisionne + connecte l'admin démo (guard 'web').
     */
    public function admin(Request $request): RedirectResponse
    {
        abort_unless((bool) config('app.demo_login'), 404);

        $user = User::firstOrCreate(
            ['email' => 'demo-admin@dloazur.test'],
            [
                'name'     => 'Pierre ADAM',
                'password' => Hash::make(Str::random(32)),
            ]
        );

        // email_verified_at + nom normalisé via forceFill. firstOrCreate ne met pas à jour
        // un compte démo déjà provisionné : on force "Pierre ADAM" pour que le tableau de
        // bord affiche "Bonjour Pierre" plutôt que "Bonjour Démo" pendant la démo.
        $patch = [];
        if ($user->email_verified_at === null) {
            $patch['email_verified_at'] = now();
        }
        if ($user->name !== 'Pierre ADAM') {
            $patch['name'] = 'Pierre ADAM';
        }
        if ($patch !== []) {
            $user->forceFill($patch)->save();
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    /**
     * Provisionne 1 piscine + 4 passages synchronisés pour le client démo.
     * Appelé une seule fois (garde doesntExist() dans client()).
     */
    private function seedDemoData(Client $client): void
    {
        $piscine = Piscine::create([
            'client_id'   => $client->id,
            'nom'         => 'Piscine principale',
            'volume_m3'   => 32,
            'type'        => 'enterrée',
            'filtration'  => 'sable',
            'traitement'  => 'chlore',
            'equipements' => ['Pompe à chaleur', 'Électrolyseur'],
            'notes'       => 'Piscine de démonstration.',
        ]);

        $passages = [
            [
                'weeks'        => 0,
                'ph_avant'     => 7.4,
                'ph_apres'     => 7.2,
                'chlore_libre' => 1.8,
                'tac'          => 110,
                'notes'        => 'Nettoyage filtre, contrôle pH.',
            ],
            [
                'weeks'        => 2,
                'ph_avant'     => 7.2,
                'ph_apres'     => 7.2,
                'chlore_libre' => 1.5,
                'tac'          => 95,
                'notes'        => 'Traitement choc préventif.',
            ],
            [
                'weeks'        => 4,
                'ph_avant'     => 7.1,
                'ph_apres'     => 7.2,
                'chlore_libre' => 1.3,
                'tac'          => 90,
                'notes'        => 'Brossage parois, contrôle skimmers.',
            ],
            [
                'weeks'        => 6,
                'ph_avant'     => 7.0,
                'ph_apres'     => 7.2,
                'chlore_libre' => 1.2,
                'tac'          => 80,
                'notes'        => 'Vérification niveau, équilibre de l\'eau.',
            ],
        ];

        foreach ($passages as $p) {
            $piscine->passages()->create([
                'client_id' => $client->id,
                // D-08 : client_uuid est la clé d'idempotence PAR passage (offline sync),
                // PAS l'UUID du client. Contrainte unique → un UUID frais par passage.
                'client_uuid'  => (string) Str::uuid(),
                'visited_at'   => now()->subWeeks($p['weeks']),
                'status'       => 'synced',
                'synced_at'    => now(),
                'ph_avant'     => $p['ph_avant'],
                'ph_apres'     => $p['ph_apres'],
                'chlore_libre' => $p['chlore_libre'],
                'tac'          => $p['tac'],
                'notes'        => $p['notes'],
            ]);
        }
    }
}
