<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CacheHeaders — Plan 01-06
 *
 * Ajoute les en-têtes HTTP Cache-Control appropriés selon le profil de la route.
 * RESEARCH Pitfall 11 : on n'installe pas spatie/laravel-responsecache pour du
 * trafic solo-opérateur + scale-to-zero ; un header public max-age=300 suffit.
 *
 * Fonctionnement en deux temps pour résister à Livewire\DisableBackButtonCacheMiddleware :
 *
 *   1. Le middleware de ROUTE (alias cache.headers:vitrine) marque la requête via un
 *      attribut PHP (cache_profile), mais ne touche pas encore la réponse.
 *
 *   2. appendToGroup('web') dans bootstrap/app.php enregistre ce même middleware
 *      EN FIN du groupe web — il s'exécute donc en PREMIER dans la direction
 *      sortante (réponse) et peut écraser l'en-tête Livewire sans risque.
 *
 * Profils :
 *   vitrine  — Cache-Control: public, max-age=300, must-revalidate  (5 min)
 *   sitemap  — Cache-Control: public, max-age=3600, must-revalidate (1 h)
 *   health   — Cache-Control: no-cache, no-store, must-revalidate   (jamais mis en cache)
 */
class CacheHeaders
{
    /** Clé de l'attribut de requête porté par le route middleware. */
    public const ATTR = '_cache_profile';

    public function handle(Request $request, Closure $next, string $profile = ''): Response
    {
        if ($profile !== '') {
            // --- Mode route middleware ---
            // Marque la requête avec le profil demandé.
            // N'écrit pas encore d'en-tête (Livewire pourrait l'écraser).
            $request->attributes->set(self::ATTR, $profile);
        }

        $response = $next($request);

        // --- Mode outbound (route middleware OU appended web group) ---
        // Résoudre le profil réel : soit depuis l'argument (appended global),
        // soit depuis l'attribut de requête (route middleware après Livewire).
        $resolvedProfile = $profile !== ''
            ? $profile
            : ($request->attributes->get(self::ATTR, ''));

        if ($resolvedProfile === '') {
            return $response;
        }

        // N'applique les directives de cache qu'aux requêtes GET/HEAD
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $response;
        }

        $rules = [
            'vitrine' => 'public, max-age=300, must-revalidate',
            'sitemap' => 'public, max-age=3600, must-revalidate',
            'health'  => 'no-cache, no-store, must-revalidate',
        ];

        $value = $rules[$resolvedProfile] ?? $rules['vitrine'];

        $response->headers->set('Cache-Control', $value);

        if ($resolvedProfile === 'vitrine' || $resolvedProfile === 'sitemap') {
            $response->headers->set('Vary', 'Accept-Encoding');
        }

        return $response;
    }
}
