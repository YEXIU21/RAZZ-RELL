<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageController extends Controller
{
    public function getFile($path)
    {
        $fullPath = $path;
        
        if (!Storage::exists($fullPath)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::response($fullPath);
    }

    public function uploadFile(Request $request, $folder)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        try {
            $file = $request->file('file');
            $path = $file->store($folder, 'local');

            if (!$path) {
                throw new \Exception('Failed to store file');
            }

            return response()->json([
                'message' => 'File uploaded successfully',
                'path' => $path,
                'url' => Storage::url($path)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
    }
}