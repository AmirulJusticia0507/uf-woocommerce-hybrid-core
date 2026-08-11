/** @type {import('tailwindcss').Config} */
module.exports = {
  prefix: 'xiv-',
  darkMode: 'media',
  content: [
    './**/*.php',
    '!./inc/admin-crud.php',
    './assets/src/js/**/*.js',
    './inc/**/*.php',
    './woocommerce/**/*.php'
  ],
  theme: {
    extend: {
      colors: {
        'xiv-bg': 'var(--xiv-bg)',
        'xiv-black': 'var(--xiv-ink)',
        'xiv-gray-light': 'var(--xiv-line)',
        'xiv-gray-text': 'var(--xiv-muted)',
        'xiv-blue-accent': 'var(--xiv-accent)'
      },
      fontFamily: {
        'display': ['"Syne"', '"Space Grotesk"', 'sans-serif'],
        'sans': ['"Inter"', 'sans-serif'],
        'mono': ['"JetBrains Mono"', 'monospace']
      },
      aspectRatio: {
        '3/4': '3 / 4'
      }
    }
  },
  plugins: []
}
