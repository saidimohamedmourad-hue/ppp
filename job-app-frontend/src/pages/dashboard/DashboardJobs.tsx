import { useEffect, useState, type ReactNode } from 'react'
import { apiFetch, isLoggedIn, getUser } from '@/utils/api'
import type { User } from '@/types'
import { EDUCATION_LEVELS } from '@/constants/education'

interface RawJob {
  id: string
  title: string
  location?: string
  type?: string
  salary?: number
  description?: string
  viewCount?: number
  company?: { id: string; name: string }
  jobCategory?: { id: string; name: string }
}

interface RawCategory { id: string; name: string }
interface RawResume { id: string; filename: string }

const JOB_TYPES = ['Full-time', 'Contract', 'Remote', 'Hybrid']

function Spin() {
  return (
    <div style={{ display: 'flex', justifyContent: 'center', padding: '60px 0' }}>
      <div style={{ width: 32, height: 32, border: '3px solid rgba(79,255,176,0.15)', borderTopColor: '#4fffb0', borderRadius: '50%', animation: 'spin .8s linear infinite' }} />
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )
}

function ApplyModal({ job, onClose, onDone }: { job: RawJob; onClose: () => void; onDone: () => void }) {
  const cachedUser = getUser() as User | null
  const [resumes, setResumes] = useState<RawResume[]>([])
  const [resumeId, setResumeId] = useState('')
  const [educationLevel, setEducationLevel] = useState('')
  const [cover, setCover] = useState('')
  // Phone is mandatory on the first job application — keep the input visible
  // even after we've cached one, so the candidate can update an outdated
  // number before posting. Stale localStorage was eating the input before.
  const [phone, setPhone] = useState(cachedUser?.phone ?? '')
  const [phoneFromServer, setPhoneFromServer] = useState<string | null>(cachedUser?.phone ?? null)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    apiFetch('resumes').then(r => setResumes((r as { data?: RawResume[] }).data ?? (r as RawResume[]))).catch(() => {}).finally(() => setLoading(false))
    // Re-sync the cached profile so phoneFromServer reflects the real state
    // (the cache may predate the introduction of the phone column).
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
    if (!resumeId) { setError('Veuillez sélectionner un CV.'); return }
    if (!educationLevel) { setError('Sélectionnez votre niveau d\'études.'); return }
    if (phone.trim().length < 6) {
      setError("Indiquez un numéro de téléphone — les recruteurs s'en serviront pour vous contacter.")
      return
    }
    setSaving(true); setError(null)
    try {
      await apiFetch(`jobs/${job.id}/apply`, {
        method: 'POST',
        body: JSON.stringify({ resume_id: resumeId, education_level: educationLevel, cover_letter: cover || undefined, phone: phone.trim() }),
      })
      if (cachedUser && phone.trim() !== cachedUser.phone) {
        localStorage.setItem('user', JSON.stringify({ ...cachedUser, phone: phone.trim() }))
      }
      onDone(); onClose()
    } catch (err) { setError(err instanceof Error ? err.message : 'Erreur') }
    finally { setSaving(false) }
  }

  return (
    <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.7)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: 20 }} onClick={onClose}>
      <div style={{ background: 'var(--d-surface)', border: '1px solid var(--d-border)', borderRadius: 20, padding: 32, width: '100%', maxWidth: 500 }} onClick={e => e.stopPropagation()}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
          <h3 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 20, color: 'var(--d-text)' }}>Postuler — {job.title}</h3>
          <button onClick={onClose} style={{ background: 'none', border: 'none', color: 'var(--d-muted)', fontSize: 20, cursor: 'pointer' }}>✕</button>
        </div>
        {error && <div style={{ background: 'rgba(248,113,113,0.1)', border: '1px solid rgba(248,113,113,0.3)', borderRadius: 10, padding: '10px 14px', fontSize: 13, color: 'var(--d-red)', marginBottom: 14 }}>{error}</div>}
        {loading ? <Spin /> : (
          <form onSubmit={submit} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
            <div>
              <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>CV <span style={{ color: 'var(--d-red)' }}>*</span></label>
              {resumes.length === 0 ? (
                <div style={{ background: 'rgba(248,113,113,0.07)', border: '1px solid rgba(248,113,113,0.25)', borderRadius: 10, padding: '10px 14px', fontSize: 12.5, color: 'var(--d-muted2)' }}>
                  Vous n'avez pas encore de CV. <a href="/dashboard/resumes" style={{ color: 'var(--d-gold)' }}>En ajouter un →</a>
                </div>
              ) : (
                <select className="d-input" value={resumeId} onChange={e => setResumeId(e.target.value)}>
                  <option value="">-- Choisir un CV --</option>
                  {resumes.map(r => <option key={r.id} value={r.id}>{r.filename}</option>)}
                </select>
              )}
            </div>
            <div>
              <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Niveau d'études <span style={{ color: 'var(--d-red)' }}>*</span></label>
              <select className="d-input" value={educationLevel} onChange={e => setEducationLevel(e.target.value)} required>
                <option value="">-- Sélectionnez votre niveau --</option>
                {EDUCATION_LEVELS.map(lvl => <option key={lvl} value={lvl}>{lvl}</option>)}
              </select>
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
                  ? 'Confirmez ou modifiez le numéro avant d\'envoyer.'
                  : 'Le recruteur s\'en servira pour vous contacter. Enregistré sur votre profil pour vos prochaines candidatures.'}
              </p>
            </div>
            <div>
              <label style={{ fontSize: 12, color: 'var(--d-muted)', fontWeight: 500, display: 'block', marginBottom: 6 }}>Lettre de motivation (optionnel)</label>
              <textarea className="d-input" rows={4} value={cover} onChange={e => setCover(e.target.value)} placeholder="Décrivez votre motivation..." style={{ resize: 'vertical' }} />
            </div>
            <div style={{ display: 'flex', gap: 10, justifyContent: 'flex-end', marginTop: 4 }}>
              <button type="button" onClick={onClose} className="d-btn-ghost" style={{ border: 'none', cursor: 'pointer' }}>Annuler</button>
              <button type="submit" disabled={saving} className="d-btn-gold" style={{ border: 'none', cursor: saving ? 'not-allowed' : 'pointer', opacity: saving ? 0.7 : 1 }}>
                {saving ? 'Envoi...' : 'Envoyer ma candidature'}
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
function JobDetailsModal({ id, onClose }: { id: string; onClose: () => void }) {
  const [job, setJob] = useState<RawJob | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    setLoading(true)
    apiFetch(`jobs/${id}`)
      .then(res => setJob((res as { data?: RawJob }).data ?? (res as RawJob)))
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
          <h3 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 22, color: 'var(--d-text)', margin: 0 }}>{job?.title ?? 'Détails de l\'offre'}</h3>
          <button onClick={onClose} style={{ background: 'none', border: 'none', color: 'var(--d-muted)', fontSize: 22, cursor: 'pointer', lineHeight: 1 }}>✕</button>
        </div>
        {loading ? <Spin /> : error ? (
          <div style={{ color: 'var(--d-red)', fontSize: 13 }}>{error}</div>
        ) : job && (
          <div>
            <Row label="Entreprise" value={job.company?.name} />
            <Row label="Type" value={job.type} />
            <Row label="Catégorie" value={job.jobCategory?.name} />
            <Row label="Lieu" value={job.location} />
            <Row label="Salaire" value={job.salary != null ? `${Number(job.salary).toLocaleString()} DA` : undefined} />
            <Row label="Vues" value={job.viewCount} />
            {job.description && (
              <div style={{ marginTop: 14 }}>
                <div style={{ color: 'var(--d-muted)', fontSize: 13, marginBottom: 6 }}>Description</div>
                <div style={{ color: 'var(--d-text)', fontSize: 13.5, lineHeight: 1.6, whiteSpace: 'pre-wrap' }}>{job.description}</div>
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

export default function DashboardJobs() {
  const [jobs, setJobs] = useState<RawJob[]>([])
  const [categories, setCategories] = useState<RawCategory[]>([])
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 })
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [searchInput, setSearchInput] = useState('')
  const [categoryId, setCategoryId] = useState('')
  const [jobType, setJobType] = useState('')
  const [page, setPage] = useState(1)
  const [applyJob, setApplyJob] = useState<RawJob | null>(null)
  const [detailsId, setDetailsId] = useState<string | null>(null)
  const [appliedIds, setAppliedIds] = useState<Set<string>>(new Set())

  useEffect(() => {
    apiFetch('job-categories').then(r => setCategories((r as { data?: RawCategory[] }).data ?? (r as RawCategory[]))).catch(() => {})
  }, [])

  useEffect(() => {
    setLoading(true)
    const p = new URLSearchParams()
    if (search) p.set('search', search)
    if (categoryId) p.set('category', categoryId)
    if (jobType) p.set('type', jobType)
    p.set('page', String(page))
    apiFetch(`jobs?${p}`)
      .then((r: { data: RawJob[]; current_page: number; last_page: number; total: number }) => {
        setJobs(r.data ?? [])
        setMeta({ current_page: r.current_page ?? 1, last_page: r.last_page ?? 1, total: r.total ?? 0 })
      })
      .catch(() => setJobs([]))
      .finally(() => setLoading(false))
  }, [search, categoryId, jobType, page])

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
            placeholder="Titre, mot-clé..."
            value={searchInput}
            onChange={e => setSearchInput(e.target.value)}
            onKeyDown={e => { if (e.key === 'Enter') { setSearch(searchInput); resetPage() } }}
          />
        </div>
        <select className="d-input" style={{ flex: '0 0 auto', width: 'auto', cursor: 'pointer' }} value={categoryId} onChange={e => { setCategoryId(e.target.value); resetPage() }}>
          <option value="">Toutes catégories</option>
          {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
        </select>
        <select className="d-input" style={{ flex: '0 0 auto', width: 'auto', cursor: 'pointer' }} value={jobType} onChange={e => { setJobType(e.target.value); resetPage() }}>
          <option value="">Tous types</option>
          {JOB_TYPES.map(t => <option key={t} value={t}>{t}</option>)}
        </select>
        {(search || categoryId || jobType) && (
          <button onClick={() => { setSearch(''); setSearchInput(''); setCategoryId(''); setJobType(''); resetPage() }}
            className="d-btn-ghost" style={{ border: 'none', cursor: 'pointer', fontSize: 12 }}>✕ Effacer</button>
        )}
      </div>

      {/* Info */}
      <div style={{ fontSize: 12, color: 'var(--d-muted)', marginBottom: 14 }}>
        {!loading && `${meta.total} offre(s) trouvée(s)`}
      </div>

      {/* List */}
      {loading ? <Spin /> : jobs.length === 0 ? (
        <div className="d-card" style={{ textAlign: 'center', padding: '48px 0' }}>
          <div style={{ fontSize: 36, marginBottom: 12, opacity: 0.3 }}>💼</div>
          <p style={{ color: 'var(--d-muted)', fontSize: 13 }}>Aucune offre trouvée.</p>
        </div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {jobs.map(job => (
            <div key={job.id} className="d-card" style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 20px' }}>
              <div style={{ width: 44, height: 44, borderRadius: 12, background: 'rgba(79,255,176,0.08)', border: '1px solid rgba(79,255,176,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20, flexShrink: 0 }}>🏢</div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ marginBottom: 3 }}>
                  <span role="button" tabIndex={0} onClick={() => setDetailsId(job.id)}
                    style={{ fontSize: 14, fontWeight: 600, color: 'var(--d-text)', cursor: 'pointer' }}
                    onMouseOver={e => (e.currentTarget.style.textDecoration = 'underline')}
                    onMouseOut={e => (e.currentTarget.style.textDecoration = 'none')}
                    title="Voir les détails">{job.title}</span>
                </div>
                <div style={{ fontSize: 12, color: 'var(--d-muted)' }}>
                  {job.company?.name}{job.location ? ` · 📍 ${job.location}` : ''}
                  {job.salary != null ? ` · ${Number(job.salary).toLocaleString()} DA` : ''}
                </div>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexShrink: 0 }}>
                {job.type && (
                  <span style={{ fontSize: 11, padding: '3px 10px', borderRadius: 100, background: 'rgba(79,255,176,0.1)', color: 'var(--d-gold)' }}>{job.type}</span>
                )}
                {isLoggedIn() && (
                  appliedIds.has(job.id) ? (
                    <span style={{ fontSize: 12, color: 'var(--d-green)', padding: '7px 14px' }}>✓ Postulé</span>
                  ) : (
                    <button onClick={() => setApplyJob(job)} className="d-btn-gold" style={{ border: 'none', cursor: 'pointer', fontSize: 13 }}>Postuler</button>
                  )
                )}
              </div>
            </div>
          ))}
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

      {/* Apply modal */}
      {applyJob && (
        <ApplyModal
          job={applyJob}
          onClose={() => setApplyJob(null)}
          onDone={() => setAppliedIds(prev => new Set([...prev, applyJob.id]))}
        />
      )}

      {/* Details modal — stays on the page, counts a view via the detail endpoint */}
      {detailsId && <JobDetailsModal id={detailsId} onClose={() => setDetailsId(null)} />}
    </div>
  )
}
