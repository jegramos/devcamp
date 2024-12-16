import { useMediaQuery } from '@vueuse/core'
import { usePage } from '@inertiajs/vue3'
import type { SharedPage } from '@/Types/shared-page.ts'

export const applyTheme = function (theme?: 'light' | 'dark' | 'auto') {
  if (!theme) {
    const page = usePage<SharedPage>()
    const fromLocalStorage = localStorage.getItem('theme') as 'light' | 'dark' | 'auto'

    // Get the theme from the LocalStorage if there is no authenticated user
    if (!page.props.auth?.user) theme = fromLocalStorage || 'auto'

    // Update the theme and in the LocalStorage if there is an authenticated user
    if (page.props.auth?.user && page.props.accountSettings?.theme) {
      theme = page.props.accountSettings.theme
      localStorage.setItem('theme', theme)
    }
  }

  if (theme === 'dark') {
    document.body.classList.remove('theme-primary-ocean')
    document.body.classList.add('dark')
    document.body.classList.add('theme-primary-space')
    return
  }

  if (theme === 'light') {
    document.body.classList.remove('dark')
    document.body.classList.remove('theme-primary-space')
    document.body.classList.add('theme-primary-ocean')
    return
  }

  // Auto mode
  const isPreferredDark = useMediaQuery('(prefers-color-scheme: dark)')
  if (isPreferredDark.value) {
    applyTheme('dark')
    return
  }

  applyTheme('light')
}
