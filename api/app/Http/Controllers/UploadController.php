<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $file = $request->file('file');

        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;

        $path = $file->storeAs('images', $fileName, 'public');

        return response()->json([
            'data' => [
                'url' => asset('storage/' . $path),
            ]
        ]);
    }
}
