export default function LoadingSpinner({ className = '' }: { className?: string }) {
  return (
    <div className={`flex justify-center items-center py-16 ${className}`}>
      <div style={{ width: 30, height: 30, border: '3px solid #e2ddd6', borderTopColor: '#e8502a', borderRadius: '50%', animation: 'spin 0.8s linear infinite' }} />
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )
}
