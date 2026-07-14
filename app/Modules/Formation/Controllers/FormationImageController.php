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
            $image = FormationImage::create([
                'image_path' => $imageName,
                'draft_token' => Str::uuid(),
                'uploaded_by' => Auth::id(),
            ]);
        } catch (\Throwable $exception) {
            $this->deleteImage($imageName, FormationImage::STORAGE_PATH);
            throw $exception;
        }

        $data = [
            'id' => $image->id,
            'path' => $this->getImageUrl($imageName, FormationImage::STORAGE_PATH),
        ];

        return $this->successResponse($data, "Image uploadée avec succès");
    }

    public function show(string $image)
    {
        $image = FormationImage::find($image);

        if (! $image) {
            return $this->errorResponse("Image introuvable");
        }

        $data = [
            'id' => $image->id,
            'path' => $this->getImageUrl($image->image_path, FormationImage::STORAGE_PATH),
        ];

        return $this->successResponse($data, "Image chargée avec succès.");
    }

    public function destroy(string $image)
    {
        $image = FormationImage::find($image);

        if (! $image) {
            return $this->errorResponse("Image introuvable");
        }

        $image->delete();

        return $this->noContentSuccessResponse("Image supprimée avec succès.");
    }
}
