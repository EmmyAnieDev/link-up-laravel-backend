<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\GetProfileImageRequest;
use App\Http\Requests\v1\UploadPhotoRequest;
use App\Models\v1\Images;
use App\Traits\v1\ApiResponse;
use App\Traits\v1\UploadPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ProfileImageController extends Controller
{

    use ApiResponse, UploadPhoto;

    /**
     * Upload/Update Image for the currently authenticated user.
     */
    public function uploadProfileImage(UploadPhotoRequest $request)
    {
        $user = Auth::id();

        if (!$user) {
            return $this->errorResponse('Please Login to upload image', 401);
        }

        $existingImage = Images::where('user_id', $user)->first();

        if ($existingImage) {
            $existingFilePath = public_path($existingImage->image_path);
            if (file_exists($existingFilePath)) {
                unlink($existingFilePath);
            }

            $existingImage->delete();
        }

        $imagePath = $this->uploadPhoto($request->validated('file'), '');

        $image = new Images();
        $image->user_id = Auth::id();
        $image->image_path = $imagePath;
        $image->save();

        return $this->successResponse($image,'Image uploaded successfully', 201);
    }


    /**
     * Retrieve Image for the currently authenticated user.
     */
    public function getProfileImage(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->errorResponse('Please Login to view image', 401);
        }

        $path = $request->query('path') ?? $request->input('path');

        if (!$path) {
            return $this->errorResponse('Path is required');
        }

        $path = ltrim($path, '/'); // Trim the leading slash

        // Ensure the path is relative to the 'public/uploads' directory
        $relativePath = str_replace('uploads/', '', $path);

        if (!Storage::disk('public')->exists($relativePath)) {
            return $this->errorResponse('Image not found', 404);
        }

        $fileContent = Storage::disk('public')->get($relativePath);
        $mimeType = Storage::disk('public')->mimeType($relativePath);

        // Generate ETag based on file content
        $etag = md5_file(Storage::disk('public')->path($relativePath));

        // Check If-None-Match header for ETag validation
        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304); // Not Modified
        }

        // Return the file with ETag and caching headers
        return Response::make($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
            'ETag' => $etag,
        ]);
    }
}
