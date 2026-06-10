import { useState } from 'react'
import { useGoogleLogin } from '@react-oauth/google'
import { apiFetch } from '@/utils/api'
import { facebookLogin, loadFacebookSdk } from '@/utils/facebookSdk'

interface Props {
  /** Called with the fresh Sanctum token when sign-in succeeds. */
  onSuccess: (token: string, user: unknown) => void
  /** Optional error sink — wired up to the page-level error banner. */
  onError?: (message: string) => void
  /** Rôle souhaité à l'inscription (appliqué seulement si nouveau compte).
   *  Sans effet pour un compte social existant (il garde son rôle). */
  role?: string
}

/**
 * Renders the available social sign-in buttons (Google, Facebook). When no
 * provider is configured the component renders nothing instead of crashing.
 *
 * Note: `useGoogleLogin` throws if `<GoogleOAuthProvider>` isn't in the
 * tree, so we have to split the "do we render anything" decision from the
 * actual component that calls the hook.
 */
export function SocialAuthButtons(props: Props) {
  const hasGoogle   = !!import.meta.env.VITE_GOOGLE_WEB_CLIENT_ID
  const hasFacebook = !!import.meta.env.VITE_FACEBOOK_APP_ID

  if (!hasGoogle && !hasFacebook) return null

  // When only Facebook is configured, skip the Google-enabled inner component
  // (which would call useGoogleLogin and crash without a provider).
  return hasGoogle
    ? <SocialAuthButtonsInner {...props} hasFacebook={hasFacebook} />
    : <FacebookOnlyButtons {...props} />
}

/**
 * Standalone Facebook-only path. Used when no Google client is configured.
 */
function FacebookOnlyButtons({ onSuccess, onError, role }: Props) {
  const [busy, setBusy] = useState(false)
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
      <FacebookButton
        busy={busy}
        setBusy={setBusy}
        onSuccess={onSuccess}
        onError={onError}
        role={role}
      />
    </div>
  )
}

/**
 * Inner component — only mounted when GoogleOAuthProvider is in the tree.
 * The `useGoogleLogin` hook throws if the provider is missing, so we must
 * gate it behind the `hasGoogle` check above.
 */
function SocialAuthButtonsInner({ onSuccess, onError, hasFacebook, role }: Props & { hasFacebook: boolean }) {
  const [busy, setBusy] = useState<string | null>(null)

  // We use the implicit auth-code flow with `flow: 'implicit'` (= ID token),
  // not the access-token flow, because our backend verifies ID tokens via
  // tokeninfo (cheaper + no scope juggling).
  const googleLogin = useGoogleLogin({
    flow: 'implicit',
    scope: 'openid email profile',
    onSuccess: async (resp) => {
      setBusy('google')
      try {
        // The implicit flow returns an access_token, not an id_token. We swap
        // it server-side via the userinfo endpoint — easier than juggling two
        // SDKs. To use a real ID token, see GoogleLogin (one-tap button)
        // instead.
        // For now we wire the access_token through; the backend's tokeninfo
        // endpoint accepts both `id_token` and `access_token` callers, but
        // we'll feed it the cleanest path via a server-side userinfo call.
        const r = await apiFetch('auth/google', {
          method: 'POST',
          body: JSON.stringify({ access_token: resp.access_token, ...(role ? { role } : {}) }),
        }) as { token: string; user: unknown }
        onSuccess(r.token, r.user)
      } catch (e) {
        onError?.(e instanceof Error ? e.message : 'Connexion Google échouée')
      } finally {
        setBusy(null)
      }
    },
    onError: () => onError?.('Connexion Google annulée ou refusée.'),
  })

  const btnStyle: React.CSSProperties = {
    width: '100%', padding: '11px 16px', borderRadius: 100,
    background: 'rgba(255,255,255,0.04)', border: '1px solid rgba(255,255,255,0.12)',
    color: '#e8ecf2', fontWeight: 600, fontSize: 14,
    cursor: 'pointer', fontFamily: '"DM Sans", sans-serif',
    display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10,
    transition: 'background .15s, border-color .15s',
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
      <button
        type="button"
        style={{ ...btnStyle, opacity: busy === 'google' ? 0.6 : 1 }}
        disabled={busy !== null}
        onClick={() => googleLogin()}
        onMouseEnter={e => { e.currentTarget.style.background = 'rgba(255,255,255,0.07)'; e.currentTarget.style.borderColor = 'rgba(255,255,255,0.2)' }}
        onMouseLeave={e => { e.currentTarget.style.background = 'rgba(255,255,255,0.04)'; e.currentTarget.style.borderColor = 'rgba(255,255,255,0.12)' }}
      >
        <GoogleGlyph />
        {busy === 'google' ? 'Connexion…' : 'Continuer avec Google'}
      </button>
      {hasFacebook && (
        <FacebookButton
          busy={busy === 'facebook'}
          setBusy={(b) => setBusy(b ? 'facebook' : null)}
          onSuccess={onSuccess}
          onError={onError}
          role={role}
        />
      )}
    </div>
  )
}

/**
 * Reusable Facebook button — extracted so the Google+Facebook combo and the
 * Facebook-only path can share the exact same logic.
 */
function FacebookButton({
  busy, setBusy, onSuccess, onError, role,
}: {
  busy: boolean
  setBusy: (b: boolean) => void
  onSuccess: (token: string, user: unknown) => void
  onError?: (msg: string) => void
  role?: string
}) {
  const handleClick = async () => {
    const appId = import.meta.env.VITE_FACEBOOK_APP_ID
    if (!appId) {
      onError?.('Facebook Login n\'est pas configuré.')
      return
    }
    setBusy(true)
    try {
      const FB = await loadFacebookSdk(appId)
      const auth = await facebookLogin(FB)
      const r = await apiFetch('auth/facebook', {
        method: 'POST',
        body: JSON.stringify({ access_token: auth.accessToken, ...(role ? { role } : {}) }),
      }) as { token: string; user: unknown }
      onSuccess(r.token, r.user)
    } catch (e) {
      onError?.(e instanceof Error ? e.message : 'Connexion Facebook échouée')
    } finally {
      setBusy(false)
    }
  }

  return (
    <button
      type="button"
      onClick={handleClick}
      disabled={busy}
      style={{
        width: '100%', padding: '11px 16px', borderRadius: 100,
        background: busy ? 'rgba(24,119,242,0.6)' : '#1877F2',
        border: '1px solid transparent',
        color: 'white', fontWeight: 600, fontSize: 14,
        cursor: busy ? 'not-allowed' : 'pointer',
        fontFamily: '"DM Sans", sans-serif',
        display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10,
        transition: 'background .15s',
      }}
      onMouseEnter={e => !busy && (e.currentTarget.style.background = '#0d6efd')}
      onMouseLeave={e => !busy && (e.currentTarget.style.background = '#1877F2')}
    >
      <FacebookGlyph />
      {busy ? 'Connexion…' : 'Continuer avec Facebook'}
    </button>
  )
}

function GoogleGlyph() {
  return (
    <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
      <path d="M17.64 9.205c0-.638-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.614z" fill="#4285F4"/>
      <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.836.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
      <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
      <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
    </svg>
  )
}

function FacebookGlyph() {
  return (
    <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
      <path d="M18 9.05C18 4.05 13.97 0 9 0S0 4.05 0 9.05c0 4.52 3.29 8.27 7.59 8.94v-6.32H5.3V9.05h2.29V7.06c0-2.27 1.34-3.52 3.39-3.52.98 0 2.01.18 2.01.18v2.22h-1.13c-1.12 0-1.46.7-1.46 1.41V9.05h2.49l-.4 2.62h-2.1V18C14.71 17.32 18 13.57 18 9.05z" fill="white"/>
    </svg>
  )
}
