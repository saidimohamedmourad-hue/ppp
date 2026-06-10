import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/utils/api'
import { EDUCATION_LEVELS } from '@/constants/education'

interface TrainingCategory { id: string; name: string }

interface SchoolSession {
  id: string
  title: string
  trainingDate?: string
  endDate?: string
  location?: string
  salary?: number
  status?: string
  maxParticipants?: number
  currentParticipants?: number
  applicants_count?: number
}

interface SessionForm {
  title: string
  description: string
  location: string
  trainingDate: string
  endDate: string
  maxParticipants: string
  salary: string
  status: string
  type: string
  cancellation_reason: string
  trainingCategoryId: string
  min_education_level: string
}

const EMPTY_FORM: SessionForm = {
  title: '', description: '', location: '', trainingDate: '',
  endDate: '', maxParticipants: '', salary: '', status: 'open',
  type: 'presentiel', cancellation_reason: '',
  trainingCategoryId: '', min_education_level: '',
}

export default function SchoolTrainings() {
  const [sessions, setSessions] = useState<SchoolSession[]>([])
  const [categories, setCategories] = useState<TrainingCategory[]>([])
  const [loading, setLoading] = useState(true)
  const [showModal, setShowModal] = useState(false)
  const [saving, setSaving] = useState(false)
  const [deleteId, setDeleteId] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [form, setForm] = useState<SessionForm>(EMPTY_FORM)

  const load = () => {
    setLoading(true)
    apiFetch('school/training-sessions')
      .then(res => setSessions((res as { data?: SchoolSession[] }).data ?? (res as SchoolSession[])))
      .catch(() => setSessions([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
    apiFetch('training-categories')
      .then(res => setCategories((res as { data?: TrainingCategory[] }).data ?? []))
      .catch(() => {})
  }, [])

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault()
    setSaving(true)
    setError(null)
    try {
      await apiFetch('school/training-sessions', {
        method: 'POST',
        body: JSON.stringify({
          title:               form.title,
          description:         form.description,
          location:            form.location,
          trainingDate:        form.trainingDate || undefined,
          endDate:             form.endDate || undefined,
          maxParticipants:     form.maxParticipants ? Number(form.maxParticipants) : undefined,
          salary:              form.salary ? Number(form.salary) : undefined,
          status:              form.status,
          type:                form.type,
          cancellation_reason: form.status === 'cancelled' ? form.cancellation_reason : undefined,
          trainingCategoryId:  form.trainingCategoryId || undefined,
          min_education_level: form.min_education_level || undefined,
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
    if (!window.confirm('Supprimer cette formation ?')) return
    setDeleteId(id)
    try {
      await apiFetch(`school/training-sessions/${id}`, { method: 'DELETE' })
      setSessions(prev => prev.filter(s => s.id !== id))
    } catch { /* ignore */ } finally { setDeleteId(null) }
  }

  const f = (key: keyof SessionForm) => (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) =>
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
          <h2 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 24, color: 'var(--d-text)', marginBottom: 4 }}>Mes formations</h2>
          <p style={{ color: 'var(--d-muted2)', fontSize: 13 }}>{sessions.length} session(s) publiée(s)</p>
        </div>
        <button onClick={() => setShowModal(true)} className="d-btn-gold" style={{ border: 'none', cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: 6 }}>
          + Nouvelle formation
        </button>
      </div>

      {/* Sessions list */}
      {sessions.length === 0 ? (
        <div className="d-card" style={{ textAlign: 'center', padding: '48px 0' }}>
          <div style={{ fontSize: 40, marginBottom: 14, opacity: 0.35 }}>🎓</div>
          <p style={{ color: 'var(--d-muted)', fontSize: 14, marginBottom: 20 }}>Aucune formation publiée pour l'instant.</p>
          <button onClick={() => setShowModal(true)} className="d-btn-gold" style={{ border: 'none', cursor: 'pointer' }}>
            Créer votre première formation
          </button>
        </div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {sessions.map(session => (
            <div key={session.id} className="d-card" style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 20px' }}>
              <div style={{ width: 42, height: 42, borderRadius: 11, background: 'rgba(79,255,176,0.1)', border: '1px solid rgba(79,255,176,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20, flexShrink: 0 }}>🎓</div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 4 }}>
                  <div style={{ fontSize: 14, fontWeight: 600, color: 'var(--d-text)' }}>{session.title}</div>
                  {session.status && (
                    <span style={{ fontSize: 11, padding: '2px 8px', borderRadius: 100, background: session.status === 'open' ? 'rgba(79,255,176,0.1)' : 'rgba(255,255,255,0.06)', color: session.status === 'open' ? '#4fffb0' : 'var(--d-muted)' }}>
                      {session.status === 'open' ? 'Ouverte' : session.status === 'closed' ? 'Fermée' : 'Annulée'}
                    </span>
                  )}
                </div>
                <div style={{ fontSize: 12, color: 'var(--d-muted)' }}>
                  {session.location && `📍 ${session.location}`}
                  {session.trainingDate && ` · Début : ${new Date(session.trainingDate).toLocaleDateString('fr-DZ')}`}
                  {session.salary != null && ` · ${Number(session.salary).toLocaleString()} DA`}
                  {session.maxParticipants && ` · ${session.currentParticipants ?? 0}/${session.maxParticipants} places`}
                </div>
              </div>
              <div style={{ display: 'flex', gap: 8 }}>
                <Link to={`/dashboard/school/sessions/${session.id}/applicants`} className="d-btn-ghost" style={{ textDecoration: 'none', fontSize: 13 }}>
                  Voir inscrits
                </Link>
                <button onClick={() => handleDelete(session.id)} disabled={deleteId === session.id}
                  style={{ padding: '7px 14px', borderRadius: 8, border: '1px solid rgba(248,113,113,0.3)', background: 'rgba(248,113,113,0.08)', color: 'var(--d-red)', fontSize: 13, cursor: 'pointer' }}>
                  {deleteId === session.id ? '...' : 'Supprimer'}
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
              <h3 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 22, color: 'var(--d-text)' }}>Nouvelle formation</h3>
              <button onClick={() => { setShowModal(false); setError(null) }} style={{ background: 'none', border: 'none', color: 'var(--d-muted)', fontSize: 20, cursor: 'pointer' }}>✕</button>
            </div>

            {error && (
              <div style={{ background: 'rgba(248,113,113,0.1)', border: '1px solid rgba(248,113,113,0.3)', borderRadius: 10, padding: '12px 16px', fontSize: 13, color: 'var(--d-red)', marginBottom: 16 }}>
                {error}
              </div>
            )}

            <form onSubmit={handleCreate} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
              <div>
                <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Titre *</label>
                <input className="d-input" value={form.title} onChange={f('title')} required placeholder="Ex: Développement Web Full Stack" />
              </div>

              <div>
                <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Description *</label>
                <textarea className="d-input" value={form.description} onChange={f('description')} required rows={4} placeholder="Contenu, objectifs, public cible..." style={{ resize: 'vertical', minHeight: 90 }} />
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                <div>
                  <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Lieu *</label>
                  <input className="d-input" value={form.location} onChange={f('location')} required placeholder="Ex: Alger, Centre-ville" />
                </div>
                <div>
                  <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Type *</label>
                  <select className="d-input" value={form.type} onChange={f('type')} required>
                    <option value="presentiel">Présentiel</option>
                    <option value="en_ligne">En ligne</option>
                    <option value="accelerer">Accéléré</option>
                    <option value="longue_duree">Longue durée</option>
                  </select>
                </div>
              </div>

              <div>
                <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Niveau d'études minimum requis *</label>
                <select className="d-input" value={form.min_education_level} onChange={f('min_education_level')} required>
                  <option value="">-- Sélectionnez --</option>
                  {EDUCATION_LEVELS.map((lvl) => (
                    <option key={lvl} value={lvl}>{lvl}</option>
                  ))}
                </select>
              </div>

              <div>
                <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Statut *</label>
                <select className="d-input" value={form.status} onChange={f('status')} required>
                  <option value="open">Ouverte</option>
                  <option value="closed">Fermée (cachée)</option>
                  <option value="cancelled">Annulée</option>
                </select>
              </div>

              {form.status === 'cancelled' && (
                <div>
                  <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Raison de l'annulation *</label>
                  <textarea className="d-input" value={form.cancellation_reason} onChange={f('cancellation_reason')} required rows={2} placeholder="Ex: Effectif insuffisant, formateur indisponible..." style={{ resize: 'vertical' }} />
                </div>
              )}

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                <div>
                  <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Date de début *</label>
                  <input type="date" className="d-input" value={form.trainingDate} onChange={f('trainingDate')} required />
                </div>
                <div>
                  <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Date de fin</label>
                  <input type="date" className="d-input" value={form.endDate} onChange={f('endDate')} />
                </div>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                <div>
                  <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Nb. places max *</label>
                  <input type="number" className="d-input" value={form.maxParticipants} onChange={f('maxParticipants')} required min="1" placeholder="Ex: 20" />
                </div>
                <div>
                  <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Prix (DA)</label>
                  <input type="number" className="d-input" value={form.salary} onChange={f('salary')} min="0" placeholder="0 = Gratuit" />
                </div>
              </div>

              {categories.length > 0 && (
                <div>
                  <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Catégorie *</label>
                  <select className="d-input" value={form.trainingCategoryId} onChange={f('trainingCategoryId')} required>
                    <option value="">Choisir une catégorie...</option>
                    {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                  </select>
                </div>
              )}

              <div style={{ display: 'flex', gap: 10, justifyContent: 'flex-end', marginTop: 8 }}>
                <button type="button" onClick={() => { setShowModal(false); setError(null) }} className="d-btn-ghost" style={{ border: 'none', cursor: 'pointer' }}>Annuler</button>
                <button type="submit" disabled={saving} className="d-btn-gold" style={{ border: 'none', cursor: saving ? 'not-allowed' : 'pointer', opacity: saving ? 0.7 : 1 }}>
                  {saving ? 'Création...' : 'Créer la formation'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
