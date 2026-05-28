<?php

namespace App\Livewire;

use App\Services\GoogleReviewsService;
use Livewire\Component;

class GoogleReviews extends Component
{
    public bool $hidden = false;

    public function render(GoogleReviewsService $svc)
    {
        if (! config('google-reviews.enabled', false) || $svc->totalCount() === 0) {
            $this->hidden = true;

            return view('livewire.google-reviews', [
                'hidden'      => true,
                'reviews'     => collect(),
                'avg'         => null,
                'total'       => 0,
                'businessUrl' => config('google-reviews.business_url'),
            ]);
        }

        $minRating  = (int) config('google-reviews.min_rating', 4);
        $homeLimit  = (int) config('google-reviews.home_limit', 5);
        $reviews    = $svc->latestFiltered($homeLimit, $minRating);
        $avg        = $svc->averageRating();
        $total      = $svc->totalCount();
        $businessUrl = config('google-reviews.business_url');

        $this->hidden = false;

        return view('livewire.google-reviews', [
            'reviews'     => $reviews,
            'avg'         => $avg,
            'total'       => $total,
            'businessUrl' => $businessUrl,
            'hidden'      => false,
        ]);
    }
}
