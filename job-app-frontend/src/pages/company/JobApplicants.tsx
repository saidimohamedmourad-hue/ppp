import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { apiFetch } from '@/utils/api'

interface Applicant {
  id: number
  status: 'pending' | 'accepted' | 'rejected'
  cover_letter?: string
  created_at: string
  user: { id: number; name: string; email: string }
  resume?: { url: string; filename: string }
}

interface JobInfo {
  id: number
  title: string
  location: string
  contract_type: string
}

const STATUS_MAP = {
  pending:  { label: 'En attente', cls: 'd-status-pending' },
  accepted: { label: 'Accepté ✓',  cls: 'd-status-accepted' },
  rejected: { label: 'Refusé',     cls: 'd-status-rejected' },
}

export default function JobApplicants() {
  const { jobId } = useParams<{ jobId: string }>()
  const [applicants, setApplicants] = useState<Applicant[]>([])
  const [job, setJob] = useState<JobInfo | null>(null)
  const [loading, setLoading] = useState(true)
  const [updating, setUpdating] = useState<number | null>(null)

  useEffect(() => {
    Promise.all([
      apiFetch(`company/jobs/${jobId}/applicants`).catch(() => null),
      apiFetch(`company/jobs/${jobId}`).catch(() => null),
    ]).then(([appRes, jobRes]) => {
      if (appRes) setApplicants((appRes as { data?: Applicant[] }).data ?? (appRes as Applicant[]))
      if (jobRes) setJob((jobRes as { data?: JobInfo }).data ?? (jobRes as JobInfo))
    }).finally(() => setLoading(false))
  }, [jobId])

  const updateStatus = async (applicationId: number, status: 'accepted' | 'rejected') => {
    setUpdating(applicationId)
    try {
      await apiFetch(`company/applications/${applicationId}/status`, {
        method: 'PUT',
        body: JSON.stringify({ status }),
      })
      setApplicants(prev => prev.map(a => a.id === applicationId ? { ...a, status } : a))
    } catch {
      /* ignore */
    } finally {
      setUpdating(null)
    }
  }

  if (loading) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '60vh' }}>
        <div style={{ width: 32, height: 32, border: '3px solid rgba(79,255,176,0.15)', borderTopColor: '#4fffb0', borderRadius: '50%', animation: 'spin 0.8s linear infinite' }} />
        <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
      </div>
    )
  }

  const pending = applicants.filter(a => a.status === 'pending').length
  const accepted = applicants.filter(a => a.status === 'accepted').length
  const rejected = applicants.filter(a => a.status === 'rejected').length

  return (
    <div>
      {/* Back + header */}
      <div style={{ marginBottom: 24 }}>
        <Link to="/dashboard/company/jobs" style={{ display: 'inline-flex', alignItems: 'center', gap: 6, color: 'var(--d-muted)', fontSize: 13, textDecoration: 'none', marginBottom: 16 }}>
          ← Retour aux offres
        </Link>
        <h2 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 24, color: 'var(--d-text)', marginBottom: 4 }}>
          {job?.title ?? `Offre #${jobId}`}
        </h2>
        {job && <p style={{ color: 'var(--d-muted2)', fontSize: 13 }}>{job.location} · {job.contract_type}</p>}
      </div>

      {/* Mini stats */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10, marginBottom: 20 }}>
        {[
          { icon: '⏳', bg: 'rgba(79,255,176,0.1)', val: pending,  lbl: 'En attente' },
          { icon: '✅', bg: 'rgba(74,222,128,0.1)', val: accepted, lbl: 'Acceptés' },
          { icon: '❌', bg: 'rgba(248,113,113,0.1)', val: rejected, lbl: 'Refusés' },
        ].map(s => (
          <div key={s.lbl} className="d-stat-card">
            <div className="d-stat-icon" style={{ background: s.bg, marginBottom: 8 }}>{s.icon}</div>
            <div className="d-stat-val">{s.val}</div>
            <div className="d-stat-lbl">{s.lbl}</div>
          </div>
        ))}
      </div>

      {/* Applicants list */}
      {applicants.length === 0 ? (
        <div className="d-card" style={{ textAlign: 'center', padding: '48px 0' }}>
          <div style={{ fontSize: 40, marginBottom: 14, opacity: 0.35 }}>👥</div>
          <p style={{ color: 'var(--d-muted)', fontSize: 14 }}>Aucune candidature reçue pour cette offre.</p>
        </div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {applicants.map(app => (
            <div key={app.id} className="d-card" style={{ padding: '18px 20px' }}>
              <div style={{ display: 'flex', alignItems: 'flex-start', gap: 14 }}>
                <div style={{ width: 44, height: 44, borderRadius: 12, background: 'rgba(79,255,176,0.12)', border: '1px solid rgba(79,255,176,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 17, fontWeight: 700, color: 'var(--d-gold)', flexShrink: 0, fontFamily: 'Inter, sans-serif' }}>
                  {app.user.name.charAt(0).toUpperCase()}
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap', marginBottom: 4 }}>
                    <span style={{ fontSize: 14, fontWeight: 600, color: 'var(--d-text)' }}>{app.user.name}</span>
                    <span className={STATUS_MAP[app.status]?.cls ?? 'd-status-pending'}>{STATUS_MAP[app.status]?.label}</span>
                  </div>
                  <div style={{ fontSize: 12, color: 'var(--d-muted)', marginBottom: app.cover_letter ? 10 : 0 }}>{app.user.email}</div>
                  {app.cover_letter && (
                    <div style={{ fontSize: 13, color: 'var(--d-muted2)', background: 'var(--d-surface2)', border: '1px solid var(--d-border)', borderRadius: 8, padding: '10px 14px', marginTop: 8, lineHeight: 1.6 }}>
                      {app.cover_letter}
                    </div>
                  )}
                </div>
                <div style={{ display: 'flex', gap: 8, flexShrink: 0 }}>
                  {app.resume && (
                    <a href={app.resume.url} target="_blank" rel="noopener noreferrer" className="d-btn-ghost" style={{ textDecoration: 'none', fontSize: 13 }}>
                      📄 CV
                    </a>
                  )}
                  {app.status !== 'accepted' && (
                    <button
                      onClick={() => updateStatus(app.id, 'accepted')}
                      disabled={updating === app.id}
                      style={{ padding: '7px 14px', borderRadius: 8, border: '1px solid rgba(74,222,128,0.3)', background: 'rgba(74,222,128,0.1)', color: 'var(--d-green)', fontSize: 13, cursor: 'pointer' }}
                    >
                      {updating === app.id ? '...' : 'Accepter'}
                    </button>
                  )}
                  {app.status !== 'rejected' && (
                    <button
                      onClick={() => updateStatus(app.id, 'rejected')}
                      disabled={updating === app.id}
                      style={{ padding: '7px 14px', borderRadius: 8, border: '1px solid rgba(248,113,113,0.3)', background: 'rgba(248,113,113,0.08)', color: 'var(--d-red)', fontSize: 13, cursor: 'pointer' }}
                    >
                      {updating === app.id ? '...' : 'Refuser'}
                    </button>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
