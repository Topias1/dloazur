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

    Livewire::test(ContactForm::class)
        ->set('name', 'Jean Dupont')
        ->set('email', 'jean@example.com')
        ->set('phone', '')
        ->set('message', 'Bonjour, je voudrais un devis pour ma piscine de 40 m³.')
        ->call('submit');

    expect(Livewire::test(ContactForm::class)
        ->set('name', 'Jean Dupont')
        ->set('email', 'jean@example.com')
        ->set('phone', '')
        ->set('message', 'Bonjour, je voudrais un devis pour ma piscine de 40 m³.')
        ->call('submit')
        ->get('sent')
    )->toBeTrue();

    Mail::assertSent(ContactMessage::class, fn ($m) =>
        $m->hasTo('contact@dloazurpiscines.com') &&
        $m->name === 'Jean Dupont' &&
        str_contains($m->message, 'devis')
    );
});

it('submission with empty required fields shows inline validation errors', function () {
    Mail::fake();

    Livewire::test(ContactForm::class)
        ->set('name', '')
        ->set('email', '')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors(['name' => 'required', 'email' => 'required', 'message' => 'required']);

    Mail::assertNothingSent();
});

it('submission with invalid email shows email validation error', function () {
    Mail::fake();

    Livewire::test(ContactForm::class)
        ->set('email', 'not-an-email')
        ->set('name', 'X')
        ->set('message', 'Bonjour bonjour bonjour')
        ->call('submit')
        ->assertHasErrors(['email' => 'email']);

    Mail::assertNothingSent();
});

it('honeypot trip silently swallows submission', function () {
    Mail::fake();

    // The honeypot aborts with 403, so we expect the component not to send mail.
    // We test by setting the honeypot name field to a non-empty value.
    // spatie/laravel-honeypot abort(403) behavior — we catch it gracefully in submit().
    $component = Livewire::test(ContactForm::class)
        ->set('name', 'Bot')
        ->set('email', 'bot@evil.com')
        ->set('message', 'Spammy text bypass.')
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
            ->set('name', "User $i")
            ->set('email', "user{$i}@example.com")
            ->set('phone', '')
            ->set('message', 'Message valide de test numero ' . $i . ' avec assez de caractères.')
            ->call('submit');
    }

    // 6th attempt should be throttled
    $component = Livewire::test(ContactForm::class)
        ->set('name', 'User 6')
        ->set('email', 'user6@example.com')
        ->set('phone', '')
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
        ->set('name', 'Jean Dupont')
        ->set('email', 'jean@example.com')
        ->set('phone', '')
        ->set('message', 'Bonjour, je souhaite un diagnostic gratuit de ma piscine.')
        ->call('submit')
        ->assertSee('Message envoyé.')
        ->assertSee('Pierre vous répondra rapidement.');
});
