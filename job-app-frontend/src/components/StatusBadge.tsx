// Used in public pages (light theme)
const config = {
  pending:  { label: 'En attente', bg: '#fdf4d8', color: '#92620a', border: '#f5d87e' },
  reviewed: { label: 'Vue',        bg: '#fdf4d8', color: '#92620a', border: '#f5d87e' },
  accepted: { label: 'Accepté',    bg: '#e2fdf0', color: '#1a7a48', border: '#a8eeca' },
  rejected: { label: 'Refusé',     bg: '#fde8e2', color: '#c0392b', border: '#fca29a' },
}

export default function StatusBadge({ status }: { status: 'pending' | 'accepted' | 'rejected' | 'reviewed' }) {
  const s = config[status] ?? config.pending
  return (
    <span style={{ display: 'inline-block', padding: '3px 10px', borderRadius: 100, fontSize: 11, fontWeight: 600, background: s.bg, color: s.color, border: `1px solid ${s.border}`, whiteSpace: 'nowrap' }}>
      {s.label}
    </span>
  )
}
