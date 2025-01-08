import defaultTheme from 'tailwindcss/defaultTheme'
import primeVueTailwind from 'tailwindcss-primeui'
import { setupInspiraUI } from '@inspira-ui/plugins'

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
        writing: ['Pacifico', 'serif'],
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
      // colors: {
      //   border: 'hsl(var(--border))',
      //   input: 'hsl(var(--input))',
      //   ring: 'hsl(var(--ring))',
      //   background: 'hsl(var(--background))',
      //   foreground: 'hsl(var(--foreground))',
      //   primary: {
      //     DEFAULT: 'hsl(var(--primary))',
      //     foreground: 'hsl(var(--primary-foreground))',
      //   },
      //   secondary: {
      //     DEFAULT: 'hsl(var(--secondary))',
      //     foreground: 'hsl(var(--secondary-foreground))',
      //   },
      //   destructive: {
      //     DEFAULT: 'hsl(var(--destructive))',
      //     foreground: 'hsl(var(--destructive-foreground))',
      //   },
      //   muted: {
      //     DEFAULT: 'hsl(var(--muted))',
      //     foreground: 'hsl(var(--muted-foreground))',
      //   },
      //   accent: {
      //     DEFAULT: 'hsl(var(--accent))',
      //     foreground: 'hsl(var(--accent-foreground))',
      //   },
      //   popover: {
      //     DEFAULT: 'hsl(var(--popover))',
      //     foreground: 'hsl(var(--popover-foreground))',
      //   },
      //   card: {
      //     DEFAULT: 'hsl(var(--card))',
      //     foreground: 'hsl(var(--card-foreground))',
      //   },
      // },
      // borderRadius: {
      //   xl: 'calc(var(--radius) + 4px)',
      //   lg: 'var(--radius)',
      //   md: 'calc(var(--radius) - 2px)',
      //   sm: 'calc(var(--radius) - 4px)',
      // },
    },
  },
  plugins: [import('tailwindcss-animate'), setupInspiraUI, primeVueTailwind],
}
