import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch, getUser } from '@/utils/api'
import type { User } from '@/types'

interface TopSession {
  id: number | string
  title: string
  totalCount?: number   // inscriptions (alias withCount)
  viewCount?: number    // vues
}

interface Applicant {
  id: number | string
  status: 'pending' | 'accepted' | 'rejected' | string
  created_at: string
  user: { id?: number | string; name: string; email: string }
  session: { id?: number | string; title: string }
}

interface SchoolDashboardData {
  totalSessions?: number
  totalApplications?: number
  pendingApplications?: number
  acceptedApplications?: number
  activeUsers?: number
  totalViews?: number
  mostAppliedSessions?: TopSession[]
  recentApplicants?: Applicant[]
}

const STATUS_MAP: Record<string, { label: string; cls: string }> = {
  pending:  { label: 'En attente', cls: 'd-status-pending' },
  accepted: { label: 'Accepté ✓',  cls: 'd-status-accepted' },
  rejected: { label: 'Refusé',     cls: 'd-status-rejected' },
}

function conversion(applications?: number, views?: number): string {
  if (!views || views <= 0) return '—'
  return `${Math.round(((applications ?? 0) / views) * 100)}%`
}

export default function SchoolDashboard() {
  const user = getUser() as User | null
  const [data, setData] = useState<SchoolDashboardData | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    apiFetch('school/dashboard')
      .then(res => setData((res as { data?: SchoolDashboardData }).data ?? (res as SchoolDashboardData)))
      .catch(() => setData(null))
      .finally(() => setLoading(false))
  }, [])

  if (loading) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '60vh' }}>
        <div style={{ width: 32, height: 32, border: '3px solid rgba(79,255,176,0.15)', borderTopColor: '#4fffb0', borderRadius: '50%', animation: 'spin 0.8s linear infinite' }} />
        <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
      </div>
    )
  }

  // Owner sans école associée (ex. inscription Google/Facebook en « École »)
  // → l'API renvoie 404 et data reste null. On affiche un état d'attente.
  if (!data) {
    return (
      <div className="d-card" style={{ textAlign: 'center', padding: '48px 24px', maxWidth: 520, margin: '40px auto' }}>
        <div style={{ fontSize: 44, marginBottom: 14 }}>🎓</div>
        <h2 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 22, color: 'var(--d-text)', marginBottom: 10 }}>Espace école en préparation</h2>
        <p style={{ color: 'var(--d-muted)', fontSize: 13.5, lineHeight: 1.6, marginBottom: 22 }}>
          Votre compte est bien créé avec le rôle <strong>École / Centre</strong>. Un administrateur doit
          encore créer et associer votre fiche école. Une fois lié, rafraîchissez cette page.
        </p>
        <button onClick={() => window.location.reload()} className="d-btn-gold" style={{ border: 'none', cursor: 'pointer' }}>Rafraîchir</button>
      </div>
    )
  }

  const topSessions = data.mostAppliedSessions ?? []
  const recent = data.recentApplicants ?? []

  const statCards = [
    { icon: '🎓', bg: 'rgba(79,255,176,0.1)', val: data?.totalSessions ?? 0,        lbl: 'Formations publiées' },
    { icon: '👁️', bg: 'rgba(96,165,250,0.1)', val: data?.totalViews ?? 0,           lbl: 'Vues totales' },
    { icon: '👥', bg: 'rgba(96,165,250,0.1)', val: data?.totalApplications ?? 0,    lbl: 'Inscriptions reçues' },
    { icon: '⏳', bg: 'rgba(240,196,90,0.1)', val: data?.pendingApplications ?? 0,  lbl: 'En attente' },
    { icon: '✅', bg: 'rgba(74,222,128,0.1)', val: data?.acceptedApplications ?? 0, lbl: 'Inscrits retenus' },
    { icon: '🔥', bg: 'rgba(79,255,176,0.1)', val: data?.activeUsers ?? 0,          lbl: 'Actifs (30 j)' },
  ]

  return (
    <div>
      {/* Welcome */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 24 }}>
        <div>
          <h2 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 26, letterSpacing: '-0.3px', color: 'var(--d-text)', marginBottom: 4 }}>
            Bienvenue, {user?.name?.split(' ')[0] ?? 'Responsable'} 🎓
          </h2>
          <p style={{ color: 'var(--d-muted2)', fontSize: 13.5 }}>Suivez vos statistiques et traitez les inscriptions reçues.</p>
        </div>
        <Link to="/dashboard/school/sessions" className="d-btn-gold" style={{ textDecoration: 'none', display: 'inline-flex', alignItems: 'center', gap: 6 }}>
          Gérer mes formations →
        </Link>
      </div>

      {/* Stats */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: 12, marginBottom: 20 }}>
        {statCards.map(s => (
          <div key={s.lbl} className="d-stat-card">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
              <div className="d-stat-icon" style={{ background: s.bg }}>{s.icon}</div>
            </div>
            <div className="d-stat-val">{s.val}</div>
            <div className="d-stat-lbl">{s.lbl}</div>
          </div>
        ))}
      </div>

      {/* Top formations — analytics (vues + conversion) */}
      <div className="d-card" style={{ marginBottom: 14 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
          <div className="d-card-title">Top formations — vues & conversion</div>
          <Link to="/dashboard/school/sessions" style={{ fontSize: 12, color: 'var(--d-gold)', textDecoration: 'none' }}>Toutes →</Link>
        </div>
        {topSessions.length === 0 ? (
          <div style={{ textAlign: 'center', padding: '24px 0', color: 'var(--d-muted)', fontSize: 13 }}>Aucune donnée pour l'instant.</div>
        ) : (
          <div>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 90px 90px 110px', gap: 8, padding: '0 0 8px', fontSize: 11.5, color: 'var(--d-muted2)', textTransform: 'uppercase', letterSpacing: 0.5, borderBottom: '1px solid var(--d-border)' }}>
              <span>Formation</span>
              <span style={{ textAlign: 'right' }}>Vues</span>
              <span style={{ textAlign: 'right' }}>Inscrits</span>
              <span style={{ textAlign: 'right' }}>Conversion</span>
            </div>
            {topSessions.map(s => (
              <div key={s.id} style={{ display: 'grid', gridTemplateColumns: '1fr 90px 90px 110px', gap: 8, padding: '11px 0', borderBottom: '1px solid var(--d-border)', alignItems: 'center', fontSize: 13 }}>
                <span style={{ color: 'var(--d-text)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{s.title}</span>
                <span style={{ textAlign: 'right', color: 'var(--d-muted)' }}>{s.viewCount ?? 0}</span>
                <span style={{ textAlign: 'right', color: 'var(--d-text)', fontWeight: 600 }}>{s.totalCount ?? 0}</span>
                <span style={{ textAlign: 'right', color: '#4fffb0', fontWeight: 600 }}>{conversion(s.totalCount, s.viewCount)}</span>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Inscriptions récentes */}
      <div className="d-card" style={{ marginBottom: 14 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
          <div className="d-card-title">Inscriptions récentes</div>
          <Link to="/dashboard/school/sessions" style={{ fontSize: 12, color: 'var(--d-gold)', textDecoration: 'none' }}>Voir par formation →</Link>
        </div>
        {recent.length === 0 ? (
          <div style={{ textAlign: 'center', padding: '32px 0', color: 'var(--d-muted)' }}>
            <div style={{ fontSize: 32, marginBottom: 10, opacity: 0.4 }}>📋</div>
            <p style={{ fontSize: 13 }}>Aucune inscription reçue pour l'instant.</p>
            <Link to="/dashboard/school/sessions" className="d-btn-gold" style={{ display: 'inline-block', marginTop: 14, textDecoration: 'none' }}>Créer une formation</Link>
          </div>
        ) : (
          recent.slice(0, 8).map(app => (
            <div key={app.id} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 0', borderBottom: '1px solid var(--d-border)' }}>
              <div style={{ width: 38, height: 38, borderRadius: 10, background: 'var(--d-surface2)', border: '1px solid var(--d-border)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 16, flexShrink: 0, fontWeight: 700, color: 'var(--d-gold)', fontFamily: 'Inter, sans-serif' }}>
                {(app.user?.name ?? '?').charAt(0).toUpperCase()}
              </div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--d-text)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{app.user?.name}</div>
                <div style={{ fontSize: 12, color: 'var(--d-muted)', marginTop: 2 }}>{app.session?.title}</div>
              </div>
              <span className={STATUS_MAP[app.status]?.cls ?? 'd-status-pending'}>{STATUS_MAP[app.status]?.label ?? app.status}</span>
            </div>
          ))
        )}
      </div>
    </div>
  )
}
