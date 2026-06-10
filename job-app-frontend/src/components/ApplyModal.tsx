import { useEffect, useState } from 'react'
import { apiFetch, getUser } from '@/utils/api'
import type { Resume, User } from '@/types'
import { EDUCATION_LEVELS } from '@/constants/education'
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
  const [educationLevel, setEducationLevel] = useState('')
  const [coverLetter, setCoverLetter] = useState('')
  // Phone capture: the backend requires it on the first application of a
  // candidate. We always show the field — pre-filled from the cached profile
  // if any — so the user can confirm or correct before posting. Hiding it
  // when "we think" the profile already has a phone confused users when the
  // cache was stale (server-side phone got cleared, dirty localStorage…).
  const cachedUser = getUser() as User | null
  const [phone, setPhone] = useState(cachedUser?.phone ?? '')
  const [phoneFromServer, setPhoneFromServer] = useState<string | null>(cachedUser?.phone ?? null)
  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    apiFetch('resumes')
      .then((res) => setResumes(res.data ?? res))
      .catch(() => setResumes([]))
      .finally(() => setLoading(false))
    // Re-fetch the live user too — the cached payload may have been stored
    // before we added the phone column, in which case `cachedUser.phone` is
    // undefined and we'd otherwise keep the input empty.
    apiFetch('me')
      .then((res) => {
        const u = (res as { data?: User }).data ?? (res as User)
        if (u?.phone && !phone) setPhone(u.phone)
        setPhoneFromServer(u?.phone ?? null)
        // Refresh the cache too so other screens see the new value.
        if (u) localStorage.setItem('user', JSON.stringify(u))
      })
      .catch(() => {})
    // We only want this on mount; phone state is the controlled input.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    // CV obligatoire pour une candidature à un emploi.
    if (!selectedResume) {
      setError('Sélectionnez un CV — il est obligatoire pour postuler à une offre.')
      return
    }
    if (!educationLevel) {
      setError('Sélectionnez votre niveau d\'études.')
      return
    }
    if (phone.trim().length < 6) {
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
          education_level: educationLevel,
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
              <label className="label">
                CV sélectionné <span className="text-red-500">*</span>
              </label>
              <select
                className="input"
                value={selectedResume ?? ''}
                onChange={(e) => setSelectedResume(e.target.value ? Number(e.target.value) : null)}
                required
              >
                <option value="">-- Choisir un CV --</option>
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

            <div>
              <label className="label">
                Niveau d'études <span className="text-red-500">*</span>
              </label>
              <select
                className="input"
                value={educationLevel}
                onChange={(e) => setEducationLevel(e.target.value)}
                required
              >
                <option value="">-- Sélectionnez votre niveau --</option>
                {EDUCATION_LEVELS.map((lvl) => (
                  <option key={lvl} value={lvl}>{lvl}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="label">
                Téléphone <span className="text-red-500">*</span>
              </label>
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
                {phoneFromServer
                  ? 'Confirmez ou modifiez le numéro avant d\'envoyer.'
                  : 'Le recruteur s\'en servira pour vous contacter. Enregistré sur votre profil pour vos prochaines candidatures.'}
              </p>
            </div>

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
