<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tarif indicatif — D-32
    |--------------------------------------------------------------------------
    |
    | L'admin met à jour PRICING_PASSAGE_STARTING dans les env vars Laravel Cloud
    | sans toucher au code. Valeur par défaut : 80€ (placeholder à confirmer).
    |
    */
    'passage_starting' => env('PRICING_PASSAGE_STARTING', 80),
    'currency'         => '€',
    'disclaimer'       => 'selon volume, accès et traitement',
];
