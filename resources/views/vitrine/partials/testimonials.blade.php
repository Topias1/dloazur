{{--
    Testimonials / Google Reviews section — Plan 04 supplies the Livewire component.
    The class_exists guard ensures this partial degrades gracefully in deployments
    where Plan 04 hasn't run yet (cross-plan seam D-28 amended ↔ Plan 03).
--}}
@if(class_exists(\App\Livewire\GoogleReviews::class))
    <livewire:google-reviews />
@endif
