<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordBase;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * French-language version of the built-in ResetPassword notification, with a
 * link that points at the SPA frontend instead of the Laravel route. The SPA
 * receives `token` + `email` as query params and POSTs them back to
 * /api/reset-password.
 */
class ResetPasswordFr extends ResetPasswordBase
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->buildResetUrl($notifiable);
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe IQRA')
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line('Nous avons reçu une demande de réinitialisation du mot de passe associé à votre compte IQRA.')
            ->action('Réinitialiser mon mot de passe', $url)
            ->line('Ce lien expire dans '.$minutes.' minutes.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email — votre mot de passe ne sera pas modifié.")
            ->salutation('— L\'équipe IQRA');
    }

    private function buildResetUrl($notifiable): string
    {
        $base = rtrim(config('app.frontend_url', config('app.url')), '/');

        return $base.'/reset-password?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
