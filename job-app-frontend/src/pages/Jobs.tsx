import { useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { apiFetch } from '@/utils/api'
import type { Job, JobCategory, PaginatedResponse } from '@/types'

const S = {
  page: { padding: '48px 60px 80px', maxWidth: 1120, margin: '0 auto' } as React.CSSProperties,
  card: { display: 'block', background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.08)', borderRadius: 16, padding: 22, textDecoration: 'none', transition: 'border-color .2s, background .2s', color: 'inherit' } as React.CSSProperties,
  input: { width: '100%', background: 'rgba(255,255,255,0.05)', border: '1px solid rgba(255,255,255,0.1)', borderRadius: 12, padding: '11px 16px 11px 44px', color: 'white', fontSize: 14, outline: 'none', fontFamily: '"DM Sans", sans-serif' } as React.CSSProperties,
}

function Spin() {
  return (
    <div style={{ display: 'flex', justifyContent: 'center', padding: '60px 0' }}>
      <div style={{ width: 32, height: 32, border: '3px solid rgba(79,255,176,0.15)', borderTopColor: '#4fffb0', borderRadius: '50%', animation: 'spin .8s linear infinite' }} />
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )
}

export default function Jobs() {
  const [searchParams, setSearchParams] = useSearchParams()
  const [jobs, setJobs] = useState<Job[]>([])
  const [categories, setCategories] = useState<JobCategory[]>([])
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1 })
  const [loading, setLoading] = useState(true)

  const search = searchParams.get('search') ?? ''
  const categoryId = searchParams.get('category_id') ? Number(searchParams.get('category_id')) : null
  const page = Number(searchParams.get('page') ?? 1)

  useEffect(() => {
    apiFetch('job-categories').then(r => setCategories(r.data ?? r)).catch(() => {})
  }, [])

  useEffect(() => {
    setLoading(true)
    const p = new URLSearchParams()
    if (search) p.set('search', search)
    if (categoryId) p.set('category_id', String(categoryId))
    p.set('page', String(page))
    apiFetch(`jobs?${p}`)
      .then((r: PaginatedResponse<Job>) => { setJobs(r.data); setMeta({ current_page: r.current_page, last_page: r.last_page }) })
      .catch(() => setJobs([]))
      .finally(() => setLoading(false))
  }, [search, categoryId, page])

  const set = (key: string, val: string | null) => setSearchParams(prev => {
    const n = new URLSearchParams(prev)
    val ? n.set(key, val) : n.delete(key)
    n.delete('page')
    return n
  })

  return (
    <div style={S.page}>
      {/* Header */}
      <div style={{ marginBottom: 32 }}>
        <h1 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 38, color: 'white', letterSpacing: '-1.5px', marginBottom: 8 }}>Offres d'emploi</h1>
        <p style={{ color: 'rgba(255,255,255,0.42)', fontSize: 15 }}>{jobs.length > 0 ? `${meta.current_page > 1 ? 'Page ' + meta.current_page + ' · ' : ''}Résultats en cours de chargement` : 'Toutes les offres disponibles'}</p>
      </div>

      {/* Search */}
      <div style={{ position: 'relative', marginBottom: 24, maxWidth: 480 }}>
        <span style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', color: 'rgba(255,255,255,0.35)', fontSize: 16 }}>🔍</span>
        <input
          style={S.input}
          placeholder="Titre, compétence, mot-clé..."
          defaultValue={search}
          onKeyDown={e => e.key === 'Enter' && set('search', (e.target as HTMLInputElement).value || null)}
          onChange={e => !e.target.value && set('search', null)}
        />
      </div>

      {/* Category chips */}
      {categories.length > 0 && (
        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 28 }}>
          <button onClick={() => set('category_id', null)} style={{ padding: '6px 16px', borderRadius: 100, border: `1px solid ${!categoryId ? 'rgba(79,255,176,0.5)' : 'rgba(255,255,255,0.1)'}`, background: !categoryId ? 'rgba(79,255,176,0.1)' : 'transparent', color: !categoryId ? '#4fffb0' : 'rgba(255,255,255,0.5)', fontSize: 13, cursor: 'pointer', fontFamily: '"DM Sans", sans-serif' }}>
            Toutes
          </button>
          {categories.map(c => (
            <button key={c.id} onClick={() => set('category_id', String(c.id))} style={{ padding: '6px 16px', borderRadius: 100, border: `1px solid ${categoryId === c.id ? 'rgba(79,255,176,0.5)' : 'rgba(255,255,255,0.1)'}`, background: categoryId === c.id ? 'rgba(79,255,176,0.1)' : 'transparent', color: categoryId === c.id ? '#4fffb0' : 'rgba(255,255,255,0.5)', fontSize: 13, cursor: 'pointer', fontFamily: '"DM Sans", sans-serif' }}>
              {c.name}
            </button>
          ))}
        </div>
      )}

      {/* Jobs grid */}
      {loading ? <Spin /> : jobs.length === 0 ? (
        <div style={{ textAlign: 'center', padding: '60px 0', color: 'rgba(255,255,255,0.3)' }}>
          <div style={{ fontSize: 40, marginBottom: 12, opacity: 0.4 }}>💼</div>
          <p style={{ fontSize: 15 }}>Aucune offre trouvée. Essayez de modifier vos filtres.</p>
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 14 }}>
          {jobs.map(job => (
            <Link key={job.id} to={`/jobs/${job.id}`} style={S.card}
              onMouseEnter={e => { e.currentTarget.style.borderColor = 'rgba(79,255,176,0.28)'; e.currentTarget.style.background = 'rgba(79,255,176,0.03)' }}
              onMouseLeave={e => { e.currentTarget.style.borderColor = 'rgba(255,255,255,0.08)'; e.currentTarget.style.background = 'rgba(255,255,255,0.03)' }}
            >
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 12 }}>
                <div style={{ width: 40, height: 40, borderRadius: 11, background: 'rgba(79,255,176,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 18 }}>🏢</div>
                {job.contract_type && <span style={{ fontSize: 11, color: '#4fffb0', background: 'rgba(79,255,176,0.1)', padding: '3px 10px', borderRadius: 100 }}>{job.contract_type}</span>}
              </div>
              <div style={{ fontFamily: '"Syne", sans-serif', fontWeight: 700, fontSize: 15, color: 'white', marginBottom: 5 }}>{job.title}</div>
              <div style={{ fontSize: 13, color: 'rgba(255,255,255,0.45)', marginBottom: 14 }}>{job.company?.name}</div>
              <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap' }}>
                {job.location && <span style={{ fontSize: 12, color: 'rgba(255,255,255,0.4)' }}>📍 {job.location}</span>}
                {job.salary && <span style={{ fontSize: 12, color: '#4fffb0', fontWeight: 600 }}>{job.salary}</span>}
              </div>
            </Link>
          ))}
        </div>
      )}

      {/* Pagination */}
      {meta.last_page > 1 && (
        <div style={{ display: 'flex', justifyContent: 'center', gap: 8, marginTop: 36 }}>
          {Array.from({ length: meta.last_page }, (_, i) => i + 1).map(p => (
            <button key={p} onClick={() => set('page', String(p))} style={{ width: 36, height: 36, borderRadius: 10, border: `1px solid ${meta.current_page === p ? 'rgba(79,255,176,0.5)' : 'rgba(255,255,255,0.1)'}`, background: meta.current_page === p ? 'rgba(79,255,176,0.15)' : 'transparent', color: meta.current_page === p ? '#4fffb0' : 'rgba(255,255,255,0.5)', fontSize: 13, cursor: 'pointer', fontFamily: '"DM Sans", sans-serif' }}>
              {p}
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
