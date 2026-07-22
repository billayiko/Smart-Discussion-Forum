<?php

namespace App\Notifications;

use App\Models\Answer;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class QuestionAnswered extends Notification
{
    public function __construct(private readonly Answer $answer)
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
        $question = $this->answer->question;

        return [
            'question_id' => $question->id,
            'question_title' => $question->title,
            'answerer_name' => $this->answer->user->name,
            'excerpt' => Str::limit($this->answer->body, 80),
            'url' => route('questions.show', $question),
        ];
    }
}
