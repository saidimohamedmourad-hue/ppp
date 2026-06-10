import { useState, useEffect, useRef, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { apiFetch } from '@/utils/api'

interface NotificationRow {
  id: string
  type: string
  data: {
    icon?: string
    title: string
    body?: string
    action_url?: string
    meta?: Record<string, unknown>
  }
  read_at: string | null
  created_at: string
}

/**
 * In-app notification bell with a dropdown showing the last few entries.
 *
 * Behaviour:
 *  - Polls /api/notifications/unread-count every 30s for the badge.
 *  - On click, opens a dropdown and lazily fetches the latest 5 notifs.
 *  - Clicking a row marks it read and navigates to its action_url.
 *  - "Tout marquer comme lu" wipes the badge in one PUT.
 *
 * Real-time push (WebSocket / Pusher) is intentionally out of scope for
 * this iteration — 30s polling is more than enough for the recruiter-flow
 * latencies we care about, and avoids piling on infra dependencies.
 */
export function NotificationBell() {
  const navigate = useNavigate()
  const [unread, setUnread] = useState(0)
  const [open, setOpen] = useState(false)
  const [items, setItems] = useState<NotificationRow[]>([])
  const [loading, setLoading] = useState(false)
  const containerRef = useRef<HTMLDivElement>(null)

  const fetchUnread = useCallback(async () => {
    try {
      const r = await apiFetch('notifications/unread-count') as { count: number }
      setUnread(r.count)
    } catch { /* silent: stale badge is fine */ }
  }, [])

  // Poll the unread count. We start a single interval so even when the
  // dropdown is open the badge keeps refreshing.
  useEffect(() => {
    fetchUnread()
    const id = setInterval(fetchUnread, 30_000)
    return () => clearInterval(id)
  }, [fetchUnread])

  // Outside-click closes the dropdown.
  useEffect(() => {
    if (!open) return
    const handler = (e: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) setOpen(false)
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [open])

  const openDropdown = async () => {
    setOpen(true)
    setLoading(true)
    try {
      const r = await apiFetch('notifications') as { data: NotificationRow[] }
      setItems(r.data.slice(0, 8))
    } catch { setItems([]) }
    finally { setLoading(false) }
  }

  const handleRowClick = async (n: NotificationRow) => {
    if (!n.read_at) {
      // Optimistic — local state first, server next.
      setItems((rows) => rows.map((r) => r.id === n.id ? { ...r, read_at: new Date().toISOString() } : r))
      setUnread((c) => Math.max(0, c - 1))
      apiFetch(`notifications/${n.id}/read`, { method: 'PUT' }).catch(() => {})
    }
    if (n.data.action_url) navigate(n.data.action_url)
    setOpen(false)
  }

  const markAllRead = async () => {
    setItems((rows) => rows.map((r) => ({ ...r, read_at: r.read_at ?? new Date().toISOString() })))
    setUnread(0)
    try { await apiFetch('notifications/read-all', { method: 'PUT' }) } catch {}
  }

  return (
    <div ref={containerRef} style={{ position: 'relative' }}>
      <button
        type="button"
        onClick={() => open ? setOpen(false) : openDropdown()}
        style={{
          width: 36, height: 36, borderRadius: 10,
          background: 'var(--d-surface2)', border: '1px solid var(--d-border)',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          cursor: 'pointer', fontSize: 15, color: 'var(--d-text)', position: 'relative',
        }}
        title="Notifications"
      >
        🔔
        {unread > 0 && (
          <span style={{
            position: 'absolute', top: -4, right: -4,
            background: '#ef4444', color: 'white',
            minWidth: 18, height: 18, padding: '0 4px',
            borderRadius: 100, fontSize: 10, fontWeight: 700,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            border: '2px solid var(--d-bg)',
          }}>{unread > 99 ? '99+' : unread}</span>
        )}
      </button>

      {open && (
        <div style={{
          position: 'absolute', top: 'calc(100% + 8px)', right: 0, width: 360,
          background: 'var(--d-surface)', border: '1px solid var(--d-border)',
          borderRadius: 14, boxShadow: '0 16px 40px rgba(0,0,0,0.5)',
          zIndex: 1000, overflow: 'hidden',
        }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '14px 16px', borderBottom: '1px solid var(--d-border)' }}>
            <strong style={{ color: 'var(--d-text)', fontSize: 14 }}>Notifications</strong>
            {unread > 0 && (
              <button type="button" onClick={markAllRead} style={{ background: 'none', border: 'none', color: '#4fffb0', fontSize: 12, cursor: 'pointer' }}>
                Tout marquer comme lu
              </button>
            )}
          </div>

          <div style={{ maxHeight: 400, overflowY: 'auto' }}>
            {loading ? (
              <div style={{ padding: 24, textAlign: 'center', color: 'var(--d-muted)', fontSize: 13 }}>Chargement…</div>
            ) : items.length === 0 ? (
              <div style={{ padding: '36px 16px', textAlign: 'center', color: 'var(--d-muted)', fontSize: 13 }}>
                <div style={{ fontSize: 28, marginBottom: 10, opacity: 0.4 }}>📭</div>
                Aucune notification pour l'instant.
              </div>
            ) : (
              items.map((n) => {
                const isUnread = !n.read_at
                return (
                  <button
                    key={n.id}
                    type="button"
                    onClick={() => handleRowClick(n)}
                    style={{
                      display: 'flex', gap: 12, alignItems: 'flex-start',
                      width: '100%', textAlign: 'left', padding: '12px 16px',
                      background: isUnread ? 'rgba(79,255,176,0.04)' : 'transparent',
                      border: 'none', borderBottom: '1px solid var(--d-border)', cursor: 'pointer',
                      color: 'var(--d-text)', fontFamily: 'inherit',
                    }}
                    onMouseEnter={(e) => (e.currentTarget.style.background = 'rgba(255,255,255,0.04)')}
                    onMouseLeave={(e) => (e.currentTarget.style.background = isUnread ? 'rgba(79,255,176,0.04)' : 'transparent')}
                  >
                    <div style={{ fontSize: 18, lineHeight: 1.2, flexShrink: 0 }}>{n.data.icon ?? '🔔'}</div>
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{ fontSize: 13, fontWeight: 600, marginBottom: 2 }}>{n.data.title}</div>
                      {n.data.body && (
                        <div style={{ fontSize: 12, color: 'var(--d-muted)', overflow: 'hidden', textOverflow: 'ellipsis', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical' }}>
                          {n.data.body}
                        </div>
                      )}
                      <div style={{ fontSize: 11, color: 'var(--d-muted2)', marginTop: 3 }}>
                        {relativeTime(n.created_at)}
                      </div>
                    </div>
                    {isUnread && <div style={{ width: 8, height: 8, borderRadius: 100, background: '#4fffb0', marginTop: 6, flexShrink: 0 }} />}
                  </button>
                )
              })
            )}
          </div>

          <button
            type="button"
            onClick={() => { setOpen(false); navigate('/dashboard/notifications') }}
            style={{
              display: 'block', width: '100%', padding: '12px',
              background: 'transparent', border: 'none', borderTop: '1px solid var(--d-border)',
              color: '#4fffb0', fontSize: 12, fontWeight: 600, cursor: 'pointer',
              fontFamily: 'inherit',
            }}
          >
            Voir toutes les notifications →
          </button>
        </div>
      )}
    </div>
  )
}

/** Human-friendly relative time. Kept inline to avoid pulling date-fns. */
function relativeTime(isoString: string): string {
  const diff = Date.now() - new Date(isoString).getTime()
  const sec = Math.floor(diff / 1000)
  if (sec < 60)         return `il y a ${sec}s`
  if (sec < 3600)       return `il y a ${Math.floor(sec / 60)} min`
  if (sec < 86400)      return `il y a ${Math.floor(sec / 3600)} h`
  if (sec < 86400 * 7)  return `il y a ${Math.floor(sec / 86400)} j`
  return new Date(isoString).toLocaleDateString('fr-FR')
}
