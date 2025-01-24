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
