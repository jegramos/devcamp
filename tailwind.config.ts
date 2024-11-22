import defaultTheme from 'tailwindcss/defaultTheme'
import primeVueTailwind from 'tailwindcss-primeui'

export default {
  content: ['./resources/**/*.blade.php', './resources/**/*.js', './resources/**/*.vue'],
  darkMode: 'selector',
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
        brand: ['Kumar One', 'serif'],
        content: ['Lato', 'sans-serif'],
        stylish: ['Comfortaa', 'serif'],
      },
      keyframes: {
        'spin-up': {
          '0%': { transform: 'translateY(0) rotate(0deg)', opacity: '1', borderRadius: '0' },
          '100%': { transform: 'translateY(-1000px) rotate(720deg)', opacity: '0', borderRadius: '50%' },
        },
        shake: {
          '10%, 90%': { transform: 'translate3d(-1px, 0, 0)' },
          '20%, 80%': { transform: 'translate3d(2px, 0, 0)' },
          '30%, 50%, 70%': { transform: 'translate3d(-4px, 0, 0)' },
          '40%, 60%': { transform: 'translate3d(4px, 0, 0)' },
        },
      },
      animation: {
        'float-up': 'spin-up 25s linear infinite',
        shake: 'shake 0.82s cubic-bezier(.36,.07,.19,.97) both',
      },
      animationDelay: {
        475: '475ms',
        2000: '2s',
      },
      animationDuration: {
        4000: '4s',
        slow: '10s',
      },
    },
  },
  plugins: [primeVueTailwind],
}
