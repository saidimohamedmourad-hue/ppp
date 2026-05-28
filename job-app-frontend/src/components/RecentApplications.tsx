import { Link } from 'react-router-dom'
import type { JobApplication } from '@/types'
import StatusBadge from './StatusBadge'

export default function RecentApplications({ applications }: { applications: JobApplication[] }) {
  if (!applications.length) {
    return <p className="text-sm text-gray-500">Aucune candidature récente.</p>
  }
  return (
    <ul className="divide-y divide-gray-100">
      {applications.slice(0, 5).map((app) => (
        <li key={app.id} className="py-3 flex items-center justify-between gap-3">
          <div className="min-w-0">
            <Link to={`/jobs/${app.job.id}`} className="text-sm font-medium text-gray-900 hover:text-primary-600 truncate block">
              {app.job.title}
            </Link>
            <p className="text-xs text-gray-500">{app.job.company?.name}</p>
          </div>
          <StatusBadge status={app.status} />
        </li>
      ))}
    </ul>
  )
}
