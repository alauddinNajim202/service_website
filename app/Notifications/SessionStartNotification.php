<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SessionStartNotification extends Notification
{
    use Queueable;

    protected $bookSession;
    protected $isForCreator;

    /**
     * Create a new notification instance.
     */
    public function __construct($bookSession, $isForCreator = false)
    {
        $this->bookSession = $bookSession;
        $this->isForCreator = $isForCreator;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->isForCreator) {
            $message = "You have a new paid session booking from " . ($this->bookSession->user->name ?? 'a user') . "!";
        } else {
            $message = "Your session with " . ($this->bookSession->creator->name ?? 'the creator') . " is now paid and active!";
        }

        return [
            'type' => 'session_started',
            'message' => $message,
            'book_session_id' => $this->bookSession->id,
            'creator_id' => $this->bookSession->creator_id,
            'user_id' => $this->bookSession->user_id,
        ];
    }
}
