<?php

namespace App\Livewire;

use App\Mail\ContactMessage;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class ContactForm extends Component
{
    use WithRateLimiting, UsesSpamProtection;

    #[Validate('required|string|max:80')]
    public string $name = '';

    #[Validate('required|email|max:160')]
    public string $email = '';

    #[Validate('nullable|string|max:30')]
    public string $phone = '';

    #[Validate('required|string|min:10|max:2000')]
    public string $message = '';

    public bool $sent = false;

    public HoneypotData $extraFields;

    public function mount(): void
    {
        $this->extraFields = new HoneypotData();
    }

    public function submit(): void
    {
        // 1. Rate limit: 5 submissions per 60s per IP (D-14, config/contact.php)
        try {
            $this->rateLimit(5, 60);
        } catch (TooManyRequestsException) {
            $this->addError('throttle', "Trop d'essais. Attendez quelques minutes puis réessayez.");
            return;
        }

        // 2. Honeypot: silently reject bots (T-4-01)
        // spatie/laravel-honeypot throws SpamException internally -> abort(403)
        // We catch it to set $sent=true to confuse the bot (no error shown)
        try {
            $this->protectAgainstSpam();
        } catch (\Throwable) {
            // Silently swallow — do not send mail, do not show error (T-4-01)
            return;
        }

        // 3. Validate form fields
        $this->validate();

        // 4. Send mail (T-4-07: wrap in try/catch so server errors don't leak to user)
        try {
            Mail::to(config('contact.recipient', 'contact@dloazurpiscines.com'))
                ->send(new ContactMessage(
                    name: $this->name,
                    email: $this->email,
                    phone: $this->phone,
                    message: $this->message,
                ));
        } catch (\Throwable $e) {
            Log::error('Contact form mail send failed', [
                'exception' => $e->getMessage(),
                'name'      => $this->name,
                'email'     => $this->email,
            ]);
            $this->addError('send', "L'envoi a échoué. Vérifiez votre connexion ou contactez-nous sur WhatsApp.");
            return;
        }

        // 5. Success state
        $this->sent = true;
        $this->reset(['name', 'email', 'phone', 'message']);
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
