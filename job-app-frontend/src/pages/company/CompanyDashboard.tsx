import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch, getUser } from '@/utils/api'
import type { User } from '@/types'

interface CompanyStats {
  jobs_count: number
  total_applicants: number
  pending_applicants: number
  accepted_applicants: number
}

interface Applicant {
  id: number
  status: 'pending' | 'accepted' | 'rejected'
  created_at: string
  user: { id: number; name: string; email: string }
  job: { id: number; title: string }
}

const STATUS_MAP = {
  pending:  { label: 'En attente', cls: 'd-status-pending' },
  accepted: { label: 'Accepté ✓',  cls: 'd-status-accepted' },
  rejected: { label: 'Refusé',     cls: 'd-status-rejected' },
}

export default function CompanyDashboard() {
  const user = getUser() as User | null
  const [stats, setStats] = useState<CompanyStats | null>(null)
  const [recent, setRecent] = useState<Applicant[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([
      apiFetch('company/dashboard').catch(() => null),
    ]).then(([data]) => {
      if (data) {
        const d = (data as { data?: CompanyStats & { recent_applicants?: Applicant[] } }).data ?? data as CompanyStats & { recent_applicants?: Applicant[] }
        setStats({ jobs_count: d.jobs_count ?? 0, total_applicants: d.total_applicants ?? 0, pending_applicants: d.pending_applicants ?? 0, accepted_applicants: d.accepted_applicants ?? 0 })
        setRecent(d.recent_applicants ?? [])
      }
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

  return (
    <div>
      {/* Welcome */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 24 }}>
        <div>
          <h2 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 26, letterSpacing: '-0.3px', color: 'var(--d-text)', marginBottom: 4 }}>
            Bienvenue, {user?.name?.split(' ')[0] ?? 'Gestionnaire'} 🏢
          </h2>
          <p style={{ color: 'var(--d-muted2)', fontSize: 13.5 }}>Gérez vos offres et traitez les candidatures reçues.</p>
        </div>
        <Link to="/dashboard/company/jobs" className="d-btn-gold" style={{ textDecoration: 'none', display: 'inline-flex', alignItems: 'center', gap: 6 }}>
          Gérer mes offres →
        </Link>
      </div>

      {/* Stats */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 12, marginBottom: 20 }}>
        {[
          { icon: '💼', bg: 'rgba(79,255,176,0.1)', val: stats?.jobs_count ?? 0, lbl: 'Offres publiées' },
          { icon: '👥', bg: 'rgba(96,165,250,0.1)', val: stats?.total_applicants ?? 0, lbl: 'Candidatures reçues' },
          { icon: '⏳', bg: 'rgba(79,255,176,0.1)', val: stats?.pending_applicants ?? 0, lbl: 'En attente de traitement' },
          { icon: '✅', bg: 'rgba(74,222,128,0.1)', val: stats?.accepted_applicants ?? 0, lbl: 'Candidats retenus' },
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

      {/* Recent applicants */}
      <div className="d-card" style={{ marginBottom: 14 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
          <div className="d-card-title">Candidatures récentes</div>
          <Link to="/dashboard/company/jobs" style={{ fontSize: 12, color: 'var(--d-gold)', textDecoration: 'none' }}>Voir par offre →</Link>
        </div>
        {recent.length === 0 ? (
          <div style={{ textAlign: 'center', padding: '32px 0', color: 'var(--d-muted)' }}>
            <div style={{ fontSize: 32, marginBottom: 10, opacity: 0.4 }}>📋</div>
            <p style={{ fontSize: 13 }}>Aucune candidature reçue pour l'instant.</p>
            <Link to="/dashboard/company/jobs" className="d-btn-gold" style={{ display: 'inline-block', marginTop: 14, textDecoration: 'none' }}>Publier une offre</Link>
          </div>
        ) : (
          recent.slice(0, 8).map(app => (
            <div key={app.id} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 0', borderBottom: '1px solid var(--d-border)' }}>
              <div style={{ width: 38, height: 38, borderRadius: 10, background: 'var(--d-surface2)', border: '1px solid var(--d-border)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 16, flexShrink: 0, fontWeight: 700, color: 'var(--d-gold)', fontFamily: 'Inter, sans-serif' }}>
                {app.user.name.charAt(0).toUpperCase()}
              </div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--d-text)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{app.user.name}</div>
                <div style={{ fontSize: 12, color: 'var(--d-muted)', marginTop: 2 }}>{app.job?.title}</div>
              </div>
              <span className={STATUS_MAP[app.status]?.cls ?? 'd-status-pending'}>{STATUS_MAP[app.status]?.label}</span>
            </div>
          ))
        )}
      </div>

      {/* Quick tips */}
      <div className="d-card">
        <div className="d-card-title" style={{ marginBottom: 16 }}>Guide rapide</div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10 }}>
          {[
            { icon: '1️⃣', title: 'Publiez une offre', desc: 'Créez votre annonce depuis "Mes offres".' },
            { icon: '2️⃣', title: 'Recevez des candidatures', desc: 'Les candidats postulent et vous êtes notifié.' },
            { icon: '3️⃣', title: 'Traitez les dossiers', desc: 'Acceptez ou refusez depuis la liste des candidats.' },
          ].map(t => (
            <div key={t.title} style={{ background: 'var(--d-surface2)', border: '1px solid var(--d-border)', borderRadius: 12, padding: 16 }}>
              <div style={{ fontSize: 22, marginBottom: 10 }}>{t.icon}</div>
              <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--d-text)', marginBottom: 6 }}>{t.title}</div>
              <div style={{ fontSize: 12, color: 'var(--d-muted)', lineHeight: 1.5 }}>{t.desc}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
