import { Link } from 'react-router-dom'
import type { Job } from '@/types'

export default function JobCard({ job }: { job: Job }) {
  return (
    <Link to={`/jobs/${job.id}`} style={{
      display: 'block', background: 'white', border: '1px solid #e2ddd6',
      borderRadius: 18, padding: 20, textDecoration: 'none', color: '#141210',
      transition: 'box-shadow .2s, transform .2s',
    }}
      onMouseEnter={e => { e.currentTarget.style.boxShadow = '0 6px 24px rgba(0,0,0,0.08)'; e.currentTarget.style.transform = 'translateY(-2px)' }}
      onMouseLeave={e => { e.currentTarget.style.boxShadow = ''; e.currentTarget.style.transform = '' }}
    >
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 10 }}>
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ fontFamily: '"Space Grotesk", sans-serif', fontWeight: 600, fontSize: 15, marginBottom: 4, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{job.title}</div>
          <div style={{ fontSize: 12, color: '#7a756e' }}>{job.company?.name}</div>
        </div>
        {job.contract_type && (
          <span style={{ fontSize: 11, background: '#efecea', color: '#7a756e', padding: '3px 9px', borderRadius: 100, whiteSpace: 'nowrap', flexShrink: 0, marginLeft: 8 }}>{job.contract_type}</span>
        )}
      </div>
      <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, fontSize: 12, color: '#7a756e', marginTop: 8, paddingTop: 10, borderTop: '1px solid #e2ddd6' }}>
        {job.location && <span>📍 {job.location}</span>}
        {job.salary && <span style={{ color: '#28a06b', fontWeight: 500 }}>{job.salary}</span>}
        {job.category && <span>{job.category.name}</span>}
      </div>
    </Link>
  )
}
