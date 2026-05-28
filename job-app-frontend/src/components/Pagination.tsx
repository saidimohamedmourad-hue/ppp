interface Props {
  currentPage: number
  lastPage: number
  onPageChange: (page: number) => void
}

export default function Pagination({ currentPage, lastPage, onPageChange }: Props) {
  if (lastPage <= 1) return null
  return (
    <div className="flex justify-center items-center gap-2 mt-8">
      <button
        className="btn-secondary px-3 py-1.5 text-xs"
        disabled={currentPage === 1}
        onClick={() => onPageChange(currentPage - 1)}
      >
        Précédent
      </button>
      <span className="text-sm text-gray-600">
        Page {currentPage} / {lastPage}
      </span>
      <button
        className="btn-secondary px-3 py-1.5 text-xs"
        disabled={currentPage === lastPage}
        onClick={() => onPageChange(currentPage + 1)}
      >
        Suivant
      </button>
    </div>
  )
}
