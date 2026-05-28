import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/utils/api'

interface App {
  id: string
  status: 'pending' | 'reviewed' | 'shortlisted' | 'accepted' | 'rejected'
  created_at: string
  aiGeneratedScore: number
  aiGeneratedFeedback: string
  job_vacancy?: { id: string; title: string; location?: string; type?: string; company?: { name: string } }
}

const S_MAP: Record<string, { label: string; cls: string }> = {
  pending:     { label: 'En attente',          cls: 'd-status-pending'  },
  reviewed:    { label: 'Vue ✓',              cls: 'd-status-pending'  },
  shortlisted: { label: 'Présélectionné ⭐',   cls: 'd-status-accepted' },
  accepted:    { label: 'Accepté ✓',          cls: 'd-status-accepted' },
  rejected:    { label: 'Non retenu',          cls: 'd-status-rejected' },
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

export default function JobApplications() {
  const [apps, setApps] = useState<App[]>([])
  const [loading, setLoading] = useState(true)
  const [filter, setFilter] = useState<'all' | 'pending' | 'shortlisted' | 'rejected'>('all')
  const [withdrawingId, setWithdrawingId] = useState<string | null>(null)
  const [expandedId, setExpandedId] = useState<string | null>(null)

  useEffect(() => {
    apiFetch('my/job-applications')
      .then(res => setApps((res as { data?: App[] }).data ?? (res as App[])))
      .catch(() => setApps([]))
      .finally(() => setLoading(false))
  }, [])

  const withdraw = async (id: string) => {
    if (!window.confirm('Retirer cette candidature ?')) return
    setWithdrawingId(id)
    try {
      await apiFetch(`my/job-applications/${id}`, { method: 'DELETE' })
      setApps(prev => prev.filter(a => a.id !== id))
    } catch { /* ignore */ } finally { setWithdrawingId(null) }
  }

  const filtered = filter === 'all' ? apps
    : filter === 'pending'     ? apps.filter(a => a.status === 'pending' || a.status === 'reviewed')
    : filter === 'shortlisted' ? apps.filter(a => a.status === 'shortlisted' || a.status === 'accepted')
    : apps.filter(a => a.status === 'rejected')

  return (
    <div>
      {/* Filter tabs */}
      <div style={{ display: 'flex', gap: 8, marginBottom: 20, flexWrap: 'wrap' }}>
        {([
          ['all',         `Toutes (${apps.length})`],
          ['pending',     `En attente (${apps.filter(a => a.status === 'pending' || a.status === 'reviewed').length})`],
          ['shortlisted', `Présélectionnés (${apps.filter(a => a.status === 'shortlisted' || a.status === 'accepted').length})`],
          ['rejected',    `Non retenus (${apps.filter(a => a.status === 'rejected').length})`],
        ] as [typeof filter, string][]).map(([f, label]) => (
          <button key={f} onClick={() => setFilter(f)}
            className={filter === f ? 'd-btn-gold' : 'd-btn-ghost'}
            style={{ padding: '8px 16px', fontSize: 13 }}
          >{label}</button>
        ))}
      </div>

      <div className="d-card">
        {loading ? (
          <div style={{ textAlign: 'center', padding: '40px 0', color: 'var(--d-muted)' }}>Chargement...</div>
        ) : filtered.length === 0 ? (
          <div style={{ textAlign: 'center', padding: '48px 0', color: 'var(--d-muted)' }}>
            <div style={{ fontSize: 36, marginBottom: 12, opacity: 0.4 }}>📋</div>
            <p style={{ fontSize: 13 }}>Aucune candidature dans cette catégorie.</p>
            <Link to="/dashboard/jobs" className="d-btn-gold" style={{ display: 'inline-block', marginTop: 16, textDecoration: 'none' }}>Explorer les offres</Link>
          </div>
        ) : (
          filtered.map(app => {
            const job = app.job_vacancy
            const noCv      = app.aiGeneratedFeedback === '__no_cv__'
            const hasScore  = app.aiGeneratedScore > 0
            const hasFeedback = !!app.aiGeneratedFeedback && !noCv && app.aiGeneratedFeedback !== 'Une erreur est survenue lors de l\'analyse IA.'
            const isExpanded = expandedId === app.id
            return (
              <div key={app.id} style={{ borderBottom: '1px solid var(--d-border)' }}>
                {/* Main row */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 0' }}>
                  <div style={{ width: 46, height: 46, borderRadius: 12, background: 'var(--d-surface2)', border: '1px solid var(--d-border)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 22, flexShrink: 0 }}>🏢</div>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    {job ? (
                      <div style={{ fontSize: 14.5, fontWeight: 500, color: 'var(--d-text)' }}>{job.title}</div>
                    ) : <span style={{ fontSize: 14.5, color: 'var(--d-muted)' }}>Offre supprimée</span>}
                    <div style={{ fontSize: 12, color: 'var(--d-muted)', marginTop: 3 }}>
                      {job?.company?.name}{job?.location ? ` · ${job.location}` : ''}{job?.type ? ` · ${job.type}` : ''}
                    </div>
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

                  <div style={{ textAlign: 'right', flexShrink: 0 }}>
                    <span className={S_MAP[app.status]?.cls ?? 'd-status-pending'}>{S_MAP[app.status]?.label ?? app.status}</span>
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
                          {withdrawingId === app.id ? '...' : 'Retirer'}
                        </button>
                      )}
                    </div>
                  </div>
                </div>

                {/* AI Feedback expanded */}
                {isExpanded && (
                  <div style={{ margin: '0 0 16px 60px', background: 'var(--d-surface2)', border: '1px solid var(--d-border)', borderRadius: 12, padding: '14px 16px' }}>
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
    </div>
  )
}
