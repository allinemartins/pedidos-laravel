<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use App\Notifications\Channels\LogChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TravelOrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public TravelOrder $order) {}

    public function via(object $notifiable): array
    {
        return [LogChannel::class];
    }

    public function toLog(object $notifiable): array
    {
        return [
            'type' => 'TravelOrderStatusChanged',
            'order_id' => $this->order->id,
            'user_id' => $notifiable->id ?? null,
            'status' => $this->order->status,
            'destination' => $this->order->destination,
            'departureDate' => $this->order->departure_date?->format('Y-m-d'),
            'returnDate' => $this->order->return_date?->format('Y-m-d'),
            'updated_at' => $this->order->updated_at?->toISOString(),
        ];
    }
}
