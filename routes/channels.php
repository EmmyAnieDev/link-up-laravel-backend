<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('chat.{id}', function ($user, $id) {
    // Log::info('Authenticating user for channel', [
    //     'user_id' => $user->id ?? 'null',
    //     'channel_id' => $id,
    // ]);

    return (int) $user->id === (int) $id;
});

Broadcast::channel('online', function ($user) {
    // $socketId = request('socket_id');
    // $channelName = request('channel_name');

    // Log::info('Signature Debug', [
    //     'socket_id' => $socketId,
    //     'channel_name' => $channelName,
    //     'user' => $user->toArray(),
    // ]);

    return $user->toArray();
});


