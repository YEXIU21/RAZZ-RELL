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
            
            if (!Storage::exists($fullPath)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            $file = Storage::get($fullPath);
            $mimeType = Storage::mimeType($fullPath);

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
            
            $path = $file->storeAs($folder, $uniqueName, 'local');

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