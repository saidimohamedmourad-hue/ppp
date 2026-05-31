import { useState, useMemo } from 'react'
import { Link, useSearchParams, useNavigate } from 'react-router-dom'
import { apiFetch } from '@/utils/api'

/**
 * Step 2 of the password recovery flow: reached via the magic link in the
 * email. The token + email are query-string params. On success the backend
 * also returns an auth token so we can log the user straight in.
 */
export default function ResetPassword() {
  const [params] = useSearchParams()
  const navigate = useNavigate()

  // Extract once so it survives state updates.
  const { token, email } = useMemo(() => ({
    token: params.get('token') ?? '',
    email: params.get('email') ?? '',
  }), [params])

  const [password, setPassword] = useState('')
  const [confirm, setConfirm] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const tokenMissing = !token || !email

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (password.length < 8) { setError('Le mot de passe doit faire au moins 8 caractères.'); return }
    if (password !== confirm) { setError('Les mots de passe ne correspondent pas.'); return }

    setLoading(true)
    setError(null)
    try {
      const res = await apiFetch('reset-password', {
        method: 'POST',
        body: JSON.stringify({ email, token, password, password_confirmation: confirm }),
      })
      // Backend returns a fresh Sanctum token + user — log the user straight in.
      localStorage.setItem('token', res.token)
      localStorage.setItem('user', JSON.stringify(res.user))
      navigate('/dashboard', { replace: true })
    } catch (err) {
      setError(err instanceof Error ? err.message : 'La réinitialisation a échoué')
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
        <div style={{ position: 'absolute', top: -80, left: -80, width: 400, height: 400, borderRadius: '50%', background: 'radial-gradient(circle, rgba(79,255,176,0.08) 0%, transparent 70%)', pointerEvents: 'none' }} />

        <Link to="/" style={{ display: 'flex', alignItems: 'center', gap: 8, textDecoration: 'none', fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 19, color: 'white', position: 'relative' }}>
          <img src="/iqra-logo.png" alt="IQRA" style={{ width: 30, height: 30, borderRadius: 8, objectFit: 'cover' }} />
          IQRA
        </Link>

        <div style={{ position: 'relative' }}>
          <h2 style={{ fontFamily: '"Syne", sans-serif', fontSize: 'clamp(28px, 3vw, 44px)', fontWeight: 800, color: 'white', letterSpacing: '-1.5px', lineHeight: 1.15, marginBottom: 20 }}>
            Choisissez<br />
            <span style={{ background: 'linear-gradient(135deg, #4fffb0, #00d4ff)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
              un nouveau mot de passe.
            </span>
          </h2>
          <p style={{ color: 'rgba(255,255,255,0.45)', fontSize: 15, lineHeight: 1.75, maxWidth: 360 }}>
            8 caractères minimum. Évitez les mots de passe que vous utilisez ailleurs.
          </p>
        </div>

        <p style={{ fontSize: 13, color: 'rgba(255,255,255,0.2)', position: 'relative' }}>© 2026 IQRA · Algérie</p>
      </div>

      {/* ── Right panel ── */}
      <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '60px' }}>
        <div style={{ width: '100%', maxWidth: 400 }}>
          {tokenMissing ? (
            <>
              <div style={{ width: 56, height: 56, borderRadius: 16, background: 'rgba(248,113,113,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: 20, fontSize: 28 }}>⚠️</div>
              <h1 style={{ fontFamily: '"Syne", sans-serif', fontSize: 28, fontWeight: 800, color: '#e8ecf2', letterSpacing: '-0.8px', marginBottom: 12 }}>Lien invalide</h1>
              <p style={{ fontSize: 14, color: 'rgba(255,255,255,0.55)', lineHeight: 1.7, marginBottom: 28 }}>
                Ce lien de réinitialisation est incomplet ou corrompu. Demandez un nouveau lien pour continuer.
              </p>
              <Link to="/forgot-password" style={{ display: 'inline-block', padding: '11px 22px', borderRadius: 100, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 14, textDecoration: 'none' }}>
                Demander un nouveau lien
              </Link>
            </>
          ) : (
            <>
              <h1 style={{ fontFamily: '"Syne", sans-serif', fontSize: 30, fontWeight: 800, color: '#e8ecf2', letterSpacing: '-1px', marginBottom: 8 }}>Nouveau mot de passe</h1>
              <p style={{ fontSize: 14, color: 'rgba(255,255,255,0.4)', marginBottom: 36 }}>
                Pour <strong style={{ color: '#e8ecf2' }}>{email}</strong>
              </p>

              {error && (
                <div style={{ background: 'rgba(248,113,113,0.1)', border: '1px solid rgba(248,113,113,0.3)', borderRadius: 10, padding: '12px 16px', fontSize: 13, color: '#f87171', marginBottom: 20 }}>
                  {error}
                </div>
              )}

              <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
                <div>
                  <label style={{ display: 'block', fontSize: 11, fontWeight: 600, letterSpacing: 1.5, textTransform: 'uppercase', color: 'rgba(255,255,255,0.4)', marginBottom: 8 }}>Nouveau mot de passe</label>
                  <input type="password" style={inputStyle} value={password} onChange={e => setPassword(e.target.value)} required autoFocus minLength={8} placeholder="8 caractères minimum"
                    onFocus={e => (e.currentTarget.style.borderColor = '#4fffb0')}
                    onBlur={e => (e.currentTarget.style.borderColor = 'rgba(255,255,255,0.1)')} />
                </div>
                <div>
                  <label style={{ display: 'block', fontSize: 11, fontWeight: 600, letterSpacing: 1.5, textTransform: 'uppercase', color: 'rgba(255,255,255,0.4)', marginBottom: 8 }}>Confirmer</label>
                  <input type="password" style={inputStyle} value={confirm} onChange={e => setConfirm(e.target.value)} required minLength={8} placeholder="Retapez le mot de passe"
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
                  {loading ? 'Réinitialisation...' : 'Réinitialiser →'}
                </button>
              </form>

              <p style={{ marginTop: 28, textAlign: 'center', fontSize: 14, color: 'rgba(255,255,255,0.35)' }}>
                <Link to="/login" style={{ color: '#4fffb0', fontWeight: 600, textDecoration: 'none' }}>← Retour à la connexion</Link>
              </p>
            </>
          )}
        </div>
      </div>
    </div>
  )
}
