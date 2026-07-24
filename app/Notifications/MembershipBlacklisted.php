<?php

namespace App\Notifications;

use Carbon\CarbonInterface;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipBlacklisted extends Notification
{
    public function __construct(private readonly ?CarbonInterface $until)
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
        return (new MailMessage)
            ->subject('Your Academic Pulse Forum account has been suspended')
            ->greeting("Hi {$notifiable->name},")
            ->line($this->message())
            ->line('Please make sure to engage with the forum regularly once your access returns.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->message(),
        ];
    }

    private function message(): string
    {
        return $this->until
            ? "Your account was suspended due to inactivity, following two prior warnings. Access will return on {$this->until->format('M j, Y')}."
            : 'Your account was suspended due to inactivity, following two prior warnings.';
    }
}
