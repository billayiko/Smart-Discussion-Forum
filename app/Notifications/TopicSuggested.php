<?php

namespace App\Notifications;

use App\Models\CourseTopic;
use Illuminate\Notifications\Notification;

class TopicSuggested extends Notification
{
    public function __construct(private readonly CourseTopic $topic)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'topic_id' => $this->topic->id,
            'topic_title' => $this->topic->title,
            'topic_description' => $this->topic->description,
        ];
    }
}
