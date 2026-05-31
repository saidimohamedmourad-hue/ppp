import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/utils/api'
import { TurnstileWidget } from '@/components/auth/TurnstileWidget'

/**
 * Step 1 of the password recovery flow: the user enters their email, we POST
 * to /api/forgot-password, the backend sends a magic link to that email.
 *
 * The backend never reveals whether the email is registered, so the success
 * UI is identical regardless — that's intentional, not a bug.
 */
export default function ForgotPassword() {
  const [email, setEmail] = useState('')
  const [loading, setLoading] = useState(false)
  const [sent, setSent] = useState(false)
  const [error, setError] = useState<string | null>(null)
  // Turnstile lazy-loaded config: undefined = still checking, null = disabled.
  const [turnstileSiteKey, setTurnstileSiteKey] = useState<string | null | undefined>(undefined)
  const [turnstileToken, setTurnstileToken] = useState<string | null>(null)
  // Used as a key to force-remount the widget after a failed POST so a fresh
  // token is fetched (Turnstile tokens are single-use).
  const [widgetNonce, setWidgetNonce] = useState(0)

  useEffect(() => {
    // Pull the server-side switch once on mount; in dev the secret is unset
    // so the widget never shows up.
    apiFetch('config')
      .then((c: { turnstile?: { enabled?: boolean; site_key?: string | null } }) => {
        if (c.turnstile?.enabled && c.turnstile.site_key) {
          setTurnstileSiteKey(c.turnstile.site_key)
        } else {
          setTurnstileSiteKey(null)
        }
      })
      .catch(() => setTurnstileSiteKey(null))
  }, [])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (turnstileSiteKey && !turnstileToken) {
      setError('Veuillez compléter la vérification anti-bot avant de continuer.')
      return
    }
    setLoading(true)
    setError(null)
    try {
      await apiFetch('forgot-password', {
        method: 'POST',
        body: JSON.stringify({
          email,
          ...(turnstileToken ? { turnstile_token: turnstileToken } : {}),
        }),
      })
      setSent(true)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Une erreur est survenue')
      // Token is consumed even on failure — force a new one.
      setTurnstileToken(null)
      setWidgetNonce((n) => n + 1)
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
            Pas de panique,<br />
            <span style={{ background: 'linear-gradient(135deg, #4fffb0, #00d4ff)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
              on s'en occupe.
            </span>
          </h2>
          <p style={{ color: 'rgba(255,255,255,0.45)', fontSize: 15, lineHeight: 1.75, maxWidth: 360 }}>
            Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe en quelques secondes.
          </p>
        </div>

        <p style={{ fontSize: 13, color: 'rgba(255,255,255,0.2)', position: 'relative' }}>© 2026 IQRA · Algérie</p>
      </div>

      {/* ── Right panel ── */}
      <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '60px' }}>
        <div style={{ width: '100%', maxWidth: 400 }}>
          {sent ? (
            <>
              <div style={{ width: 56, height: 56, borderRadius: 16, background: 'rgba(79,255,176,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: 20, fontSize: 28 }}>📬</div>
              <h1 style={{ fontFamily: '"Syne", sans-serif', fontSize: 28, fontWeight: 800, color: '#e8ecf2', letterSpacing: '-0.8px', marginBottom: 12 }}>Vérifiez votre boîte mail</h1>
              <p style={{ fontSize: 14, color: 'rgba(255,255,255,0.55)', lineHeight: 1.7, marginBottom: 28 }}>
                Si un compte est associé à <strong style={{ color: '#e8ecf2' }}>{email}</strong>, un email contenant un lien de réinitialisation vient d'être envoyé. Pensez à vérifier vos spams.
              </p>
              <Link to="/login" style={{ display: 'inline-block', padding: '11px 22px', borderRadius: 100, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 14, textDecoration: 'none' }}>
                Retour à la connexion
              </Link>
            </>
          ) : (
            <>
              <h1 style={{ fontFamily: '"Syne", sans-serif', fontSize: 30, fontWeight: 800, color: '#e8ecf2', letterSpacing: '-1px', marginBottom: 8 }}>Mot de passe oublié ?</h1>
              <p style={{ fontSize: 14, color: 'rgba(255,255,255,0.4)', marginBottom: 36 }}>Indiquez votre e-mail pour recevoir le lien.</p>

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
                {turnstileSiteKey && (
                  <div style={{ marginTop: 4 }}>
                    <TurnstileWidget
                      key={widgetNonce}
                      siteKey={turnstileSiteKey}
                      onToken={(t) => setTurnstileToken(t)}
                      onError={() => setTurnstileToken(null)}
                    />
                  </div>
                )}
                <button type="submit" disabled={loading} style={{
                  marginTop: 8, padding: '13px', borderRadius: 100,
                  background: loading ? 'rgba(79,255,176,0.4)' : '#4fffb0',
                  color: '#080c14', fontWeight: 700, fontSize: 15, border: 'none',
                  cursor: loading ? 'not-allowed' : 'pointer',
                  fontFamily: '"DM Sans", sans-serif', transition: 'opacity .2s',
                }}>
                  {loading ? 'Envoi en cours...' : 'Envoyer le lien →'}
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
