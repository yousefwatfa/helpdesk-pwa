<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\Notification;
use Illuminate\Log\LogManager;

abstract class Controller
{
    /**
     * Send a notification, but never let a failing channel (for example an
     * expired push subscription) break the request; log it instead.
     */
    protected function safeNotify($notifiable, Notification $notification)
    {
        try {
            $notifiable->notify($notification);
        } catch (\Exception $e) {
            // fallback for anything else
            logger()->error('Notification failed', [
                'error' => $e->getMessage(),
                'notifiable' => get_class($notifiable),
                'notification' => get_class($notification),
            ]);
        }
    }
}
