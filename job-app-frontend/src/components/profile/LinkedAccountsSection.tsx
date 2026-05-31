import { useEffect, useState, useCallback } from 'react'
import { useGoogleLogin } from '@react-oauth/google'
import { apiFetch } from '@/utils/api'
import { facebookLogin, loadFacebookSdk } from '@/utils/facebookSdk'

interface LinkedProvider {
  id: string
  provider: 'google' | 'facebook'
  display_id: string
  linked_at: string
}

interface AccountsState {
  has_password: boolean
  providers: LinkedProvider[]
}

/**
 * Profile section that lets the user manage the social providers attached
 * to their account, plus a "set a password" form for social-only accounts.
 *
 * Mounted from the candidate Profile page only — companies/schools don't
 * use social sign-in in MVP.
 */
export function LinkedAccountsSection() {
  const hasGoogleEnv   = !!import.meta.env.VITE_GOOGLE_WEB_CLIENT_ID
  const hasFacebookEnv = !!import.meta.env.VITE_FACEBOOK_APP_ID
  if (!hasGoogleEnv && !hasFacebookEnv) return null

  return hasGoogleEnv
    ? <LinkedAccountsInner hasFacebookEnv={hasFacebookEnv} />
    : <LinkedAccountsInner hasFacebookEnv={hasFacebookEnv} googleDisabled />
}

function LinkedAccountsInner({
  hasFacebookEnv,
  googleDisabled = false,
}: {
  hasFacebookEnv: boolean
  googleDisabled?: boolean
}) {
  const [state, setState] = useState<AccountsState | null>(null)
  const [busy, setBusy] = useState<string | null>(null)
  const [msg, setMsg] = useState<{ type: 'ok' | 'err'; text: string } | null>(null)
  const [pwdForm, setPwdForm] = useState({ current: '', next: '', confirm: '' })

  const refresh = useCallback(async () => {
    try {
      const r = await apiFetch('profile/auth-providers') as AccountsState
      setState(r)
    } catch (e) {
      toast('err', e instanceof Error ? e.message : 'Erreur de chargement')
    }
  }, [])

  useEffect(() => { void refresh() }, [refresh])

  const toast = (type: 'ok' | 'err', text: string) => {
    setMsg({ type, text })
    setTimeout(() => setMsg(null), 3500)
  }

  const linkGoogle = useGoogleLogin({
    flow: 'implicit',
    scope: 'openid email profile',
    onSuccess: async (resp) => {
      setBusy('google')
      try {
        await apiFetch('profile/auth-providers/google', {
          method: 'POST',
          body: JSON.stringify({ access_token: resp.access_token }),
        })
        await refresh()
        toast('ok', 'Compte Google lié.')
      } catch (e) {
        toast('err', e instanceof Error ? e.message : 'Erreur de liaison Google')
      } finally {
        setBusy(null)
      }
    },
    onError: () => toast('err', 'Connexion Google annulée.'),
  })

  const linkFacebook = async () => {
    const appId = import.meta.env.VITE_FACEBOOK_APP_ID
    if (!appId) return
    setBusy('facebook')
    try {
      const FB = await loadFacebookSdk(appId)
      const auth = await facebookLogin(FB)
      await apiFetch('profile/auth-providers/facebook', {
        method: 'POST',
        body: JSON.stringify({ access_token: auth.accessToken }),
      })
      await refresh()
      toast('ok', 'Compte Facebook lié.')
    } catch (e) {
      toast('err', e instanceof Error ? e.message : 'Erreur de liaison Facebook')
    } finally {
      setBusy(null)
    }
  }

  const unlink = async (p: LinkedProvider) => {
    if (!window.confirm(`Supprimer le compte ${p.provider} lié à votre profil ?`)) return
    setBusy('unlink:'+p.id)
    try {
      await apiFetch('profile/auth-providers/'+p.id, { method: 'DELETE' })
      await refresh()
      toast('ok', 'Méthode de connexion supprimée.')
    } catch (e) {
      toast('err', e instanceof Error ? e.message : 'Erreur de suppression')
    } finally {
      setBusy(null)
    }
  }

  const setPassword = async (e: React.FormEvent) => {
    e.preventDefault()
    if (pwdForm.next !== pwdForm.confirm) { toast('err', 'Les mots de passe ne correspondent pas.'); return }
    setBusy('set-password')
    try {
      await apiFetch('profile/password-init', {
        method: 'POST',
        body: JSON.stringify({
          ...(state?.has_password ? { current_password: pwdForm.current } : {}),
          password: pwdForm.next,
          password_confirmation: pwdForm.confirm,
        }),
      })
      await refresh()
      setPwdForm({ current: '', next: '', confirm: '' })
      toast('ok', 'Mot de passe défini.')
    } catch (e) {
      toast('err', e instanceof Error ? e.message : 'Erreur')
    } finally {
      setBusy(null)
    }
  }

  if (state === null) {
    return (
      <div className="d-card" style={{ color: 'var(--d-muted)', fontSize: 13 }}>
        Chargement des méthodes de connexion…
      </div>
    )
  }

  // A user can always unlink if they still have a password,
  // OR they still have ≥2 linked providers after removing this one.
  const canUnlink = state.has_password || state.providers.length > 1

  return (
    <div className="d-card" style={{ marginTop: 14 }}>
      {msg && (
        <div style={{
          marginBottom: 14, padding: '10px 14px', borderRadius: 10,
          background: msg.type === 'ok' ? 'rgba(74,222,128,0.12)' : 'rgba(248,113,113,0.12)',
          border: `1px solid ${msg.type === 'ok' ? 'var(--d-green)' : 'var(--d-red)'}`,
          color: msg.type === 'ok' ? 'var(--d-green)' : 'var(--d-red)',
          fontSize: 13,
        }}>{msg.text}</div>
      )}

      <div className="d-card-title" style={{ marginBottom: 6 }}>Méthodes de connexion</div>
      <p style={{ fontSize: 13, color: 'var(--d-muted)', marginBottom: 18 }}>
        Liez vos comptes Google ou Facebook pour vous connecter plus rapidement, ou définissez un mot de passe local.
      </p>

      {/* ── Linked providers list ───────────────────────────────────── */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
        {!googleDisabled && <ProviderRow
          provider="google"
          name="Google"
          linked={state.providers.find(p => p.provider === 'google') ?? null}
          busyBucket={busy}
          canUnlink={canUnlink}
          onLink={() => linkGoogle()}
          onUnlink={unlink}
        />}
        {hasFacebookEnv && <ProviderRow
          provider="facebook"
          name="Facebook"
          linked={state.providers.find(p => p.provider === 'facebook') ?? null}
          busyBucket={busy}
          canUnlink={canUnlink}
          onLink={linkFacebook}
          onUnlink={unlink}
        />}
        {/* Password is also a "method" — show it as a row too. */}
        <PasswordRow hasPassword={state.has_password} />
      </div>

      {/* ── Set / change password form ──────────────────────────────── */}
      <div style={{ marginTop: 22, paddingTop: 18, borderTop: '1px solid var(--d-border)' }}>
        <div className="d-card-title" style={{ marginBottom: 12 }}>
          {state.has_password ? 'Changer le mot de passe' : 'Définir un mot de passe'}
        </div>
        <p style={{ fontSize: 12.5, color: 'var(--d-muted)', marginBottom: 14 }}>
          {state.has_password
            ? 'Pour modifier votre mot de passe local, indiquez d\'abord l\'ancien.'
            : 'Votre compte n\'a pas encore de mot de passe — vous ne pouvez vous connecter que via Google ou Facebook. Définissez-en un pour avoir une alternative.'}
        </p>
        <form onSubmit={setPassword} style={{ display: 'flex', flexDirection: 'column', gap: 12, maxWidth: 420 }}>
          {state.has_password && (
            <div>
              <label className="d-label">Mot de passe actuel</label>
              <input className="d-input" type="password" required minLength={8}
                value={pwdForm.current}
                onChange={e => setPwdForm(f => ({ ...f, current: e.target.value }))}
              />
            </div>
          )}
          <div>
            <label className="d-label">Nouveau mot de passe</label>
            <input className="d-input" type="password" required minLength={8}
              value={pwdForm.next}
              onChange={e => setPwdForm(f => ({ ...f, next: e.target.value }))}
            />
          </div>
          <div>
            <label className="d-label">Confirmer</label>
            <input className="d-input" type="password" required minLength={8}
              value={pwdForm.confirm}
              onChange={e => setPwdForm(f => ({ ...f, confirm: e.target.value }))}
            />
          </div>
          <button type="submit" className="d-btn-ghost" disabled={busy === 'set-password'}>
            {busy === 'set-password' ? 'Enregistrement…' : state.has_password ? 'Modifier' : 'Définir le mot de passe'}
          </button>
        </form>
      </div>
    </div>
  )
}

function ProviderRow({
  provider, name, linked, canUnlink, busyBucket, onLink, onUnlink,
}: {
  provider: 'google' | 'facebook'
  name: string
  linked: LinkedProvider | null
  canUnlink: boolean
  busyBucket: string | null
  onLink: () => void
  onUnlink: (p: LinkedProvider) => void
}) {
  const isBusy = busyBucket === provider || (linked && busyBucket === 'unlink:'+linked.id)
  return (
    <div style={{
      display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      padding: '12px 16px', borderRadius: 12, background: 'var(--d-surface2)',
      border: '1px solid var(--d-border)',
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
        <ProviderIcon provider={provider} />
        <div>
          <div style={{ fontSize: 14, fontWeight: 600, color: 'var(--d-text)' }}>{name}</div>
          <div style={{ fontSize: 12, color: 'var(--d-muted)' }}>
            {linked ? `Lié · id ${linked.display_id}` : 'Non lié'}
          </div>
        </div>
      </div>
      {linked ? (
        <button
          onClick={() => onUnlink(linked)}
          disabled={!canUnlink || !!isBusy}
          title={!canUnlink ? 'Définissez un mot de passe d\'abord' : ''}
          style={{
            padding: '7px 14px', borderRadius: 100, fontSize: 12, fontWeight: 500,
            background: 'transparent', color: canUnlink ? 'var(--d-red)' : 'var(--d-muted)',
            border: `1px solid ${canUnlink ? 'rgba(248,113,113,0.4)' : 'var(--d-border)'}`,
            cursor: canUnlink ? 'pointer' : 'not-allowed',
          }}
        >
          {isBusy ? '…' : 'Délier'}
        </button>
      ) : (
        <button
          onClick={onLink}
          disabled={!!isBusy}
          style={{
            padding: '7px 16px', borderRadius: 100, fontSize: 12, fontWeight: 600,
            background: '#4fffb0', color: '#080c14', border: 'none', cursor: 'pointer',
          }}
        >
          {isBusy ? 'Liaison…' : 'Lier'}
        </button>
      )}
    </div>
  )
}

function PasswordRow({ hasPassword }: { hasPassword: boolean }) {
  return (
    <div style={{
      display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      padding: '12px 16px', borderRadius: 12, background: 'var(--d-surface2)',
      border: '1px solid var(--d-border)',
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
        <div style={{
          width: 32, height: 32, borderRadius: 8,
          background: 'rgba(79,255,176,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center',
          fontSize: 16,
        }}>🔑</div>
        <div>
          <div style={{ fontSize: 14, fontWeight: 600, color: 'var(--d-text)' }}>Mot de passe</div>
          <div style={{ fontSize: 12, color: hasPassword ? 'var(--d-green)' : 'var(--d-muted)' }}>
            {hasPassword ? 'Défini' : 'Non défini — utilisez le formulaire ci-dessous'}
          </div>
        </div>
      </div>
    </div>
  )
}

function ProviderIcon({ provider }: { provider: 'google' | 'facebook' }) {
  if (provider === 'google') return (
    <svg width="32" height="32" viewBox="0 0 32 32" style={{ background: '#fff', borderRadius: 8, padding: 4 }}>
      <g transform="scale(1.3) translate(2, 2)">
        <path d="M17.64 9.205c0-.638-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.614z" fill="#4285F4"/>
        <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.836.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
        <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
        <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
      </g>
    </svg>
  )
  return (
    <div style={{
      width: 32, height: 32, borderRadius: 8, background: '#1877F2',
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      color: 'white', fontWeight: 900, fontSize: 20,
    }}>f</div>
  )
}
