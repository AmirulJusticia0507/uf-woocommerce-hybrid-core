/** @type {import('tailwindcss').Config} */
module.exports = {
  prefix: 'xiv-',
  // Preflight dimatikan agar tidak merusak styling bawaan wp-admin.
  corePlugins: { preflight: false },
  content: [
    './inc/admin-crud.php',
    './inc/store-locator.php',
    './inc/back-in-stock.php',
    './assets/admin/js/**/*.js'
  ],
  theme: {
    extend: {
      colors: {
        'xiv-bg': '#f4f4f2',
        'xiv-black': '#0a0a0a',
        'xiv-gray-light': '#e5e5e0',
        'xiv-gray-text': '#767676',
        'xiv-blue-accent': '#2541b2'
      },
      fontFamily: {
        'display': ['"Syne"', '"Space Grotesk"', 'sans-serif'],
        'sans': ['"Inter"', 'sans-serif'],
        'mono': ['"JetBrains Mono"', 'monospace']
      }
    }
  },
  plugins: []
}
