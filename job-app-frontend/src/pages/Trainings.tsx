import { useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { apiFetch } from '@/utils/api'
import type { TrainingSession, TrainingCategory, PaginatedResponse, TrainingType } from '@/types'

const TYPE_LABELS: Record<TrainingType, string> = {
  presentiel: 'Présentiel',
  en_ligne: 'En ligne',
  accelerer: 'Accéléré',
  longue_duree: 'Longue durée',
}
const TYPE_COLORS: Record<TrainingType, string> = {
  presentiel: '#4fffb0',
  en_ligne: '#60a5fa',
  accelerer: '#f0c45a',
  longue_duree: '#a78bfa',
}

function Spin() {
  return (
    <div style={{ display: 'flex', justifyContent: 'center', padding: '60px 0' }}>
      <div style={{ width: 32, height: 32, border: '3px solid rgba(79,255,176,0.15)', borderTopColor: '#4fffb0', borderRadius: '50%', animation: 'spin .8s linear infinite' }} />
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )
}

export default function Trainings() {
  const [searchParams, setSearchParams] = useSearchParams()
  const [trainings, setTrainings] = useState<TrainingSession[]>([])
  const [categories, setCategories] = useState<TrainingCategory[]>([])
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1 })
  const [loading, setLoading] = useState(true)

  const categoryId = searchParams.get('category_id') ? Number(searchParams.get('category_id')) : null
  const type = searchParams.get('type') as TrainingType | null
  const page = Number(searchParams.get('page') ?? 1)

  useEffect(() => {
    apiFetch('training-categories').then(r => setCategories(r.data ?? r)).catch(() => {})
  }, [])

  useEffect(() => {
    setLoading(true)
    const p = new URLSearchParams()
    if (categoryId) p.set('category', String(categoryId))
    if (type) p.set('type', type)
    p.set('page', String(page))
    apiFetch(`training-sessions?${p}`)
      .then((r: PaginatedResponse<TrainingSession>) => { setTrainings(r.data); setMeta({ current_page: r.current_page, last_page: r.last_page }) })
      .catch(() => setTrainings([]))
      .finally(() => setLoading(false))
  }, [categoryId, type, page])

  const set = (key: string, val: string | null) => setSearchParams(prev => {
    const n = new URLSearchParams(prev)
    val ? n.set(key, val) : n.delete(key)
    n.delete('page')
    return n
  })

  return (
    <div style={{ padding: '48px 60px 80px', maxWidth: 1120, margin: '0 auto' }}>
      {/* Header */}
      <div style={{ marginBottom: 32 }}>
        <h1 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 38, color: 'white', letterSpacing: '-1.5px', marginBottom: 8 }}>Formations</h1>
        <p style={{ color: 'rgba(255,255,255,0.42)', fontSize: 15 }}>Formations professionnelles certifiantes partout en Algérie.</p>
      </div>

      {/* Type chips */}
      <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 12 }}>
        <button onClick={() => set('type', null)} style={{ padding: '6px 16px', borderRadius: 100, border: `1px solid ${!type ? 'rgba(79,255,176,0.4)' : 'rgba(255,255,255,0.1)'}`, background: !type ? 'rgba(79,255,176,0.1)' : 'transparent', color: !type ? '#4fffb0' : 'rgba(255,255,255,0.5)', fontSize: 13, cursor: 'pointer', fontFamily: '"DM Sans", sans-serif' }}>
          Tous types
        </button>
        {(Object.keys(TYPE_LABELS) as TrainingType[]).map(tt => (
          <button key={tt} onClick={() => set('type', tt)} style={{ padding: '6px 16px', borderRadius: 100, border: `1px solid ${type === tt ? `${TYPE_COLORS[tt]}66` : 'rgba(255,255,255,0.1)'}`, background: type === tt ? `${TYPE_COLORS[tt]}1a` : 'transparent', color: type === tt ? TYPE_COLORS[tt] : 'rgba(255,255,255,0.5)', fontSize: 13, cursor: 'pointer', fontFamily: '"DM Sans", sans-serif' }}>
            {TYPE_LABELS[tt]}
          </button>
        ))}
      </div>

      {/* Category chips */}
      {categories.length > 0 && (
        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 28 }}>
          <button onClick={() => set('category_id', null)} style={{ padding: '6px 16px', borderRadius: 100, border: `1px solid ${!categoryId ? 'rgba(79,255,176,0.4)' : 'rgba(255,255,255,0.1)'}`, background: !categoryId ? 'rgba(79,255,176,0.1)' : 'transparent', color: !categoryId ? '#4fffb0' : 'rgba(255,255,255,0.5)', fontSize: 13, cursor: 'pointer', fontFamily: '"DM Sans", sans-serif' }}>
            Toutes
          </button>
          {categories.map(c => (
            <button key={c.id} onClick={() => set('category_id', String(c.id))} style={{ padding: '6px 16px', borderRadius: 100, border: `1px solid ${categoryId === c.id ? 'rgba(79,255,176,0.4)' : 'rgba(255,255,255,0.1)'}`, background: categoryId === c.id ? 'rgba(79,255,176,0.1)' : 'transparent', color: categoryId === c.id ? '#4fffb0' : 'rgba(255,255,255,0.5)', fontSize: 13, cursor: 'pointer', fontFamily: '"DM Sans", sans-serif' }}>
              {c.name}
            </button>
          ))}
        </div>
      )}

      {/* Grid */}
      {loading ? <Spin /> : trainings.length === 0 ? (
        <div style={{ textAlign: 'center', padding: '60px 0', color: 'rgba(255,255,255,0.3)' }}>
          <div style={{ fontSize: 40, marginBottom: 12, opacity: 0.4 }}>🎓</div>
          <p style={{ fontSize: 15 }}>Aucune formation trouvée.</p>
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 14 }}>
          {trainings.map(t => {
            const cancelled = t.status === 'cancelled'
            const tt = t.type ?? 'presentiel'
            const typeColor = TYPE_COLORS[tt]
            return (
              <Link key={t.id} to={`/trainings/${t.id}`} style={{ display: 'block', background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.08)', borderRadius: 16, padding: 22, textDecoration: 'none', color: 'inherit', transition: 'border-color .2s, background .2s', opacity: cancelled ? 0.7 : 1 }}
                onMouseEnter={e => { e.currentTarget.style.borderColor = 'rgba(79,255,176,0.25)'; e.currentTarget.style.background = 'rgba(79,255,176,0.03)' }}
                onMouseLeave={e => { e.currentTarget.style.borderColor = 'rgba(255,255,255,0.08)'; e.currentTarget.style.background = 'rgba(255,255,255,0.03)' }}
              >
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 12 }}>
                  <div style={{ width: 40, height: 40, borderRadius: 11, background: `${typeColor}1a`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 18 }}>🎓</div>
                  <span style={{ fontSize: 11, color: typeColor, background: `${typeColor}1a`, padding: '3px 10px', borderRadius: 100, fontWeight: 600 }}>{TYPE_LABELS[tt]}</span>
                </div>
                <div style={{ fontFamily: '"Syne", sans-serif', fontWeight: 700, fontSize: 15, color: 'white', marginBottom: 5 }}>{t.title}</div>
                <div style={{ fontSize: 13, color: 'rgba(255,255,255,0.45)', marginBottom: 8 }}>{t.school?.name}</div>
                {t.min_education_level && (
                  <div style={{ fontSize: 11.5, color: '#60a5fa', background: 'rgba(96,165,250,0.12)', padding: '3px 10px', borderRadius: 100, fontWeight: 600, display: 'inline-block', marginBottom: 14 }}>
                    🎓 Niveau min : {t.min_education_level}
                  </div>
                )}
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 8 }}>
                  {t.price != null && (
                    <span style={{ fontSize: 12, color: '#4fffb0', fontWeight: 600 }}>
                      {t.price === 0 ? 'Gratuit' : `${t.price.toLocaleString()} DA`}
                    </span>
                  )}
                  {cancelled && (
                    <span style={{ fontSize: 11, color: '#ef4444', background: 'rgba(239,68,68,0.12)', padding: '3px 10px', borderRadius: 100, fontWeight: 600 }}>Annulée</span>
                  )}
                  {!cancelled && t.is_full && (
                    <span style={{ fontSize: 11, color: '#f0c45a', background: 'rgba(240,196,90,0.12)', padding: '3px 10px', borderRadius: 100, fontWeight: 600 }}>Complète — liste d'attente</span>
                  )}
                </div>
              </Link>
            )
          })}
        </div>
      )}

      {/* Pagination */}
      {meta.last_page > 1 && (
        <div style={{ display: 'flex', justifyContent: 'center', gap: 8, marginTop: 36 }}>
          {Array.from({ length: meta.last_page }, (_, i) => i + 1).map(p => (
            <button key={p} onClick={() => set('page', String(p))} style={{ width: 36, height: 36, borderRadius: 10, border: `1px solid ${meta.current_page === p ? 'rgba(79,255,176,0.4)' : 'rgba(255,255,255,0.1)'}`, background: meta.current_page === p ? 'rgba(79,255,176,0.12)' : 'transparent', color: meta.current_page === p ? '#4fffb0' : 'rgba(255,255,255,0.5)', fontSize: 13, cursor: 'pointer', fontFamily: '"DM Sans", sans-serif' }}>
              {p}
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
