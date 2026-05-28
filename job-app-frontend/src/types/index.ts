export interface User {
  id: number
  name: string
  email: string
  role: 'job-seeker' | 'company-owner' | 'school-owner' | 'admin'
  photo?: string
}

export interface JobCategory {
  id: number
  name: string
}

export interface TrainingCategory {
  id: number
  name: string
}

export interface Company {
  id: number
  name: string
  logo?: string
  description?: string
  sector?: string
}

export interface School {
  id: number
  name: string
  logo?: string
  description?: string
}

export interface Job {
  id: number
  title: string
  company: Company
  location: string
  contract_type: string
  salary?: string
  description: string
  category?: JobCategory
  created_at: string
}

export type TrainingType = 'en_ligne' | 'accelerer' | 'presentiel'
export type TrainingStatus = 'open' | 'closed' | 'cancelled'

export interface TrainingSession {
  id: number
  title: string
  school: School
  duration?: string
  price?: number
  description: string
  category?: TrainingCategory
  start_date?: string
  type?: TrainingType
  status?: TrainingStatus
  cancellation_reason?: string | null
  maxParticipants?: number
  currentParticipants?: number
  is_full?: boolean
}

export interface Resume {
  id: number
  filename: string
  url: string
  created_at: string
}

export interface JobApplication {
  id: number
  job: Job
  status: 'pending' | 'accepted' | 'rejected'
  cover_letter?: string
  created_at: string
}

export interface TrainingApplication {
  id: number
  training_session: TrainingSession
  status: 'pending' | 'accepted' | 'rejected' | 'reviewed'
  is_waitlist?: boolean
  created_at: string
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
