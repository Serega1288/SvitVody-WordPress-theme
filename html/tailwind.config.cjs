module.exports = {
  content: ['./*.html', './assets/js/*.js'],
  theme: {
    extend: {
      colors: {
        brand: {
          blue: '#0066CC',
          orange: '#FF6B00',
          lightblue: '#E6F2FF',
          formblue: '#87D1ED',
          darkblue: '#F5F9FF',
          navy: '#002B54',
          line: '#CCE4FF',
        },
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        heading: ['Montserrat', 'sans-serif'],
      },
      dropShadow: {
        '3xl': '0 35px 35px rgba(0, 43, 84, 0.22)',
      },
    },
  },
  safelist: ['hidden', 'flex', 'scale-110', 'text-brand-orange'],
};