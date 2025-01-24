<?php

namespace App\Http\Controllers\v1;

use App\Events\v1\SendMessageEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\SendMessageRequest;
use App\Models\User;
use App\Models\v1\Message;
use App\Traits\v1\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Laravel\Prompts\error;

class ChatController extends Controller
{

    use ApiResponse;

    /**
     * Fetch the authenticated user's details and their associated messages.
     *
     * This function retrieves a user's information from the database based on the provided `user_id`.
     * Additionally, it fetches all messages associated with the user through a defined relationship.
     * The data is returned as a JSON response, including the user's details and a list of their messages.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    function fetchMessages(Request $request)
    {

        $other_user = User::findOrFail($request->other_user_id);

        $messages = Message::where('sender_id', Auth::id())->where('receiver_id', $request->other_user_id)
           ->orWhere('sender_id', $request->other_user_id)->where('receiver_id', Auth::id())->get();

        return response()->json([
            'user' => $other_user,
            'messages' => $messages,
            'status' => 200,
            'success' => true
        ]);
    }


    function sendMessage(SendMessageRequest $request)
    {
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'delivered' => false, 
            'read' => false, 
        ]);

        event(new SendMessageEvent($request->message, Auth::id() , $request->receiver_id));
    

        return $this->successResponse($message, 'Message sent', 201);
    }
}
