/**
 * Lazy loader for the Cloudflare Turnstile widget.
 *
 * Loaded on demand (the script is only fetched the first time we mount the
 * widget) and idempotent — subsequent mounts reuse the already-injected
 * script. Returns the global `turnstile` API once it's ready.
 */

declare global {
  interface Window {
    turnstile?: TurnstileSdk
  }
}

interface TurnstileRenderOptions {
  sitekey: string
  callback: (token: string) => void
  'error-callback'?: () => void
  'expired-callback'?: () => void
  theme?: 'light' | 'dark' | 'auto'
  size?: 'normal' | 'compact' | 'flexible' | 'invisible'
}

interface TurnstileSdk {
  render: (container: string | HTMLElement, opts: TurnstileRenderOptions) => string
  reset: (widgetId: string) => void
  remove: (widgetId: string) => void
}

let loadPromise: Promise<TurnstileSdk> | null = null

export function loadTurnstile(): Promise<TurnstileSdk> {
  if (loadPromise) return loadPromise

  loadPromise = new Promise<TurnstileSdk>((resolve, reject) => {
    if (window.turnstile) {
      resolve(window.turnstile)
      return
    }

    const script = document.createElement('script')
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit'
    script.async = true
    script.defer = true
    script.onload = () => {
      if (window.turnstile) resolve(window.turnstile)
      else reject(new Error('Turnstile script loaded but the global is missing.'))
    }
    script.onerror = () => reject(new Error('Impossible de charger le widget Turnstile.'))
    document.head.appendChild(script)
  })

  return loadPromise
}
