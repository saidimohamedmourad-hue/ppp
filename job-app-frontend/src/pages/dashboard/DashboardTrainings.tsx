import { useEffect, useState, type ReactNode } from 'react'
import { apiFetch, isLoggedIn, getUser } from '@/utils/api'
import type { User } from '@/types'
import { EDUCATION_LEVELS } from '@/constants/education'

interface RawTraining {
  id: string
  title: string
  location?: string
  trainingDate?: string
  endDate?: string
  salary?: number
  status?: string
  type?: 'en_ligne' | 'accelerer' | 'presentiel'
  cancellation_reason?: string | null
  maxParticipants?: number
  currentParticipants?: number
  is_full?: boolean
  min_education_level?: string | null
  description?: string
  startTime?: string
  endTime?: string
  viewCount?: number
  school?: { id: string; name: string }
  trainingCategory?: { id: string; name: string }
}

interface RawCategory { id: string; name: string }
interface RawResume { id: string; filename: string }

const TYPE_LABELS: Record<string, string> = { presentiel: 'Présentiel', en_ligne: 'En ligne', accelerer: 'Accéléré', longue_duree: 'Longue durée' }
const TYPE_COLORS: Record<string, string> = { presentiel: '#4fffb0', en_ligne: '#60a5fa', accelerer: '#f0c45a', longue_duree: '#a78bfa' }

function Spin() {
  return (
    <div style={{ display: 'flex', justifyContent: 'center', padding: '60px 0' }}>
      <div style={{ width: 32, height: 32, border: '3px solid rgba(79,255,176,0.15)', borderTopColor: '#4fffb0', borderRadius: '50%', animation: 'spin .8s linear infinite' }} />
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )
}

function EnrollModal({ training, onClose, onDone }: { training: RawTraining; onClose: () => void; onDone: () => void }) {
  const cachedUser = getUser() as User | null
  const [resumes, setResumes] = useState<RawResume[]>([])
  const [resumeId, setResumeId] = useState('')
  const [educationLevel, setEducationLevel] = useState('')
  const [cover, setCover] = useState('')
  // Phone must come along when the backend doesn't have one on the profile —
  // we always show the field (pre-filled if known) so the user can confirm.
  const [phone, setPhone] = useState(cachedUser?.phone ?? '')
  const [phoneFromServer, setPhoneFromServer] = useState<string | null>(cachedUser?.phone ?? null)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    apiFetch('resumes').then(r => setResumes((r as { data?: RawResume[] }).data ?? (r as RawResume[]))).catch(() => {}).finally(() => setLoading(false))
    // Re-fetch live user so a stale cache doesn't either hide the input or
    // pretend we have a phone we don't.
    apiFetch('me').then((res) => {
      const u = (res as { data?: User }).data ?? (res as User)
      if (u?.phone && !phone) setPhone(u.phone)
      setPhoneFromServer(u?.phone ?? null)
      if (u) localStorage.setItem('user', JSON.stringify(u))
    }).catch(() => {})
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const submit = async (e: React.FormEvent) => {
    e.preventDefault()
    // CV optionnel pour une formation ; niveau d'études obligatoire.
    if (!educationLevel) { setError('Sélectionnez votre niveau d\'études.'); return }
    if (phone.trim().length < 6) {
      setError("Indiquez un numéro de téléphone — l'école s'en servira pour vous contacter.")
      return
    }
    setSaving(true); setError(null)
    try {
      await apiFetch(`training-sessions/${training.id}/apply`, {
        method: 'POST',
        body: JSON.stringify({
          education_level: educationLevel,
          cover_letter: cover || undefined,
          phone: phone.trim(),
          ...(resumeId ? { resume_id: resumeId } : {}),
        }),
      })
      if (cachedUser && phone.trim() !== cachedUser.phone) {
        localStorage.setItem('user', JSON.stringify({ ...cachedUser, phone: phone.trim() }))
      }
      onDone(); onClose()
    } catch (err) { setError(err instanceof Error ? err.message : 'Erreur') }
    finally { setSaving(false) }
  }

  const spots = training.maxParticipants
    ? `${training.currentParticipants ?? 0}/${training.maxParticipants} places`
    : null

  return (
    <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.7)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: 20 }} onClick={onClose}>
      <div style={{ background: 'var(--d-surface)', border: '1px solid var(--d-border)', borderRadius: 20, padding: 32, width: '100%', maxWidth: 500 }} onClick={e => e.stopPropagation()}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
          <h3 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 20, color: 'var(--d-text)' }}>S'inscrire — {training.title}</h3>
          <button onClick={onClose} style={{ background: 'none', border: 'none', color: 'var(--d-muted)', fontSize: 20, cursor: 'pointer' }}>✕</button>
        </div>
        <p style={{ fontSize: 13, color: 'var(--d-muted2)', marginBottom: 16 }}>
          {training.school?.name && `${training.school.name}`}
          {spots && ` · ${spots}`}
        </p>
        {error && <div style={{ background: 'rgba(248,113,113,0.1)', border: '1px solid rgba(248,113,113,0.3)', borderRadius: 10, padding: '10px 14px', fontSize: 13, color: 'var(--d-red)', marginBottom: 14 }}>{error}</div>}
        {loading ? <Spin /> : (
          <form onSubmit={submit} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
            <div>
              <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Niveau d'études <span style={{ color: 'var(--d-red)' }}>*</span></label>
              <select className="d-input" value={educationLevel} onChange={e => setEducationLevel(e.target.value)} required>
                <option value="">-- Sélectionnez votre niveau --</option>
                {EDUCATION_LEVELS.map(lvl => <option key={lvl} value={lvl}>{lvl}</option>)}
              </select>
            </div>
            <div>
              <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>CV <span style={{ color: 'var(--d-muted2)', fontWeight: 400 }}>(optionnel)</span></label>
              {resumes.length === 0 ? (
                <div style={{ background: 'rgba(255,255,255,0.04)', border: '1px solid var(--d-border)', borderRadius: 10, padding: '10px 14px', fontSize: 12.5, color: 'var(--d-muted2)' }}>
                  Facultatif pour une formation. <a href="/dashboard/resumes" style={{ color: 'var(--d-gold)' }}>Ajouter un CV →</a>
                </div>
              ) : (
                <select className="d-input" value={resumeId} onChange={e => setResumeId(e.target.value)}>
                  <option value="">-- Aucun CV --</option>
                  {resumes.map(r => <option key={r.id} value={r.id}>{r.filename}</option>)}
                </select>
              )}
            </div>
            <div>
              <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>
                Téléphone <span style={{ color: '#f87171' }}>*</span>
              </label>
              <input
                type="tel"
                className="d-input"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
                placeholder="+213 555 123 456"
                autoComplete="tel"
                required
              />
              <p style={{ fontSize: 11, color: 'var(--d-muted2)', marginTop: 4 }}>
                {phoneFromServer
                  ? 'Confirmez ou modifiez le numéro avant de vous inscrire.'
                  : "L'école s'en servira pour vous contacter. Enregistré sur votre profil pour vos prochaines inscriptions."}
              </p>
            </div>
            <div>
              <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Lettre de motivation (optionnel)</label>
              <textarea className="d-input" rows={4} value={cover} onChange={e => setCover(e.target.value)} placeholder="Décrivez votre motivation pour cette formation..." style={{ resize: 'vertical' }} />
            </div>
            <div style={{ display: 'flex', gap: 10, justifyContent: 'flex-end' }}>
              <button type="button" onClick={onClose} className="d-btn-ghost" style={{ border: 'none', cursor: 'pointer' }}>Annuler</button>
              <button type="submit" disabled={saving} className="d-btn-gold" style={{ border: 'none', cursor: saving ? 'not-allowed' : 'pointer', opacity: saving ? 0.7 : 1 }}>
                {saving ? 'Inscription...' : "S'inscrire"}
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  )
}

// Modal de détails : reste sur la page (overlay) et compte une vue en
// appelant l'endpoint détail (qui incrémente viewCount côté serveur).
function TrainingDetailsModal({ id, onClose }: { id: string; onClose: () => void }) {
  const [t, setT] = useState<RawTraining | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    setLoading(true)
    apiFetch(`training-sessions/${id}`)
      .then(res => setT((res as { data?: RawTraining }).data ?? (res as RawTraining)))
      .catch(err => setError(err instanceof Error ? err.message : 'Erreur'))
      .finally(() => setLoading(false))
  }, [id])

  const Row = ({ label, value }: { label: string; value?: ReactNode }) =>
    value == null || value === '' ? null : (
      <div style={{ display: 'flex', gap: 10, fontSize: 13, padding: '6px 0', borderBottom: '1px solid var(--d-border)' }}>
        <span style={{ color: 'var(--d-muted)', minWidth: 130, flexShrink: 0 }}>{label}</span>
        <span style={{ color: 'var(--d-text)' }}>{value}</span>
      </div>
    )

  return (
    <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.7)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: 20 }} onClick={onClose}>
      <div style={{ background: 'var(--d-surface)', border: '1px solid var(--d-border)', borderRadius: 20, padding: 28, width: '100%', maxWidth: 560, maxHeight: '85vh', overflowY: 'auto' }} onClick={e => e.stopPropagation()}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 16, gap: 12 }}>
          <h3 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 22, color: 'var(--d-text)', margin: 0 }}>{t?.title ?? 'Détails de la formation'}</h3>
          <button onClick={onClose} style={{ background: 'none', border: 'none', color: 'var(--d-muted)', fontSize: 22, cursor: 'pointer', lineHeight: 1 }}>✕</button>
        </div>
        {loading ? <Spin /> : error ? (
          <div style={{ color: 'var(--d-red)', fontSize: 13 }}>{error}</div>
        ) : t && (
          <div>
            <Row label="École" value={t.school?.name} />
            <Row label="Type" value={t.type ? TYPE_LABELS[t.type] : undefined} />
            <Row label="Catégorie" value={t.trainingCategory?.name} />
            <Row label="Lieu" value={t.location} />
            <Row label="Date de début" value={t.trainingDate ? new Date(t.trainingDate).toLocaleDateString('fr-DZ') : undefined} />
            <Row label="Date de fin" value={t.endDate ? new Date(t.endDate).toLocaleDateString('fr-DZ') : undefined} />
            <Row label="Horaires" value={t.startTime && t.endTime ? `${t.startTime} → ${t.endTime}` : undefined} />
            <Row label="Participants" value={t.maxParticipants ? `${t.currentParticipants ?? 0} / ${t.maxParticipants}` : undefined} />
            <Row label="Prix" value={t.salary != null ? (t.salary === 0 ? 'Gratuit' : `${Number(t.salary).toLocaleString()} DA`) : undefined} />
            <Row label="Niveau min. requis" value={t.min_education_level ?? '—'} />
            <Row label="Statut" value={t.status} />
            {t.status === 'cancelled' && t.cancellation_reason && <Row label="Raison annulation" value={t.cancellation_reason} />}
            <Row label="Vues" value={t.viewCount} />
            {t.description && (
              <div style={{ marginTop: 14 }}>
                <div style={{ color: 'var(--d-muted)', fontSize: 13, marginBottom: 6 }}>Description</div>
                <div style={{ color: 'var(--d-text)', fontSize: 13.5, lineHeight: 1.6, whiteSpace: 'pre-wrap' }}>{t.description}</div>
              </div>
            )}
          </div>
        )}
        <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: 18 }}>
          <button onClick={onClose} className="d-btn-secondary" style={{ cursor: 'pointer' }}>Fermer</button>
        </div>
      </div>
    </div>
  )
}

export default function DashboardTrainings() {
  const [trainings, setTrainings] = useState<RawTraining[]>([])
  const [categories, setCategories] = useState<RawCategory[]>([])
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 })
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [searchInput, setSearchInput] = useState('')
  const [categoryId, setCategoryId] = useState('')
  const [type, setType] = useState<'' | 'en_ligne' | 'accelerer' | 'presentiel' | 'longue_duree'>('')
  const [page, setPage] = useState(1)
  const [enrollTraining, setEnrollTraining] = useState<RawTraining | null>(null)
  const [enrolledIds, setEnrolledIds] = useState<Set<string>>(new Set())
  const [detailsId, setDetailsId] = useState<string | null>(null)

  useEffect(() => {
    apiFetch('training-categories').then(r => setCategories((r as { data?: RawCategory[] }).data ?? (r as RawCategory[]))).catch(() => {})
  }, [])

  useEffect(() => {
    setLoading(true)
    const p = new URLSearchParams()
    if (search) p.set('search', search)
    if (categoryId) p.set('category', categoryId)
    if (type) p.set('type', type)
    p.set('page', String(page))
    apiFetch(`training-sessions?${p}`)
      .then((r: { data: RawTraining[]; current_page: number; last_page: number; total: number }) => {
        setTrainings(r.data ?? [])
        setMeta({ current_page: r.current_page ?? 1, last_page: r.last_page ?? 1, total: r.total ?? 0 })
      })
      .catch(() => setTrainings([]))
      .finally(() => setLoading(false))
  }, [search, categoryId, type, page])

  const resetPage = () => setPage(1)

  return (
    <div>
      {/* Search + filters */}
      <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap', marginBottom: 20, alignItems: 'center' }}>
        <div style={{ position: 'relative', flex: '1 1 260px', minWidth: 200 }}>
          <span style={{ position: 'absolute', left: 12, top: '50%', transform: 'translateY(-50%)', color: 'var(--d-muted)', fontSize: 14 }}>🔍</span>
          <input
            className="d-input"
            style={{ paddingLeft: 36 }}
            placeholder="Titre, école, mot-clé..."
            value={searchInput}
            onChange={e => setSearchInput(e.target.value)}
            onKeyDown={e => { if (e.key === 'Enter') { setSearch(searchInput); resetPage() } }}
          />
        </div>
        <select className="d-input" style={{ flex: '0 0 auto', width: 'auto', cursor: 'pointer' }} value={categoryId} onChange={e => { setCategoryId(e.target.value); resetPage() }}>
          <option value="">Toutes catégories</option>
          {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
        </select>
        <select className="d-input" style={{ flex: '0 0 auto', width: 'auto', cursor: 'pointer' }} value={type} onChange={e => { setType(e.target.value as typeof type); resetPage() }}>
          <option value="">Tous types</option>
          <option value="presentiel">Présentiel</option>
          <option value="en_ligne">En ligne</option>
          <option value="accelerer">Accéléré</option>
          <option value="longue_duree">Longue durée</option>
        </select>
        {(search || categoryId || type) && (
          <button onClick={() => { setSearch(''); setSearchInput(''); setCategoryId(''); setType(''); resetPage() }}
            className="d-btn-ghost" style={{ border: 'none', cursor: 'pointer', fontSize: 12 }}>✕ Effacer</button>
        )}
      </div>

      <div style={{ fontSize: 12, color: 'var(--d-muted)', marginBottom: 14 }}>
        {!loading && `${meta.total} formation(s) trouvée(s)`}
      </div>

      {/* List */}
      {loading ? <Spin /> : trainings.length === 0 ? (
        <div className="d-card" style={{ textAlign: 'center', padding: '48px 0' }}>
          <div style={{ fontSize: 36, marginBottom: 12, opacity: 0.3 }}>🎓</div>
          <p style={{ color: 'var(--d-muted)', fontSize: 13 }}>Aucune formation trouvée.</p>
        </div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {trainings.map(t => {
            const cancelled = t.status === 'cancelled'
            const tt = t.type ?? 'presentiel'
            const typeColor = TYPE_COLORS[tt]
            return (
              <div key={t.id} className="d-card" style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 20px', opacity: cancelled ? 0.7 : 1 }}>
                <div style={{ width: 44, height: 44, borderRadius: 12, background: `${typeColor}1a`, border: `1px solid ${typeColor}26`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20, flexShrink: 0 }}>🎓</div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap', marginBottom: 3 }}>
                    <span role="button" tabIndex={0} onClick={() => setDetailsId(t.id)}
                      style={{ fontSize: 14, fontWeight: 600, color: 'var(--d-text)', cursor: 'pointer' }}
                      onMouseOver={e => (e.currentTarget.style.textDecoration = 'underline')}
                      onMouseOut={e => (e.currentTarget.style.textDecoration = 'none')}
                      title="Voir les détails">{t.title}</span>
                    <span style={{ fontSize: 10.5, padding: '2px 8px', borderRadius: 100, background: `${typeColor}1a`, color: typeColor, fontWeight: 600 }}>{TYPE_LABELS[tt]}</span>
                    {cancelled && (
                      <span style={{ fontSize: 10.5, padding: '2px 8px', borderRadius: 100, background: 'rgba(239,68,68,0.12)', color: '#ef4444', fontWeight: 600 }}>Annulée</span>
                    )}
                    {!cancelled && t.is_full && (
                      <span style={{ fontSize: 10.5, padding: '2px 8px', borderRadius: 100, background: 'rgba(240,196,90,0.12)', color: '#f0c45a', fontWeight: 600 }}>⏳ Liste d'attente</span>
                    )}
                  </div>
                  <div style={{ fontSize: 12, color: 'var(--d-muted)' }}>
                    {t.school?.name}
                    {t.location ? ` · 📍 ${t.location}` : ''}
                    {t.trainingDate ? ` · Début : ${new Date(t.trainingDate).toLocaleDateString('fr-DZ')}` : ''}
                    {t.salary != null ? ` · ${t.salary === 0 ? 'Gratuit' : `${Number(t.salary).toLocaleString()} DA`}` : ''}
                    {t.min_education_level ? ` · 🎓 Niveau min : ${t.min_education_level}` : ''}
                  </div>
                  {cancelled && t.cancellation_reason && (
                    <div style={{ fontSize: 11.5, color: '#ef4444', marginTop: 4, fontStyle: 'italic' }}>Raison : {t.cancellation_reason}</div>
                  )}
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexShrink: 0 }}>
                  {t.maxParticipants && (
                    <span style={{ fontSize: 11, color: 'var(--d-muted)', whiteSpace: 'nowrap' }}>
                      {t.currentParticipants ?? 0}/{t.maxParticipants} places
                    </span>
                  )}
                  {t.trainingCategory?.name && (
                    <span style={{ fontSize: 11, padding: '3px 10px', borderRadius: 100, background: 'rgba(96,165,250,0.1)', color: 'var(--d-blue)' }}>{t.trainingCategory.name}</span>
                  )}
                  {isLoggedIn() && !cancelled && (
                    enrolledIds.has(t.id) ? (
                      <span style={{ fontSize: 12, color: 'var(--d-green)', padding: '7px 14px' }}>✓ Inscrit</span>
                    ) : (
                      <button onClick={() => setEnrollTraining(t)} className="d-btn-gold" style={{ border: 'none', cursor: 'pointer', fontSize: 13 }}>
                        {t.is_full ? "Liste d'attente" : "S'inscrire"}
                      </button>
                    )
                  )}
                </div>
              </div>
            )
          })}
        </div>
      )}

      {/* Pagination */}
      {meta.last_page > 1 && (
        <div style={{ display: 'flex', justifyContent: 'center', gap: 8, marginTop: 24 }}>
          {Array.from({ length: meta.last_page }, (_, i) => i + 1).map(p => (
            <button key={p} onClick={() => setPage(p)} style={{ width: 34, height: 34, borderRadius: 8, border: `1px solid ${meta.current_page === p ? 'var(--d-gold)' : 'var(--d-border)'}`, background: meta.current_page === p ? 'rgba(79,255,176,0.1)' : 'transparent', color: meta.current_page === p ? 'var(--d-gold)' : 'var(--d-muted)', fontSize: 13, cursor: 'pointer', fontFamily: 'Inter, sans-serif' }}>{p}</button>
          ))}
        </div>
      )}

      {/* Enroll modal */}
      {enrollTraining && (
        <EnrollModal
          training={enrollTraining}
          onClose={() => setEnrollTraining(null)}
          onDone={() => setEnrolledIds(prev => new Set([...prev, enrollTraining.id]))}
        />
      )}

      {/* Details modal — stays on the page, counts a view via the detail endpoint */}
      {detailsId && <TrainingDetailsModal id={detailsId} onClose={() => setDetailsId(null)} />}
    </div>
  )
}
