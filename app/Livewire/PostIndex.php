<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PostIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $driver = DB::connection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        // Admin sees ALL posts (drafts + published) — do NOT apply scopePublished here.
        // scopePublished() is only for the public BlogRepository path (D-03).
        $posts = Post::query()
            ->when($this->search, fn ($q) =>
                $q->where('title', $likeOp, '%' . $this->search . '%')
            )
            ->orderByDesc('date')
            ->paginate(25);

        return view('livewire.post-index', compact('posts'));
    }
}
