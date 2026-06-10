<?php

namespace App\Notifications;

use App\Models\TrainingApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fired when the school-owner updates a candidate's training application
 * status (accepted / rejected / waitlisted-now-promoted).
 * Notified party: the candidate.
 */
class TrainingApplicationStatusChanged extends Notification
{
    use Queueable;

    public function __construct(private TrainingApplication $application) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $session = $this->application->trainingSession;
        $status  = $this->application->status;

        return [
            'icon'       => $this->iconForStatus($status),
            'title'      => $this->titleForStatus($status),
            'body'       => 'Votre inscription pour "'.($session?->title ?? '—').'" a été mise à jour',
            'action_url' => '/dashboard/training-applications',
            'meta'       => [
                'training_application_id' => $this->application->id,
                'training_session_id'     => $session?->id,
                'status'                  => $status,
            ],
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $session = $this->application->trainingSession;
        $status  = $this->application->status;
        $url     = config('app.frontend_url').'/dashboard/training-applications';

        return (new MailMessage)
            ->subject('Statut de votre inscription — '.($session?->title ?? 'IQRA'))
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line('Le statut de votre inscription à **'.($session?->title ?? '—').'** est désormais : **'.$this->labelForStatus($status).'**.')
            ->action('Voir mes inscriptions', $url)
            ->salutation("— L'équipe IQRA");
    }

    private function iconForStatus(string $status): string
    {
        return match ($status) {
            'accepted' => '✅',
            'rejected' => '❌',
            'reviewed' => '👀',
            default    => '📝',
        };
    }

    private function titleForStatus(string $status): string
    {
        return match ($status) {
            'accepted' => 'Inscription confirmée',
            'rejected' => 'Inscription non retenue',
            'reviewed' => 'Inscription consultée',
            default    => 'Statut d\'inscription mis à jour',
        };
    }

    private function labelForStatus(string $status): string
    {
        return match ($status) {
            'accepted' => 'Confirmé',
            'rejected' => 'Non retenu',
            'reviewed' => 'Vue',
            'pending'  => 'En attente',
            default    => $status,
        };
    }
}
