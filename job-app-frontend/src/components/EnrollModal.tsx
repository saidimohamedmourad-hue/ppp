import { useEffect, useState } from 'react'
import { apiFetch, getUser } from '@/utils/api'
import type { Resume, User } from '@/types'
import { EDUCATION_LEVELS } from '@/constants/education'
import ErrorMessage from './ErrorMessage'

interface Props {
  sessionId: number
  sessionTitle: string
  onClose: () => void
  onSuccess: () => void
}

/**
 * Confirms enrollment into a training session and captures the candidate's
 * phone if not already on profile. The backend requires phone the same way
 * the job-apply endpoint does — see TrainingApiController::apply().
 */
export default function EnrollModal({ sessionId, sessionTitle, onClose, onSuccess }: Props) {
  const cachedUser = getUser() as User | null
  const [phone, setPhone] = useState(cachedUser?.phone ?? '')
  const [phoneFromServer, setPhoneFromServer] = useState<string | null>(cachedUser?.phone ?? null)
  // CV optionnel pour une inscription à une formation.
  const [resumes, setResumes] = useState<Resume[]>([])
  const [selectedResume, setSelectedResume] = useState<number | null>(null)
  const [educationLevel, setEducationLevel] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  // Pull the live profile so a stale cache doesn't hide an existing phone
  // (or worse: ask the user to type one they already have). Also load the
  // candidate's resumes — optional for trainings, offered as a picker.
  useEffect(() => {
    apiFetch('me')
      .then((res) => {
        const u = (res as { data?: User }).data ?? (res as User)
        if (u?.phone && !phone) setPhone(u.phone)
        setPhoneFromServer(u?.phone ?? null)
        if (u) localStorage.setItem('user', JSON.stringify(u))
      })
      .catch(() => {})
    apiFetch('resumes')
      .then((res) => setResumes((res as { data?: Resume[] }).data ?? (res as Resume[])))
      .catch(() => setResumes([]))
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const handleConfirm = async () => {
    if (!educationLevel) {
      setError('Sélectionnez votre niveau d\'études.')
      return
    }
    if (phone.trim().length < 6) {
      setError("Indiquez un numéro de téléphone — l'école s'en servira pour vous contacter.")
      return
    }
    setSubmitting(true)
    setError(null)
    try {
      await apiFetch(`training-sessions/${sessionId}/apply`, {
        method: 'POST',
        body: JSON.stringify({
          phone: phone.trim(),
          education_level: educationLevel,
          // CV facultatif : envoyé seulement si choisi.
          ...(selectedResume ? { resume_id: selectedResume } : {}),
        }),
      })
      if (cachedUser && phone.trim() !== cachedUser.phone) {
        localStorage.setItem('user', JSON.stringify({ ...cachedUser, phone: phone.trim() }))
      }
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

          <div className="mb-4">
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

          <div className="mb-4">
            <label className="label">CV (optionnel)</label>
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
            <p className="text-xs text-gray-500 mt-1">
              Facultatif pour une formation. En ajouter un améliore l'analyse de votre profil.
            </p>
          </div>

          <div className="mb-4">
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
                ? 'Confirmez ou modifiez le numéro avant de vous inscrire.'
                : "L'école s'en servira pour vous contacter. Enregistré sur votre profil pour vos prochaines inscriptions."}
            </p>
          </div>

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
