import { useState } from 'react'
import { apiFetch } from '@/utils/api'
import ErrorMessage from './ErrorMessage'

export default function PasswordForm() {
  const [current, setCurrent] = useState('')
  const [password, setPassword] = useState('')
  const [confirm, setConfirm] = useState('')
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [success, setSuccess] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (password !== confirm) { setError('Les mots de passe ne correspondent pas.'); return }
    setSaving(true)
    setError(null)
    setSuccess(false)
    try {
      await apiFetch('profile/password', {
        method: 'PUT',
        body: JSON.stringify({ current_password: current, password, password_confirmation: confirm }),
      })
      setSuccess(true)
      setCurrent(''); setPassword(''); setConfirm('')
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur')
    } finally {
      setSaving(false)
    }
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4 max-w-md">
      {error && <ErrorMessage message={error} />}
      {success && <div className="text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">Mot de passe modifié.</div>}
      <div>
        <label className="label">Mot de passe actuel</label>
        <input type="password" className="input" value={current} onChange={(e) => setCurrent(e.target.value)} required />
      </div>
      <div>
        <label className="label">Nouveau mot de passe</label>
        <input type="password" className="input" value={password} onChange={(e) => setPassword(e.target.value)} required minLength={8} />
      </div>
      <div>
        <label className="label">Confirmer le mot de passe</label>
        <input type="password" className="input" value={confirm} onChange={(e) => setConfirm(e.target.value)} required />
      </div>
      <button type="submit" disabled={saving} className="btn-primary">
        {saving ? 'Modification...' : 'Changer le mot de passe'}
      </button>
    </form>
  )
}
