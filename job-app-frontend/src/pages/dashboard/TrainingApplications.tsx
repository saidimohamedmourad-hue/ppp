import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/utils/api'

interface RawTrainingApp {
  id: string
  status: 'pending' | 'accepted' | 'rejected' | 'reviewed'
  is_waitlist?: boolean
  created_at: string
  aiGeneratedScore: number
  aiGeneratedFeedback: string
  training_session: { id: string; title: string; school?: { name: string } }
}

const S_MAP: Record<string, { label: string; cls: string }> = {
  pending:  { label: 'En attente', cls: 'd-status-pending' },
  reviewed: { label: 'Vue ✓',     cls: 'd-status-pending' },
  accepted: { label: 'Confirmé ✓', cls: 'd-status-accepted' },
  rejected: { label: 'Refusé',     cls: 'd-status-rejected' },
}

function scoreColor(score: number) {
  if (score >= 70) return '#4fffb0'
  if (score >= 45) return '#f0c45a'
  return '#f87171'
}

function ScoreCircle({ score }: { score: number }) {
  const r = 22
  const circ = 2 * Math.PI * r
  const offset = circ - (score / 100) * circ
  const color = scoreColor(score)
  return (
    <div style={{ position: 'relative', width: 56, height: 56, flexShrink: 0 }}>
      <svg width="56" height="56" viewBox="0 0 56 56">
        <circle cx="28" cy="28" r={r} fill="none" stroke="var(--d-surface2)" strokeWidth="5" />
        <circle cx="28" cy="28" r={r} fill="none" stroke={color} strokeWidth="5"
          strokeDasharray={circ} strokeDashoffset={offset}
          strokeLinecap="round" transform="rotate(-90 28 28)"
          style={{ transition: 'stroke-dashoffset 1s ease' }}
        />
      </svg>
      <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 12, fontWeight: 700, color }}>
        {score}
      </div>
    </div>
  )
}

export default function TrainingApplications() {
  const [apps, setApps] = useState<RawTrainingApp[]>([])
  const [loading, setLoading] = useState(true)
  const [withdrawingId, setWithdrawingId] = useState<string | null>(null)
  const [expandedId, setExpandedId] = useState<string | null>(null)

  useEffect(() => {
    apiFetch('my/training-applications')
      .then(res => setApps((res as { data?: RawTrainingApp[] }).data ?? (res as RawTrainingApp[])))
      .catch(() => setApps([]))
      .finally(() => setLoading(false))
  }, [])

  const withdraw = async (id: string) => {
    if (!window.confirm('Annuler cette inscription ?')) return
    setWithdrawingId(id)
    try {
      await apiFetch(`my/training-applications/${id}`, { method: 'DELETE' })
      setApps(prev => prev.filter(a => a.id !== id))
    } catch { /* ignore */ } finally { setWithdrawingId(null) }
  }

  return (
    <div className="d-card">
      <div className="d-card-title" style={{ marginBottom: 16 }}>Mes formations inscrites</div>
      {loading ? (
        <div style={{ textAlign: 'center', padding: '40px 0', color: 'var(--d-muted)', fontSize: 13 }}>Chargement...</div>
      ) : apps.length === 0 ? (
        <div style={{ textAlign: 'center', padding: '48px 0', color: 'var(--d-muted)' }}>
          <div style={{ fontSize: 36, marginBottom: 12, opacity: 0.4 }}>🎓</div>
          <p style={{ fontSize: 13 }}>Aucune inscription pour l'instant.</p>
          <Link to="/dashboard/trainings" className="d-btn-gold" style={{ display: 'inline-block', marginTop: 16, textDecoration: 'none' }}>Voir les formations</Link>
        </div>
      ) : (
        apps.map(app => {
          const noCv      = app.aiGeneratedFeedback === '__no_cv__'
          const hasScore  = app.aiGeneratedScore > 0
          const hasFeedback = !!app.aiGeneratedFeedback && !noCv && app.aiGeneratedFeedback !== 'Une erreur est survenue lors de l\'analyse IA.'
          const isExpanded = expandedId === app.id
          return (
            <div key={app.id} style={{ borderBottom: '1px solid var(--d-border)' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '14px 0' }}>
                <div style={{ width: 42, height: 42, borderRadius: 10, background: 'rgba(96,165,250,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20, flexShrink: 0 }}>🎓</div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--d-text)', display: 'flex', alignItems: 'center', flexWrap: 'wrap', gap: 6 }}>
                    {app.training_session?.title ?? '—'}
                    {app.is_waitlist && (
                      <span style={{ fontSize: 10, fontWeight: 700, color: '#f0c45a', background: 'rgba(240,196,90,0.12)', border: '1px solid rgba(240,196,90,0.25)', padding: '2px 8px', borderRadius: 100 }}>
                        ⏳ Liste d'attente
                      </span>
                    )}
                  </div>
                  <div style={{ fontSize: 11, color: 'var(--d-muted)', marginTop: 2 }}>{app.training_session?.school?.name}</div>
                </div>

                {/* AI Score */}
                {hasScore ? (
                  <ScoreCircle score={app.aiGeneratedScore} />
                ) : noCv ? (
                  <div style={{ fontSize: 11, color: 'var(--d-muted)', textAlign: 'center', width: 56, flexShrink: 0 }}>
                    <div style={{ fontSize: 16, marginBottom: 2 }}>📄</div>
                    <div>Sans CV</div>
                  </div>
                ) : (
                  <div style={{ fontSize: 11, color: 'var(--d-muted)', textAlign: 'center', width: 56, flexShrink: 0 }}>
                    <div style={{ fontSize: 16, marginBottom: 2 }}>⏳</div>
                    <div>Analyse...</div>
                  </div>
                )}

                <div style={{ textAlign: 'right' }}>
                  <span className={S_MAP[app.status]?.cls ?? 'd-status-pending'}>{S_MAP[app.status]?.label}</span>
                  <div style={{ fontSize: 11, color: 'var(--d-muted)', marginTop: 4 }}>
                    {new Date(app.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })}
                  </div>
                  <div style={{ display: 'flex', gap: 8, justifyContent: 'flex-end', marginTop: 6 }}>
                    <button onClick={() => setExpandedId(isExpanded ? null : app.id)}
                      style={{ fontSize: 11, color: 'var(--d-gold)', background: 'none', border: 'none', cursor: 'pointer', padding: '2px 0', fontFamily: 'Inter, sans-serif' }}>
                      {isExpanded ? '▲ Masquer' : '▼ Analyse IA'}
                    </button>
                    {(app.status === 'pending' || app.status === 'reviewed') && (
                      <button onClick={() => withdraw(app.id)} disabled={withdrawingId === app.id}
                        style={{ fontSize: 11, color: 'var(--d-red)', background: 'none', border: 'none', cursor: 'pointer', padding: '2px 0', fontFamily: 'Inter, sans-serif', opacity: withdrawingId === app.id ? 0.5 : 1 }}>
                        {withdrawingId === app.id ? '...' : 'Annuler'}
                      </button>
                    )}
                  </div>
                </div>
              </div>

              {/* AI Feedback expanded */}
              {isExpanded && (
                <div style={{ margin: '0 0 16px 56px', background: 'var(--d-surface2)', border: '1px solid var(--d-border)', borderRadius: 12, padding: '14px 16px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 10 }}>
                    <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--d-text)' }}>🤖 Analyse IA</span>
                    {hasFeedback && (
                      <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <div style={{ height: 6, width: 80, background: 'var(--d-border)', borderRadius: 100, overflow: 'hidden' }}>
                          <div style={{ height: '100%', width: `${app.aiGeneratedScore}%`, background: scoreColor(app.aiGeneratedScore), borderRadius: 100, transition: 'width 1s ease' }} />
                        </div>
                        <span style={{ fontSize: 12, fontWeight: 700, color: scoreColor(app.aiGeneratedScore) }}>{app.aiGeneratedScore}/100</span>
                      </div>
                    )}
                  </div>
                  {hasFeedback ? (
                    <p style={{ fontSize: 12.5, color: 'var(--d-muted2)', lineHeight: 1.7, margin: 0, whiteSpace: 'pre-wrap' }}>{app.aiGeneratedFeedback}</p>
                  ) : (
                    <p style={{ fontSize: 12.5, color: 'var(--d-muted)', margin: 0 }}>⏳ Analyse en cours, veuillez revenir plus tard.</p>
                  )}
                </div>
              )}
            </div>
          )
        })
      )}
    </div>
  )
}
