<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * AdminSeeder — Production-safe idempotent seeder for the operator account.
 *
 * Per D-09: this seeder is NOT env-gated (unlike DatabaseSeeder).
 * It is designed to run from the Laravel Cloud deploy hook:
 *   php artisan db:seed --class=AdminSeeder --force
 *
 * Running it twice is safe: updateOrCreate keyed on email yields 1 row.
 *
 * Env vars read at runtime:
 *   OPERATOR_EMAIL            (default: admin@dloazurpiscines.com)
 *   OPERATOR_NAME             (default: Pierre)
 *   OPERATOR_INITIAL_PASSWORD (default: change-me-now)
 *
 * Note: email_verified_at is set via forceFill() to bypass the $fillable guard
 * (only 'name', 'email', 'password' are in the User fillable list).
 * The admin account is pre-verified by definition — no email verification needed (CONTEXT.md <deferred>).
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => env('OPERATOR_EMAIL', 'admin@dloazurpiscines.com')],
            [
                'name'     => env('OPERATOR_NAME', 'Pierre'),
                'password' => Hash::make(env('OPERATOR_INITIAL_PASSWORD', 'change-me-now')),
            ]
        );

        // Set email_verified_at via forceFill (bypasses $fillable guard).
        // The admin account is seeded pre-verified: no email verification flow needed.
        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }
}
