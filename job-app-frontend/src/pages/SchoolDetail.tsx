import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { apiFetch } from '@/utils/api'
import type { School, TrainingSession } from '@/types'

interface SchoolFull extends School { training_sessions?: TrainingSession[] }

export default function SchoolDetail() {
  const { id } = useParams<{ id: string }>()
  const [school, setSchool] = useState<SchoolFull | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (!id) return
    apiFetch(`schools/${id}`).then(r => setSchool(r.data ?? r)).catch(e => setError(e.message)).finally(() => setLoading(false))
  }, [id])

  if (loading) return (
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '60vh' }}>
      <div style={{ width: 32, height: 32, border: '3px solid rgba(192,132,252,0.15)', borderTopColor: '#c084fc', borderRadius: '50%', animation: 'spin .8s linear infinite' }} />
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )

  if (error || !school) return (
    <div style={{ padding: '80px 60px', textAlign: 'center', color: 'rgba(255,255,255,0.4)' }}>
      <div style={{ fontSize: 40, marginBottom: 16 }}>⚠️</div>
      <p>{error ?? 'École introuvable'}</p>
      <Link to="/schools" style={{ display: 'inline-block', marginTop: 20, color: '#c084fc', textDecoration: 'none', fontSize: 14 }}>← Retour aux écoles</Link>
    </div>
  )

  return (
    <div style={{ padding: '48px 60px 80px', maxWidth: 1000, margin: '0 auto' }}>
      <Link to="/schools" style={{ display: 'inline-flex', alignItems: 'center', gap: 6, color: 'rgba(255,255,255,0.4)', textDecoration: 'none', fontSize: 13, marginBottom: 28 }}
        onMouseEnter={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.8)')}
        onMouseLeave={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.4)')}
      >
        ← Retour aux écoles
      </Link>

      {/* School card */}
      <div style={{ background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.1)', borderRadius: 20, padding: 32, marginBottom: 32 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 18, marginBottom: school.description ? 20 : 0 }}>
          {school.logo ? (
            <img src={school.logo} alt={school.name} style={{ width: 64, height: 64, borderRadius: 16, objectFit: 'contain', border: '1px solid rgba(255,255,255,0.1)', background: 'rgba(255,255,255,0.05)', padding: 6, flexShrink: 0 }} />
          ) : (
            <div style={{ width: 64, height: 64, borderRadius: 16, background: 'rgba(192,132,252,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 26, fontWeight: 700, color: '#c084fc', flexShrink: 0, fontFamily: '"Syne", sans-serif' }}>
              {school.name.charAt(0)}
            </div>
          )}
          <h1 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 26, color: 'white', letterSpacing: '-0.5px' }}>{school.name}</h1>
        </div>
        {school.description && (
          <p style={{ fontSize: 14, color: 'rgba(255,255,255,0.55)', lineHeight: 1.75, borderTop: '1px solid rgba(255,255,255,0.07)', paddingTop: 18 }}>{school.description}</p>
        )}
      </div>

      {/* Trainings */}
      <h2 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 700, fontSize: 20, color: 'white', marginBottom: 20 }}>Formations disponibles</h2>
      {school.training_sessions && school.training_sessions.length > 0 ? (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 14 }}>
          {school.training_sessions.map(t => (
            <Link key={t.id} to={`/trainings/${t.id}`} style={{ display: 'block', background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.08)', borderRadius: 16, padding: 22, textDecoration: 'none', color: 'inherit', transition: 'border-color .2s, background .2s' }}
              onMouseEnter={e => { e.currentTarget.style.borderColor = 'rgba(192,132,252,0.3)'; e.currentTarget.style.background = 'rgba(192,132,252,0.04)' }}
              onMouseLeave={e => { e.currentTarget.style.borderColor = 'rgba(255,255,255,0.08)'; e.currentTarget.style.background = 'rgba(255,255,255,0.03)' }}
            >
              <div style={{ fontSize: 22, marginBottom: 12 }}>🎓</div>
              <div style={{ fontFamily: '"Syne", sans-serif', fontWeight: 700, fontSize: 14, color: 'white', marginBottom: 6 }}>{t.title}</div>
              {t.duration && <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.4)', marginBottom: 10 }}>{t.duration}</div>}
              {t.price != null && <span style={{ fontSize: 12, color: '#4fffb0', fontWeight: 600 }}>{t.price === 0 ? 'Gratuit' : `${t.price.toLocaleString()} DA`}</span>}
            </Link>
          ))}
        </div>
      ) : (
        <div style={{ textAlign: 'center', padding: '40px 0', color: 'rgba(255,255,255,0.3)', background: 'rgba(255,255,255,0.02)', border: '1px solid rgba(255,255,255,0.06)', borderRadius: 16 }}>
          <div style={{ fontSize: 32, marginBottom: 10, opacity: 0.4 }}>🎓</div>
          <p style={{ fontSize: 14 }}>Aucune formation disponible pour l'instant.</p>
        </div>
      )}
    </div>
  )
}
