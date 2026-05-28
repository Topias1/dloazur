<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ServiceWorkerHeaders — Plan 02-01
 *
 * Ajoute le header Service-Worker-Allowed: / sur /build/sw.js.
 *
 * Contexte D-60, Pitfall 2 : Laravel place le build Vite dans /public/build/.
 * Sans ce header, le Service Worker est scopé à /build/ et n'intercepte rien
 * sur /admin/*, /portail/*, etc. Le header étend le scope au site entier.
 *
 * Référence : vite-pwa-org.netlify.app/frameworks/laravel
 */
class ServiceWorkerHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('build/sw.js')) {
            $response->headers->set('Service-Worker-Allowed', '/');
            $response->headers->set('Cache-Control', 'no-cache');
        }

        return $response;
    }
}
