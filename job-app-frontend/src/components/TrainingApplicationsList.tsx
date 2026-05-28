import { Link } from 'react-router-dom'
import type { TrainingApplication } from '@/types'
import StatusBadge from './StatusBadge'
import EmptyState from './EmptyState'

export default function TrainingApplicationsList({ applications }: { applications: TrainingApplication[] }) {
  if (!applications.length) {
    return <EmptyState title="Aucune inscription" description="Inscrivez-vous à des formations pour les voir ici." />
  }
  return (
    <div className="divide-y divide-gray-100">
      {applications.map((app) => (
        <div key={app.id} className="py-4 flex items-start justify-between gap-4">
          <div className="min-w-0">
            <Link to={`/trainings/${app.training_session?.id}`} className="font-medium text-gray-900 hover:text-primary-600">
              {app.training_session?.title ?? '—'}
            </Link>
            <p className="text-sm text-gray-500 mt-0.5">{app.training_session?.school?.name}</p>
            <p className="text-xs text-gray-400 mt-1">
              Inscrit le {new Date(app.created_at).toLocaleDateString('fr-FR')}
            </p>
          </div>
          <StatusBadge status={app.status} />
        </div>
      ))}
    </div>
  )
}
