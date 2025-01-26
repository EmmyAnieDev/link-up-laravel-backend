<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\UploadPhotoRequest;
use App\Http\Requests\v1\UpdateUserRequest;
use App\Models\User;
use App\Models\v1\Images;
use App\Models\v1\Message;
use App\Models\v1\MessageRoom;
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
        $authUserId = Auth::id();

        $users = User::where('id', '!=', $authUserId)->with('image')->get();
    
        // Map users to include last message and time
        $userData = $users->map(function ($user) use ($authUserId) {
            
            // Fetch the message room between the authenticated user and the current user
            $messageRoom = MessageRoom::where(function ($query) use ($authUserId, $user) {
                $query->where('user_one_id', $authUserId)
                    ->where('user_two_id', $user->id);
            })
            ->orWhere(function ($query) use ($authUserId, $user) {
                $query->where('user_one_id', $user->id)
                    ->where('user_two_id', $authUserId);
            })
            ->first();
    
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image ? $user->image->image_path : null,
                'last_message' => $messageRoom ? $messageRoom->last_message : null,
                'last_message_time' => $messageRoom && $messageRoom->last_message_time
                    ? $messageRoom->last_message_time->toIso8601String()
                    : null,
            ];
        });

        // Sort users by last_message_time in descending order, with nulls last
        $sortedUserData = $userData->sortByDesc(function ($user) {
            return $user['last_message_time'] ?? '0000-00-00 00:00:00';
        })->values();
    
        return $this->successResponse($sortedUserData, 'Successfully retrieved users');
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
