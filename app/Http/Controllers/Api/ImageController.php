<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Image\UploadImageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ImageController extends Controller
{
    public function upload(UploadImageRequest $request): JsonResponse
    {
        $file = $request->file('image');

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid()->toString().'.'.$extension;

        // Store in articles directory with date-based subdirectories
        $directory = 'articles/'.date('Y/m');
        $path = $file->storeAs($directory, $filename, 'public');

        $url = Storage::disk('public')->url($path);

        return response()->json([
            'data' => [
                'url' => $url,
                'path' => $path,
                'filename' => $filename,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ],
        ], 201);
    }
}
