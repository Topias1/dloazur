<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Passage;
use App\Models\PhotoMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PassagePhotoController extends Controller
{
    /**
     * POST /api/passages/{passageUuid}/photos
     *
     * Upload + UPSERT idempotent sur photos_meta.client_uuid (D-42).
     * - Validation mime JPEG + max 10 MB (D-48, T-6-02)
     * - Storage::disk('r2') direct — pas medialibrary (RESEARCH §Pitfall 6)
     * - 404 si le passage n'existe pas (sécurité anti-orphelin)
     * - T-6-03 : putFile() génère un nom de fichier aléatoire (pas user-supplied)
     */
    public function store(Request $request, string $passageUuid): JsonResponse
    {
        $request->validate([
            'photo'       => ['required', 'file', 'mimes:jpeg,jpg', 'max:10240'],
            'client_uuid' => ['required', 'uuid'],
            'captured_at' => ['nullable', 'date'],
        ]);

        // 404 explicite si le passage n'existe pas
        $passage = Passage::where('client_uuid', $passageUuid)->firstOrFail();

        $file = $request->file('photo');
        $path = Storage::disk('r2')->putFile("passages/{$passageUuid}/photos", $file);

        // UPSERT idempotent sur client_uuid UNIQUE (D-42)
        PhotoMeta::updateOrCreate(
            ['client_uuid' => $request->input('client_uuid')],
            [
                'passage_id'  => $passage->id,
                'disk'        => 'r2',
                'path'        => $path,
                'mime_type'   => $file->getMimeType() ?: 'image/jpeg',
                'size_bytes'  => $file->getSize(),
                'captured_at' => $request->input('captured_at') ?? now(),
            ]
        );

        return response()->json(['ok' => true], 200);
    }
}
