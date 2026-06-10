import { useEffect, useState, useCallback } from 'react'
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
  }
  read_at: string | null
  created_at: string
}

type Filter = 'all' | 'unread'

/**
 * Full-page notifications view. Used as the "see more" destination of the
 * bell dropdown — paginated, filterable, with bulk-read and per-row dismiss.
 */
export default function Notifications() {
  const navigate = useNavigate()
  const [items, setItems] = useState<NotificationRow[]>([])
  const [filter, setFilter] = useState<Filter>('all')
  const [loading, setLoading] = useState(true)
  const [page, setPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)

  const load = useCallback(async () => {
    setLoading(true)
    try {
      const q = filter === 'unread' ? '?filter=unread' : ''
      const r = await apiFetch(`notifications${q}${q ? '&' : '?'}page=${page}`) as {
        data: NotificationRow[]
        meta: { current_page: number; last_page: number; total: number }
      }
      setItems(r.data)
      setLastPage(r.meta.last_page)
    } catch {
      setItems([])
    } finally {
      setLoading(false)
    }
  }, [filter, page])

  useEffect(() => { load() }, [load])

  const handleRowClick = async (n: NotificationRow) => {
    if (!n.read_at) {
      setItems((rows) => rows.map((r) => r.id === n.id ? { ...r, read_at: new Date().toISOString() } : r))
      apiFetch(`notifications/${n.id}/read`, { method: 'PUT' }).catch(() => {})
    }
    if (n.data.action_url) navigate(n.data.action_url)
  }

  const dismiss = async (id: string, e: React.MouseEvent) => {
    e.stopPropagation()
    setItems((rows) => rows.filter((r) => r.id !== id))
    try { await apiFetch(`notifications/${id}`, { method: 'DELETE' }) } catch {}
  }

  const markAllRead = async () => {
    setItems((rows) => rows.map((r) => ({ ...r, read_at: r.read_at ?? new Date().toISOString() })))
    try { await apiFetch('notifications/read-all', { method: 'PUT' }) } catch {}
  }

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
        <div style={{ display: 'flex', gap: 8 }}>
          {(['all', 'unread'] as Filter[]).map((f) => (
            <button
              key={f}
              type="button"
              onClick={() => { setFilter(f); setPage(1) }}
              style={{
                padding: '8px 16px', borderRadius: 100, fontSize: 13, fontWeight: 600,
                background: filter === f ? '#4fffb0' : 'var(--d-surface2)',
                color: filter === f ? 'var(--d-bg)' : 'var(--d-text)',
                border: filter === f ? 'none' : '1px solid var(--d-border)',
                cursor: 'pointer', fontFamily: 'inherit',
              }}
            >
              {f === 'all' ? 'Toutes' : 'Non lues'}
            </button>
          ))}
        </div>
        <button
          type="button"
          onClick={markAllRead}
          style={{
            padding: '8px 14px', borderRadius: 100, fontSize: 12, fontWeight: 600,
            background: 'transparent', color: '#4fffb0',
            border: '1px solid var(--d-border)', cursor: 'pointer', fontFamily: 'inherit',
          }}
        >
          Tout marquer comme lu
        </button>
      </div>

      <div className="d-card">
        {loading ? (
          <div style={{ padding: 40, textAlign: 'center', color: 'var(--d-muted)' }}>Chargement…</div>
        ) : items.length === 0 ? (
          <div style={{ padding: '60px 0', textAlign: 'center', color: 'var(--d-muted)' }}>
            <div style={{ fontSize: 42, marginBottom: 14, opacity: 0.4 }}>📭</div>
            <p style={{ fontSize: 14 }}>
              {filter === 'unread' ? 'Tu es à jour ! Aucune notification non lue.' : 'Pas encore de notification.'}
            </p>
          </div>
        ) : (
          items.map((n) => {
            const isUnread = !n.read_at
            return (
              <div
                key={n.id}
                role="button"
                tabIndex={0}
                onClick={() => handleRowClick(n)}
                onKeyDown={(e) => { if (e.key === 'Enter') handleRowClick(n) }}
                style={{
                  display: 'flex', gap: 14, alignItems: 'flex-start',
                  borderBottom: '1px solid var(--d-border)', cursor: 'pointer',
                  background: isUnread ? 'rgba(79,255,176,0.02)' : 'transparent',
                  margin: '0 -8px', padding: '14px 8px',
                  borderRadius: 8, transition: 'background .15s',
                }}
                onMouseEnter={(e) => (e.currentTarget.style.background = 'rgba(255,255,255,0.03)')}
                onMouseLeave={(e) => (e.currentTarget.style.background = isUnread ? 'rgba(79,255,176,0.02)' : 'transparent')}
              >
                <div style={{ fontSize: 22, lineHeight: 1, flexShrink: 0 }}>{n.data.icon ?? '🔔'}</div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                    <div style={{ fontSize: 14, fontWeight: 600, color: 'var(--d-text)' }}>{n.data.title}</div>
                    {isUnread && <div style={{ width: 7, height: 7, borderRadius: 100, background: '#4fffb0' }} />}
                  </div>
                  {n.data.body && (
                    <div style={{ fontSize: 13, color: 'var(--d-muted)', marginTop: 4, lineHeight: 1.5 }}>{n.data.body}</div>
                  )}
                  <div style={{ fontSize: 11, color: 'var(--d-muted2)', marginTop: 5 }}>{new Date(n.created_at).toLocaleString('fr-FR')}</div>
                </div>
                <button
                  type="button"
                  onClick={(e) => dismiss(n.id, e)}
                  style={{ background: 'none', border: 'none', color: 'var(--d-muted2)', fontSize: 16, cursor: 'pointer', padding: 4 }}
                  title="Supprimer"
                >×</button>
              </div>
            )
          })
        )}

        {lastPage > 1 && (
          <div style={{ display: 'flex', gap: 8, justifyContent: 'center', marginTop: 18 }}>
            <button type="button" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page === 1}
              style={{ padding: '8px 14px', borderRadius: 8, background: 'var(--d-surface2)', border: '1px solid var(--d-border)', color: 'var(--d-text)', cursor: page === 1 ? 'not-allowed' : 'pointer', opacity: page === 1 ? 0.4 : 1, fontFamily: 'inherit', fontSize: 13 }}>
              ← Précédent
            </button>
            <span style={{ padding: '8px 14px', color: 'var(--d-muted)', fontSize: 13 }}>{page} / {lastPage}</span>
            <button type="button" onClick={() => setPage((p) => Math.min(lastPage, p + 1))} disabled={page === lastPage}
              style={{ padding: '8px 14px', borderRadius: 8, background: 'var(--d-surface2)', border: '1px solid var(--d-border)', color: 'var(--d-text)', cursor: page === lastPage ? 'not-allowed' : 'pointer', opacity: page === lastPage ? 0.4 : 1, fontFamily: 'inherit', fontSize: 13 }}>
              Suivant →
            </button>
          </div>
        )}
      </div>
    </div>
  )
}
