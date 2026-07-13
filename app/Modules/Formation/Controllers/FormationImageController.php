<?php

namespace App\Modules\Formation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Formation\Models\FormationImage;
use App\Modules\Formation\Requests\StoreFormationImageRequest;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FormationImageController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function store(StoreFormationImageRequest $request)
    {
        $imageName = $this->uploadImage($request->file('upload'), FormationImage::STORAGE_PATH);

        try {
            FormationImage::create([
                'image_path' => $imageName,
                'draft_token' => Str::uuid(),
                'uploaded_by' => Auth::id(),
            ]);
        } catch (\Throwable $exception) {
            $this->deleteImage($imageName, FormationImage::STORAGE_PATH);
            throw $exception;
        }

        return response()->json([
            'url' => url('/api/v1/formation-images/'.$imageName),
        ]);
    }

    public function show(string $image)
    {
        if (! FormationImage::where('image_path', $image)->exists()) {
            return $this->errorResponse('Image introuvable.');
        }

        $fullPath = 'images/'.FormationImage::STORAGE_PATH.'/'.$image;
        if (! Storage::disk($this->disk)->exists($fullPath)) {
            return $this->errorResponse('Image introuvable.');
        }

        return redirect()->away($this->getImageUrl($image, FormationImage::STORAGE_PATH));
    }
}
