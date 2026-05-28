/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        warm: '#f7f5f0',
        'warm-surface': '#efecea',
        'warm-border': '#e2ddd6',
        'warm-dark': '#141210',
        'warm-muted': '#7a756e',
        orange: {
          DEFAULT: '#e8502a',
          hover: '#d4401c',
          light: 'rgba(232,80,42,0.1)',
        },
        cobalt: '#2a6ee8',
        emerald2: '#28a06b',
        // Dashboard dark
        'dash-bg': '#0d0f12',
        'dash-surface': '#13161b',
        'dash-surface2': '#1a1e25',
        'dash-border': '#232830',
        'dash-border2': '#2e3540',
        'dash-text': '#e8ecf2',
        'dash-muted': '#6b7585',
        'dash-muted2': '#9aa3b0',
        gold: '#f0c45a',
        'gold-dim': 'rgba(240,196,90,0.12)',
      },
      fontFamily: {
        display: ['"Space Grotesk"', 'system-ui', 'sans-serif'],
        body: ['"Bricolage Grotesque"', 'system-ui', 'sans-serif'],
        serif: ['"Instrument Serif"', 'Georgia', 'serif'],
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
