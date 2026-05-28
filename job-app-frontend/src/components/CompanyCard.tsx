import { Link } from 'react-router-dom'
import type { Company } from '@/types'

export default function CompanyCard({ company }: { company: Company }) {
  return (
    <Link to={`/companies/${company.id}`} className="card block p-5 hover:border-primary-300">
      <div className="flex items-center gap-3">
        {company.logo ? (
          <img src={company.logo} alt={company.name} className="w-12 h-12 rounded-lg object-contain border border-gray-100 bg-white p-1" />
        ) : (
          <div className="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 font-bold text-lg">
            {company.name.charAt(0)}
          </div>
        )}
        <div className="min-w-0">
          <h3 className="font-semibold text-gray-900 truncate">{company.name}</h3>
          {company.sector && <p className="text-xs text-gray-500 mt-0.5">{company.sector}</p>}
        </div>
      </div>
    </Link>
  )
}
