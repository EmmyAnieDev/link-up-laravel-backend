<?php

namespace App\Http\Controllers\v1;

use App\Events\v1\SendMessageEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\SendMessageRequest;
use App\Models\User;
use App\Models\v1\Message;
use App\Models\v1\MessageRoom;
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
           ->orWhere('sender_id', $request->other_user_id)->where('receiver_id', Auth::id())->orderBy('created_at', 'asc')->get();

        return response()->json([
            'user' => $other_user,
            'messages' => $messages,
            'status' => 200,
            'success' => true
        ]);
    }


    public function sendMessage(SendMessageRequest $request)
    {
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'delivered' => false, 
            'read' => false,
            'sent_at' => now(),
        ]);

        // Update or create the message room for the sender and receiver
        MessageRoom::updateOrCreate(
            [
                'user_one_id' => min(Auth::id(), $request->receiver_id),
                'user_two_id' => max(Auth::id(), $request->receiver_id),
            ],
            [
                'last_message' => $request->message,
                'last_message_time' => now(),
            ]
        );

        event(new SendMessageEvent($request->message, Auth::id(), $request->receiver_id));

        return $this->successResponse($message, 'Message sent', 201);
    }


    public function unreadMessageCounts()
    {
        $unreadCounts = Message::select('sender_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as unread_count'))
            ->where('receiver_id', Auth::id())
            ->where('read', false)
            ->groupBy('sender_id')
            ->get();

        return $this->successResponse($unreadCounts, 'unread message count fetched');
    }


    public function removeUnreadMessageCounts(Request $request)
    {
        $validated = $request->validate([
            'sender_id' => 'required|integer|exists:users,id',
        ]);

        Message::where('receiver_id', Auth::id())
            ->where('sender_id', $validated['sender_id'])
            ->where('read', false) // Only update unread messages
            ->update([
                'read' => true,
                'read_at' => now(), // Optional: update the read_at timestamp
            ]);

        return $this->successResponse([], 'Unread count removed.');
    }

}
