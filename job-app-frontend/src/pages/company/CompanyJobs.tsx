import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/utils/api'

interface JobCategory { id: string; name: string }

interface CompanyJob {
  id: string
  title: string
  location: string
  type: string
  salary?: number
  status?: string
  created_at: string
  applicants_count?: number
}

interface JobForm {
  title: string
  description: string
  location: string
  type: string
  salary: string
  jobCategoryId: string
}

const EMPTY_FORM: JobForm = {
  title: '', description: '', location: '', type: 'Full-time', salary: '', jobCategoryId: '',
}

const JOB_TYPES = ['Full-time', 'Contract', 'Remote', 'Hybrid']

export default function CompanyJobs() {
  const [jobs, setJobs] = useState<CompanyJob[]>([])
  const [categories, setCategories] = useState<JobCategory[]>([])
  const [loading, setLoading] = useState(true)
  const [showModal, setShowModal] = useState(false)
  const [saving, setSaving] = useState(false)
  const [deleteId, setDeleteId] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [form, setForm] = useState<JobForm>(EMPTY_FORM)

  const load = () => {
    setLoading(true)
    apiFetch('company/jobs')
      .then(res => setJobs((res as { data?: CompanyJob[] }).data ?? (res as CompanyJob[])))
      .catch(() => setJobs([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
    apiFetch('job-categories')
      .then(res => setCategories((res as { data?: JobCategory[] }).data ?? []))
      .catch(() => {})
  }, [])

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault()
    setSaving(true)
    setError(null)
    try {
      await apiFetch('company/jobs', {
        method: 'POST',
        body: JSON.stringify({
          title:          form.title,
          description:    form.description,
          location:       form.location,
          type:           form.type,
          salary:         form.salary ? Number(form.salary) : undefined,
          jobCategoryId:  form.jobCategoryId || undefined,
        }),
      })
      setShowModal(false)
      setForm(EMPTY_FORM)
      load()
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur lors de la création')
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async (id: string) => {
    if (!window.confirm('Supprimer cette offre ?')) return
    setDeleteId(id)
    try {
      await apiFetch(`company/jobs/${id}`, { method: 'DELETE' })
      setJobs(prev => prev.filter(j => j.id !== id))
    } catch { /* ignore */ } finally { setDeleteId(null) }
  }

  const f = (key: keyof JobForm) => (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) =>
    setForm(prev => ({ ...prev, [key]: e.target.value }))

  if (loading) return (
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '60vh' }}>
      <div style={{ width: 32, height: 32, border: '3px solid rgba(79,255,176,0.15)', borderTopColor: '#4fffb0', borderRadius: '50%', animation: 'spin 0.8s linear infinite' }} />
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )

  return (
    <div>
      {/* Header */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 24 }}>
        <div>
          <h2 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 24, color: 'var(--d-text)', marginBottom: 4 }}>Mes offres d'emploi</h2>
          <p style={{ color: 'var(--d-muted2)', fontSize: 13 }}>{jobs.length} offre(s) publiée(s)</p>
        </div>
        <button onClick={() => setShowModal(true)} className="d-btn-gold" style={{ border: 'none', cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: 6 }}>
          + Nouvelle offre
        </button>
      </div>

      {/* Jobs list */}
      {jobs.length === 0 ? (
        <div className="d-card" style={{ textAlign: 'center', padding: '48px 0' }}>
          <div style={{ fontSize: 40, marginBottom: 14, opacity: 0.35 }}>💼</div>
          <p style={{ color: 'var(--d-muted)', fontSize: 14, marginBottom: 20 }}>Aucune offre publiée pour l'instant.</p>
          <button onClick={() => setShowModal(true)} className="d-btn-gold" style={{ border: 'none', cursor: 'pointer' }}>
            Publier votre première offre
          </button>
        </div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {jobs.map(job => (
            <div key={job.id} className="d-card" style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 20px' }}>
              <div style={{ width: 42, height: 42, borderRadius: 11, background: 'rgba(79,255,176,0.1)', border: '1px solid rgba(79,255,176,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20, flexShrink: 0 }}>💼</div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 4 }}>
                  <div style={{ fontSize: 14, fontWeight: 600, color: 'var(--d-text)' }}>{job.title}</div>
                  {job.status && (
                    <span style={{ fontSize: 11, padding: '2px 8px', borderRadius: 100, background: job.status === 'open' ? 'rgba(79,255,176,0.1)' : 'rgba(255,255,255,0.06)', color: job.status === 'open' ? '#4fffb0' : 'var(--d-muted)' }}>
                      {job.status === 'open' ? 'Active' : job.status === 'draft' ? 'Brouillon' : 'Fermée'}
                    </span>
                  )}
                </div>
                <div style={{ fontSize: 12, color: 'var(--d-muted)' }}>
                  {job.location && `📍 ${job.location}`}
                  {job.type && ` · ${job.type}`}
                  {job.salary != null && ` · ${Number(job.salary).toLocaleString()} DA`}
                </div>
              </div>
              {job.applicants_count !== undefined && (
                <div style={{ textAlign: 'center', marginRight: 8 }}>
                  <div style={{ fontSize: 18, fontWeight: 700, color: 'var(--d-gold)' }}>{job.applicants_count}</div>
                  <div style={{ fontSize: 11, color: 'var(--d-muted)' }}>candidat(s)</div>
                </div>
              )}
              <div style={{ display: 'flex', gap: 8 }}>
                <Link to={`/dashboard/company/jobs/${job.id}/applicants`} className="d-btn-ghost" style={{ textDecoration: 'none', fontSize: 13 }}>
                  Voir candidatures
                </Link>
                <button onClick={() => handleDelete(job.id)} disabled={deleteId === job.id}
                  style={{ padding: '7px 14px', borderRadius: 8, border: '1px solid rgba(248,113,113,0.3)', background: 'rgba(248,113,113,0.08)', color: 'var(--d-red)', fontSize: 13, cursor: 'pointer' }}>
                  {deleteId === job.id ? '...' : 'Supprimer'}
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Create modal */}
      {showModal && (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.7)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: 20 }}>
          <div style={{ background: 'var(--d-surface)', border: '1px solid var(--d-border)', borderRadius: 20, padding: 32, width: '100%', maxWidth: 560, maxHeight: '90vh', overflowY: 'auto' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
              <h3 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 22, color: 'var(--d-text)' }}>Nouvelle offre d'emploi</h3>
              <button onClick={() => { setShowModal(false); setError(null) }} style={{ background: 'none', border: 'none', color: 'var(--d-muted)', fontSize: 20, cursor: 'pointer' }}>✕</button>
            </div>

            {error && (
              <div style={{ background: 'rgba(248,113,113,0.1)', border: '1px solid rgba(248,113,113,0.3)', borderRadius: 10, padding: '12px 16px', fontSize: 13, color: 'var(--d-red)', marginBottom: 16 }}>
                {error}
              </div>
            )}

            <form onSubmit={handleCreate} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
              <div>
                <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Titre du poste *</label>
                <input className="d-input" value={form.title} onChange={f('title')} required placeholder="Ex: Développeur Full Stack" />
              </div>

              <div>
                <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Description *</label>
                <textarea className="d-input" value={form.description} onChange={f('description')} required rows={5} placeholder="Décrivez le poste, les missions, le profil recherché..." style={{ resize: 'vertical', minHeight: 110 }} />
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                <div>
                  <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Lieu *</label>
                  <input className="d-input" value={form.location} onChange={f('location')} required placeholder="Ex: Alger, Oran..." />
                </div>
                <div>
                  <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Type de contrat *</label>
                  <select className="d-input" value={form.type} onChange={f('type')} required style={{ cursor: 'pointer' }}>
                    {JOB_TYPES.map(t => <option key={t} value={t}>{t}</option>)}
                  </select>
                </div>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                <div>
                  <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Salaire (DA) *</label>
                  <input type="number" className="d-input" value={form.salary} onChange={f('salary')} required min="0" placeholder="Ex: 80000" />
                </div>
                {categories.length > 0 && (
                  <div>
                    <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Catégorie *</label>
                    <select className="d-input" value={form.jobCategoryId} onChange={f('jobCategoryId')} required style={{ cursor: 'pointer' }}>
                      <option value="">Choisir une catégorie...</option>
                      {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                    </select>
                  </div>
                )}
              </div>

              <div style={{ display: 'flex', gap: 10, justifyContent: 'flex-end', marginTop: 8 }}>
                <button type="button" onClick={() => { setShowModal(false); setError(null) }} className="d-btn-ghost" style={{ border: 'none', cursor: 'pointer' }}>Annuler</button>
                <button type="submit" disabled={saving} className="d-btn-gold" style={{ border: 'none', cursor: saving ? 'not-allowed' : 'pointer', opacity: saving ? 0.7 : 1 }}>
                  {saving ? 'Publication...' : 'Publier l\'offre'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
