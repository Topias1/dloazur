{{--
    Stat card component for admin dashboard (D-19).
    Props: $label (string), $value (string, default '—'), $state (string, default 'default').
    Phase 1: all values are '—' (em-dash) — no live data yet.
    Phase 2 will pass real values (clients count, passages this week, etc.).
--}}
@props(['label', 'value' => '—', 'state' => 'default'])

<div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-5">
    <p class="text-sm text-ink-500">{{ $label }}</p>
    <p class="mt-1 font-display font-semibold text-3xl {{ $state === 'warn' ? 'text-danger' : 'text-ink-950' }}">
        {{ $value }}
    </p>
</div>
