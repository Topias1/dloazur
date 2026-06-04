<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SearchIndexing — garde d'indexation des environnements de test.
 *
 * Tant que config('app.indexable') est false (fail-closed par défaut), pose
 * sur CHAQUE réponse l'en-tête X-Robots-Tag interdisant l'indexation moteur
 * (noindex,nofollow) ET l'aspiration par les crawlers IA (noai,noimageai).
 *
 * X-Robots-Tag (en-tête HTTP) est plus fiable que la balise <meta robots> :
 * il s'applique à toutes les réponses — y compris assets et pages déjà connues
 * de Google — et n'exige aucune modification des vues. Couplé au robots.txt
 * dynamique (route /robots.txt), il couvre les deux sites de test.
 *
 * Au lancement public réel : SITE_INDEXABLE=true sur l'environnement de prod
 * → l'en-tête disparaît, le site devient indexable.
 */
class SearchIndexing
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('app.indexable')) {
            $response->headers->set(
                'X-Robots-Tag',
                'noindex, nofollow, noai, noimageai'
            );
        }

        return $response;
    }
}
