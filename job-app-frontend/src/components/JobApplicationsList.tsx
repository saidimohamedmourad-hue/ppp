import { Link } from 'react-router-dom'
import type { JobApplication } from '@/types'
import StatusBadge from './StatusBadge'
import EmptyState from './EmptyState'

export default function JobApplicationsList({ applications }: { applications: JobApplication[] }) {
  if (!applications.length) {
    return <EmptyState title="Aucune candidature" description="Postulez à des offres d'emploi pour les voir ici." />
  }
  return (
    <div className="divide-y divide-gray-100">
      {applications.map((app) => (
        <div key={app.id} className="py-4 flex items-start justify-between gap-4">
          <div className="min-w-0">
            <Link to={`/jobs/${app.job.id}`} className="font-medium text-gray-900 hover:text-primary-600">
              {app.job.title}
            </Link>
            <p className="text-sm text-gray-500 mt-0.5">{app.job.company?.name} — {app.job.location}</p>
            <p className="text-xs text-gray-400 mt-1">
              Postulé le {new Date(app.created_at).toLocaleDateString('fr-FR')}
            </p>
          </div>
          <StatusBadge status={app.status} />
        </div>
      ))}
    </div>
  )
}
