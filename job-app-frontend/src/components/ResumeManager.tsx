import { useEffect, useRef, useState } from 'react'
import { apiFetch, API_URL, getToken } from '@/utils/api'
import type { Resume } from '@/types'
import LoadingSpinner from './LoadingSpinner'
import ErrorMessage from './ErrorMessage'
import EmptyState from './EmptyState'

export default function ResumeManager() {
  const [resumes, setResumes] = useState<Resume[]>([])
  const [loading, setLoading] = useState(true)
  const [uploading, setUploading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const inputRef = useRef<HTMLInputElement>(null)

  const load = () => {
    setLoading(true)
    apiFetch('resumes')
      .then((res) => setResumes(res.data ?? res))
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false))
  }

  useEffect(load, [])

  const handleUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    setUploading(true)
    setError(null)
    try {
      const formData = new FormData()
      formData.append('resume', file)
      const res = await fetch(`${API_URL}/resumes`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${getToken()}` },
        body: formData,
      })
      if (!res.ok) throw new Error(`Erreur ${res.status}`)
      load()
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur lors de l'upload")
    } finally {
      setUploading(false)
      if (inputRef.current) inputRef.current.value = ''
    }
  }

  const handleDelete = async (id: number) => {
    if (!confirm('Supprimer ce CV ?')) return
    try {
      await apiFetch(`resumes/${id}`, { method: 'DELETE' })
      setResumes((prev) => prev.filter((r) => r.id !== id))
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur lors de la suppression')
    }
  }

  return (
    <div>
      {error && <ErrorMessage message={error} />}
      <div className="flex items-center justify-between mb-4">
        <h3 className="font-semibold text-gray-700">Mes CVs ({resumes.length})</h3>
        <label className="btn-primary cursor-pointer text-sm">
          {uploading ? 'Upload...' : '+ Ajouter un CV'}
          <input ref={inputRef} type="file" accept=".pdf,.doc,.docx" className="hidden" onChange={handleUpload} disabled={uploading} />
        </label>
      </div>

      {loading ? (
        <LoadingSpinner />
      ) : resumes.length === 0 ? (
        <EmptyState title="Aucun CV" description="Ajoutez votre premier CV (PDF, DOC)." />
      ) : (
        <ul className="divide-y divide-gray-100">
          {resumes.map((r) => (
            <li key={r.id} className="flex items-center justify-between py-3 gap-3">
              <div className="flex items-center gap-2 min-w-0">
                <svg className="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <a href={r.url} target="_blank" rel="noreferrer" className="text-sm text-gray-800 hover:text-primary-600 truncate">
                  {r.filename}
                </a>
              </div>
              <button onClick={() => handleDelete(r.id)} className="shrink-0 text-xs text-red-500 hover:text-red-700">
                Supprimer
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
