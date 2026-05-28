import { useRef, useState } from 'react'
import { API_URL, getToken } from '@/utils/api'
import type { User } from '@/types'
import ErrorMessage from './ErrorMessage'

export default function PhotoUpload({ user, onUpdated }: { user: User; onUpdated: (u: User) => void }) {
  const [uploading, setUploading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const inputRef = useRef<HTMLInputElement>(null)

  const handleChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    setUploading(true)
    setError(null)
    try {
      const formData = new FormData()
      formData.append('photo', file)
      const res = await fetch(`${API_URL}/profile/photo`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${getToken()}` },
        body: formData,
      })
      if (!res.ok) throw new Error(`Erreur ${res.status}`)
      const data = await res.json()
      const updated = data.data ?? data
      localStorage.setItem('user', JSON.stringify(updated))
      onUpdated(updated)
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur lors de l'upload")
    } finally {
      setUploading(false)
      if (inputRef.current) inputRef.current.value = ''
    }
  }

  return (
    <div className="flex items-center gap-4">
      {user.photo ? (
        <img src={user.photo} className="w-20 h-20 rounded-full object-cover border-2 border-white shadow" alt={user.name} />
      ) : (
        <div className="w-20 h-20 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-3xl font-bold border-2 border-white shadow">
          {user.name.charAt(0).toUpperCase()}
        </div>
      )}
      <div>
        {error && <ErrorMessage message={error} />}
        <label className="btn-secondary cursor-pointer text-sm">
          {uploading ? 'Upload...' : 'Changer la photo'}
          <input ref={inputRef} type="file" accept="image/*" className="hidden" onChange={handleChange} disabled={uploading} />
        </label>
      </div>
    </div>
  )
}
