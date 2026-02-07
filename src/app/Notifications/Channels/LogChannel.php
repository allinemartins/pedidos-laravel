<?php

namespace App\Notifications\Channels;

use Illuminate\Support\Facades\Log;

class LogChannel
{
    public function send($notifiable, $notification): void
    {
        $payload = method_exists($notification, 'toLog')
            ? $notification->toLog($notifiable)
            : [
                'type' => class_basename($notification),
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null,
            ];

        Log::info('Notification', $payload);
    }
}
