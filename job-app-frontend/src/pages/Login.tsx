import { useState } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/utils/api'

export default function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    setError(null)
    try {
      const res = await apiFetch('login', { method: 'POST', body: JSON.stringify({ email, password }) })
      localStorage.setItem('token', res.token)
      localStorage.setItem('user', JSON.stringify(res.user))
      window.location.href = '/dashboard'
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Identifiants incorrects')
    } finally {
      setLoading(false)
    }
  }

  const inputStyle: React.CSSProperties = {
    width: '100%', padding: '11px 14px', borderRadius: 10,
    background: 'rgba(255,255,255,0.05)', border: '1px solid rgba(255,255,255,0.1)',
    color: '#e8ecf2', fontSize: 14, outline: 'none', fontFamily: '"DM Sans", sans-serif',
    transition: 'border-color .2s', boxSizing: 'border-box',
  }

  return (
    <div style={{ minHeight: '100vh', display: 'flex', background: '#080c14', fontFamily: '"DM Sans", sans-serif' }}>

      {/* ── Left panel ── */}
      <div style={{ flex: 1, padding: '60px', display: 'flex', flexDirection: 'column', justifyContent: 'space-between', borderRight: '1px solid rgba(255,255,255,0.06)', position: 'relative', overflow: 'hidden' }}>
        {/* Glow */}
        <div style={{ position: 'absolute', top: -80, left: -80, width: 400, height: 400, borderRadius: '50%', background: 'radial-gradient(circle, rgba(79,255,176,0.08) 0%, transparent 70%)', pointerEvents: 'none' }} />

        <Link to="/" style={{ display: 'flex', alignItems: 'center', gap: 8, textDecoration: 'none', fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 19, color: 'white', position: 'relative' }}>
          <img src="/iqra-logo.png" alt="IQRA" style={{ width: 30, height: 30, borderRadius: 8, objectFit: 'cover' }} />
          IQRA
        </Link>

        <div style={{ position: 'relative' }}>
          <h2 style={{ fontFamily: '"Syne", sans-serif', fontSize: 'clamp(28px, 3vw, 44px)', fontWeight: 800, color: 'white', letterSpacing: '-1.5px', lineHeight: 1.15, marginBottom: 20 }}>
            Des centaines d'offres<br />
            <span style={{ background: 'linear-gradient(135deg, #4fffb0, #00d4ff)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
              vous attendent.
            </span>
          </h2>
          <p style={{ color: 'rgba(255,255,255,0.45)', fontSize: 15, lineHeight: 1.75, maxWidth: 360 }}>
            Connectez-vous pour accéder à votre espace candidat, gérer vos candidatures et formations.
          </p>
          <div style={{ display: 'flex', gap: 36, marginTop: 44 }}>
            {[{ n: '12k+', l: 'Candidats' }, { n: '850+', l: 'Entreprises' }, { n: '94%', l: 'Satisfaction' }].map(s => (
              <div key={s.l}>
                <div style={{ fontFamily: '"Syne", sans-serif', fontSize: 28, fontWeight: 800, color: '#4fffb0', letterSpacing: '-1px' }}>{s.n}</div>
                <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.35)', marginTop: 3 }}>{s.l}</div>
              </div>
            ))}
          </div>
        </div>

        <p style={{ fontSize: 13, color: 'rgba(255,255,255,0.2)', position: 'relative' }}>© 2026 IQRA · Algérie</p>
      </div>

      {/* ── Right panel ── */}
      <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '60px' }}>
        <div style={{ width: '100%', maxWidth: 400 }}>
          <h1 style={{ fontFamily: '"Syne", sans-serif', fontSize: 30, fontWeight: 800, color: '#e8ecf2', letterSpacing: '-1px', marginBottom: 8 }}>Connexion</h1>
          <p style={{ fontSize: 14, color: 'rgba(255,255,255,0.4)', marginBottom: 36 }}>Bon retour ! Entrez vos identifiants.</p>

          {error && (
            <div style={{ background: 'rgba(248,113,113,0.1)', border: '1px solid rgba(248,113,113,0.3)', borderRadius: 10, padding: '12px 16px', fontSize: 13, color: '#f87171', marginBottom: 20 }}>
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
            <div>
              <label style={{ display: 'block', fontSize: 11, fontWeight: 600, letterSpacing: 1.5, textTransform: 'uppercase', color: 'rgba(255,255,255,0.4)', marginBottom: 8 }}>Adresse e-mail</label>
              <input type="email" style={inputStyle} value={email} onChange={e => setEmail(e.target.value)} required autoFocus placeholder="vous@exemple.com"
                onFocus={e => (e.currentTarget.style.borderColor = '#4fffb0')}
                onBlur={e => (e.currentTarget.style.borderColor = 'rgba(255,255,255,0.1)')} />
            </div>
            <div>
              <label style={{ display: 'block', fontSize: 11, fontWeight: 600, letterSpacing: 1.5, textTransform: 'uppercase', color: 'rgba(255,255,255,0.4)', marginBottom: 8 }}>Mot de passe</label>
              <input type="password" style={inputStyle} value={password} onChange={e => setPassword(e.target.value)} required placeholder="••••••••"
                onFocus={e => (e.currentTarget.style.borderColor = '#4fffb0')}
                onBlur={e => (e.currentTarget.style.borderColor = 'rgba(255,255,255,0.1)')} />
            </div>
            <button type="submit" disabled={loading} style={{
              marginTop: 8, padding: '13px', borderRadius: 100,
              background: loading ? 'rgba(79,255,176,0.4)' : '#4fffb0',
              color: '#080c14', fontWeight: 700, fontSize: 15, border: 'none',
              cursor: loading ? 'not-allowed' : 'pointer',
              fontFamily: '"DM Sans", sans-serif', transition: 'opacity .2s',
            }}>
              {loading ? 'Connexion...' : 'Se connecter →'}
            </button>
          </form>

          <p style={{ marginTop: 28, textAlign: 'center', fontSize: 14, color: 'rgba(255,255,255,0.35)' }}>
            Pas encore de compte ?{' '}
            <Link to="/register" style={{ color: '#4fffb0', fontWeight: 600, textDecoration: 'none' }}>S'inscrire gratuitement</Link>
          </p>
        </div>
      </div>
    </div>
  )
}
