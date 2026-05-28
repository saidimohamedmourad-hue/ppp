import { Link } from 'react-router-dom'
import { isLoggedIn, getUser } from '@/utils/api'

export default function Home() {
  const loggedIn = isLoggedIn()
  const role = getUser()?.role
  const spaceHref = (role === 'company-owner' || role === 'school-owner') ? '/dashboard' : '/dashboard/jobs'
  const spaceLabel = role === 'company-owner' ? 'Mon entreprise →' : role === 'school-owner' ? 'Mon école →' : 'Voir les offres →'

  return (
    <div style={{ background: '#080c14', minHeight: '100vh', color: '#e8ecf2', fontFamily: '"DM Sans", sans-serif' }}>

      {/* ── Nav ── */}
      <nav style={{
        position: 'sticky', top: 0, zIndex: 100,
        borderBottom: '1px solid rgba(255,255,255,0.06)',
        background: 'rgba(8,12,20,0.85)', backdropFilter: 'blur(12px)',
        padding: '0 60px', display: 'flex', alignItems: 'center', height: 64, gap: 40,
      }}>
        <Link to="/" style={{ display: 'flex', alignItems: 'center', gap: 8, textDecoration: 'none', fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 19, color: 'white', flexShrink: 0 }}>
          <img src="/iqra-logo.png" alt="IQRA" style={{ width: 28, height: 28, borderRadius: 7, objectFit: 'cover' }} />
          IQRA
        </Link>
        <div style={{ display: 'flex', alignItems: 'center', gap: 28, flex: 1 }}>
          {([['Offres', '/jobs'], ['Formations', '/trainings'], ['Entreprises', '/companies'], ['Écoles', '/schools']] as [string, string][]).map(([l, h]) => (
            <Link key={l} to={h} style={{ color: 'rgba(255,255,255,0.55)', textDecoration: 'none', fontSize: 14, fontWeight: 500, transition: 'color .2s' }}
              onMouseEnter={e => (e.currentTarget.style.color = 'white')}
              onMouseLeave={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.55)')}
            >{l}</Link>
          ))}
        </div>
        <div style={{ display: 'flex', gap: 10 }}>
          {loggedIn ? (
            <Link to={spaceHref} style={{ padding: '8px 20px', borderRadius: 100, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 14, textDecoration: 'none' }}>
              {spaceLabel}
            </Link>
          ) : (
            <>
              <Link to="/login" style={{ padding: '8px 18px', borderRadius: 100, border: '1px solid rgba(255,255,255,0.15)', color: 'rgba(255,255,255,0.75)', fontWeight: 500, fontSize: 14, textDecoration: 'none' }}>
                Connexion
              </Link>
              <Link to="/register" style={{ padding: '8px 20px', borderRadius: 100, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 14, textDecoration: 'none' }}>
                S'inscrire
              </Link>
            </>
          )}
        </div>
      </nav>

      {/* ── Hero ── */}
      <section style={{ position: 'relative', overflow: 'hidden', padding: '110px 40px 90px', textAlign: 'center' }}>
        <div style={{ position: 'absolute', inset: 0, background: 'radial-gradient(ellipse 80% 55% at 50% -5%, rgba(79,255,176,0.13) 0%, transparent 68%)', pointerEvents: 'none' }} />
        <style>{`@keyframes pulse-dot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(1.35)}}`}</style>

        <div style={{ display: 'inline-flex', alignItems: 'center', gap: 8, border: '1px solid rgba(79,255,176,0.3)', borderRadius: 100, padding: '6px 16px', marginBottom: 32, background: 'rgba(79,255,176,0.06)' }}>
          <span style={{ width: 7, height: 7, borderRadius: '50%', background: '#4fffb0', boxShadow: '0 0 10px #4fffb0', display: 'inline-block', animation: 'pulse-dot 2s ease-in-out infinite' }} />
          <span style={{ fontSize: 13, color: '#4fffb0', fontWeight: 500 }}>Plateforme N°1 en Algérie</span>
        </div>

        <h1 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 'clamp(44px, 6.5vw, 84px)', lineHeight: 1.02, letterSpacing: '-3px', marginBottom: 28, maxWidth: 820, margin: '0 auto 28px' }}>
          <span style={{ background: 'linear-gradient(160deg, #ffffff 0%, rgba(255,255,255,0.7) 100%)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
            Connectez talent
          </span>
          <br />
          <span style={{ background: 'linear-gradient(135deg, #4fffb0 0%, #00d4ff 100%)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
            et opportunité
          </span>
        </h1>

        <p style={{ fontSize: 17, color: 'rgba(255,255,255,0.5)', maxWidth: 540, margin: '0 auto 44px', lineHeight: 1.75 }}>
          Offres d'emploi, formations professionnelles et connexion directe avec les entreprises algériennes. Tout en un seul endroit.
        </p>

        <div style={{ display: 'flex', gap: 12, justifyContent: 'center', flexWrap: 'wrap' }}>
          {loggedIn ? (
            <Link to={spaceHref} style={{ padding: '14px 30px', borderRadius: 100, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 15, textDecoration: 'none' }}>
              {spaceLabel}
            </Link>
          ) : (
            <>
              <Link to="/register" style={{ padding: '14px 30px', borderRadius: 100, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 15, textDecoration: 'none' }}>
                Commencer gratuitement →
              </Link>
              <Link to="/login" style={{ padding: '14px 30px', borderRadius: 100, border: '1px solid rgba(255,255,255,0.15)', color: 'rgba(255,255,255,0.8)', fontWeight: 500, fontSize: 15, textDecoration: 'none' }}>
                Se connecter
              </Link>
            </>
          )}
        </div>

        <div style={{ display: 'flex', justifyContent: 'center', gap: 64, marginTop: 80, flexWrap: 'wrap' }}>
          {([['2 000+', 'Offres actives'], ['500+', 'Entreprises'], ['150+', 'Formations'], ['12 000+', 'Candidats']] as [string, string][]).map(([v, l]) => (
            <div key={l} style={{ textAlign: 'center' }}>
              <div style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 32, color: 'white', letterSpacing: '-1px' }}>{v}</div>
              <div style={{ fontSize: 13, color: 'rgba(255,255,255,0.38)', marginTop: 5 }}>{l}</div>
            </div>
          ))}
        </div>
      </section>

      {/* ── Demo video ── */}
      <section style={{ padding: '0 60px 90px', maxWidth: 1120, margin: '0 auto' }}>
        <div style={{ textAlign: 'center', marginBottom: 36 }}>
          <div style={{ display: 'inline-block', fontSize: 11, fontWeight: 700, letterSpacing: 2.5, color: '#4fffb0', textTransform: 'uppercase', marginBottom: 14 }}>Aperçu de la plateforme</div>
          <h2 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 34, color: 'white', letterSpacing: '-1px', marginBottom: 10 }}>Voyez IQRA en action</h2>
          <p style={{ color: 'rgba(255,255,255,0.42)', fontSize: 15, maxWidth: 520, margin: '0 auto' }}>Recherche, filtres, candidature, analyse IA du CV — toutes les fonctionnalités en un coup d'œil.</p>
        </div>
        <div style={{ position: 'relative', borderRadius: 20, overflow: 'hidden', border: '1px solid rgba(79,255,176,0.2)', boxShadow: '0 0 60px rgba(79,255,176,0.08)' }}>
          <img
            src="/iqra-demo-candidat.gif"
            alt="Démo IQRA — tableau de bord candidat, recherche, analyse IA"
            style={{ width: '100%', display: 'block', borderRadius: 20 }}
          />
          <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(to bottom, transparent 80%, rgba(8,12,20,0.6) 100%)', pointerEvents: 'none', borderRadius: 20 }} />
        </div>
      </section>

      {/* ── Features ── */}
      <section style={{ padding: '80px 60px', maxWidth: 1120, margin: '0 auto' }}>
        <div style={{ textAlign: 'center', marginBottom: 56 }}>
          <div style={{ display: 'inline-block', fontSize: 11, fontWeight: 700, letterSpacing: 2.5, color: '#4fffb0', textTransform: 'uppercase', marginBottom: 16 }}>Fonctionnalités</div>
          <h2 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 38, color: 'white', letterSpacing: '-1.5px', marginBottom: 14 }}>Tout ce dont vous avez besoin</h2>
          <p style={{ color: 'rgba(255,255,255,0.42)', fontSize: 16, maxWidth: 480, margin: '0 auto', lineHeight: 1.7 }}>
            Une plateforme complète pour candidats, entreprises et établissements.
          </p>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 14 }}>
          {[
            { icon: '💼', title: 'Offres ciblées', desc: 'Milliers d\'offres filtrées par secteur, ville et type de contrat.' },
            { icon: '🎓', title: 'Formations pro', desc: 'Accédez à des formations certifiantes partout en Algérie.' },
            { icon: '⚡', title: 'Candidature rapide', desc: 'Postulez en un clic avec votre CV déjà enregistré sur la plateforme.' },
            { icon: '📊', title: 'Suivi en direct', desc: 'Visualisez le statut de vos candidatures en temps réel.' },
            { icon: '🏢', title: 'Espace entreprise', desc: 'Gérez vos offres et traitez les candidatures depuis votre tableau de bord.' },
            { icon: '🔔', title: 'Notifications', desc: 'Soyez alerté dès qu\'une opportunité correspond à votre profil.' },
          ].map(f => (
            <div key={f.title}
              style={{ background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 16, padding: 26, cursor: 'default', transition: 'border-color .2s, background .2s' }}
              onMouseEnter={e => { e.currentTarget.style.borderColor = 'rgba(79,255,176,0.28)'; e.currentTarget.style.background = 'rgba(79,255,176,0.04)' }}
              onMouseLeave={e => { e.currentTarget.style.borderColor = 'rgba(255,255,255,0.07)'; e.currentTarget.style.background = 'rgba(255,255,255,0.03)' }}
            >
              <div style={{ fontSize: 28, marginBottom: 14 }}>{f.icon}</div>
              <div style={{ fontFamily: '"Syne", sans-serif', fontWeight: 700, fontSize: 16, color: 'white', marginBottom: 8 }}>{f.title}</div>
              <div style={{ fontSize: 14, color: 'rgba(255,255,255,0.42)', lineHeight: 1.65 }}>{f.desc}</div>
            </div>
          ))}
        </div>
      </section>

      {/* ── Roles ── */}
      <section style={{ padding: '60px 60px 90px', maxWidth: 1120, margin: '0 auto' }}>
        <div style={{ textAlign: 'center', marginBottom: 52 }}>
          <div style={{ display: 'inline-block', fontSize: 11, fontWeight: 700, letterSpacing: 2.5, color: '#4fffb0', textTransform: 'uppercase', marginBottom: 16 }}>Rejoindre en tant que</div>
          <h2 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 38, color: 'white', letterSpacing: '-1.5px' }}>Choisissez votre profil</h2>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 16 }}>
          {[
            { icon: '🙋', role: 'Candidat', desc: 'Cherchez un emploi, inscrivez-vous à des formations et suivez vos candidatures en temps réel.', cta: 'Je suis candidat', color: '#4fffb0', href: '/register?role=job-seeker' },
            { icon: '🏢', role: 'Entreprise', desc: 'Publiez vos offres, gérez vos candidatures et trouvez les talents qu\'il vous faut rapidement.', cta: 'Je suis une entreprise', color: '#60a5fa', href: '/register?role=company-owner' },
            { icon: '🎓', role: 'École / Centre', desc: 'Proposez vos formations, gérez les inscriptions et accompagnez les apprenants.', cta: 'Je suis un établissement', color: '#a78bfa', href: '/register?role=school-owner' },
          ].map(r => (
            <div key={r.role} style={{ background: 'rgba(255,255,255,0.02)', border: `1px solid ${r.color}20`, borderRadius: 20, padding: 32, display: 'flex', flexDirection: 'column', gap: 18 }}>
              <div style={{ width: 50, height: 50, borderRadius: 14, background: `${r.color}15`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 24 }}>{r.icon}</div>
              <div>
                <div style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 21, color: 'white', marginBottom: 10 }}>{r.role}</div>
                <div style={{ fontSize: 14, color: 'rgba(255,255,255,0.48)', lineHeight: 1.7 }}>{r.desc}</div>
              </div>
              <Link to={r.href} style={{ display: 'inline-block', padding: '11px 22px', borderRadius: 100, background: `${r.color}15`, color: r.color, fontWeight: 600, fontSize: 14, textDecoration: 'none', border: `1px solid ${r.color}28`, marginTop: 'auto', textAlign: 'center', transition: 'background .2s' }}
                onMouseEnter={e => (e.currentTarget.style.background = `${r.color}28`)}
                onMouseLeave={e => (e.currentTarget.style.background = `${r.color}15`)}
              >
                {r.cta} →
              </Link>
            </div>
          ))}
        </div>
      </section>

      {/* ── Locked preview ── */}
      <section style={{ padding: '0 60px 90px', maxWidth: 1120, margin: '0 auto' }}>
        {/* Jobs preview */}
        <div style={{ marginBottom: 56 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
            <h2 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 24, color: 'white', letterSpacing: '-0.5px' }}>Offres d'emploi</h2>
            <Link to="/register" style={{ fontSize: 13, color: '#4fffb0', textDecoration: 'none', fontWeight: 500 }}>S'inscrire pour voir →</Link>
          </div>
          <div style={{ position: 'relative' }}>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 12, filter: 'blur(4px)', pointerEvents: 'none', userSelect: 'none' }}>
              {[
                { icon: '🏢', title: '████████████', sub: '████████ · Alger', tag: 'Remote' },
                { icon: '🏢', title: '██████████████', sub: '██████ · Oran', tag: 'Full-time' },
                { icon: '🏢', title: '█████████████', sub: '█████████ · Constantine', tag: 'Hybrid' },
              ].map((c, i) => (
                <div key={i} style={{ background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 16, padding: 22 }}>
                  <div style={{ fontSize: 26, marginBottom: 12 }}>{c.icon}</div>
                  <div style={{ fontSize: 14, fontWeight: 600, color: 'white', marginBottom: 5, fontFamily: '"Syne", sans-serif', letterSpacing: 2 }}>{c.title}</div>
                  <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.38)', letterSpacing: 2 }}>{c.sub}</div>
                  <span style={{ display: 'inline-block', marginTop: 12, fontSize: 11, color: '#4fffb0', background: 'rgba(79,255,176,0.1)', padding: '3px 10px', borderRadius: 100 }}>{c.tag}</span>
                </div>
              ))}
            </div>
            <div style={{ position: 'absolute', inset: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 14, background: 'rgba(8,12,20,0.55)', backdropFilter: 'blur(2px)', borderRadius: 16 }}>
              <div style={{ fontSize: 28 }}>🔒</div>
              <p style={{ color: 'rgba(255,255,255,0.7)', fontSize: 14, fontWeight: 500, textAlign: 'center' }}>Créez un compte gratuit pour voir toutes les offres</p>
              <Link to="/register" style={{ padding: '10px 24px', borderRadius: 100, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 14, textDecoration: 'none' }}>
                Voir les offres →
              </Link>
            </div>
          </div>
        </div>

        {/* Trainings preview */}
        <div>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
            <h2 style={{ fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 24, color: 'white', letterSpacing: '-0.5px' }}>Formations professionnelles</h2>
            <Link to="/register" style={{ fontSize: 13, color: '#4fffb0', textDecoration: 'none', fontWeight: 500 }}>S'inscrire pour voir →</Link>
          </div>
          <div style={{ position: 'relative' }}>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 12, filter: 'blur(4px)', pointerEvents: 'none', userSelect: 'none' }}>
              {[
                { title: '█████████████ IA', school: 'USTHB · 3 mois' },
                { title: '██████ & Cloud AWS', school: 'ISI Alger · 2 mois' },
                { title: '████ Science Python', school: 'ESI · 6 semaines' },
              ].map((c, i) => (
                <div key={i} style={{ background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 16, padding: 22 }}>
                  <div style={{ fontSize: 26, marginBottom: 12 }}>🎓</div>
                  <div style={{ fontSize: 14, fontWeight: 600, color: 'white', marginBottom: 5, fontFamily: '"Syne", sans-serif', letterSpacing: 2 }}>{c.title}</div>
                  <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.38)' }}>{c.school}</div>
                  <span style={{ display: 'inline-block', marginTop: 12, fontSize: 11, color: '#4fffb0', background: 'rgba(79,255,176,0.1)', padding: '3px 10px', borderRadius: 100 }}>Disponible</span>
                </div>
              ))}
            </div>
            <div style={{ position: 'absolute', inset: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 14, background: 'rgba(8,12,20,0.55)', backdropFilter: 'blur(2px)', borderRadius: 16 }}>
              <div style={{ fontSize: 28 }}>🔒</div>
              <p style={{ color: 'rgba(255,255,255,0.7)', fontSize: 14, fontWeight: 500, textAlign: 'center' }}>Créez un compte gratuit pour voir toutes les formations</p>
              <Link to="/register" style={{ padding: '10px 24px', borderRadius: 100, background: '#4fffb0', color: '#080c14', fontWeight: 700, fontSize: 14, textDecoration: 'none' }}>
                Voir les formations →
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* ── Footer ── */}
      <footer style={{ borderTop: '1px solid rgba(255,255,255,0.06)', padding: '40px 60px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 16 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 18, color: 'white' }}>
          <img src="/iqra-logo.png" alt="IQRA" style={{ width: 26, height: 26, borderRadius: 6, objectFit: 'cover' }} />
          IQRA
        </div>
        <div style={{ display: 'flex', gap: 28 }}>
          {([['Offres', '/jobs'], ['Formations', '/trainings'], ['Entreprises', '/companies'], ['Écoles', '/schools']] as [string, string][]).map(([l, h]) => (
            <Link key={l} to={h} style={{ textDecoration: 'none', color: 'rgba(255,255,255,0.35)', fontSize: 13, transition: 'color .2s' }}
              onMouseEnter={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.7)')}
              onMouseLeave={e => (e.currentTarget.style.color = 'rgba(255,255,255,0.35)')}
            >{l}</Link>
          ))}
        </div>
        <p style={{ color: 'rgba(255,255,255,0.22)', fontSize: 13 }}>© 2026 IQRA · Algérie</p>
      </footer>
    </div>
  )
}
