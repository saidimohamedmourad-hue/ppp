import { BrowserRouter, Routes, Route, Link, Navigate } from 'react-router-dom'
import DashboardLayout from '@/layouts/DashboardLayout'
import { isLoggedIn, getUser } from '@/utils/api'

/* ── Auth guard for listing/detail pages ── */
function RequireAuth({ children }: { children: React.ReactNode }) {
  if (!isLoggedIn()) return <Navigate to="/login" replace />
  return <>{children}</>
}

import Home from '@/pages/Home'
import Jobs from '@/pages/Jobs'
import JobDetail from '@/pages/JobDetail'
import Trainings from '@/pages/Trainings'
import TrainingDetail from '@/pages/TrainingDetail'
import Companies from '@/pages/Companies'
import CompanyDetail from '@/pages/CompanyDetail'
import Schools from '@/pages/Schools'
import SchoolDetail from '@/pages/SchoolDetail'
import Login from '@/pages/Login'
import Register from '@/pages/Register'
import ForgotPassword from '@/pages/ForgotPassword'
import ResetPassword from '@/pages/ResetPassword'

import Dashboard from '@/pages/dashboard/Dashboard'
import Profile from '@/pages/dashboard/Profile'
import Resumes from '@/pages/dashboard/Resumes'
import JobApplications from '@/pages/dashboard/JobApplications'
import TrainingApplications from '@/pages/dashboard/TrainingApplications'
import DashboardJobs from '@/pages/dashboard/DashboardJobs'
import DashboardTrainings from '@/pages/dashboard/DashboardTrainings'

import CompanyJobs from '@/pages/company/CompanyJobs'
import JobApplicants from '@/pages/company/JobApplicants'

import SchoolTrainings from '@/pages/school/SchoolTrainings'
import SessionApplicants from '@/pages/school/SessionApplicants'

/* ── Dark public layout shared by all listing/detail pages ── */
function DarkPublicLayout({ children }: { children: React.ReactNode }) {
  const loggedIn = isLoggedIn()
  const role = getUser()?.role
  const spaceHref = (role === 'company-owner' || role === 'school-owner') ? '/dashboard' : '/jobs'
  const spaceLabel = role === 'company-owner' ? 'Mon entreprise →' : role === 'school-owner' ? 'Mon école →' : 'Voir les offres →'

  return (
    <div style={{ background: '#080c14', minHeight: '100vh', color: 'rgba(255,255,255,0.8)', fontFamily: '"DM Sans", sans-serif' }}>
      {/* Nav */}
      <nav style={{
        position: 'sticky', top: 0, zIndex: 100,
        borderBottom: '1px solid rgba(255,255,255,0.06)',
        background: 'rgba(8,12,20,0.9)', backdropFilter: 'blur(12px)',
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

      {/* Page content */}
      <main>{children}</main>

      {/* Footer */}
      <footer style={{ borderTop: '1px solid rgba(255,255,255,0.06)', padding: '36px 60px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 16 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontFamily: '"Syne", sans-serif', fontWeight: 800, fontSize: 17, color: 'white' }}>
          <img src="/iqra-logo.png" alt="IQRA" style={{ width: 24, height: 24, borderRadius: 6, objectFit: 'cover' }} />
          IQRA
        </div>
        <div style={{ display: 'flex', gap: 24 }}>
          {([['Offres', '/jobs'], ['Formations', '/trainings'], ['Entreprises', '/companies'], ['Écoles', '/schools']] as [string, string][]).map(([l, h]) => (
            <Link key={l} to={h} style={{ textDecoration: 'none', color: 'rgba(255,255,255,0.32)', fontSize: 13 }}>{l}</Link>
          ))}
        </div>
        <p style={{ color: 'rgba(255,255,255,0.2)', fontSize: 13 }}>© 2026 IQRA · Algérie</p>
      </footer>
    </div>
  )
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        {/* Auth — standalone */}
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/forgot-password" element={<ForgotPassword />} />
        <Route path="/reset-password" element={<ResetPassword />} />

        {/* Dashboard — dark sidebar layout */}
        <Route path="/dashboard" element={<DashboardLayout><Dashboard /></DashboardLayout>} />
        <Route path="/dashboard/jobs" element={<DashboardLayout><DashboardJobs /></DashboardLayout>} />
        <Route path="/dashboard/trainings" element={<DashboardLayout><DashboardTrainings /></DashboardLayout>} />
        <Route path="/dashboard/profile" element={<DashboardLayout><Profile /></DashboardLayout>} />
        <Route path="/dashboard/resumes" element={<DashboardLayout><Resumes /></DashboardLayout>} />
        <Route path="/dashboard/job-applications" element={<DashboardLayout><JobApplications /></DashboardLayout>} />
        <Route path="/dashboard/training-applications" element={<DashboardLayout><TrainingApplications /></DashboardLayout>} />
        <Route path="/dashboard/company/jobs" element={<DashboardLayout><CompanyJobs /></DashboardLayout>} />
        <Route path="/dashboard/company/jobs/:jobId/applicants" element={<DashboardLayout><JobApplicants /></DashboardLayout>} />
        <Route path="/dashboard/school/sessions" element={<DashboardLayout><SchoolTrainings /></DashboardLayout>} />
        <Route path="/dashboard/school/sessions/:sessionId/applicants" element={<DashboardLayout><SessionApplicants /></DashboardLayout>} />

        {/* Home — standalone dark page (own nav) */}
        <Route path="/" element={<Home />} />

        {/* Public pages — auth required */}
        <Route path="/jobs" element={<RequireAuth><DarkPublicLayout><Jobs /></DarkPublicLayout></RequireAuth>} />
        <Route path="/jobs/:id" element={<RequireAuth><DarkPublicLayout><JobDetail /></DarkPublicLayout></RequireAuth>} />
        <Route path="/trainings" element={<RequireAuth><DarkPublicLayout><Trainings /></DarkPublicLayout></RequireAuth>} />
        <Route path="/trainings/:id" element={<RequireAuth><DarkPublicLayout><TrainingDetail /></DarkPublicLayout></RequireAuth>} />
        <Route path="/companies" element={<RequireAuth><DarkPublicLayout><Companies /></DarkPublicLayout></RequireAuth>} />
        <Route path="/companies/:id" element={<RequireAuth><DarkPublicLayout><CompanyDetail /></DarkPublicLayout></RequireAuth>} />
        <Route path="/schools" element={<RequireAuth><DarkPublicLayout><Schools /></DarkPublicLayout></RequireAuth>} />
        <Route path="/schools/:id" element={<RequireAuth><DarkPublicLayout><SchoolDetail /></DarkPublicLayout></RequireAuth>} />
      </Routes>
    </BrowserRouter>
  )
}
