<div wire:key="google-reviews">
@if ($hidden)
    {{-- Auto-mask: section is invisible when config is disabled or table is empty (D-28 amended) --}}
@else
    <section class="py-20 bg-white" aria-label="Avis clients Google">
        <div class="max-w-5xl mx-auto px-5">
            {{-- Section header --}}
            <h2 class="font-display font-bold text-3xl text-ink-950 text-center mb-2">
                Ce qu'ils disent de nous
            </h2>

            {{-- Average summary --}}
            <div class="flex items-baseline justify-center gap-3 mb-10">
                <span class="text-4xl font-display font-bold text-ink-950">
                    {{ number_format($avg, 1, ',', '\u{202F}') }}
                </span>
                <span class="text-yellow-400 text-2xl" aria-label="{{ $avg }} étoiles sur 5">★★★★★</span>
                <span class="text-sm text-ink-500">({{ $total }} avis Google)</span>
            </div>

            {{-- Reviews grid --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($reviews as $review)
                <div class="bg-sand-50 rounded-2xl p-5 flex flex-col gap-3 shadow-sm">
                    {{-- Reviewer identity --}}
                    <div class="flex items-center gap-3">
                        @if ($review->profile_photo_url)
                        <img
                            src="{{ $review->profile_photo_url }}"
                            alt="{{ $review->author_name }}"
                            width="40"
                            height="40"
                            class="rounded-full w-10 h-10 object-cover"
                            loading="lazy"
                            referrerpolicy="no-referrer"
                        >
                        @else
                        <div class="w-10 h-10 rounded-full bg-azure-100 flex items-center justify-center text-azure-600 font-bold text-sm flex-shrink-0">
                            {{ mb_strtoupper(mb_substr($review->author_name, 0, 1)) }}
                        </div>
                        @endif
                        <div>
                            <p class="font-semibold text-sm text-ink-900 leading-tight">{{ $review->author_name }}</p>
                            <p class="text-xs text-ink-400">{{ $review->relative_time_description }}</p>
                        </div>
                    </div>

                    {{-- Star rating --}}
                    <div class="text-yellow-400 text-sm" aria-label="{{ $review->rating }} étoiles sur 5">
                        @for ($i = 0; $i < $review->rating; $i++)★@endfor
                    </div>

                    {{-- Review text --}}
                    <p class="text-sm text-ink-700 leading-relaxed line-clamp-4">
                        {{ $review->comment }}
                    </p>
                </div>
                @endforeach
            </div>

            {{-- Link to Google Business profile --}}
            <div class="mt-8 text-center">
                <a
                    href="{{ $businessUrl }}"
                    rel="noopener"
                    target="_blank"
                    class="inline-flex items-center gap-2 text-azure-500 hover:text-azure-700 hover:underline text-sm font-medium transition-colors"
                >
                    Voir tous les avis sur Google →
                </a>
            </div>
        </div>
    </section>
@endif
</div>
