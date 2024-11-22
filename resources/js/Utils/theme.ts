import { useMediaQuery } from '@vueuse/core'
import { usePage } from '@inertiajs/vue3'
import type { SharedPage } from '@/Types/shared-page.ts'

export const applyTheme = function (theme?: 'light' | 'dark' | 'auto') {
  if (!theme) {
    const fromLocalStorage = localStorage.getItem('theme') as 'light' | 'dark' | 'auto'
    theme = fromLocalStorage || usePage<SharedPage>().props.accountSettings?.theme || 'auto'
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
