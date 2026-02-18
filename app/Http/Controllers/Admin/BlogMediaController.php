<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogMediaController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'extensions:jpg,jpeg,png,gif,webp,svg'],
        ]);

        $path = $validated['file']->store('blog/gallery', 'public');
        $routePath = Str::startsWith($path, 'blog/') ? (string) Str::after($path, 'blog/') : $path;

        return response()->json([
            'location' => route('blog.media', ['path' => $routePath]),
        ]);
    }
}
