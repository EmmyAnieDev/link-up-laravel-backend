<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\UploadPhotoRequest;
use App\Http\Requests\v1\UpdateUserRequest;
use App\Models\User;
use App\Models\v1\Images;
use App\Traits\v1\ApiResponse;
use App\Traits\v1\UploadPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use ApiResponse, UploadPhoto;

    /**
     * Display a list of all users except the currently authenticated user.
     */
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())->with('image')->orderBy('id', 'desc')->get();

        $userData = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image ? $user->image->image_path : null,
            ];
        });

        return $this->successResponse($userData, 'Successfully retrieved users');
    }


    /**
     * Display the currently authenticated user profile.
     */
    public function showProfile()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->errorResponse('Please login to view profile', 401);
        }

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'image' => $user->image ? $user->image->image_path : null,
        ];

        return $this->successResponse($userData, 'User profile retrieved successfully');
    }



    /**
     * Update the currently authenticated user profile.
     */
    public function updateProfile(UpdateUserRequest $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->errorResponse('Pleas login to update profile', 401);
        }

        $user->update($request->validated());

        return $this->successResponse($user, 'User updated successfully');
    }


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
     * delete the currently authenticated user account.
     */
    public function deleteAccount()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->errorResponse('Please Login to delete account', 401);
        }

        if ($user->image) {
            $imagePath = public_path($user->image->image_path);

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            $user->image->delete();
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }

}
