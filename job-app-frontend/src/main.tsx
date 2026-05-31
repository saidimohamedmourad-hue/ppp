import React from 'react'
import ReactDOM from 'react-dom/client'
import { GoogleOAuthProvider } from '@react-oauth/google'
import App from './App'
import './index.css'

// The Google client ID is set via .env (VITE_GOOGLE_WEB_CLIENT_ID). When it's
// missing in dev we still render the app — Google buttons just do nothing —
// instead of crashing the whole page.
const googleClientId = import.meta.env.VITE_GOOGLE_WEB_CLIENT_ID ?? ''

const tree = (
  <React.StrictMode>
    <App />
  </React.StrictMode>
)

ReactDOM.createRoot(document.getElementById('root')!).render(
  googleClientId ? <GoogleOAuthProvider clientId={googleClientId}>{tree}</GoogleOAuthProvider> : tree,
)
