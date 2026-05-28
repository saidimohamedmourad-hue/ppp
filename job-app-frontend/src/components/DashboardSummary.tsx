import type { User } from '@/types'

interface Props {
  user: User
  jobCount: number
  trainingCount: number
}

export default function DashboardSummary({ user, jobCount, trainingCount }: Props) {
  return (
    <div className="card p-6">
      <div className="flex items-center gap-4">
        {user.photo ? (
          <img src={user.photo} className="w-16 h-16 rounded-full object-cover" alt={user.name} />
        ) : (
          <div className="w-16 h-16 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-2xl font-bold">
            {user.name.charAt(0).toUpperCase()}
          </div>
        )}
        <div>
          <h2 className="text-xl font-bold text-gray-900">{user.name}</h2>
          <p className="text-sm text-gray-500">{user.email}</p>
        </div>
      </div>
      <div className="grid grid-cols-2 gap-4 mt-6">
        <div className="rounded-lg bg-blue-50 px-4 py-3 text-center">
          <div className="text-2xl font-bold text-blue-700">{jobCount}</div>
          <div className="text-xs text-blue-600 mt-0.5">Candidatures emploi</div>
        </div>
        <div className="rounded-lg bg-purple-50 px-4 py-3 text-center">
          <div className="text-2xl font-bold text-purple-700">{trainingCount}</div>
          <div className="text-xs text-purple-600 mt-0.5">Inscriptions formation</div>
        </div>
      </div>
    </div>
  )
}
