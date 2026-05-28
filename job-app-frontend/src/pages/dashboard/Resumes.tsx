import { useEffect, useRef, useState } from 'react'
import { apiFetch, API_URL, getToken } from '@/utils/api'
import type { Resume } from '@/types'

export default function Resumes() {
  const [resumes, setResumes] = useState<Resume[]>([])
  const [loading, setLoading] = useState(true)
  const [uploading, setUploading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const inputRef = useRef<HTMLInputElement>(null)

  const load = () => {
    setLoading(true)
    apiFetch('resumes')
      .then(res => setResumes((res as { data?: Resume[] }).data ?? (res as Resume[])))
      .catch(e => setError((e as Error).message))
      .finally(() => setLoading(false))
  }
  useEffect(load, [])

  const handleUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]; if (!file) return
    setUploading(true); setError(null)
    try {
      const fd = new FormData(); fd.append('resume', file)
      const res = await fetch(`${API_URL}/resumes`, { method: 'POST', headers: { Authorization: `Bearer ${getToken()}` }, body: fd })
      if (!res.ok) throw new Error(`Erreur ${res.status}`)
      load()
    } catch (err) { setError((err as Error).message) }
    finally { setUploading(false); if (inputRef.current) inputRef.current.value = '' }
  }

  const del = async (id: number) => {
    if (!confirm('Supprimer ce CV ?')) return
    try { await apiFetch(`resumes/${id}`, { method: 'DELETE' }); setResumes(p => p.filter(r => r.id !== id)) }
    catch (err) { setError((err as Error).message) }
  }

  return (
    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
      {/* CV Upload */}
      <div className="d-card">
        <div className="d-card-title" style={{ marginBottom: 18 }}>Mon CV actuel</div>
        {error && <div style={{ color: 'var(--d-red)', fontSize: 13, marginBottom: 12 }}>{error}</div>}
        {loading ? (
          <div style={{ color: 'var(--d-muted)', fontSize: 13 }}>Chargement...</div>
        ) : resumes.length > 0 ? (
          <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 16 }}>
            {resumes.map(r => (
              <div key={r.id} style={{ background: 'var(--d-surface2)', border: '1px solid var(--d-border)', borderRadius: 12, padding: '14px 16px', display: 'flex', alignItems: 'center', gap: 14 }}>
                <span style={{ fontSize: 26 }}>📄</span>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <a href={r.url} target="_blank" rel="noreferrer" style={{ fontSize: 13, fontWeight: 500, color: 'var(--d-text)', textDecoration: 'none', display: 'block', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{r.filename}</a>
                  <div style={{ fontSize: 11, color: 'var(--d-muted)', marginTop: 2 }}>{new Date(r.created_at).toLocaleDateString('fr-FR')}</div>
                </div>
                <button onClick={() => del(r.id)} style={{ fontSize: 12, color: 'var(--d-red)', background: 'none', border: 'none', cursor: 'pointer', padding: '4px 8px', borderRadius: 6, fontFamily: 'Inter, sans-serif' }}>Supprimer</button>
              </div>
            ))}
          </div>
        ) : null}
        <label style={{ cursor: 'pointer' }}>
          <div style={{ border: '1.5px dashed var(--d-border2)', borderRadius: 14, padding: 28, textAlign: 'center', background: 'var(--d-surface2)', transition: 'all .2s', cursor: 'pointer' }}
            onMouseEnter={e => { e.currentTarget.style.borderColor = 'var(--d-gold)'; e.currentTarget.style.background = 'rgba(79,255,176,0.05)' }}
            onMouseLeave={e => { e.currentTarget.style.borderColor = 'var(--d-border2)'; e.currentTarget.style.background = 'var(--d-surface2)' }}
          >
            <div style={{ fontSize: 28, marginBottom: 10 }}>📤</div>
            <div style={{ fontSize: 13, color: 'var(--d-muted2)' }}>
              {uploading ? 'Upload en cours...' : <><span style={{ color: 'var(--d-gold)', fontWeight: 500 }}>Cliquez pour choisir</span> votre CV</>}
            </div>
            <div style={{ fontSize: 11, color: 'var(--d-muted)', marginTop: 6 }}>PDF, DOC, DOCX · Max 5MB</div>
          </div>
          <input ref={inputRef} type="file" accept=".pdf,.doc,.docx" style={{ display: 'none' }} onChange={handleUpload} disabled={uploading} />
        </label>
      </div>

      {/* Stats */}
      <div className="d-card">
        <div className="d-card-title" style={{ marginBottom: 18 }}>Statistiques CV</div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          {[
            { label: 'Téléchargements par recruteurs', val: 12, color: 'var(--d-gold)' },
            { label: 'Vues cette semaine', val: 8, color: 'var(--d-blue)' },
            { label: 'Score ATS (compatibilité)', val: '78%', color: 'var(--d-green)' },
          ].map(s => (
            <div key={s.label} style={{ background: 'var(--d-surface2)', borderRadius: 12, padding: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ color: 'var(--d-muted2)', fontSize: 13 }}>{s.label}</span>
              <span style={{ fontFamily: '"Instrument Serif", serif', fontSize: 24, color: s.color }}>{s.val}</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
