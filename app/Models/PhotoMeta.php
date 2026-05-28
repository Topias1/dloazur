<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoMeta extends Model
{
    // Laravel would pluralize incorrectly to 'photo_metas'
    protected $table = 'photos_meta';

    protected $fillable = [
        'client_uuid',
        'passage_id',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'captured_at',
    ];

    protected $casts = [
        'client_uuid' => 'string',
        'captured_at' => 'datetime',
        'size_bytes'  => 'integer',
        'width'       => 'integer',
        'height'      => 'integer',
    ];

    public function passage(): BelongsTo
    {
        return $this->belongsTo(Passage::class);
    }
}
