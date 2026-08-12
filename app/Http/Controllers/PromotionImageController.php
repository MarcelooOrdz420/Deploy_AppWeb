<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PromotionImageController extends Controller
{
    public function __invoke(string $path): BinaryFileResponse|Response
    {
        $path = rawurldecode(trim($path, '/'));
        abort_if($path === '' || Str::contains($path, ['..', '\\']), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        $absolutePath = $disk->path($path);
        abort_unless(is_file($absolutePath), 404);

        return response()->file($absolutePath, [
            'Cache-Control' => 'public, max-age=86400',
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
