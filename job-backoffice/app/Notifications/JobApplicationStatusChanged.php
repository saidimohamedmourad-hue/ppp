<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fired when the company-owner updates a candidate's application status.
 * Notified party: the candidate.
 *
 * The data payload's `meta.status` lets the UI show a status-specific badge
 * (green for accepted, red for rejected, etc.) without re-fetching.
 */
class JobApplicationStatusChanged extends Notification
{
    use Queueable;

    public function __construct(private JobApplication $application) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $job    = $this->application->jobVacancy;
        $status = $this->application->status;

        return [
            'icon'       => $this->iconForStatus($status),
            'title'      => $this->titleForStatus($status),
            'body'       => 'Votre candidature pour "'.($job?->title ?? '—').'" a été mise à jour',
            'action_url' => '/dashboard/job-applications',
            'meta'       => [
                'job_application_id' => $this->application->id,
                'job_vacancy_id'     => $job?->id,
                'status'             => $status,
            ],
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $job    = $this->application->jobVacancy;
        $status = $this->application->status;
        $url    = config('app.frontend_url').'/dashboard/job-applications';

        return (new MailMessage)
            ->subject('Statut de votre candidature — '.($job?->title ?? 'IQRA'))
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line('Le statut de votre candidature pour **'.($job?->title ?? '—').'** est désormais : **'.$this->labelForStatus($status).'**.')
            ->action('Voir mes candidatures', $url)
            ->salutation("— L'équipe IQRA");
    }

    private function iconForStatus(string $status): string
    {
        return match ($status) {
            'accepted'    => '✅',
            'shortlisted' => '⭐',
            'rejected'    => '❌',
            'reviewed'    => '👀',
            default       => '📝',
        };
    }

    private function titleForStatus(string $status): string
    {
        return match ($status) {
            'accepted'    => 'Candidature acceptée',
            'shortlisted' => 'Vous êtes présélectionné',
            'rejected'    => 'Candidature non retenue',
            'reviewed'    => 'Candidature consultée',
            default       => 'Statut de candidature mis à jour',
        };
    }

    private function labelForStatus(string $status): string
    {
        return match ($status) {
            'accepted'    => 'Accepté',
            'shortlisted' => 'Présélectionné',
            'rejected'    => 'Non retenu',
            'reviewed'    => 'Vue',
            'pending'     => 'En attente',
            default       => $status,
        };
    }
}
