import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch, getUser } from '@/utils/api'
import type { User, TrainingApplication } from '@/types'

interface RawJobApp {
  id: string
  status: string
  created_at: string
  job_vacancy?: { id: string; title: string; location?: string; company?: { name: string } }
}

const STATUS_MAP: Record<string, { label: string; cls: string }> = {
  pending:     { label: 'En attente',       cls: 'd-status-pending'  },
  reviewed:    { label: 'Vue ✓',           cls: 'd-status-pending'  },
  shortlisted: { label: 'Présélectionné ⭐', cls: 'd-status-accepted' },
  accepted:    { label: 'Accepté ✓',       cls: 'd-status-accepted' },
  rejected:    { label: 'Non retenu',       cls: 'd-status-rejected' },
}

export default function CandidatDashboard() {
  const [user, setUser] = useState<User | null>(getUser())
  const [jobApps, setJobApps] = useState<RawJobApp[]>([])
  const [trainingApps, setTrainingApps] = useState<TrainingApplication[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([
      apiFetch('me').catch(() => null),
      apiFetch('my/job-applications').catch(() => ({ data: [] })),
      apiFetch('my/training-applications').catch(() => ({ data: [] })),
    ]).then(([me, jobs, trainings]) => {
      if (me) {
        const u = (me as { data?: User }).data ?? (me as User)
        setUser(u)
        localStorage.setItem('user', JSON.stringify(u))
      }
      setJobApps((jobs as { data?: RawJobApp[] }).data ?? (jobs as RawJobApp[]))
      setTrainingApps((trainings as { data?: TrainingApplication[] }).data ?? (trainings as TrainingApplication[]))
    }).finally(() => setLoading(false))
  }, [])

  if (loading) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '60vh' }}>
        <div style={{ width: 32, height: 32, border: '3px solid rgba(79,255,176,0.15)', borderTopColor: '#4fffb0', borderRadius: '50%', animation: 'spin 0.8s linear infinite' }} />
        <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
      </div>
    )
  }

  const pendingJobs = jobApps.filter(a => a.status === 'pending').length
  const acceptedJobs = jobApps.filter(a => a.status === 'accepted').length

  return (
    <div>
      {/* Welcome */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 24 }}>
        <div>
          <h2 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 26, letterSpacing: '-0.3px', color: 'var(--d-text)', marginBottom: 4 }}>
            Bienvenue, {user?.name?.split(' ')[0] ?? 'Candidat'} ✦
          </h2>
          <p style={{ color: 'var(--d-muted2)', fontSize: 13.5 }}>
            {jobApps.length > 0 ? `${jobApps.length} candidature(s) envoyée(s)` : 'Commencez par explorer les offres d\'emploi'}
          </p>
        </div>
        <Link to="/dashboard/jobs" className="d-btn-gold" style={{ textDecoration: 'none', display: 'inline-flex', alignItems: 'center', gap: 6 }}>
          Explorer les offres →
        </Link>
      </div>

      {/* Stats */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 12, marginBottom: 20 }}>
        {[
          { icon: '💼', bg: 'rgba(79,255,176,0.1)', val: jobApps.length, lbl: 'Candidatures envoyées' },
          { icon: '✅', bg: 'rgba(74,222,128,0.1)',  val: acceptedJobs,  lbl: 'Entretiens obtenus' },
          { icon: '🎓', bg: 'rgba(96,165,250,0.1)',  val: trainingApps.length, lbl: 'Formations inscrites' },
          { icon: '⏳', bg: 'rgba(79,255,176,0.1)',  val: pendingJobs,   lbl: 'En attente de réponse' },
        ].map(s => (
          <div key={s.lbl} className="d-stat-card">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
              <div className="d-stat-icon" style={{ background: s.bg }}>{s.icon}</div>
            </div>
            <div className="d-stat-val">{s.val}</div>
            <div className="d-stat-lbl">{s.lbl}</div>
          </div>
        ))}
      </div>

      {/* Content row */}
      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 14, marginBottom: 14 }}>
        {/* Recent applications */}
        <div className="d-card">
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
            <div className="d-card-title">Candidatures récentes</div>
            <Link to="/dashboard/job-applications" style={{ fontSize: 12, color: 'var(--d-gold)', textDecoration: 'none' }}>Voir tout →</Link>
          </div>
          {jobApps.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '32px 0', color: 'var(--d-muted)' }}>
              <div style={{ fontSize: 32, marginBottom: 10, opacity: 0.4 }}>📋</div>
              <p style={{ fontSize: 13 }}>Aucune candidature pour l'instant.</p>
              <Link to="/dashboard/jobs" className="d-btn-gold" style={{ display: 'inline-block', marginTop: 14, textDecoration: 'none' }}>Voir les offres</Link>
            </div>
          ) : (
            jobApps.slice(0, 5).map(app => (
              <div key={app.id} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 0', borderBottom: '1px solid var(--d-border)' }}>
                <div style={{ width: 38, height: 38, borderRadius: 10, background: 'var(--d-surface2)', border: '1px solid var(--d-border)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 18, flexShrink: 0 }}>🏢</div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <span style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--d-text)', display: 'block', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                    {app.job_vacancy?.title}
                  </span>
                  <div style={{ fontSize: 12, color: 'var(--d-muted)', marginTop: 2 }}>{app.job_vacancy?.company?.name} · {app.job_vacancy?.location}</div>
                </div>
                <span className={STATUS_MAP[app.status]?.cls ?? 'd-status-pending'}>{STATUS_MAP[app.status]?.label}</span>
              </div>
            ))
          )}
        </div>

        {/* Profile completion */}
        <div className="d-card">
          <div className="d-card-title" style={{ marginBottom: 18 }}>Complétion profil</div>
          <div style={{ textAlign: 'center', marginBottom: 20 }}>
            <svg width="90" height="90" viewBox="0 0 90 90">
              <circle cx="45" cy="45" r="38" fill="none" stroke="var(--d-surface2)" strokeWidth="8" />
              <circle cx="45" cy="45" r="38" fill="none" stroke="var(--d-gold)" strokeWidth="8"
                strokeDasharray="239" strokeDashoffset="60" strokeLinecap="round"
                transform="rotate(-90 45 45)" />
            </svg>
            <div style={{ marginTop: -60, fontFamily: '"Instrument Serif", serif', fontSize: 22, color: 'var(--d-text)' }}>75%</div>
            <div style={{ marginTop: 46 }} />
          </div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            {[
              { label: 'Infos personnelles', pct: 100, color: 'var(--d-green)' },
              { label: 'CV uploadé', pct: 100, color: 'var(--d-green)' },
              { label: 'Photo de profil', pct: 0, color: 'var(--d-gold)' },
            ].map(row => (
              <div key={row.label}>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 12, color: 'var(--d-muted)', marginBottom: 5 }}>
                  <span>{row.label}</span>
                  <span style={{ color: row.pct === 100 ? 'var(--d-green)' : 'var(--d-muted2)' }}>{row.pct === 100 ? '✓' : `${row.pct}%`}</span>
                </div>
                <div style={{ height: 4, background: 'var(--d-surface2)', borderRadius: 100, overflow: 'hidden' }}>
                  <div style={{ height: '100%', width: `${row.pct}%`, background: row.color, borderRadius: 100, transition: 'width 1s ease' }} />
                </div>
              </div>
            ))}
          </div>
          <Link to="/dashboard/profile" className="d-btn-ghost" style={{ display: 'block', textAlign: 'center', marginTop: 16, textDecoration: 'none' }}>
            Compléter →
          </Link>
        </div>
      </div>

      {/* Trainings recommended */}
      <div className="d-card">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
          <div className="d-card-title">Formations recommandées</div>
          <Link to="/dashboard/trainings" style={{ fontSize: 12, color: 'var(--d-gold)', textDecoration: 'none' }}>Voir tout →</Link>
        </div>
        {trainingApps.length > 0 ? (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10 }}>
            {trainingApps.filter(app => app.training_session).slice(0, 3).map(app => (
              <div key={app.id} style={{ background: 'var(--d-surface2)', border: '1px solid var(--d-border)', borderRadius: 12, padding: 16 }}>
                <div style={{ fontSize: 22, marginBottom: 10 }}>🎓</div>
                <div style={{ fontSize: 13, fontWeight: 600, marginBottom: 4, color: 'var(--d-text)' }}>{app.training_session?.title}</div>
                <div style={{ fontSize: 11, color: 'var(--d-muted)' }}>{app.training_session?.school?.name}</div>
                <span className={STATUS_MAP[app.status]?.cls ?? 'd-status-pending'} style={{ display: 'inline-block', marginTop: 10 }}>
                  {STATUS_MAP[app.status]?.label}
                </span>
              </div>
            ))}
          </div>
        ) : (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10 }}>
            {[
              { icon: '🤖', title: 'Bootcamp IA & ML', school: 'USTHB · 3 mois', color: 'var(--d-blue)', label: 'Présentiel' },
              { icon: '☁️', title: 'DevOps & Cloud AWS', school: 'ISI Alger · 2 mois', color: 'var(--d-green)', label: 'En ligne' },
              { icon: '📊', title: 'Data Science Python', school: 'ESI · 6 semaines', color: 'var(--d-gold)', label: 'Hybride' },
            ].map(t => (
              <div key={t.title} style={{ background: 'var(--d-surface2)', border: '1px solid var(--d-border)', borderRadius: 12, padding: 16 }}>
                <div style={{ fontSize: 22, marginBottom: 10 }}>{t.icon}</div>
                <div style={{ fontSize: 13, fontWeight: 600, marginBottom: 4, color: 'var(--d-text)' }}>{t.title}</div>
                <div style={{ fontSize: 11, color: 'var(--d-muted)', marginBottom: 10 }}>{t.school}</div>
                <span style={{ fontSize: 11, color: t.color, background: `${t.color}18`, padding: '3px 8px', borderRadius: 6, display: 'inline-block' }}>{t.label}</span>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}
