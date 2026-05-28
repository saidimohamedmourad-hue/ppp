import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch, getUser } from '@/utils/api'
import type { User } from '@/types'

interface SchoolStats {
  totalSessions: number
  totalApplications: number
  pendingApplications: number
  mostAppliedSessions: { id: string; title: string; totalCount: number }[]
}


export default function SchoolDashboard() {
  const user = getUser() as User | null
  const [stats, setStats] = useState<SchoolStats | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    apiFetch('school/dashboard')
      .then(res => {
        const d = res as SchoolStats
        setStats({ totalSessions: d.totalSessions ?? 0, totalApplications: d.totalApplications ?? 0, pendingApplications: d.pendingApplications ?? 0, mostAppliedSessions: d.mostAppliedSessions ?? [] })
      })
      .catch(() => {})
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

  return (
    <div>
      {/* Welcome */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 24 }}>
        <div>
          <h2 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 26, letterSpacing: '-0.3px', color: 'var(--d-text)', marginBottom: 4 }}>
            Bienvenue, {user?.name?.split(' ')[0] ?? 'Responsable'} 🎓
          </h2>
          <p style={{ color: 'var(--d-muted2)', fontSize: 13.5 }}>Gérez vos formations et traitez les inscriptions reçues.</p>
        </div>
        <Link to="/dashboard/school/sessions" className="d-btn-gold" style={{ textDecoration: 'none', display: 'inline-flex', alignItems: 'center', gap: 6 }}>
          Gérer mes formations →
        </Link>
      </div>

      {/* Stats */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 12, marginBottom: 20 }}>
        {[
          { icon: '🎓', bg: 'rgba(79,255,176,0.1)', val: stats?.totalSessions ?? 0, lbl: 'Formations publiées' },
          { icon: '👥', bg: 'rgba(96,165,250,0.1)', val: stats?.totalApplications ?? 0, lbl: 'Inscriptions reçues' },
          { icon: '⏳', bg: 'rgba(79,255,176,0.1)', val: stats?.pendingApplications ?? 0, lbl: 'En attente de traitement' },
          { icon: '🏆', bg: 'rgba(74,222,128,0.1)', val: stats?.mostAppliedSessions?.length ?? 0, lbl: 'Formations avec inscrits' },
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

      {/* Most applied sessions */}
      <div className="d-card" style={{ marginBottom: 14 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
          <div className="d-card-title">Formations les plus demandées</div>
          <Link to="/dashboard/school/sessions" style={{ fontSize: 12, color: 'var(--d-gold)', textDecoration: 'none' }}>Toutes les formations →</Link>
        </div>
        {(stats?.mostAppliedSessions ?? []).length === 0 ? (
          <div style={{ textAlign: 'center', padding: '32px 0', color: 'var(--d-muted)' }}>
            <div style={{ fontSize: 32, marginBottom: 10, opacity: 0.4 }}>📋</div>
            <p style={{ fontSize: 13 }}>Aucune inscription reçue pour l'instant.</p>
            <Link to="/dashboard/school/sessions" className="d-btn-gold" style={{ display: 'inline-block', marginTop: 14, textDecoration: 'none' }}>Créer une formation</Link>
          </div>
        ) : (
          (stats?.mostAppliedSessions ?? []).map(s => (
            <div key={s.id} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 0', borderBottom: '1px solid var(--d-border)' }}>
              <div style={{ width: 38, height: 38, borderRadius: 10, background: 'rgba(79,255,176,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 18, flexShrink: 0 }}>🎓</div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--d-text)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{s.title}</div>
              </div>
              <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--d-gold)' }}>{s.totalCount} inscrit(s)</span>
            </div>
          ))
        )}
      </div>

      {/* Guide */}
      <div className="d-card">
        <div className="d-card-title" style={{ marginBottom: 16 }}>Guide rapide</div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10 }}>
          {[
            { icon: '1️⃣', title: 'Créez une formation', desc: 'Ajoutez vos sessions de formation depuis "Mes formations".' },
            { icon: '2️⃣', title: 'Recevez des inscriptions', desc: 'Les candidats s\'inscrivent à vos sessions.' },
            { icon: '3️⃣', title: 'Confirmez les inscriptions', desc: 'Acceptez ou refusez depuis la liste des inscrits.' },
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
