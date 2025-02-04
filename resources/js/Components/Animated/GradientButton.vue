<script lang="ts" setup>
import { cn } from '@/Utils/inspira.ts'
import { computed } from 'vue'

interface GradientButtonProps {
  borderWidth?: number
  colors?: string[]
  duration?: number
  borderRadius?: number
  blur?: number
  class?: string
  bgColor?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<GradientButtonProps>(), {
  colors: () => ['#FF0000', '#ff7300', '#ff41c2'],
  duration: 2500,
  borderWidth: 2,
  borderRadius: 8,
  blur: 4,
  bgColor: '#000',
  class: '',
})

const durationInMilliseconds = computed(() => `${props.duration}ms`)
const allColors = computed(() => props.colors.join(', '))
const borderWidthInPx = computed(() => `${props.borderWidth}px`)
const borderRadiusInPx = computed(() => `${props.borderRadius}px`)
const blurPx = computed(() => `${props.blur}px`)
</script>

<template>
  <button
    :class="
      cn(
        'animate-rainbow rainbow-btn relative flex min-h-10 min-w-28 items-center justify-center overflow-hidden before:absolute before:-inset-[200%]',
        props.class
      )
    "
  >
    <span class="btn-content inline-flex size-full items-center justify-center px-4 py-2">
      <slot />
    </span>
  </button>
</template>

<style scoped>
.animate-rainbow::before {
  content: '';
  background: conic-gradient(v-bind(allColors));
  animation: rotate-rainbow v-bind(durationInMilliseconds) linear infinite;
  filter: blur(v-bind(blurPx));
  padding: v-bind(borderWidthInPx);
}

.rainbow-btn {
  padding: v-bind(borderWidthInPx);
  border-radius: v-bind(borderRadiusInPx);
}

.btn-content {
  border-radius: v-bind(borderRadiusInPx);
  background-color: v-bind(bgColor);
  z-index: 0;
}

@keyframes rotate-rainbow {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
