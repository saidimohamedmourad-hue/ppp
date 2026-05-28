import { useState } from 'react'
import { apiFetch } from '@/utils/api'
import ErrorMessage from './ErrorMessage'

interface Props {
  sessionId: number
  sessionTitle: string
  onClose: () => void
  onSuccess: () => void
}

export default function EnrollModal({ sessionId, sessionTitle, onClose, onSuccess }: Props) {
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const handleConfirm = async () => {
    setSubmitting(true)
    setError(null)
    try {
      await apiFetch(`training-sessions/${sessionId}/apply`, { method: 'POST' })
      onSuccess()
      onClose()
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur lors de l'inscription")
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onClick={onClose}>
      <div className="bg-white rounded-xl w-full max-w-md shadow-xl" onClick={(e) => e.stopPropagation()}>
        <div className="p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-2">Confirmer l'inscription</h2>
          <p className="text-sm text-gray-600 mb-4">
            Voulez-vous vous inscrire à la formation <strong>{sessionTitle}</strong> ?
          </p>
          {error && <ErrorMessage message={error} />}
          <div className="flex gap-3 justify-end mt-4">
            <button onClick={onClose} className="btn-secondary">Annuler</button>
            <button onClick={handleConfirm} disabled={submitting} className="btn-primary">
              {submitting ? 'Inscription...' : "S'inscrire"}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
