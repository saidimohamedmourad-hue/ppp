<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fired when a candidate applies to a job vacancy.
 * Notified party: the company-owner who owns the vacancy.
 *
 * Stored as JSON in `notifications.data`. The frontend uses these keys to
 * render the bell entry:
 *   - icon       → emoji shown in the row
 *   - title      → short heading (one line, no HTML)
 *   - body       → secondary line (e.g. candidate name + job title)
 *   - action_url → where to navigate when the user clicks the row
 *   - meta       → arbitrary tag IDs the UI may want (job id, applicant id…)
 */
class JobApplicationReceived extends Notification
{
    use Queueable;

    public function __construct(private JobApplication $application) {}

    public function via(object $notifiable): array
    {
        // Database for the bell + email for offline reach. Both channels are
        // queued where applicable to keep the API request fast.
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $job       = $this->application->jobVacancy;
        $applicant = $this->application->user;

        return [
            'icon'       => '📨',
            'title'      => 'Nouvelle candidature reçue',
            'body'       => ($applicant?->name ?? 'Un candidat').' a postulé à "'.($job?->title ?? '—').'"',
            'action_url' => '/dashboard/company/jobs/'.$job?->id.'/applicants',
            'meta'       => [
                'job_application_id' => $this->application->id,
                'job_vacancy_id'     => $job?->id,
                'applicant_id'       => $applicant?->id,
            ],
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $job       = $this->application->jobVacancy;
        $applicant = $this->application->user;
        $url       = config('app.frontend_url').'/dashboard/company/jobs/'.$job?->id.'/applicants';

        return (new MailMessage)
            ->subject('Nouvelle candidature — '.($job?->title ?? 'IQRA'))
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line(($applicant?->name ?? 'Un candidat').' vient de postuler à votre offre **'.($job?->title ?? '—').'**.')
            ->action('Voir la candidature', $url)
            ->line('Vous pouvez consulter le CV et répondre depuis votre tableau de bord.')
            ->salutation("— L'équipe IQRA");
    }
}
