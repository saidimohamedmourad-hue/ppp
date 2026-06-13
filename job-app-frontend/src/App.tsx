import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import DashboardLayout from '@/layouts/DashboardLayout'

import Home from '@/pages/Home'
import Login from '@/pages/Login'
import Register from '@/pages/Register'
import ForgotPassword from '@/pages/ForgotPassword'
import ResetPassword from '@/pages/ResetPassword'

import Dashboard from '@/pages/dashboard/Dashboard'
import Profile from '@/pages/dashboard/Profile'
import Notifications from '@/pages/dashboard/Notifications'
import Resumes from '@/pages/dashboard/Resumes'
import JobApplications from '@/pages/dashboard/JobApplications'
import TrainingApplications from '@/pages/dashboard/TrainingApplications'
import DashboardJobs from '@/pages/dashboard/DashboardJobs'
import DashboardTrainings from '@/pages/dashboard/DashboardTrainings'

import CompanyJobs from '@/pages/company/CompanyJobs'
import JobApplicants from '@/pages/company/JobApplicants'

import SchoolTrainings from '@/pages/school/SchoolTrainings'
import SessionApplicants from '@/pages/school/SessionApplicants'

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
        <Route path="/dashboard/notifications" element={<DashboardLayout><Notifications /></DashboardLayout>} />
        <Route path="/dashboard/resumes" element={<DashboardLayout><Resumes /></DashboardLayout>} />
        <Route path="/dashboard/job-applications" element={<DashboardLayout><JobApplications /></DashboardLayout>} />
        <Route path="/dashboard/training-applications" element={<DashboardLayout><TrainingApplications /></DashboardLayout>} />
        <Route path="/dashboard/company/jobs" element={<DashboardLayout><CompanyJobs /></DashboardLayout>} />
        <Route path="/dashboard/company/jobs/:jobId/applicants" element={<DashboardLayout><JobApplicants /></DashboardLayout>} />
        <Route path="/dashboard/school/sessions" element={<DashboardLayout><SchoolTrainings /></DashboardLayout>} />
        <Route path="/dashboard/school/sessions/:sessionId/applicants" element={<DashboardLayout><SessionApplicants /></DashboardLayout>} />

        {/* Home — standalone dark page (own nav) */}
        <Route path="/" element={<Home />} />

        {/* Ancien parcours public (Offres/Formations/Entreprises/Écoles) retiré —
            ces chemins renvoient maintenant vers l'accueil. Le candidat parcourt
            offres & formations depuis son tableau de bord. */}
        <Route path="/jobs" element={<Navigate to="/" replace />} />
        <Route path="/jobs/:id" element={<Navigate to="/" replace />} />
        <Route path="/trainings" element={<Navigate to="/" replace />} />
        <Route path="/trainings/:id" element={<Navigate to="/" replace />} />
        <Route path="/companies" element={<Navigate to="/" replace />} />
        <Route path="/companies/:id" element={<Navigate to="/" replace />} />
        <Route path="/schools" element={<Navigate to="/" replace />} />
        <Route path="/schools/:id" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
