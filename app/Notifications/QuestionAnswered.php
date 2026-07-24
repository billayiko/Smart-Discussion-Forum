<?php

namespace App\Notifications;

use App\Models\Answer;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $question = $this->answer->question;

        return (new MailMessage)
            ->subject("{$this->answer->user->name} replied to your question")
            ->greeting("Hi {$notifiable->name},")
            ->line("{$this->answer->user->name} replied to your question \"{$question->title}\":")
            ->line(Str::limit($this->answer->body, 160))
            ->action('View the reply', route('questions.show', $question));
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
