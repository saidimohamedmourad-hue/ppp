import { useState } from 'react'
import { Link, NavLink } from 'react-router-dom'
import { getUser, isLoggedIn, logout } from '@/utils/api'
import type { User } from '@/types'

export default function Navbar() {
  const [menuOpen, setMenuOpen] = useState(false)
  const loggedIn = isLoggedIn()
  const user: User | null = getUser()

  return (
    <nav style={{
      display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      padding: '18px 64px',
      position: 'sticky', top: 0, zIndex: 100,
      background: 'rgba(247,245,240,0.92)',
      backdropFilter: 'blur(16px)',
      borderBottom: '1px solid #e2ddd6',
    }}>
      {/* Logo */}
      <Link to="/" style={{ display: 'flex', alignItems: 'center', gap: 8, textDecoration: 'none', fontFamily: '"Space Grotesk", sans-serif', fontWeight: 700, fontSize: 20, color: '#141210' }}>
        <div style={{ width: 30, height: 30, borderRadius: 8, background: '#e8502a', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'white', fontSize: 14, fontWeight: 700 }}>T</div>
        IQRA
      </Link>

      {/* Links */}
      <ul style={{ display: 'flex', gap: 32, listStyle: 'none', margin: 0, padding: 0 }}>
        {[
          { to: '/jobs', label: "Offres d'emploi" },
          { to: '/trainings', label: 'Formations' },
          { to: '/companies', label: 'Entreprises' },
          { to: '/schools', label: 'Écoles' },
        ].map(({ to, label }) => (
          <li key={to}>
            <NavLink to={to} style={({ isActive }) => ({
              textDecoration: 'none',
              color: isActive ? '#141210' : '#7a756e',
              fontSize: 14, fontWeight: 500, transition: 'color .15s',
            })}>
              {label}
            </NavLink>
          </li>
        ))}
      </ul>

      {/* Auth */}
      <div style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
        {loggedIn ? (
          <div style={{ position: 'relative' }}>
            <button
              onClick={() => setMenuOpen(!menuOpen)}
              style={{ display: 'flex', alignItems: 'center', gap: 8, background: 'none', border: 'none', cursor: 'pointer', fontSize: 14, fontWeight: 500, color: '#141210', fontFamily: '"Bricolage Grotesque", sans-serif' }}
            >
              <div style={{ width: 32, height: 32, borderRadius: 8, background: 'linear-gradient(135deg, #e8502a, #f0742a)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'white', fontSize: 13, fontWeight: 700 }}>
                {user?.name?.charAt(0).toUpperCase()}
              </div>
              <span>{user?.name}</span>
              <span style={{ fontSize: 10, color: '#7a756e' }}>▾</span>
            </button>
            {menuOpen && (
              <div style={{
                position: 'absolute', right: 0, top: 'calc(100% + 8px)', width: 200,
                background: 'white', borderRadius: 14, border: '1px solid #e2ddd6',
                boxShadow: '0 8px 32px rgba(0,0,0,0.12)', padding: '6px', zIndex: 200,
              }}>
                {[
                  { to: '/dashboard', label: '⊞ Dashboard' },
                  { to: '/dashboard/profile', label: '👤 Mon profil' },
                  { to: '/dashboard/resumes', label: '📄 Mes CVs' },
                  { to: '/dashboard/job-applications', label: '📋 Candidatures' },
                ].map(({ to, label }) => (
                  <Link key={to} to={to} onClick={() => setMenuOpen(false)} style={{ display: 'block', padding: '9px 12px', fontSize: 13, color: '#141210', textDecoration: 'none', borderRadius: 8, transition: 'background .1s' }}
                    onMouseEnter={e => (e.currentTarget.style.background = '#f7f5f0')}
                    onMouseLeave={e => (e.currentTarget.style.background = 'transparent')}
                  >
                    {label}
                  </Link>
                ))}
                <hr style={{ border: 'none', borderTop: '1px solid #e2ddd6', margin: '6px 0' }} />
                <button onClick={() => { setMenuOpen(false); logout() }} style={{ display: 'block', width: '100%', textAlign: 'left', padding: '9px 12px', fontSize: 13, color: '#e8502a', background: 'none', border: 'none', cursor: 'pointer', borderRadius: 8, fontFamily: '"Bricolage Grotesque", sans-serif' }}
                  onMouseEnter={e => (e.currentTarget.style.background = '#fef2f0')}
                  onMouseLeave={e => (e.currentTarget.style.background = 'transparent')}
                >
                  ↩ Déconnexion
                </button>
              </div>
            )}
          </div>
        ) : (
          <>
            <Link to="/login" className="btn-outline-sm">Se connecter</Link>
            <Link to="/register" className="btn-fill-sm">S'inscrire gratuitement</Link>
          </>
        )}
      </div>
    </nav>
  )
}
