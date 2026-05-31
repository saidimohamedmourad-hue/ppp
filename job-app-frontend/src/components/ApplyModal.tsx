import { useEffect, useState } from 'react'
import { apiFetch, getUser } from '@/utils/api'
import type { Resume, User } from '@/types'
import LoadingSpinner from './LoadingSpinner'
import ErrorMessage from './ErrorMessage'

interface Props {
  jobId: number
  jobTitle: string
  onClose: () => void
  onSuccess: () => void
}

export default function ApplyModal({ jobId, jobTitle, onClose, onSuccess }: Props) {
  const [resumes, setResumes] = useState<Resume[]>([])
  const [selectedResume, setSelectedResume] = useState<number | null>(null)
  const [coverLetter, setCoverLetter] = useState('')
  // Phone capture: the backend requires it when the user has no phone on
  // their profile yet. We show the input only in that case to keep returning
  // candidates' apply flow a one-click affair.
  const cachedUser = getUser() as User | null
  const phoneAlreadyOnFile = !!cachedUser?.phone
  const [phone, setPhone] = useState(cachedUser?.phone ?? '')
  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    apiFetch('resumes')
      .then((res) => setResumes(res.data ?? res))
      .catch(() => setResumes([]))
      .finally(() => setLoading(false))
  }, [])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!phoneAlreadyOnFile && phone.trim().length < 6) {
      setError('Indiquez un numéro de téléphone — les recruteurs s\'en serviront pour vous contacter.')
      return
    }
    setSubmitting(true)
    setError(null)
    try {
      await apiFetch(`jobs/${jobId}/apply`, {
        method: 'POST',
        body: JSON.stringify({
          cover_letter: coverLetter,
          resume_id: selectedResume,
          // Only send phone if the user typed one (the backend treats it as
          // a profile update on first non-empty value).
          ...(phone.trim() ? { phone: phone.trim() } : {}),
        }),
      })
      // Persist the freshly-saved phone in the cached user payload so the
      // next apply skips this step.
      if (cachedUser && phone.trim() && phone.trim() !== cachedUser.phone) {
        localStorage.setItem('user', JSON.stringify({ ...cachedUser, phone: phone.trim() }))
      }
      onSuccess()
      onClose()
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur lors de la candidature')
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onClick={onClose}>
      <div className="bg-white rounded-xl w-full max-w-lg shadow-xl" onClick={(e) => e.stopPropagation()}>
        <div className="flex items-center justify-between p-5 border-b border-gray-200">
          <h2 className="text-lg font-semibold text-gray-900">Postuler — {jobTitle}</h2>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600">
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {loading ? (
          <LoadingSpinner />
        ) : (
          <form onSubmit={handleSubmit} className="p-5 space-y-4">
            {error && <ErrorMessage message={error} />}

            <div>
              <label className="label">CV sélectionné</label>
              <select
                className="input"
                value={selectedResume ?? ''}
                onChange={(e) => setSelectedResume(e.target.value ? Number(e.target.value) : null)}
              >
                <option value="">-- Aucun CV --</option>
                {resumes.map((r) => (
                  <option key={r.id} value={r.id}>{r.filename}</option>
                ))}
              </select>
              {resumes.length === 0 && (
                <p className="text-xs text-gray-500 mt-1">
                  Vous n'avez pas encore de CV.{' '}
                  <a href="/dashboard/resumes" className="text-primary-600 underline">En ajouter un</a>
                </p>
              )}
            </div>

            {!phoneAlreadyOnFile && (
              <div>
                <label className="label">Téléphone <span className="text-red-500">*</span></label>
                <input
                  type="tel"
                  className="input"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  placeholder="+213 555 123 456"
                  autoComplete="tel"
                  required
                />
                <p className="text-xs text-gray-500 mt-1">
                  Le recruteur s'en servira pour vous contacter. Enregistré sur votre profil pour vos prochaines candidatures.
                </p>
              </div>
            )}

            <div>
              <label className="label">Lettre de motivation</label>
              <textarea
                className="input min-h-[120px]"
                value={coverLetter}
                onChange={(e) => setCoverLetter(e.target.value)}
                placeholder="Décrivez votre motivation..."
                rows={5}
              />
            </div>

            <div className="flex gap-3 justify-end pt-2">
              <button type="button" onClick={onClose} className="btn-secondary">Annuler</button>
              <button type="submit" disabled={submitting} className="btn-primary">
                {submitting ? 'Envoi...' : 'Envoyer ma candidature'}
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  )
}
