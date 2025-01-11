<script lang="ts">
import CmsLayout from '@/Layouts/CmsLayout.vue'

export default {
  layout: CmsLayout,
}
</script>

<template>
  <div class="mx-auto flex h-[100%] items-center justify-center">
    <div class="flex flex-col items-center">
      <div class="loader"></div>
      <h1 class="mt-4 font-stylish text-lg dark:text-surface-100">Nothing to see here. It's a work in progress.</h1>
    </div>
  </div>
</template>

<style scoped>
.loader {
  display: inline-flex;
  gap: 10px;
}

.loader:before,
.loader:after {
  content: '';
  height: 20px;
  aspect-ratio: 1;
  border-radius: 50%;
  background:
    linear-gradient(#222 0 0) top/100% 40% no-repeat,
    radial-gradient(farthest-side, #000 95%, #0000) 50%/8px 8px no-repeat #fff;
  animation: l7 1.5s infinite alternate ease-in;
}

@keyframes l7 {
  0%,
  70% {
    background-size:
      100% 40%,
      8px 8px;
  }
  85% {
    background-size:
      100% 120%,
      8px 8px;
  }
  100% {
    background-size:
      100% 40%,
      8px 8px;
  }
}
</style>
