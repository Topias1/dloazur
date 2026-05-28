<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Contact Form Configuration (SITE-05 — D-13/D-14/D-15/D-16)
    |--------------------------------------------------------------------------
    */

    /*
     * Email recipient for contact form submissions.
     * Override via CONTACT_RECIPIENT env var.
     */
    'recipient' => env('CONTACT_RECIPIENT', 'contact@dloazurpiscines.com'),

    /*
     * WhatsApp fallback — visible below the contact form per D-16.
     */
    'whatsapp_number' => env('WHATSAPP_NUMBER', '596696940054'),
    'whatsapp_url'    => 'https://wa.me/596696940054',

    /*
     * Rate limiting — 5 submissions per 60 seconds per IP (D-14).
     * This maps to $this->rateLimit(attempts, decay_seconds) in ContactForm.php.
     */
    'rate_limit' => [
        'attempts'       => 5,
        'decay_seconds'  => 60,
    ],

];
