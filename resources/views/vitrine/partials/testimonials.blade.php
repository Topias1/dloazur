{{-- Testimonials — 1:1 from mockups/v1/vitrine.html --}}
{{-- D-28 amended: Plan 04 supplies the GoogleReviews Livewire component --}}
<section class="bg-white border-t border-navy-900/8">
    <div class="mx-auto max-w-content px-5 sm:px-8 py-18 sm:py-24">
        <div class="flex flex-col items-center text-center mb-12">
            <div class="flex items-center gap-1 text-sun-500" aria-hidden="true">
                <x-icon.star :size="22" />
                <x-icon.star :size="22" />
                <x-icon.star :size="22" />
                <x-icon.star :size="22" />
                <x-icon.star :size="22" />
            </div>
            <p class="mt-3 text-ink-700">Ce que disent les propriétaires accompagnés par Dlo Azur</p>
        </div>
        <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <blockquote class="rounded-2xl bg-sand-50 ring-1 ring-sand-200 p-7">
                <p class="text-lg leading-relaxed text-ink-900">« Pierre est ponctuel et sérieux. Notre piscine n'a jamais été aussi claire, et on reçoit les photos après chaque passage. »</p>
                <footer class="mt-4 text-sm font-semibold text-ink-500">Sandrine M., Les Trois-Îlets</footer>
            </blockquote>
            <blockquote class="rounded-2xl bg-sand-50 ring-1 ring-sand-200 p-7">
                <p class="text-lg leading-relaxed text-ink-900">« On gère plusieurs villas en location. Avoir un seul pisciniste fiable, avec un suivi photo, ça nous change la vie. »</p>
                <footer class="mt-4 text-sm font-semibold text-ink-500">Conciergerie du Sud, Sainte-Anne</footer>
            </blockquote>
        </div>

        {{-- D-28 amended: Plan 04 supplies the GoogleReviews Livewire component --}}
        @if(class_exists(\App\Livewire\GoogleReviews::class))
            <div class="mt-10">
                <livewire:google-reviews />
            </div>
        @endif
    </div>
</section>
