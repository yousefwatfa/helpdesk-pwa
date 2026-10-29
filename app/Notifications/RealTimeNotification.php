<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class RealTimeNotification extends Notification implements ShouldBroadcastNow{
    use Queueable;

    public $message;
    public $ticketId;
    public $userId;

    public function __construct($message, $ticketId, $userId)
    {
        $this->message = $message;
        $this->ticketId = $ticketId;
        $this->userId = $userId;
    }

    public function via($notifiable)
    {
        $channels = ['database', 'broadcast'];

        // phones/desktops that enabled push from the profile page (PWA)
        if (method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toWebPush($notifiable, $notification)
    {
        $isAdmin = $notifiable->role?->name === 'Admin';
        $path = $this->ticketId
            ? ($isAdmin ? "/admin/tickets/{$this->ticketId}" : "/tickets/{$this->ticketId}")
            : '/dashboard';

        return (new WebPushMessage)
            ->title(branding()->name . ($this->ticketId ? " - تذكرة #{$this->ticketId}" : ''))
            ->body($this->message)
            ->icon(branding()->iconUrl(192))
            ->tag($this->ticketId ? "ticket-{$this->ticketId}" : 'general')
            ->data(['url' => $path])
            ->options(['TTL' => 86400, 'urgency' => 'high']);
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'ticket_id' => $this->ticketId,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => $this->message,
            'ticket_id' => $this->ticketId,
        ]);
    }
    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->userId);
    }

}
