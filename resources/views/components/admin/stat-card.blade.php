{{--
    Stat card component for admin dashboard (D-19, Plan 02-03).
    Props: $label (string), $value (string|int, default '—'), $state (string, default 'default').

    States (UI-SPEC §Dashboard admin Stat cards — Règle ambre, Critical Flag #5 PATTERNS.md):
    - 'default' → text-ink-950 (normal)
    - 'offline'  → text-[oklch(0.5_0.11_72)] AMBRE (À synchroniser — jamais text-danger)
    - 'warn'     → text-danger ROUGE (eau hors plage — jamais oklch pour warn)
    Ne JAMAIS inverser offline/warn.
--}}
@props(['label', 'value' => '—', 'state' => 'default'])

<div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-5">
    <p class="text-sm text-ink-500">{{ $label }}</p>
    <p @class([
        'mt-1 font-display font-semibold text-3xl tabular-nums',
        'text-ink-950'              => $state === 'default',
        'text-[oklch(0.5_0.11_72)]' => $state === 'offline',
        'text-danger'               => $state === 'warn',
    ])>
        {{ $value }}
    </p>
</div>
