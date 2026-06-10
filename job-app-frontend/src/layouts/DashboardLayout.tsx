import { useEffect, useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { getUser, isLoggedIn, logout } from '@/utils/api'
import type { User } from '@/types'
import { NotificationBell } from '@/components/notifications/NotificationBell'

type NavItem = { icon: string; label: string; to: string }
type NavGroup = { label: string; items: NavItem[] }

const NAV_BY_ROLE: Record<string, NavGroup[]> = {
  'job-seeker': [
    {
      label: 'Principal',
      items: [
        { icon: '⊞', label: 'Tableau de bord', to: '/dashboard' },
        { icon: '💼', label: 'Offres d\'emploi', to: '/dashboard/jobs' },
        { icon: '📋', label: 'Mes candidatures', to: '/dashboard/job-applications' },
      ],
    },
    {
      label: 'Formation',
      items: [
        { icon: '🎓', label: 'Formations', to: '/dashboard/trainings' },
        { icon: '📚', label: 'Mes inscriptions', to: '/dashboard/training-applications' },
      ],
    },
    {
      label: 'Compte',
      items: [
        { icon: '👤', label: 'Mon profil', to: '/dashboard/profile' },
        { icon: '📄', label: 'Mon CV', to: '/dashboard/resumes' },
      ],
    },
  ],
  'company-owner': [
    {
      label: 'Entreprise',
      items: [
        { icon: '⊞', label: 'Tableau de bord', to: '/dashboard' },
        { icon: '💼', label: 'Mes offres', to: '/dashboard/company/jobs' },
      ],
    },
    {
      label: 'Compte',
      items: [
        { icon: '👤', label: 'Mon compte', to: '/dashboard/profile' },
      ],
    },
  ],
  'school-owner': [
    {
      label: 'École',
      items: [
        { icon: '⊞', label: 'Tableau de bord', to: '/dashboard' },
        { icon: '🎓', label: 'Mes formations', to: '/dashboard/school/sessions' },
      ],
    },
    {
      label: 'Compte',
      items: [
        { icon: '👤', label: 'Mon compte', to: '/dashboard/profile' },
      ],
    },
  ],
}

const ROLE_LABELS: Record<string, string> = {
  'job-seeker': 'Candidat actif',
  'company-owner': 'Compte entreprise',
  'school-owner': 'Compte établissement',
  'admin': 'Administrateur',
}

const PAGE_TITLES: Record<string, { title: string; sub: string }> = {
  '/dashboard': { title: 'Tableau de bord', sub: 'Résumé de votre activité' },
  '/dashboard/jobs': { title: 'Offres d\'emploi', sub: 'Explorez et postulez aux offres' },
  '/dashboard/trainings': { title: 'Formations', sub: 'Explorez et inscrivez-vous aux formations' },
  '/dashboard/job-applications': { title: 'Mes candidatures', sub: 'Suivi de vos candidatures emploi' },
  '/dashboard/training-applications': { title: 'Mes inscriptions', sub: 'Formations auxquelles vous êtes inscrits' },
  '/dashboard/profile': { title: 'Mon profil', sub: 'Gérez vos informations personnelles' },
  '/dashboard/notifications': { title: 'Notifications', sub: 'Toutes vos notifications récentes' },
  '/dashboard/resumes': { title: 'Mon CV', sub: 'Uploadez et gérez vos CVs' },
  '/dashboard/company/jobs': { title: 'Mes offres d\'emploi', sub: 'Gérez vos annonces et candidatures' },
  '/dashboard/school/sessions': { title: 'Mes formations', sub: 'Gérez vos sessions et inscriptions' },
}

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  const location = useLocation()
  const navigate = useNavigate()
  const [user, setUser] = useState<User | null>(getUser())

  useEffect(() => {
    if (!isLoggedIn()) { navigate('/login'); return }
    const stored = getUser()
    if (stored) setUser(stored)
  }, [navigate])

  const role = user?.role ?? 'job-seeker'
  const navGroups = NAV_BY_ROLE[role] ?? NAV_BY_ROLE['job-seeker']

  const pageKey = Object.keys(PAGE_TITLES).find(k => location.pathname === k || location.pathname.startsWith(k + '/') && k !== '/dashboard') ?? location.pathname
  const page = PAGE_TITLES[pageKey] ?? PAGE_TITLES[location.pathname] ?? { title: 'Espace utilisateur', sub: '' }

  return (
    <div className="dashboard">
      {/* Sidebar */}
      <aside className="d-sidebar">
        <div className="d-sidebar-logo">
          <img src="/iqra-logo.png" alt="IQRA" style={{ width: 32, height: 32, borderRadius: 8, objectFit: 'cover', flexShrink: 0 }} />
          <span className="d-logo-text" style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 18, letterSpacing: '-0.5px' }}>IQRA</span>
        </div>

        <nav className="d-nav">
          {navGroups.map((group) => (
            <div key={group.label}>
              <div className="d-nav-label">{group.label}</div>
              {group.items.map((item) => (
                <Link
                  key={item.to}
                  to={item.to}
                  className={`d-nav-item ${location.pathname === item.to ? 'active' : ''}`}
                >
                  <span style={{ width: 18, textAlign: 'center', fontSize: 15 }}>{item.icon}</span>
                  {item.label}
                </Link>
              ))}
            </div>
          ))}
        </nav>

        <div style={{ padding: '0 12px 8px' }}>
          <Link
            to="/dashboard/profile"
            style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px', borderRadius: 10, textDecoration: 'none', marginBottom: 6, background: location.pathname === '/dashboard/profile' ? 'rgba(79,255,176,0.08)' : 'transparent', border: '1px solid var(--d-border)', transition: 'all .2s' }}
            onMouseEnter={e => { e.currentTarget.style.background = 'rgba(79,255,176,0.08)' }}
            onMouseLeave={e => { if (location.pathname !== '/dashboard/profile') e.currentTarget.style.background = 'transparent' }}
          >
            <div className="d-avatar" style={{ width: 32, height: 32, fontSize: 13, flexShrink: 0 }}>{user?.name?.charAt(0).toUpperCase() ?? '?'}</div>
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ fontSize: 12.5, fontWeight: 500, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', color: 'var(--d-text)' }}>
                {user?.name ?? 'Utilisateur'}
              </div>
              <div style={{ fontSize: 11, color: 'var(--d-muted)' }}>{ROLE_LABELS[role] ?? 'Utilisateur'}</div>
            </div>
            <span style={{ fontSize: 12, color: 'var(--d-muted)' }}>👤</span>
          </Link>
          <button
            onClick={() => logout()}
            style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 8, padding: '9px 12px', borderRadius: 10, border: '1px solid rgba(248,113,113,0.25)', background: 'rgba(248,113,113,0.06)', color: 'var(--d-red)', fontSize: 13, cursor: 'pointer', fontFamily: 'Inter, sans-serif', transition: 'all .2s' }}
            onMouseEnter={e => { e.currentTarget.style.background = 'rgba(248,113,113,0.12)' }}
            onMouseLeave={e => { e.currentTarget.style.background = 'rgba(248,113,113,0.06)' }}
          >
            <span style={{ fontSize: 15 }}>↩</span>
            Déconnexion
          </button>
        </div>
      </aside>

      {/* Main */}
      <main className="d-main">
        <header className="d-topbar">
          <div>
            <div className="d-topbar-title">{page.title}</div>
            {page.sub && <div className="d-topbar-sub">{page.sub}</div>}
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <div style={{
              display: 'flex', alignItems: 'center', gap: 8,
              background: 'var(--d-surface2)', border: '1px solid var(--d-border)',
              borderRadius: 10, padding: '8px 14px', width: 200,
            }}>
              <span style={{ color: 'var(--d-muted)', fontSize: 13 }}>🔍</span>
              <input
                type="text"
                placeholder="Rechercher..."
                style={{ background: 'none', border: 'none', outline: 'none', color: 'var(--d-text)', fontSize: 13, width: '100%', fontFamily: 'Inter, sans-serif' }}
              />
            </div>
            <NotificationBell />
            <Link to="/" style={{ width: 36, height: 36, borderRadius: 10, background: 'var(--d-surface2)', border: '1px solid var(--d-border)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 15, textDecoration: 'none' }} title="Accueil">
              🏠
            </Link>
          </div>
        </header>

        <div className="d-content d-page-enter">{children}</div>
      </main>
    </div>
  )
}
