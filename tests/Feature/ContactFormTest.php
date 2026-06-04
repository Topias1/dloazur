<?php

use App\Livewire\ContactForm;
use App\Mail\ContactMessage;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

it('contact page renders the Livewire form', function () {
    $response = $this->get('/contact');

    $response->assertStatus(200);
    $response->assertSee('Envoyer mon message');
});

it('valid submission sends the mail', function () {
    Mail::fake();

    expect(Livewire::test(ContactForm::class)
        ->set('firstname', 'Jean')
        ->set('lastname', 'Dupont')
        ->set('email', 'jean@example.com')
        ->set('phone', '0696940054')
        ->set('message', 'Bonjour, je voudrais un devis pour ma piscine de 40 m³.')
        ->call('submit')
        ->get('sent')
    )->toBeTrue();

    Mail::assertSent(ContactMessage::class, fn ($m) =>
        $m->hasTo('contact@dloazurpiscines.com') &&
        $m->firstname === 'Jean' &&
        $m->lastname === 'Dupont' &&
        $m->phone === '0696940054' &&
        str_contains($m->message, 'devis')
    );
});

it('submission with empty required fields shows inline validation errors', function () {
    Mail::fake();

    Livewire::test(ContactForm::class)
        ->set('firstname', '')
        ->set('lastname', '')
        ->set('email', '')
        ->set('phone', '')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors([
            'firstname' => 'required',
            'lastname'  => 'required',
            'email'     => 'required',
            'phone'     => 'required',
            'message'   => 'required',
        ]);

    Mail::assertNothingSent();
});

it('submission with invalid email shows email validation error', function () {
    Mail::fake();

    Livewire::test(ContactForm::class)
        ->set('email', 'not-an-email')
        ->set('firstname', 'Jean')
        ->set('lastname', 'Dupont')
        ->set('phone', '0696940054')
        ->set('message', 'Bonjour bonjour bonjour')
        ->call('submit')
        ->assertHasErrors(['email' => 'email']);

    Mail::assertNothingSent();
});

it('honeypot trip silently swallows submission', function () {
    Mail::fake();

    // With HONEYPOT_RANDOMIZE=false the honeypot field stays 'my_name'.
    // Setting it to a non-empty value triggers SpamException -> abort(403)
    // which ContactForm::submit() catches and swallows silently (no mail sent).
    Livewire::test(ContactForm::class)
        ->set('firstname', 'Bot')
        ->set('lastname', 'Net')
        ->set('email', 'bot@evil.com')
        ->set('phone', '0696940054')
        ->set('message', 'Spammy text bypass.')
        ->set('extraFields.my_name', 'I am a bot')
        ->call('submit');

    Mail::assertNothingSent();
});

it('rate limit triggers after 5 submissions in 60s', function () {
    Mail::fake();

    // Clear rate limiter before the test
    $key = 'livewire-rate-limiter:' . sha1(ContactForm::class . '|submit|' . request()->ip());
    RateLimiter::clear($key);

    // 5 successful submissions
    for ($i = 1; $i <= 5; $i++) {
        Livewire::test(ContactForm::class)
            ->set('firstname', "User")
            ->set('lastname', "Number $i")
            ->set('email', "user{$i}@example.com")
            ->set('phone', '0696940054')
            ->set('message', 'Message valide de test numero ' . $i . ' avec assez de caractères.')
            ->call('submit');
    }

    // 6th attempt should be throttled
    $component = Livewire::test(ContactForm::class)
        ->set('firstname', 'User')
        ->set('lastname', 'Number 6')
        ->set('email', 'user6@example.com')
        ->set('phone', '0696940054')
        ->set('message', 'Message valide de test numero 6 avec assez de caractères.')
        ->call('submit')
        ->assertHasErrors(['throttle'])
        ->assertSee("Trop d'essais. Attendez quelques minutes puis réessayez.");

    expect($component->get('sent'))->toBeFalse();

    // Clean up
    RateLimiter::clear($key);
});

it('WhatsApp fallback link is rendered in the form view', function () {
    $response = $this->get('/contact');

    $response->assertStatus(200);
    $response->assertSee('wa.me/596696940054', false);
    $response->assertSee('directement sur WhatsApp');
});

it('success state replaces the form on $sent=true', function () {
    Mail::fake();

    Livewire::test(ContactForm::class)
        ->set('firstname', 'Jean')
        ->set('lastname', 'Dupont')
        ->set('email', 'jean@example.com')
        ->set('phone', '0696940054')
        ->set('message', 'Bonjour, je souhaite un diagnostic gratuit de ma piscine.')
        ->call('submit')
        ->assertSee('Message envoyé.')
        ->assertSee('Nous vous répondrons rapidement.');
});
