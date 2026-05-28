import { getUser } from '@/utils/api'
import CandidatDashboard from './CandidatDashboard'
import CompanyDashboard from '../company/CompanyDashboard'
import SchoolDashboard from '../school/SchoolDashboard'

export default function Dashboard() {
  const user = getUser()
  const role = user?.role ?? 'job-seeker'

  if (role === 'company-owner') return <CompanyDashboard />
  if (role === 'school-owner') return <SchoolDashboard />
  return <CandidatDashboard />
}
