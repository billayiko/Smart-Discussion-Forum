<?php

namespace App\Notifications;

use App\Models\Quiz;
use Illuminate\Notifications\Notification;

class QuizScheduled extends Notification
{
    public function __construct(private readonly Quiz $quiz)
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
            'quiz_id' => $this->quiz->id,
            'quiz_title' => $this->quiz->title,
            'subject' => $this->quiz->subject,
            'scheduled_at' => $this->quiz->scheduled_at?->toIso8601String(),
            'duration_minutes' => $this->quiz->duration_minutes,
            'url' => $this->quiz->course_topic_id
                ? route('topics.show', $this->quiz->course_topic_id)
                : route('student.dashboard'),
        ];
    }
}
