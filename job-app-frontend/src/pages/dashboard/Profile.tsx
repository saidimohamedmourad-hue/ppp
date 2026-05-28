import { useEffect, useState } from 'react'
import { apiFetch, API_URL, getToken, getUser } from '@/utils/api'
import type { User } from '@/types'

export default function Profile() {
  const [user, setUser] = useState<User | null>(getUser())
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [curPwd, setCurPwd] = useState('')
  const [newPwd, setNewPwd] = useState('')
  const [confPwd, setConfPwd] = useState('')
  const [savingInfo, setSavingInfo] = useState(false)
  const [savingPwd, setSavingPwd] = useState(false)
  const [uploadingPhoto, setUploadingPhoto] = useState(false)
  const [msg, setMsg] = useState<{ type: 'ok' | 'err'; text: string } | null>(null)

  useEffect(() => {
    apiFetch('profile')
      .then(res => {
        const u = (res as { data?: User }).data ?? (res as User)
        setUser(u); setName(u.name); setEmail(u.email)
        localStorage.setItem('user', JSON.stringify(u))
      })
      .catch(() => { const u = getUser(); if (u) { setName(u.name); setEmail(u.email) } })
  }, [])

  const toast = (type: 'ok' | 'err', text: string) => {
    setMsg({ type, text })
    setTimeout(() => setMsg(null), 3000)
  }

  const saveInfo = async (e: React.FormEvent) => {
    e.preventDefault()
    setSavingInfo(true)
    try {
      const res = await apiFetch('profile', { method: 'PUT', body: JSON.stringify({ name, email }) })
      const u = (res as { data?: User }).data ?? (res as User)
      setUser(u); localStorage.setItem('user', JSON.stringify(u))
      toast('ok', 'Profil mis à jour.')
    } catch (err) { toast('err', err instanceof Error ? err.message : 'Erreur') }
    finally { setSavingInfo(false) }
  }

  const savePwd = async (e: React.FormEvent) => {
    e.preventDefault()
    if (newPwd !== confPwd) { toast('err', 'Les mots de passe ne correspondent pas.'); return }
    setSavingPwd(true)
    try {
      await apiFetch('profile/password', { method: 'PUT', body: JSON.stringify({ current_password: curPwd, password: newPwd, password_confirmation: confPwd }) })
      toast('ok', 'Mot de passe modifié.')
      setCurPwd(''); setNewPwd(''); setConfPwd('')
    } catch (err) { toast('err', err instanceof Error ? err.message : 'Erreur') }
    finally { setSavingPwd(false) }
  }

  const uploadPhoto = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    setUploadingPhoto(true)
    try {
      const fd = new FormData(); fd.append('photo', file)
      const res = await fetch(`${API_URL}/profile/photo`, { method: 'POST', headers: { Authorization: `Bearer ${getToken()}` }, body: fd })
      if (!res.ok) throw new Error(`Erreur ${res.status}`)
      const data = await res.json()
      const u = (data as { data?: User }).data ?? (data as User)
      setUser(u); localStorage.setItem('user', JSON.stringify(u))
      toast('ok', 'Photo mise à jour.')
    } catch (err) { toast('err', err instanceof Error ? err.message : 'Erreur') }
    finally { setUploadingPhoto(false) }
  }

  return (
    <div>
      {msg && (
        <div style={{ position: 'fixed', top: 16, right: 16, zIndex: 200, padding: '12px 20px', borderRadius: 10, background: msg.type === 'ok' ? 'rgba(74,222,128,0.15)' : 'rgba(248,113,113,0.15)', border: `1px solid ${msg.type === 'ok' ? 'var(--d-green)' : 'var(--d-red)'}`, color: msg.type === 'ok' ? 'var(--d-green)' : 'var(--d-red)', fontSize: 13, fontWeight: 500 }}>
          {msg.text}
        </div>
      )}

      {/* Profile header */}
      <div style={{ background: 'var(--d-surface)', border: '1px solid var(--d-border)', borderRadius: 20, padding: 28, display: 'flex', alignItems: 'center', gap: 20, marginBottom: 18, position: 'relative', overflow: 'hidden' }}>
        <div style={{ position: 'absolute', top: 0, left: 0, right: 0, height: 80, background: 'linear-gradient(135deg, rgba(79,255,176,0.07), rgba(0,212,255,0.05))', pointerEvents: 'none' }} />
        <div style={{ width: 72, height: 72, borderRadius: 18, background: 'linear-gradient(135deg, #4fffb0, #00d4ff)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: '"Instrument Serif", serif', fontSize: 28, color: '#080c14', flexShrink: 0, position: 'relative', zIndex: 1 }}>
          {user?.photo ? <img src={user.photo} alt="" style={{ width: '100%', height: '100%', borderRadius: 18, objectFit: 'cover' }} /> : (user?.name?.charAt(0).toUpperCase() ?? '?')}
        </div>
        <div style={{ position: 'relative', zIndex: 1, flex: 1 }}>
          <h2 style={{ fontFamily: '"Instrument Serif", serif', fontSize: 22, letterSpacing: '-0.3px', color: 'var(--d-text)', marginBottom: 4 }}>{user?.name}</h2>
          <p style={{ color: 'var(--d-muted2)', fontSize: 13.5 }}>{user?.email}</p>
          <div style={{ display: 'flex', gap: 8, marginTop: 8 }}>
            <span style={{ padding: '3px 12px', borderRadius: 100, fontSize: 11, fontWeight: 500, background: 'rgba(74,222,128,0.1)', color: 'var(--d-green)' }}>✓ Profil vérifié</span>
            <span style={{ padding: '3px 12px', borderRadius: 100, fontSize: 11, fontWeight: 500, background: 'rgba(96,165,250,0.1)', color: 'var(--d-blue)' }}>Candidat actif</span>
          </div>
        </div>
        <label style={{ cursor: 'pointer', position: 'relative', zIndex: 1 }}>
          <span className="d-btn-ghost" style={{ fontSize: 12 }}>{uploadingPhoto ? 'Upload...' : '📷 Changer la photo'}</span>
          <input type="file" accept="image/*" style={{ display: 'none' }} onChange={uploadPhoto} disabled={uploadingPhoto} />
        </label>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
        {/* Infos */}
        <div className="d-card">
          <div className="d-card-title" style={{ marginBottom: 18 }}>Informations personnelles</div>
          <form onSubmit={saveInfo} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
            <div><label className="d-label">Nom complet</label><input className="d-input" value={name} onChange={e => setName(e.target.value)} required /></div>
            <div><label className="d-label">Email</label><input type="email" className="d-input" value={email} onChange={e => setEmail(e.target.value)} required /></div>
            <button type="submit" disabled={savingInfo} className="d-btn-gold" style={{ marginTop: 4 }}>{savingInfo ? 'Enregistrement...' : 'Enregistrer'}</button>
          </form>
        </div>

        {/* Password */}
        <div className="d-card">
          <div className="d-card-title" style={{ marginBottom: 18 }}>Changer le mot de passe</div>
          <form onSubmit={savePwd} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
            <div><label className="d-label">Mot de passe actuel</label><input type="password" className="d-input" value={curPwd} onChange={e => setCurPwd(e.target.value)} required /></div>
            <div><label className="d-label">Nouveau mot de passe</label><input type="password" className="d-input" value={newPwd} onChange={e => setNewPwd(e.target.value)} required minLength={8} /></div>
            <div><label className="d-label">Confirmer</label><input type="password" className="d-input" value={confPwd} onChange={e => setConfPwd(e.target.value)} required /></div>
            <button type="submit" disabled={savingPwd} className="d-btn-ghost" style={{ marginTop: 4 }}>{savingPwd ? 'Modification...' : 'Modifier le mot de passe'}</button>
          </form>
        </div>
      </div>
    </div>
  )
}
