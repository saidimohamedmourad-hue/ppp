import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { apiFetch } from '@/utils/api'

interface Inscription {
  id: number
  status: 'pending' | 'accepted' | 'rejected'
  is_waitlist?: boolean
  created_at: string
  user: { id: number; name: string; email: string; phone?: string | null }
  resume?: { url: string; filename: string }
}

interface SessionInfo {
  id: number
  title: string
  duration?: string
  start_date?: string
}

const STATUS_MAP = {
  pending:  { label: 'En attente', cls: 'd-status-pending' },
  accepted: { label: 'Confirmé ✓', cls: 'd-status-accepted' },
  rejected: { label: 'Refusé',     cls: 'd-status-rejected' },
}

export default function SessionApplicants() {
  const { sessionId } = useParams<{ sessionId: string }>()
  const [inscriptions, setInscriptions] = useState<Inscription[]>([])
  const [session, setSession] = useState<SessionInfo | null>(null)
  const [loading, setLoading] = useState(true)
  const [updating, setUpdating] = useState<number | null>(null)

  useEffect(() => {
    Promise.all([
      apiFetch(`school/training-sessions/${sessionId}/applicants`).catch(() => null),
      apiFetch(`school/training-sessions/${sessionId}`).catch(() => null),
    ]).then(([insRes, sessRes]) => {
      if (insRes) setInscriptions((insRes as { data?: Inscription[] }).data ?? (insRes as Inscription[]))
      if (sessRes) setSession((sessRes as { data?: SessionInfo }).data ?? (sessRes as SessionInfo))
    }).finally(() => setLoading(false))
  }, [sessionId])

  const updateStatus = async (inscriptionId: number, status: 'accepted' | 'rejected') => {
    setUpdating(inscriptionId)
    try {
      await apiFetch(`school/training-applications/${inscriptionId}/status`, {
        method: 'PUT',
        body: JSON.stringify({ status }),
      })
      setInscriptions(prev => prev.map(i => i.id === inscriptionId ? { ...i, status } : i))
    } catch {
      /* ignore */
    } finally {
      setUpdating(null)
    }
  }

  if (loading) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '60vh' }}>
        <div style={{ width: 32, height: 32, border: '3px solid rgba(79,255,176,0.15)', borderTopColor: '#4fffb0', borderRadius: '50%', animation: 'spin 0.8s linear infinite' }} />
        <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
      </div>
    )
  }

  const pending  = inscriptions.filter(i => i.status === 'pending').length
  const accepted = inscriptions.filter(i => i.status === 'accepted').length
  const rejected = inscriptions.filter(i => i.status === 'rejected').length

  return (
    <div>
      {/* Back + header */}
      <div style={{ marginBottom: 24 }}>
        <Link to="/dashboard/school/sessions" style={{ display: 'inline-flex', alignItems: 'center', gap: 6, color: 'var(--d-muted)', fontSize: 13, textDecoration: 'none', marginBottom: 16 }}>
          ← Retour aux formations
        </Link>
        <h2 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 24, color: 'var(--d-text)', marginBottom: 4 }}>
          {session?.title ?? `Formation #${sessionId}`}
        </h2>
        {session && (
          <p style={{ color: 'var(--d-muted2)', fontSize: 13 }}>
            {session.duration && `${session.duration}`}
            {session.start_date && ` · Début: ${new Date(session.start_date).toLocaleDateString('fr-DZ')}`}
          </p>
        )}
      </div>

      {/* Mini stats */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10, marginBottom: 20 }}>
        {[
          { icon: '⏳', bg: 'rgba(79,255,176,0.1)', val: pending,  lbl: 'En attente' },
          { icon: '✅', bg: 'rgba(74,222,128,0.1)', val: accepted, lbl: 'Confirmés' },
          { icon: '❌', bg: 'rgba(248,113,113,0.1)', val: rejected, lbl: 'Refusés' },
        ].map(s => (
          <div key={s.lbl} className="d-stat-card">
            <div className="d-stat-icon" style={{ background: s.bg, marginBottom: 8 }}>{s.icon}</div>
            <div className="d-stat-val">{s.val}</div>
            <div className="d-stat-lbl">{s.lbl}</div>
          </div>
        ))}
      </div>

      {/* Inscriptions list */}
      {inscriptions.length === 0 ? (
        <div className="d-card" style={{ textAlign: 'center', padding: '48px 0' }}>
          <div style={{ fontSize: 40, marginBottom: 14, opacity: 0.35 }}>👥</div>
          <p style={{ color: 'var(--d-muted)', fontSize: 14 }}>Aucune inscription pour cette formation.</p>
        </div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {inscriptions.map(ins => (
            <div key={ins.id} className="d-card" style={{ padding: '18px 20px' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                <div style={{ width: 44, height: 44, borderRadius: 12, background: 'rgba(79,255,176,0.12)', border: '1px solid rgba(79,255,176,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 17, fontWeight: 700, color: 'var(--d-gold)', flexShrink: 0, fontFamily: 'Inter, sans-serif' }}>
                  {ins.user.name.charAt(0).toUpperCase()}
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap', marginBottom: 4 }}>
                    <span style={{ fontSize: 14, fontWeight: 600, color: 'var(--d-text)' }}>{ins.user.name}</span>
                    <span className={STATUS_MAP[ins.status]?.cls ?? 'd-status-pending'}>{STATUS_MAP[ins.status]?.label}</span>
                    {ins.is_waitlist && (
                      <span style={{ fontSize: 10.5, fontWeight: 700, color: '#f0c45a', background: 'rgba(240,196,90,0.12)', border: '1px solid rgba(240,196,90,0.25)', padding: '2px 8px', borderRadius: 100 }}>
                        ⏳ Liste d'attente
                      </span>
                    )}
                  </div>
                  <div style={{ fontSize: 12, color: 'var(--d-muted)', display: 'flex', gap: 12, flexWrap: 'wrap', alignItems: 'center' }}>
                    <a href={`mailto:${ins.user.email}`} style={{ color: 'inherit', textDecoration: 'none' }}>✉ {ins.user.email}</a>
                    {ins.user.phone && (
                      <a href={`tel:${ins.user.phone}`} style={{ color: '#4fffb0', textDecoration: 'none', fontWeight: 500 }}>
                        📞 {ins.user.phone}
                      </a>
                    )}
                    <span style={{ opacity: 0.6 }}>· {new Date(ins.created_at).toLocaleDateString('fr-DZ')}</span>
                  </div>
                </div>
                <div style={{ display: 'flex', gap: 8, flexShrink: 0 }}>
                  {ins.resume && (
                    <a href={ins.resume.url} target="_blank" rel="noopener noreferrer" className="d-btn-ghost" style={{ textDecoration: 'none', fontSize: 13 }}>
                      📄 CV
                    </a>
                  )}
                  {ins.status !== 'accepted' && (
                    <button
                      onClick={() => updateStatus(ins.id, 'accepted')}
                      disabled={updating === ins.id}
                      style={{ padding: '7px 14px', borderRadius: 8, border: '1px solid rgba(74,222,128,0.3)', background: 'rgba(74,222,128,0.1)', color: 'var(--d-green)', fontSize: 13, cursor: 'pointer' }}
                    >
                      {updating === ins.id ? '...' : 'Confirmer'}
                    </button>
                  )}
                  {ins.status !== 'rejected' && (
                    <button
                      onClick={() => updateStatus(ins.id, 'rejected')}
                      disabled={updating === ins.id}
                      style={{ padding: '7px 14px', borderRadius: 8, border: '1px solid rgba(248,113,113,0.3)', background: 'rgba(248,113,113,0.08)', color: 'var(--d-red)', fontSize: 13, cursor: 'pointer' }}
                    >
                      {updating === ins.id ? '...' : 'Refuser'}
                    </button>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
