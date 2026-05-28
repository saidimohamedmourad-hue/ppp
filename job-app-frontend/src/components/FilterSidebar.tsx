interface FilterOption {
  id: number
  name: string
}

interface Props {
  title: string
  options: FilterOption[]
  selected: number | null
  onSelect: (id: number | null) => void
}

export default function FilterSidebar({ title, options, selected, onSelect }: Props) {
  return (
    <aside className="w-56 shrink-0">
      <div className="card p-4">
        <h4 className="text-sm font-semibold text-gray-700 mb-3">{title}</h4>
        <ul className="space-y-1">
          <li>
            <button
              onClick={() => onSelect(null)}
              className={`w-full text-left text-sm px-2 py-1.5 rounded-md transition-colors ${
                selected === null ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600 hover:bg-gray-50'
              }`}
            >
              Tous
            </button>
          </li>
          {options.map((opt) => (
            <li key={opt.id}>
              <button
                onClick={() => onSelect(opt.id)}
                className={`w-full text-left text-sm px-2 py-1.5 rounded-md transition-colors ${
                  selected === opt.id ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600 hover:bg-gray-50'
                }`}
              >
                {opt.name}
              </button>
            </li>
          ))}
        </ul>
      </div>
    </aside>
  )
}
