<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

$serveStorageFile = function (Request $request, string $path) {
    $publicPath = storage_path('app/public/' . $path);
    $localPath = storage_path('app/' . $path);

    // Prefer public path; fall back to local if needed
    if (file_exists($publicPath)) {
        $filePath = $publicPath;
        $disk = 'public';
        $storagePath = $path;
    } elseif (file_exists($localPath)) {
        $filePath = $localPath;
        $disk = 'local';
        $storagePath = $path;
    } else {
        abort(404);
    }

    $mimeType = Storage::disk($disk)->mimeType($storagePath) ?? 'application/octet-stream';
    $lastModified = filemtime($filePath) ?: time();

    $response = response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        'Cache-Control' => 'public, max-age=604800, stale-while-revalidate=86400',
    ]);

    $response->setEtag(md5($filePath . '|' . $lastModified . '|' . filesize($filePath)));
    $response->setLastModified(\Carbon\Carbon::createFromTimestamp($lastModified));
    $response->isNotModified($request);

    return $response;
};

// Route to serve storage files when symlink doesn't work
Route::get('/files/{path}', $serveStorageFile)->where('path', '.*')->name('storage.public');

// Legacy /storage path support for existing links
Route::get('/storage/{path}', $serveStorageFile)->where('path', '.*');

