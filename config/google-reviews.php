<?php

return [
    /*
     * Google Places API key — store only in Laravel Cloud Secrets (T-4-11).
     * Never commit a real key to the repo.
     */
    'api_key' => env('GOOGLE_PLACES_API_KEY'),

    /*
     * Google Places ID for Dlo Azur Piscines.
     * Find via: https://developers.google.com/maps/documentation/javascript/place-id
     */
    'place_id' => env('GOOGLE_PLACE_ID'),

    /*
     * Minimum star rating to display in the home component (D-28 amended).
     * Reviews below this threshold are stored but filtered from the UI.
     */
    'min_rating' => (int) env('GOOGLE_REVIEWS_MIN_RATING', 4),

    /*
     * Maximum number of reviews displayed on the home page.
     */
    'home_limit' => 5,

    /*
     * Google Business Profile URL for the "Voir tous les avis" link.
     */
    'business_url' => env('GOOGLE_BUSINESS_URL', 'https://g.page/dlo-azur-piscines'),

    /*
     * Auto-derives from whether both api_key AND place_id are set.
     * Set to false to fully disable the feature (e.g., before onboarding).
     */
    'enabled' => filled(env('GOOGLE_PLACES_API_KEY')) && filled(env('GOOGLE_PLACE_ID')),
];
