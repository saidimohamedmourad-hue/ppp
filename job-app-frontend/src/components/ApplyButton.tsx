import { isLoggedIn } from '@/utils/api'

interface Props {
  onApply: () => void
}

export default function ApplyButton({ onApply }: Props) {
  const handleClick = () => {
    if (!isLoggedIn()) {
      window.location.href = '/login'
      return
    }
    onApply()
  }

  return (
    <button onClick={handleClick} className="btn-primary w-full sm:w-auto">
      Postuler à cette offre
    </button>
  )
}
