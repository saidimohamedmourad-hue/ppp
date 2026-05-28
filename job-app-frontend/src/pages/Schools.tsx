import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { apiFetch } from '@/utils/api'
import type { School } from '@/types'

export default function Schools() {
  const [schools, setSchools] = useState<School[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    apiFetch('schools').then(r => setSchools(r.data ?? r)).catch(() => setSchools([])).finally(() => setLoading(false))
  }, [])

  return (
    <div style={{ padding: '48px 60px 80px', maxWidth: 1120, margin: '0 auto' }}>
      <div style={{ marginBottom: 36 }}>
        <h1 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 38, color: 'white', letterSpacing: '-1.5px', marginBottom: 8 }}>Écoles & Centres de formation</h1>
        <p style={{ color: 'rgba(255,255,255,0.42)', fontSize: 15 }}>Les établissements partenaires qui proposent des formations certifiantes.</p>
      </div>

      {loading ? (
        <div style={{ display: 'flex', justifyContent: 'center', padding: '60px 0' }}>
          <div style={{ width: 32, height: 32, border: '3px solid rgba(192,132,252,0.15)', borderTopColor: '#c084fc', borderRadius: '50%', animation: 'spin .8s linear infinite' }} />
          <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
        </div>
      ) : schools.length === 0 ? (
        <div style={{ textAlign: 'center', padding: '60px 0', color: 'rgba(255,255,255,0.3)' }}>
          <div style={{ fontSize: 40, marginBottom: 12, opacity: 0.4 }}>🎓</div>
          <p style={{ fontSize: 15 }}>Aucune école pour l'instant.</p>
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 14 }}>
          {schools.map(s => (
            <Link key={s.id} to={`/schools/${s.id}`} style={{ display: 'flex', alignItems: 'center', gap: 14, background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.08)', borderRadius: 16, padding: '18px 20px', textDecoration: 'none', color: 'inherit', transition: 'border-color .2s, background .2s' }}
              onMouseEnter={e => { e.currentTarget.style.borderColor = 'rgba(192,132,252,0.3)'; e.currentTarget.style.background = 'rgba(192,132,252,0.04)' }}
              onMouseLeave={e => { e.currentTarget.style.borderColor = 'rgba(255,255,255,0.08)'; e.currentTarget.style.background = 'rgba(255,255,255,0.03)' }}
            >
              {s.logo ? (
                <img src={s.logo} alt={s.name} style={{ width: 46, height: 46, borderRadius: 12, objectFit: 'contain', border: '1px solid rgba(255,255,255,0.1)', background: 'rgba(255,255,255,0.05)', padding: 4, flexShrink: 0 }} />
              ) : (
                <div style={{ width: 46, height: 46, borderRadius: 12, background: 'rgba(192,132,252,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20, fontWeight: 700, color: '#c084fc', flexShrink: 0, fontFamily: '"Syne", sans-serif' }}>
                  {s.name.charAt(0)}
                </div>
              )}
              <div style={{ fontFamily: '"Syne", sans-serif', fontWeight: 700, fontSize: 14, color: 'white', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                {s.name}
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  )
}
