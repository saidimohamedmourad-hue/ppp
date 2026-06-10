<?php

namespace App\Notifications;

use App\Models\TrainingApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fired when a candidate registers for a training session.
 * Notified party: the school-owner who runs the session.
 *
 * Mirror of JobApplicationReceived for the training side — kept as a
 * separate class so each side can evolve independently (different copy,
 * different action URL, etc.).
 */
class TrainingApplicationReceived extends Notification
{
    use Queueable;

    public function __construct(private TrainingApplication $application) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $session   = $this->application->trainingSession;
        $applicant = $this->application->user;
        $waitlist  = (bool) $this->application->is_waitlist;

        return [
            'icon'       => $waitlist ? '⏳' : '🎓',
            'title'      => $waitlist ? 'Nouvelle inscription (liste d\'attente)' : 'Nouvelle inscription',
            'body'       => ($applicant?->name ?? 'Un candidat').' s\'est inscrit à "'.($session?->title ?? '—').'"',
            'action_url' => '/dashboard/school/sessions/'.$session?->id.'/applicants',
            'meta'       => [
                'training_application_id' => $this->application->id,
                'training_session_id'     => $session?->id,
                'applicant_id'            => $applicant?->id,
                'is_waitlist'             => $waitlist,
            ],
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $session   = $this->application->trainingSession;
        $applicant = $this->application->user;
        $url       = config('app.frontend_url').'/dashboard/school/sessions/'.$session?->id.'/applicants';
        $waitlist  = (bool) $this->application->is_waitlist;

        return (new MailMessage)
            ->subject('Nouvelle inscription — '.($session?->title ?? 'IQRA'))
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line(($applicant?->name ?? 'Un candidat').' vient de s\'inscrire à votre formation **'.($session?->title ?? '—').'**'.
                ($waitlist ? ' (liste d\'attente, la session est complète).' : '.'))
            ->action('Voir l\'inscription', $url)
            ->salutation("— L'équipe IQRA");
    }
}
