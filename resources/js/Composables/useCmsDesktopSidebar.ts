import { ref } from 'vue'

const isMinimized = ref(false)
const isMaximized = ref(true)

/**
 * @description Used as the central state store of the Sidebar in Desktop view
 */
export function useCmsDesktopSidebar() {
  const toggle = function () {
    isMinimized.value = !isMinimized.value
    isMaximized.value = !isMaximized.value
  }

  const minimize = function () {
    isMinimized.value = true
    isMaximized.value = false
  }

  const maximize = function () {
    isMinimized.value = false
    isMaximized.value = true
  }

  return {
    toggle,
    minimize,
    maximize,
    isMinimized,
    isMaximized,
  }
}
