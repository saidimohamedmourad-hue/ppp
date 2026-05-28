import { Link } from 'react-router-dom'
import type { TrainingSession } from '@/types'

export default function TrainingCard({ training }: { training: TrainingSession }) {
  return (
    <Link to={`/trainings/${training.id}`} className="card block p-5 hover:border-primary-300">
      <h3 className="font-semibold text-gray-900 truncate">{training.title}</h3>
      <p className="text-sm text-gray-600 mt-0.5">{training.school?.name}</p>
      <div className="mt-3 flex flex-wrap gap-2 text-xs text-gray-500">
        {training.duration && <span>Durée : {training.duration}</span>}
        {training.price != null && (
          <span className="font-medium text-primary-600">
            {training.price === 0 ? 'Gratuit' : `${training.price} €`}
          </span>
        )}
        {training.category && <span>{training.category.name}</span>}
      </div>
    </Link>
  )
}
