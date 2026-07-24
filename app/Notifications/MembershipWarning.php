<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipWarning extends Notification
{
    public function __construct(
        private readonly int $warningCount,
        private readonly int $thresholdDays,
    ) {
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
        $isFinal = $this->warningCount >= 2;

        return (new MailMessage)
            ->subject($isFinal ? 'Final warning: inactivity on Academic Pulse Forum' : 'Inactivity warning: Academic Pulse Forum')
            ->greeting("Hi {$notifiable->name},")
            ->line($this->message())
            ->action('Go to dashboard', route($notifiable->dashboardRouteName()));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'warning_count' => $this->warningCount,
            'message' => $this->message(),
        ];
    }

    private function message(): string
    {
        return $this->warningCount >= 2
            ? "This is your final warning: you haven't been active for {$this->thresholdDays}+ days. Continued inactivity will result in your account being temporarily blacklisted."
            : "You haven't been active for {$this->thresholdDays}+ days. Please engage with the forum soon to avoid further warnings.";
    }
}
