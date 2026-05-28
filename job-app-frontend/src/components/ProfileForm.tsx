import { useState } from 'react'
import { apiFetch } from '@/utils/api'
import type { User } from '@/types'
import ErrorMessage from './ErrorMessage'

export default function ProfileForm({ user, onUpdated }: { user: User; onUpdated: (u: User) => void }) {
  const [name, setName] = useState(user.name)
  const [email, setEmail] = useState(user.email)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [success, setSuccess] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setSaving(true)
    setError(null)
    setSuccess(false)
    try {
      const res = await apiFetch('profile', {
        method: 'PUT',
        body: JSON.stringify({ name, email }),
      })
      const updated = res.data ?? res
      localStorage.setItem('user', JSON.stringify(updated))
      onUpdated(updated)
      setSuccess(true)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur lors de la mise à jour')
    } finally {
      setSaving(false)
    }
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4 max-w-md">
      {error && <ErrorMessage message={error} />}
      {success && <div className="text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">Profil mis à jour.</div>}
      <div>
        <label className="label">Nom complet</label>
        <input className="input" value={name} onChange={(e) => setName(e.target.value)} required />
      </div>
      <div>
        <label className="label">Adresse e-mail</label>
        <input type="email" className="input" value={email} onChange={(e) => setEmail(e.target.value)} required />
      </div>
      <button type="submit" disabled={saving} className="btn-primary">
        {saving ? 'Enregistrement...' : 'Enregistrer'}
      </button>
    </form>
  )
}
