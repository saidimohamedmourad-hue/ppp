import { useEffect, useRef } from 'react'
import { loadTurnstile } from '@/utils/turnstileSdk'

interface Props {
  siteKey: string
  /** Called with a fresh widget token once the user passes the challenge. */
  onToken: (token: string) => void
  /** Called when the widget hits an error or the token expires. */
  onError?: () => void
  theme?: 'light' | 'dark' | 'auto'
}

/**
 * Renders the Cloudflare Turnstile challenge. Most users see no UI at all
 * (Cloudflare scores them as human and emits the token silently); only
 * suspicious traffic gets a checkbox / puzzle.
 *
 * The parent passes the token to the API; on backend rejection the widget
 * should be reset by remounting (key prop) so a fresh token is issued.
 */
export function TurnstileWidget({ siteKey, onToken, onError, theme = 'dark' }: Props) {
  const container = useRef<HTMLDivElement>(null)
  const widgetIdRef = useRef<string | null>(null)

  useEffect(() => {
    let cancelled = false
    let sdk: Awaited<ReturnType<typeof loadTurnstile>> | null = null

    loadTurnstile()
      .then((api) => {
        if (cancelled || !container.current) return
        sdk = api
        widgetIdRef.current = api.render(container.current, {
          sitekey: siteKey,
          callback: onToken,
          'error-callback':  () => onError?.(),
          'expired-callback': () => onError?.(),
          theme,
        })
      })
      .catch(() => onError?.())

    return () => {
      cancelled = true
      if (sdk && widgetIdRef.current) {
        try { sdk.remove(widgetIdRef.current) } catch { /* widget already gone */ }
      }
    }
    // siteKey is the only prop that should force a remount; callbacks can change.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [siteKey])

  return <div ref={container} style={{ display: 'flex', justifyContent: 'center' }} />
}
