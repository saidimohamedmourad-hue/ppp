import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { apiFetch, isLoggedIn } from '@/utils/api'
import type { TrainingSession, TrainingType } from '@/types'
import EnrollModal from '@/components/EnrollModal'

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

export default function TrainingDetail() {
  const { id } = useParams<{ id: string }>()
  const [training, setTraining] = useState<TrainingSession | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showModal, setShowModal] = useState(false)
  const [enrolled, setEnrolled] = useState(false)

  useEffect(() => {
    if (!id) return
    apiFetch(`training-sessions/${id}`).then(r => setTraining(r.data ?? r)).catch(e => setError(e.message)).finally(() => setLoading(false))
  }, [id])

  if (loading) return (
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '60vh' }}>
      <div style={{ width: 32, height: 32, border: '3px solid rgba(79,255,176,0.12)', borderTopColor: '#4fffb0', borderRadius: '50%', animation: 'spin .8s linear infinite' }} />
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )

  if (error || !training) return (
    <div style={{ padding: '80px 60px', textAlign: 'center', color: 'rgba(255,255,255,0.4)' }}>
      <div style={{ fontSize: 40, marginBottom: 16 }}>⚠️</div>
      <p style={{ fontSize: 16 }}>{error ?? 'Formation introuvable'}</p>
      <Link to="/trainings" style={{ display: 'inline-block', marginTop: 20, color: '#4fffb0', textDecoration: 'none', fontSize: 14 }}>← Retour aux formations</Link>
    </div>
  )

  const handleEnroll = () => {
    if (!isLoggedIn()) { window.location.href = '/login'; return }
    setShowModal(true)
  }

  const cancelled = training.status === 'cancelled'
  const full = !!training.is_full && !cancelled
  const tt: TrainingType = training.type ?? 'presentiel'
  const typeColor = TYPE_COLORS[tt]

  return (
    <div style={{ padding: '48px 60px 80px', maxWidth: 860, margin: '0 auto' }}>
      {/* Back */}
      <Link to="/trainings" style={{ display: 'inline-flex', alignItems: 'center', gap: 6, color: 'rgba(255,255,255,0.4)', textDecoration: 'none', fontSize: 13, marginBottom: 28, transition: 'color .2s' }}
        onMouseEnter={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.8)')}
        onMouseLeave={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.4)')}
      >
        ← Retour aux formations
      </Link>

      {/* Cancelled banner */}
      {cancelled && (
        <div style={{ background: 'rgba(239,68,68,0.08)', border: '1px solid rgba(239,68,68,0.3)', borderRadius: 14, padding: 18, marginBottom: 20 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: training.cancellation_reason ? 8 : 0 }}>
            <span style={{ fontSize: 20 }}>⛔</span>
            <span style={{ fontSize: 15, fontWeight: 700, color: '#ef4444' }}>Formation annulée</span>
          </div>
          {training.cancellation_reason && (
            <p style={{ fontSize: 13.5, color: 'rgba(255,255,255,0.7)', lineHeight: 1.6, margin: 0 }}>
              <strong style={{ color: 'rgba(255,255,255,0.85)' }}>Raison :</strong> {training.cancellation_reason}
            </p>
          )}
        </div>
      )}

      {/* Waitlist banner */}
      {full && (
        <div style={{ background: 'rgba(240,196,90,0.08)', border: '1px solid rgba(240,196,90,0.3)', borderRadius: 14, padding: 18, marginBottom: 20 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 6 }}>
            <span style={{ fontSize: 20 }}>⏳</span>
            <span style={{ fontSize: 15, fontWeight: 700, color: '#f0c45a' }}>Formation complète</span>
          </div>
          <p style={{ fontSize: 13.5, color: 'rgba(255,255,255,0.7)', lineHeight: 1.6, margin: 0 }}>
            Tu peux quand même t'inscrire sur la <strong style={{ color: 'rgba(255,255,255,0.85)' }}>liste d'attente</strong>. L'école te contactera si une place se libère ou si elle accepte ta candidature.
          </p>
        </div>
      )}

      {/* Main card */}
      <div style={{ background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.1)', borderRadius: 20, padding: 36 }}>
        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 20, flexWrap: 'wrap', marginBottom: 24 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
            <div style={{ width: 54, height: 54, borderRadius: 14, background: `${typeColor}1a`, border: `1px solid ${typeColor}26`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 24, flexShrink: 0 }}>🎓</div>
            <div>
              <h1 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 26, color: 'white', letterSpacing: '-0.5px', marginBottom: 4 }}>{training.title}</h1>
              <p style={{ fontSize: 14, color: '#4fffb0', fontWeight: 500 }}>{training.school?.name}</p>
            </div>
          </div>
          {cancelled ? (
            <span style={{ padding: '10px 20px', background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.3)', borderRadius: 10, fontSize: 14, color: '#ef4444', fontWeight: 600 }}>
              Inscription fermée
            </span>
          ) : enrolled ? (
            <span style={{ padding: '10px 20px', background: 'rgba(74,222,128,0.1)', border: '1px solid rgba(74,222,128,0.3)', borderRadius: 10, fontSize: 14, color: '#4ade80', fontWeight: 600 }}>
              Inscrit ✓
            </span>
          ) : (
            <button onClick={handleEnroll} style={{ padding: '11px 26px', borderRadius: 10, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 14, border: 'none', cursor: 'pointer', fontFamily: '"DM Sans", sans-serif', transition: 'opacity .2s' }}
              onMouseEnter={e => (e.currentTarget.style.opacity = '0.85')}
              onMouseLeave={e => (e.currentTarget.style.opacity = '1')}
            >
              {full ? "Rejoindre la liste d'attente →" : "S'inscrire →"}
            </button>
          )}
        </div>

        {/* Tags */}
        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 28 }}>
          <span style={{ fontSize: 13, background: `${typeColor}1a`, color: typeColor, padding: '5px 12px', borderRadius: 100, fontWeight: 600 }}>{TYPE_LABELS[tt]}</span>
          {training.duration && <span style={{ fontSize: 13, background: 'rgba(79,255,176,0.1)', color: '#4fffb0', padding: '5px 12px', borderRadius: 100 }}>⏱ {training.duration}</span>}
          {training.start_date && <span style={{ fontSize: 13, background: 'rgba(255,255,255,0.06)', color: 'rgba(255,255,255,0.6)', padding: '5px 12px', borderRadius: 100 }}>📅 {new Date(training.start_date).toLocaleDateString('fr-DZ')}</span>}
          {training.price != null && (
            <span style={{ fontSize: 13, background: 'rgba(74,222,128,0.1)', color: '#4ade80', padding: '5px 12px', borderRadius: 100, fontWeight: 600 }}>
              {training.price === 0 ? 'Gratuit' : `${training.price.toLocaleString()} DA`}
            </span>
          )}
          {training.category && <span style={{ fontSize: 13, background: 'rgba(255,255,255,0.06)', color: 'rgba(255,255,255,0.5)', padding: '5px 12px', borderRadius: 100 }}>{training.category.name}</span>}
          {training.min_education_level && (
            <span style={{ fontSize: 13, background: 'rgba(96,165,250,0.12)', color: '#60a5fa', padding: '5px 12px', borderRadius: 100, fontWeight: 600 }}>
              🎓 Niveau min : {training.min_education_level}
            </span>
          )}
        </div>

        {/* Divider */}
        <div style={{ borderTop: '1px solid rgba(255,255,255,0.08)', marginBottom: 28 }} />

        {/* Description */}
        <div style={{ fontSize: 14, color: 'rgba(255,255,255,0.65)', lineHeight: 1.8, whiteSpace: 'pre-line' }}>
          {training.description}
        </div>

        {/* School contact card — phone / website / address, each conditional. */}
        {(training.school?.phone || training.school?.website || training.school?.address) && (
          <div style={{ marginTop: 28, padding: '18px 20px', background: 'rgba(79,255,176,0.06)', border: '1px solid rgba(79,255,176,0.18)', borderRadius: 12, display: 'flex', flexDirection: 'column', gap: 14 }}>
            <div style={{ fontSize: 11, fontWeight: 700, color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase', letterSpacing: 1.3 }}>
              Contact école — {training.school?.name}
            </div>

            {training.school?.phone && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <span style={{ fontSize: 16 }}>📞</span>
                <a href={`tel:${training.school.phone}`} style={{ fontSize: 14, color: '#4fffb0', fontWeight: 600, textDecoration: 'none' }}>
                  {training.school.phone}
                </a>
              </div>
            )}

            {training.school?.website && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <span style={{ fontSize: 16 }}>🌐</span>
                <a href={training.school.website.startsWith('http') ? training.school.website : `https://${training.school.website}`}
                  target="_blank" rel="noopener noreferrer"
                  style={{ fontSize: 14, color: '#60a5fa', fontWeight: 500, textDecoration: 'none' }}>
                  {training.school.website}
                </a>
              </div>
            )}

            {training.school?.address && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <span style={{ fontSize: 16 }}>📍</span>
                <span style={{ fontSize: 14, color: 'rgba(255,255,255,0.7)' }}>{training.school.address}</span>
              </div>
            )}
          </div>
        )}

        {/* Footer */}
        {!enrolled && !cancelled && (
          <div style={{ marginTop: 32, paddingTop: 24, borderTop: '1px solid rgba(255,255,255,0.08)' }}>
            <button onClick={handleEnroll} style={{ padding: '12px 28px', borderRadius: 10, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 14, border: 'none', cursor: 'pointer', fontFamily: '"DM Sans", sans-serif' }}>
              {full ? "Rejoindre la liste d'attente →" : "S'inscrire à cette formation →"}
            </button>
          </div>
        )}
      </div>

      {showModal && (
        <EnrollModal sessionId={training.id} sessionTitle={training.title} onClose={() => setShowModal(false)} onSuccess={() => { setEnrolled(true); setShowModal(false) }} />
      )}
    </div>
  )
}
