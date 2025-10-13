<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = config('app.frontend_url', config('app.url')) . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);

        return (new MailMessage)
                    ->subject('Reset Password - ' . config('app.name'))
                    ->greeting('Halo, ' . $notifiable->name . '!')
                    ->line('Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.')
                    ->action('Reset Password', $resetUrl)
                    ->line('Link reset password ini akan kadaluarsa dalam ' . config('auth.passwords.users.expire') . ' menit.')
                    ->line('Jika Anda tidak meminta reset password, silakan abaikan email ini.')
                    ->salutation('Salam, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
