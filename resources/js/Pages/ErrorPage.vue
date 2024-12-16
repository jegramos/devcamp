<script setup lang="ts">
import { computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import AppAnimatedFloaters from '@/Components/AppAnimatedFloaters.vue'
import { applyTheme } from '@/Utils/theme.ts'

const props = defineProps({
  status: {
    type: Number,
    default: 500,
  },
  message: {
    type: String || null,
    default: null,
  },
})

const title = computed(() => {
  return {
    503: 'Service Unavailable',
    500: 'Server Error',
    404: 'Page Not Found',
    403: 'Forbidden',
    413: 'Request Entity Too Large',
  }[props.status]
})

const description = computed(() => {
  return {
    503: 'Sorry, we are doing some maintenance. Please check back soon.',
    500: 'Whoops, something went wrong on our servers.',
    404: 'Sorry, the page you are looking for could not be found.',
    403: 'Sorry, you are forbidden from accessing this page.',
    413: 'Sorry, you are uploading a file that is too large.',
  }[props.status]
})

const navigateBack = () => window.history.back()
const navigateToHome = () => router.get('/')

applyTheme()
</script>

<template>
  <Head :title="title"></Head>
  <div
    class="flex h-[100vh] w-[100vw] flex-col items-center justify-start bg-gradient-to-b from-primary/90 to-primary pt-16 md:justify-center md:pt-0"
  >
    <AppAnimatedFloaters />
    <h1 class="font-stylish text-[7rem] font-black text-surface-200">{{ status }}</h1>
    <p class="text-surface-200">{{ props.message ?? description }}</p>
    <div class="flex gap-x-4">
      <Button icon="pi pi-caret-left" label="PREVIOUS PAGE" class="mt-6 dark:!text-surface-0" @click="navigateBack"></Button>
      <Button icon="pi pi-home" label="BACK TO HOME" class="mt-6 dark:!text-surface-0" @click="navigateToHome"></Button>
    </div>
  </div>
</template>
