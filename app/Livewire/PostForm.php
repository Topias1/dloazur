<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * PostForm — blog admin create/edit write component (Plan 06-04, CONTENT-01).
 *
 * Mirrors ClientForm (validate / mount / submit + try-catch Log + addError('save')
 * + dispatch + redirect navigate) and adds the blog-specific behaviour:
 *  - EasyMDE Markdown body (synced via the post-editor.js Alpine factory into `body`)
 *  - cover upload via WithFileUploads → medialibrary → Scaleway S3 (D-02)
 *  - slug auto-generates from title while draft, LOCKED once published (D-04)
 *  - Cache::forget('blog.index') on submit so the public blog reflects the change
 */
class PostForm extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:160')]
    public string $title = '';

    #[Validate('nullable|string')]
    public string $slug = '';

    #[Validate('required|string')]
    public string $body = '';

    #[Validate('nullable|string|max:300')]
    public string $excerpt = '';

    #[Validate('nullable|string|max:16')]
    public string $status = 'draft';

    // TemporaryUploadedFile|null — validated before any S3 write (T-06-10).
    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:4096')]
    public $cover = null;

    public ?int $postId = null;

    public function mount(?int $postId = null): void
    {
        if ($postId) {
            $post = Post::findOrFail($postId);
            $this->postId  = $post->id;
            $this->title   = (string) $post->title;
            $this->slug    = (string) $post->slug;
            $this->body    = (string) $post->body;
            $this->excerpt = (string) $post->excerpt;
            $this->status  = (string) $post->status;
            // Do NOT hydrate cover — it's a fresh upload field, not the persisted media.
        }
    }

    public function submit(): void
    {
        $this->validate();

        try {
            $post = $this->postId ? Post::findOrFail($this->postId) : new Post();

            // D-04: slug locks once a post is published. When editing a post that
            // is ALREADY published, keep the persisted slug (preserve SEO); never
            // overwrite it from the (possibly tampered) $this->slug input.
            if ($post->exists && $post->status === 'published') {
                $slug = $post->slug;
            } else {
                $slug = $this->slug ?: Str::slug($this->title);
                $slug = $this->uniqueSlug($slug);
            }

            $post->fill([
                'title'   => $this->title,
                'slug'    => $slug,
                'body'    => $this->body,
                'excerpt' => $this->excerpt ?: null,
                'status'  => $this->status ?: 'draft',
                'author'  => $post->author ?: 'Pierre ADAM',
                // CR-01: stamp the publish date once on the first transition to
                // published, then keep it stable. Leaving it null lets the public
                // read path fall back to now() on every cache rebuild — silently
                // drifting datePublished / sitemap lastmod (breaks the 999.1 SEO acquis).
                'date'    => $post->date ?? ($this->status === 'published' ? now() : null),
            ]);
            $post->save();

            if ($this->cover) {
                // With LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3 the temp upload lives in
                // S3 (not on the serverless ephemeral fs), so getRealPath() would point
                // at a non-existent local path. Stage to s3 then addMediaFromDisk (D-02,
                // RESEARCH Pattern 2 / Open Question 1 RESOLVED).
                $tmpPath = $this->cover->store('livewire-tmp', 's3');
                $post->addMediaFromDisk($tmpPath, 's3')
                     ->usingFileName(Str::slug($this->title) . '.' . $this->cover->getClientOriginalExtension())
                     ->toMediaCollection('cover', 's3');
            }

            // Flush the public blog index cache so the change is visible immediately
            // (BlogRepository caches under 'blog.index').
            Cache::forget('blog.index');
        } catch (\Throwable $e) {
            Log::error('Post save failed', ['exception' => $e->getMessage()]);
            $this->addError('save', "L'enregistrement a échoué.");

            return;
        }

        $this->dispatch('post-saved');
        session()->flash('status', 'post-saved');
        $this->redirect(route('admin.blog.index'), navigate: true);
    }

    /**
     * Resolve slug collisions by appending a numeric suffix (-2, -3, …).
     * Excludes the current post so re-saving an edited post keeps its own slug.
     */
    private function uniqueSlug(string $slug): string
    {
        $base = $slug;
        $i = 1;

        while (
            Post::where('slug', $slug)
                ->when($this->postId, fn ($q) => $q->whereNot('id', $this->postId))
                ->exists()
        ) {
            $i++;
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    public function render(): View
    {
        return view('livewire.post-form');
    }
}
