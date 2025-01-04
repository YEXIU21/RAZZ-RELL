<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageController extends Controller
{
    public function getFile($path)
    {
        try {
            $fullPath = $path;
            
            // Check both public and local disks
            if (!Storage::disk('public')->exists($fullPath) && !Storage::disk('local')->exists($fullPath)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            // Try public disk first, then fall back to local
            $disk = Storage::disk('public')->exists($fullPath) ? 'public' : 'local';
            $file = Storage::disk($disk)->get($fullPath);
            $mimeType = Storage::disk($disk)->mimeType($fullPath);

            return response($file, 200)->header('Content-Type', $mimeType);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving file: ' . $e->getMessage()], 500);
        }
    }

    public function uploadFile(Request $request, $folder)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileName = pathinfo($originalName, PATHINFO_FILENAME);
            $uniqueName = $fileName . '_' . time() . '.' . $extension;
            
            // Store in public disk for better accessibility
            $path = Storage::disk('public')->putFileAs($folder, $file, $uniqueName);

            if (!$path) {
                throw new \Exception('Failed to store file');
            }

            $url = env('VITE_STORAGE_URL', env('APP_URL')) . '/api/storage/' . $path;

            return response()->json([
                'message' => 'File uploaded successfully',
                'path' => $path,
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
    }
}