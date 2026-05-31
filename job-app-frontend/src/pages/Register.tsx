import { useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { apiFetch } from '@/utils/api'
import { SocialAuthButtons } from '@/components/auth/SocialAuthButtons'

type Role = 'job-seeker' | 'company-owner' | 'school-owner'

const ROLES: { id: Role; icon: string; label: string; desc: string; color: string }[] = [
  { id: 'job-seeker',    icon: '🙋', label: 'Candidat',       desc: 'Je cherche un emploi ou une formation',      color: '#4fffb0' },
  { id: 'company-owner', icon: '🏢', label: 'Entreprise',     desc: 'Je recrute et publie des offres d\'emploi',   color: '#00d4ff' },
  { id: 'school-owner',  icon: '🎓', label: 'École / Centre', desc: 'Je propose des formations professionnelles',  color: '#a78bfa' },
]

export default function Register() {
  const [searchParams] = useSearchParams()
  const presetRole = searchParams.get('role') as Role | null

  const [step, setStep] = useState<1 | 2>(presetRole ? 2 : 1)
  const [role, setRole] = useState<Role | null>(presetRole)
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [phone, setPhone] = useState('')
  const [password, setPassword] = useState('')
  const [confirm, setConfirm] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  // Phone is mandatory at signup for company-owner and school-owner because
  // it becomes the public contact number on their jobs/training sessions.
  // Candidates can leave it blank and provide it on first job application.
  const needsPhoneAtSignup = role === 'company-owner' || role === 'school-owner'

  useEffect(() => {
    if (presetRole) { setRole(presetRole); setStep(2) }
  }, [presetRole])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (password !== confirm) { setError('Les mots de passe ne correspondent pas.'); return }
    if (needsPhoneAtSignup && phone.trim().length < 6) {
      setError('Le numéro de téléphone est obligatoire pour les entreprises et écoles.')
      return
    }
    setLoading(true); setError(null)
    try {
      const res = await apiFetch('register', {
        method: 'POST',
        body: JSON.stringify({
          name, email, password, password_confirmation: confirm, role,
          // Send phone whenever the user typed one — backend will validate
          // it's present for owners and accept it optionally for candidates.
          ...(phone.trim() ? { phone: phone.trim() } : {}),
        }),
      })
      localStorage.setItem('token', res.token)
      localStorage.setItem('user', JSON.stringify(res.user))
      window.location.href = '/dashboard'
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur lors de l'inscription")
    } finally {
      setLoading(false)
    }
  }

  const selectedRole = ROLES.find(r => r.id === role)

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
        <div style={{ position: 'absolute', top: -80, right: -80, width: 400, height: 400, borderRadius: '50%', background: 'radial-gradient(circle, rgba(79,255,176,0.07) 0%, transparent 70%)', pointerEvents: 'none' }} />

        <Link to="/" style={{ display: 'flex', alignItems: 'center', gap: 8, textDecoration: 'none', fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 19, color: 'white', position: 'relative' }}>
          <img src="/iqra-logo.png" alt="IQRA" style={{ width: 30, height: 30, borderRadius: 8, objectFit: 'cover' }} />
          IQRA
        </Link>

        <div style={{ position: 'relative' }}>
          <h2 style={{ fontFamily: '"Syne", sans-serif', fontSize: 'clamp(28px, 3vw, 44px)', fontWeight: 800, color: 'white', letterSpacing: '-1.5px', lineHeight: 1.15, marginBottom: 20 }}>
            Lancez votre carrière<br />
            <span style={{ background: 'linear-gradient(135deg, #4fffb0, #00d4ff)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
              dès aujourd'hui.
            </span>
          </h2>
          <p style={{ color: 'rgba(255,255,255,0.45)', fontSize: 15, lineHeight: 1.75, maxWidth: 360 }}>
            Rejoignez des milliers de membres qui utilisent IQRA pour trouver emploi et formation.
          </p>
          <ul style={{ marginTop: 36, display: 'flex', flexDirection: 'column', gap: 14, listStyle: 'none', padding: 0 }}>
            {["Accès à toutes les offres d'emploi", 'Candidature en un clic', 'Suivi en temps réel', 'Gestion des équipes pour entreprises'].map(f => (
              <li key={f} style={{ display: 'flex', alignItems: 'center', gap: 10, fontSize: 14, color: 'rgba(255,255,255,0.55)' }}>
                <span style={{ width: 20, height: 20, borderRadius: '50%', background: 'rgba(79,255,176,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 10, color: '#4fffb0', flexShrink: 0 }}>✓</span>
                {f}
              </li>
            ))}
          </ul>
        </div>

        <p style={{ fontSize: 13, color: 'rgba(255,255,255,0.2)', position: 'relative' }}>© 2026 IQRA · Algérie</p>
      </div>

      {/* ── Right panel ── */}
      <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '60px' }}>
        <div style={{ width: '100%', maxWidth: 440 }}>

          {step === 1 ? (
            <>
              <h1 style={{ fontFamily: '"Syne", sans-serif', fontSize: 28, fontWeight: 800, color: '#e8ecf2', letterSpacing: '-1px', marginBottom: 8 }}>Créer un compte</h1>
              <p style={{ fontSize: 14, color: 'rgba(255,255,255,0.4)', marginBottom: 32 }}>Quel est votre profil ?</p>

              <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 28 }}>
                {ROLES.map(r => (
                  <button key={r.id} type="button" onClick={() => setRole(r.id)}
                    style={{
                      display: 'flex', alignItems: 'center', gap: 14, padding: '14px 18px',
                      borderRadius: 14, border: `1px solid ${role === r.id ? r.color + '50' : 'rgba(255,255,255,0.08)'}`,
                      background: role === r.id ? `${r.color}0d` : 'rgba(255,255,255,0.02)',
                      cursor: 'pointer', textAlign: 'left', transition: 'all .15s', width: '100%',
                    }}
                  >
                    <div style={{ width: 42, height: 42, borderRadius: 12, background: `${r.color}15`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20, flexShrink: 0 }}>{r.icon}</div>
                    <div style={{ flex: 1 }}>
                      <div style={{ fontFamily: '"Syne", sans-serif', fontWeight: 700, fontSize: 14, color: '#e8ecf2' }}>{r.label}</div>
                      <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.38)', marginTop: 2 }}>{r.desc}</div>
                    </div>
                    {role === r.id && <span style={{ color: r.color, fontSize: 16, flexShrink: 0 }}>✓</span>}
                  </button>
                ))}
              </div>

              <button type="button" disabled={!role} onClick={() => role && setStep(2)}
                style={{
                  width: '100%', padding: '13px', borderRadius: 100,
                  background: role ? '#4fffb0' : 'rgba(255,255,255,0.08)',
                  color: role ? '#080c14' : 'rgba(255,255,255,0.3)',
                  fontWeight: 700, fontSize: 15, border: 'none', cursor: role ? 'pointer' : 'not-allowed',
                  fontFamily: '"DM Sans", sans-serif', transition: 'all .2s',
                }}
              >Continuer →</button>

              {/* Social signup shortcut — only for the candidat role since social
                  accounts default to job-seeker on the backend. Hidden when no
                  Google client ID is configured. */}
              {role === 'job-seeker' && (import.meta.env.VITE_GOOGLE_WEB_CLIENT_ID || import.meta.env.VITE_FACEBOOK_APP_ID) && (
                <>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 12, margin: '20px 0' }}>
                    <div style={{ flex: 1, height: 1, background: 'rgba(255,255,255,0.08)' }} />
                    <span style={{ fontSize: 11, fontWeight: 600, letterSpacing: 1.5, textTransform: 'uppercase', color: 'rgba(255,255,255,0.3)' }}>ou en un clic</span>
                    <div style={{ flex: 1, height: 1, background: 'rgba(255,255,255,0.08)' }} />
                  </div>
                  <SocialAuthButtons
                    onSuccess={(token, user) => {
                      localStorage.setItem('token', token)
                      localStorage.setItem('user', JSON.stringify(user))
                      window.location.href = '/dashboard'
                    }}
                    onError={() => {/* silent on the role-selection step */}}
                  />
                </>
              )}

              <p style={{ marginTop: 24, textAlign: 'center', fontSize: 14, color: 'rgba(255,255,255,0.35)' }}>
                Déjà un compte ?{' '}
                <Link to="/login" style={{ color: '#4fffb0', fontWeight: 600, textDecoration: 'none' }}>Se connecter</Link>
              </p>
            </>
          ) : (
            <>
              <button onClick={() => setStep(1)} style={{ display: 'flex', alignItems: 'center', gap: 6, color: 'rgba(255,255,255,0.4)', fontSize: 13, background: 'none', border: 'none', cursor: 'pointer', marginBottom: 28, padding: 0 }}>
                ← Retour
              </button>
              <h1 style={{ fontFamily: '"Syne", sans-serif', fontSize: 28, fontWeight: 800, color: '#e8ecf2', letterSpacing: '-1px', marginBottom: 16 }}>Vos informations</h1>

              {selectedRole && (
                <div style={{ display: 'inline-flex', alignItems: 'center', gap: 8, marginBottom: 24, background: `${selectedRole.color}10`, border: `1px solid ${selectedRole.color}30`, borderRadius: 10, padding: '8px 14px' }}>
                  <span style={{ fontSize: 16 }}>{selectedRole.icon}</span>
                  <span style={{ fontSize: 13, fontWeight: 600, color: selectedRole.color }}>{selectedRole.label}</span>
                </div>
              )}

              {error && (
                <div style={{ background: 'rgba(248,113,113,0.1)', border: '1px solid rgba(248,113,113,0.3)', borderRadius: 10, padding: '12px 16px', fontSize: 13, color: '#f87171', marginBottom: 20 }}>
                  {error}
                </div>
              )}

              <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
                {[
                  { label: 'Nom complet', type: 'text', value: name, set: setName, ph: 'Ahmed Benali' },
                  { label: 'Adresse e-mail', type: 'email', value: email, set: setEmail, ph: 'vous@exemple.com' },
                  {
                    label: needsPhoneAtSignup
                      ? 'Téléphone de contact (obligatoire)'
                      : 'Téléphone (optionnel, requis avant de postuler)',
                    type: 'tel',
                    value: phone,
                    set: setPhone,
                    ph: '+213 555 123 456',
                    required: needsPhoneAtSignup,
                  },
                  { label: 'Mot de passe', type: 'password', value: password, set: setPassword, ph: '8 caractères minimum' },
                  { label: 'Confirmer le mot de passe', type: 'password', value: confirm, set: setConfirm, ph: '••••••••' },
                ].map(f => (
                  <div key={f.label}>
                    <label style={{ display: 'block', fontSize: 11, fontWeight: 600, letterSpacing: 1.5, textTransform: 'uppercase', color: 'rgba(255,255,255,0.4)', marginBottom: 8 }}>{f.label}</label>
                    <input
                      type={f.type}
                      style={inputStyle}
                      value={f.value}
                      onChange={e => f.set(e.target.value)}
                      required={f.type !== 'tel' || (f as { required?: boolean }).required === true}
                      placeholder={f.ph}
                      autoComplete={f.type === 'tel' ? 'tel' : undefined}
                      onFocus={e => (e.currentTarget.style.borderColor = '#4fffb0')}
                      onBlur={e => (e.currentTarget.style.borderColor = 'rgba(255,255,255,0.1)')} />
                  </div>
                ))}
                <button type="submit" disabled={loading}
                  style={{
                    marginTop: 8, padding: '13px', borderRadius: 100,
                    background: loading ? 'rgba(79,255,176,0.4)' : '#4fffb0',
                    color: '#080c14', fontWeight: 700, fontSize: 15, border: 'none',
                    cursor: loading ? 'not-allowed' : 'pointer',
                    fontFamily: '"DM Sans", sans-serif',
                  }}
                >
                  {loading ? 'Création du compte...' : 'Créer mon compte →'}
                </button>
              </form>
            </>
          )}
        </div>
      </div>
    </div>
  )
}
