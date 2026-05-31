/**
 * Lazy loader for the Facebook JS SDK. Returns the global `FB` object once
 * the SDK has finished initializing. Subsequent calls reuse the same Promise
 * — no double-load, no double-init.
 *
 * We avoid pulling a React wrapper just for a single button: this saves a
 * dependency and lets us trigger the SDK only when the user clicks the
 * button.
 */

declare global {
  interface Window {
    fbAsyncInit?: () => void
    FB?: FacebookSdk
  }
}

interface FacebookAuthResponse {
  accessToken: string
  userID: string
  expiresIn: number
  signedRequest: string
}

interface FacebookLoginResponse {
  status: 'connected' | 'not_authorized' | 'unknown'
  authResponse: FacebookAuthResponse | null
}

interface FacebookSdk {
  init: (config: { appId: string; cookie: boolean; xfbml: boolean; version: string }) => void
  login: (cb: (resp: FacebookLoginResponse) => void, opts: { scope: string }) => void
  logout: (cb: () => void) => void
  getLoginStatus: (cb: (resp: FacebookLoginResponse) => void) => void
}

let loadPromise: Promise<FacebookSdk> | null = null

export function loadFacebookSdk(appId: string): Promise<FacebookSdk> {
  if (loadPromise) return loadPromise

  loadPromise = new Promise<FacebookSdk>((resolve, reject) => {
    // Already loaded (e.g. via a previous component instance).
    if (window.FB) {
      resolve(window.FB)
      return
    }

    window.fbAsyncInit = () => {
      if (!window.FB) {
        reject(new Error('Facebook SDK loaded but FB global is missing.'))
        return
      }
      window.FB.init({
        appId,
        cookie: true,
        xfbml: false,
        version: 'v19.0',
      })
      resolve(window.FB)
    }

    const script = document.createElement('script')
    script.id = 'facebook-jssdk'
    script.async = true
    script.defer = true
    script.crossOrigin = 'anonymous'
    script.src = 'https://connect.facebook.net/en_US/sdk.js'
    script.onerror = () => reject(new Error('Impossible de charger le SDK Facebook.'))
    document.head.appendChild(script)
  })

  return loadPromise
}

export function facebookLogin(FB: FacebookSdk): Promise<FacebookAuthResponse> {
  return new Promise((resolve, reject) => {
    FB.login(
      (resp) => {
        if (resp.status === 'connected' && resp.authResponse) {
          resolve(resp.authResponse)
        } else {
          reject(new Error('Connexion Facebook annulée ou refusée.'))
        }
      },
      { scope: 'email,public_profile' },
    )
  })
}
