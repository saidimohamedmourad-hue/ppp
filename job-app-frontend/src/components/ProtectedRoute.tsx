import { useEffect } from 'react'
import { isLoggedIn } from '@/utils/api'

export default function ProtectedRoute({ children }: { children: React.ReactNode }) {
  useEffect(() => {
    if (!isLoggedIn()) {
      window.location.href = '/login'
    }
  }, [])

  if (!isLoggedIn()) return null
  return <>{children}</>
}
