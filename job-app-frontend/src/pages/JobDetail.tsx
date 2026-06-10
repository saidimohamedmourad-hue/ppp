import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { apiFetch, isLoggedIn } from '@/utils/api'
import type { Job } from '@/types'
import ApplyModal from '@/components/ApplyModal'

export default function JobDetail() {
  const { id } = useParams<{ id: string }>()
  const [job, setJob] = useState<Job | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showModal, setShowModal] = useState(false)
  const [applied, setApplied] = useState(false)

  useEffect(() => {
    if (!id) return
    apiFetch(`jobs/${id}`).then(r => setJob(r.data ?? r)).catch(e => setError(e.message)).finally(() => setLoading(false))
  }, [id])

  if (loading) return (
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '60vh' }}>
      <div style={{ width: 32, height: 32, border: '3px solid rgba(79,255,176,0.15)', borderTopColor: '#4fffb0', borderRadius: '50%', animation: 'spin .8s linear infinite' }} />
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )

  if (error || !job) return (
    <div style={{ padding: '80px 60px', textAlign: 'center', color: 'rgba(255,255,255,0.4)' }}>
      <div style={{ fontSize: 40, marginBottom: 16 }}>⚠️</div>
      <p style={{ fontSize: 16 }}>{error ?? 'Offre introuvable'}</p>
      <Link to="/jobs" style={{ display: 'inline-block', marginTop: 20, color: '#4fffb0', textDecoration: 'none', fontSize: 14 }}>← Retour aux offres</Link>
    </div>
  )

  const handleApply = () => {
    if (!isLoggedIn()) { window.location.href = '/login'; return }
    setShowModal(true)
  }

  return (
    <div style={{ padding: '48px 60px 80px', maxWidth: 860, margin: '0 auto' }}>
      {/* Back */}
      <Link to="/jobs" style={{ display: 'inline-flex', alignItems: 'center', gap: 6, color: 'rgba(255,255,255,0.4)', textDecoration: 'none', fontSize: 13, marginBottom: 28, transition: 'color .2s' }}
        onMouseEnter={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.8)')}
        onMouseLeave={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.4)')}
      >
        ← Retour aux offres
      </Link>

      {/* Main card */}
      <div style={{ background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.1)', borderRadius: 20, padding: 36 }}>
        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 20, flexWrap: 'wrap', marginBottom: 24 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
            <div style={{ width: 54, height: 54, borderRadius: 14, background: 'rgba(79,255,176,0.1)', border: '1px solid rgba(79,255,176,0.2)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 24, flexShrink: 0 }}>🏢</div>
            <div>
              <h1 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 26, color: 'white', letterSpacing: '-0.5px', marginBottom: 4 }}>{job.title}</h1>
              <p style={{ fontSize: 14, color: '#4fffb0', fontWeight: 500 }}>{job.company?.name}</p>
            </div>
          </div>
          {applied ? (
            <span style={{ padding: '10px 20px', background: 'rgba(74,222,128,0.1)', border: '1px solid rgba(74,222,128,0.3)', borderRadius: 10, fontSize: 14, color: '#4ade80', fontWeight: 600 }}>
              Candidature envoyée ✓
            </span>
          ) : (
            <button onClick={handleApply} style={{ padding: '11px 26px', borderRadius: 10, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 14, border: 'none', cursor: 'pointer', fontFamily: '"DM Sans", sans-serif', transition: 'opacity .2s' }}
              onMouseEnter={e => (e.currentTarget.style.opacity = '0.85')}
              onMouseLeave={e => (e.currentTarget.style.opacity = '1')}
            >
              Postuler →
            </button>
          )}
        </div>

        {/* Tags */}
        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 28 }}>
          {job.location && <span style={{ fontSize: 13, background: 'rgba(255,255,255,0.06)', color: 'rgba(255,255,255,0.6)', padding: '5px 12px', borderRadius: 100 }}>📍 {job.location}</span>}
          {job.contract_type && <span style={{ fontSize: 13, background: 'rgba(79,255,176,0.1)', color: '#4fffb0', padding: '5px 12px', borderRadius: 100 }}>{job.contract_type}</span>}
          {job.salary && <span style={{ fontSize: 13, background: 'rgba(74,222,128,0.1)', color: '#4ade80', padding: '5px 12px', borderRadius: 100, fontWeight: 600 }}>{job.salary}</span>}
          {job.category && <span style={{ fontSize: 13, background: 'rgba(255,255,255,0.06)', color: 'rgba(255,255,255,0.5)', padding: '5px 12px', borderRadius: 100 }}>{job.category.name}</span>}
        </div>

        {/* Divider */}
        <div style={{ borderTop: '1px solid rgba(255,255,255,0.08)', marginBottom: 28 }} />

        {/* Description */}
        <div style={{ fontSize: 14, color: 'rgba(255,255,255,0.65)', lineHeight: 1.8, whiteSpace: 'pre-line' }}>
          {job.description}
        </div>

        {/* Company contact card — phone / website / address, each shown only
            when present so recruiters control how reachable they are. */}
        {(job.company?.phone || job.company?.website || job.company?.address) && (
          <div style={{ marginTop: 28, padding: '18px 20px', background: 'rgba(79,255,176,0.06)', border: '1px solid rgba(79,255,176,0.18)', borderRadius: 12, display: 'flex', flexDirection: 'column', gap: 14 }}>
            <div style={{ fontSize: 11, fontWeight: 700, color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase', letterSpacing: 1.3 }}>
              Contact recruteur — {job.company?.name}
            </div>

            {job.company?.phone && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <span style={{ fontSize: 16 }}>📞</span>
                <a href={`tel:${job.company.phone}`} style={{ fontSize: 14, color: '#4fffb0', fontWeight: 600, textDecoration: 'none' }}>
                  {job.company.phone}
                </a>
              </div>
            )}

            {job.company?.website && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <span style={{ fontSize: 16 }}>🌐</span>
                <a href={job.company.website.startsWith('http') ? job.company.website : `https://${job.company.website}`}
                  target="_blank" rel="noopener noreferrer"
                  style={{ fontSize: 14, color: '#60a5fa', fontWeight: 500, textDecoration: 'none' }}>
                  {job.company.website}
                </a>
              </div>
            )}

            {job.company?.address && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <span style={{ fontSize: 16 }}>📍</span>
                <span style={{ fontSize: 14, color: 'rgba(255,255,255,0.7)' }}>{job.company.address}</span>
              </div>
            )}
          </div>
        )}

        {/* Footer actions */}
        {!applied && (
          <div style={{ marginTop: 32, paddingTop: 24, borderTop: '1px solid rgba(255,255,255,0.08)' }}>
            <button onClick={handleApply} style={{ padding: '12px 28px', borderRadius: 10, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 14, border: 'none', cursor: 'pointer', fontFamily: '"DM Sans", sans-serif' }}>
              Postuler à cette offre →
            </button>
          </div>
        )}
      </div>

      {showModal && (
        <ApplyModal jobId={job.id} jobTitle={job.title} onClose={() => setShowModal(false)} onSuccess={() => { setApplied(true); setShowModal(false) }} />
      )}
    </div>
  )
}
