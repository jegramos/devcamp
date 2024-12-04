<script setup lang="ts">
import type { PropType } from 'vue'
import { router } from '@inertiajs/vue3'

export type PaginatedList = {
  data: Array<object>
  current_page: number
  last_page: number
  prev_page_url: string | null
  next_page_url: string | null
  first_page_url: string
  last_page_url: string
  path: string
  from: number
  to: number
  total: number
  per_page: number
  links: {
    active: boolean
    label: string
    url: string | null
  }
}

const props = defineProps({
  list: {
    type: Object as PropType<PaginatedList>,
    required: true,
  },
})

const handleFirstPage = function () {
  if (props.list.current_page === 1) return
  router.get(props.list?.first_page_url)
}

const handlePrevPage = function () {
  if (!props.list.prev_page_url) return

  router.get(props.list.prev_page_url)
}

const handleNextPage = function () {
  if (!props.list.next_page_url) return

  router.get(props.list.next_page_url)
}

const handleLastPage = function () {
  if (props.list.current_page === props.list.last_page) return
  router.get(props.list.last_page_url)
}
</script>

<template>
  <div class="my-6 flex flex-col items-center justify-center">
    <div
      class="flex w-auto items-center justify-center gap-x-4 rounded-lg bg-surface-0 px-6 py-4 text-surface-500 dark:border dark:border-surface-700 dark:bg-surface-950 dark:text-surface-400"
    >
      <button class="transition-transform hover:scale-110 hover:cursor-pointer hover:text-primary" @click="handleFirstPage">
        <i class="pi pi-angle-double-left mr-4"></i>
      </button>
      <button class="transition-transform hover:scale-110 hover:cursor-pointer hover:text-primary" @click="handlePrevPage">
        <i class="pi pi-angle-left"></i>
      </button>
      <p class="text-sm">
        Showing <b>{{ props.list.from }}</b> to <b>{{ props.list.to }}</b> of <b>{{ props.list.total }}</b>
      </p>
      <button class="transition-transform hover:scale-110 hover:cursor-pointer hover:text-primary" @click="handleNextPage">
        <i class="pi pi-angle-right"></i>
      </button>
      <button class="transition-transform hover:scale-110 hover:cursor-pointer hover:text-primary" @click="handleLastPage">
        <i class="pi pi-angle-double-right ml-4"></i>
      </button>
    </div>
  </div>
</template>

<style scoped></style>
