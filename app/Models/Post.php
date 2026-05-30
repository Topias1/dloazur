<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'excerpt',
        'status',
        'author',
        'date',
        'show_date',
    ];

    protected $casts = [
        'date'      => 'date',
        'show_date' => 'boolean',
    ];

    /**
     * Only return published posts (status = 'published').
     * Apply on every PUBLIC query path (BlogRepository, SitemapController).
     * Admin UI shows ALL statuses — do NOT apply this scope there (D-03).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Auto-generate slug from title on create when slug is not provided (D-04).
     * Slug is editable while status=draft, locked (read-only form field) once published.
     */
    protected static function booted(): void
    {
        static::creating(function (Post $post): void {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    /**
     * Register the 'cover' media collection for blog post cover images (D-02).
     *
     * Uses Scaleway S3 (`s3` disk per config/filesystems.php — Scaleway endpoint).
     * singleFile() replaces the previous cover on re-upload.
     * thumbnail conversion: 1200×630 (og:image dimensions), nonQueued for serverless.
     * getFirstMediaUrl('cover', 'thumbnail') returns '' when no cover — fallback to og-default.jpg.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
             ->singleFile()
             ->useDisk('s3')
             ->registerMediaConversions(function (Media $media): void {
                 $this->addMediaConversion('thumbnail')
                      ->width(1200)
                      ->height(630)
                      ->nonQueued();
             });
    }
}
