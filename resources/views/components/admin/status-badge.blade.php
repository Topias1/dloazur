{{--
    Admin status badge component (Phase 6, Plan 06-03).
    Props: $status (string) — 'published' | 'draft'

    Chip: rounded-full, Inter 600, inline-flex.
    Publié  → success/10 bg, dark-green text (oklch 0.42 0.12 155, WCAG AA ≥4.5:1 on success/10)
    Brouillon → sand-100 bg, ink-500 text, ink-400 dot

    Labels exactly "Publié" and "Brouillon" per UI-SPEC copywriting table.
--}}
@props(['status'])

@php $isPublished = $status === 'published'; @endphp

<span
    @class([
        'rounded-full px-2.5 py-0.5 text-xs font-semibold inline-flex items-center gap-1.5',
        'ring-1'               => true,
        // Publié
        'bg-success/10 ring-success/30' => $isPublished,
        // Brouillon
        'bg-sand-100 ring-sand-200 text-ink-500' => ! $isPublished,
    ])
    @style([
        // Dark green text for Publié — oklch(0.42 0.12 155) per UI-SPEC (WCAG AA ≥4.5:1)
        'color: oklch(0.42 0.12 155)' => $isPublished,
    ])
>
    {{-- Dot --}}
    <span
        @class([
            'inline-block h-1.5 w-1.5 rounded-full shrink-0',
            'bg-success' => $isPublished,
            'bg-ink-400' => ! $isPublished,
        ])
        aria-hidden="true"
    ></span>

    {{ $isPublished ? 'Publié' : 'Brouillon' }}
</span>
