<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * PierreSeeder — Production-safe idempotent seeder for the operator account.
 *
 * Per D-09: this seeder is NOT env-gated (unlike DatabaseSeeder).
 * It is designed to run from the Laravel Cloud deploy hook:
 *   php artisan db:seed --class=PierreSeeder --force
 *
 * Running it twice is safe: updateOrCreate keyed on email yields 1 row.
 *
 * Env vars read at runtime:
 *   OPERATOR_EMAIL            (default: pierre@dloazurpiscines.com)
 *   OPERATOR_NAME             (default: Pierre ADAM)
 *   OPERATOR_INITIAL_PASSWORD (default: change-me-now)
 *
 * Note: email_verified_at is set via forceFill() to bypass the $fillable guard
 * (only 'name', 'email', 'password' are in the User fillable list).
 * Pierre is pre-verified by definition — no email verification needed (CONTEXT.md <deferred>).
 */
class PierreSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => env('OPERATOR_EMAIL', 'pierre@dloazurpiscines.com')],
            [
                'name'     => env('OPERATOR_NAME', 'Pierre ADAM'),
                'password' => Hash::make(env('OPERATOR_INITIAL_PASSWORD', 'change-me-now')),
            ]
        );

        // Set email_verified_at via forceFill (bypasses $fillable guard).
        // Pierre is seeded pre-verified: no email verification flow needed.
        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }
}
