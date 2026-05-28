import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { apiFetch } from '@/utils/api'
import type { Company, Job } from '@/types'

interface CompanyFull extends Company { jobs?: Job[] }

export default function CompanyDetail() {
  const { id } = useParams<{ id: string }>()
  const [company, setCompany] = useState<CompanyFull | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (!id) return
    apiFetch(`companies/${id}`).then(r => setCompany(r.data ?? r)).catch(e => setError(e.message)).finally(() => setLoading(false))
  }, [id])

  if (loading) return (
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '60vh' }}>
      <div style={{ width: 32, height: 32, border: '3px solid rgba(96,165,250,0.15)', borderTopColor: '#60a5fa', borderRadius: '50%', animation: 'spin .8s linear infinite' }} />
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )

  if (error || !company) return (
    <div style={{ padding: '80px 60px', textAlign: 'center', color: 'rgba(255,255,255,0.4)' }}>
      <div style={{ fontSize: 40, marginBottom: 16 }}>⚠️</div>
      <p>{error ?? 'Entreprise introuvable'}</p>
      <Link to="/companies" style={{ display: 'inline-block', marginTop: 20, color: '#60a5fa', textDecoration: 'none', fontSize: 14 }}>← Retour aux entreprises</Link>
    </div>
  )

  return (
    <div style={{ padding: '48px 60px 80px', maxWidth: 1000, margin: '0 auto' }}>
      <Link to="/companies" style={{ display: 'inline-flex', alignItems: 'center', gap: 6, color: 'rgba(255,255,255,0.4)', textDecoration: 'none', fontSize: 13, marginBottom: 28 }}
        onMouseEnter={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.8)')}
        onMouseLeave={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.4)')}
      >
        ← Retour aux entreprises
      </Link>

      {/* Company card */}
      <div style={{ background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.1)', borderRadius: 20, padding: 32, marginBottom: 32 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 18, marginBottom: company.description ? 20 : 0 }}>
          {company.logo ? (
            <img src={company.logo} alt={company.name} style={{ width: 64, height: 64, borderRadius: 16, objectFit: 'contain', border: '1px solid rgba(255,255,255,0.1)', background: 'rgba(255,255,255,0.05)', padding: 6, flexShrink: 0 }} />
          ) : (
            <div style={{ width: 64, height: 64, borderRadius: 16, background: 'rgba(96,165,250,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 26, fontWeight: 700, color: '#60a5fa', flexShrink: 0, fontFamily: '"Syne", sans-serif' }}>
              {company.name.charAt(0)}
            </div>
          )}
          <div>
            <h1 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 26, color: 'white', letterSpacing: '-0.5px', marginBottom: 4 }}>{company.name}</h1>
            {company.sector && <span style={{ fontSize: 13, background: 'rgba(96,165,250,0.1)', color: '#60a5fa', padding: '4px 12px', borderRadius: 100 }}>{company.sector}</span>}
          </div>
        </div>
        {company.description && (
          <p style={{ fontSize: 14, color: 'rgba(255,255,255,0.55)', lineHeight: 1.75, borderTop: '1px solid rgba(255,255,255,0.07)', paddingTop: 18 }}>{company.description}</p>
        )}
      </div>

      {/* Jobs */}
      <div style={{ marginBottom: 20, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <h2 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 700, fontSize: 20, color: 'white' }}>Offres d'emploi actives</h2>
      </div>
      {company.jobs && company.jobs.length > 0 ? (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 14 }}>
          {company.jobs.map(job => (
            <Link key={job.id} to={`/jobs/${job.id}`} style={{ display: 'block', background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.08)', borderRadius: 16, padding: 22, textDecoration: 'none', color: 'inherit', transition: 'border-color .2s, background .2s' }}
              onMouseEnter={e => { e.currentTarget.style.borderColor = 'rgba(79,255,176,0.28)'; e.currentTarget.style.background = 'rgba(79,255,176,0.03)' }}
              onMouseLeave={e => { e.currentTarget.style.borderColor = 'rgba(255,255,255,0.08)'; e.currentTarget.style.background = 'rgba(255,255,255,0.03)' }}
            >
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 10 }}>
                <div style={{ fontFamily: '"Syne", sans-serif', fontWeight: 700, fontSize: 14, color: 'white' }}>{job.title}</div>
                {job.contract_type && <span style={{ fontSize: 11, color: '#4fffb0', background: 'rgba(79,255,176,0.1)', padding: '3px 9px', borderRadius: 100 }}>{job.contract_type}</span>}
              </div>
              {job.location && <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.4)' }}>📍 {job.location}</div>}
            </Link>
          ))}
        </div>
      ) : (
        <div style={{ textAlign: 'center', padding: '40px 0', color: 'rgba(255,255,255,0.3)', background: 'rgba(255,255,255,0.02)', border: '1px solid rgba(255,255,255,0.06)', borderRadius: 16 }}>
          <div style={{ fontSize: 32, marginBottom: 10, opacity: 0.4 }}>💼</div>
          <p style={{ fontSize: 14 }}>Aucune offre active pour cette entreprise.</p>
        </div>
      )}
    </div>
  )
}
