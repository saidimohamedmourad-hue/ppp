// Use relative URL so Vite proxy handles CORS in dev
export const API_URL = '/api'

export async function apiFetch(endpoint: string, options: RequestInit = {}) {
  const token = localStorage.getItem('token')
  const res = await fetch(`${API_URL}/${endpoint}`, {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    ...options,
  })
  if (res.status === 401) {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    window.location.href = '/login'
    throw new Error('Non authentifié')
  }
  if (!res.ok) {
    const err = await res.json().catch(() => ({}))
    throw new Error((err as { message?: string }).message ?? `Erreur ${res.status}`)
  }
  return res.json()
}

export const isLoggedIn = () => !!localStorage.getItem('token')
export const getToken = () => localStorage.getItem('token')

export const getUser = () => {
  try {
    return JSON.parse(localStorage.getItem('user') ?? 'null')
  } catch {
    return null
  }
}

export const logout = async () => {
  const token = getToken()
  if (token) {
    await fetch(`${API_URL}/logout`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
    }).catch(() => {})
  }
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  window.location.href = '/'
}
