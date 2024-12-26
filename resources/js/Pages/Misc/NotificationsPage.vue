<script lang="ts">
import CmsLayout from '@/Layouts/CmsLayout.vue'

export default {
  layout: CmsLayout,
}
</script>
<script setup lang="ts">
import Card from 'primevue/card'
import { type PropType } from 'vue'
import { useDateFormat } from '@vueuse/core'
import DfPaginator, { type PaginatedList } from '@/Components/DfPaginator.vue'
import { useForm } from '@inertiajs/vue3'
import Tag from 'primevue/tag'

type Notification = {
  id: string
  read_at: string
  created_at: string
  label: string
  data: {
    name: string
    message: string
    email: string
  }
}

type NotificationList = PaginatedList & {
  data: Array<Notification>
}

const props = defineProps({
  portfolioInquiries: {
    type: Object as PropType<NotificationList>,
    required: true,
  },
  markAsReadUrl: {
    type: String,
    required: true,
  },
})

const form = useForm<{ id: string | null }>({
  id: null,
})

const submitMarkAsReadForm = function (notification: Notification) {
  if (notification.read_at) return

  form.id = notification.id

  form.post(props.markAsReadUrl, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <section class="mt-2 flex flex-col gap-3">
    <span class="mb-2 text-sm font-bold uppercase">Portfolio Inquiries</span>
    <Card
      v-for="n in props.portfolioInquiries.data"
      :key="n.id"
      class="w-full transition-transform hover:scale-[101%] hover:cursor-pointer"
      @mouseover="submitMarkAsReadForm(n)"
    >
      <template #content>
        <div class="relative flex flex-col">
          <div class="mb-2 flex flex-wrap justify-between">
            <Tag icon="pi pi-book" severity="warn" :value="n.label" class="!text-xs"></Tag>
            <small class="text-surface-500 dark:text-surface-400">{{ useDateFormat(n.created_at, 'MMMM DD, YYYY') }}</small>
          </div>
          <span class="font-bold uppercase">{{ n.data.name }}</span>
          <small class="font-bold">{{ n.data.email }}</small>
          <p class="mt-2">"{{ n.data.message }}"</p>
          <!-- Start Unread Indicator -->
          <span v-if="!n.read_at" class="absolute -right-1 top-0 flex h-3 w-3">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
            <span class="absolute inline-flex h-3 w-3 rounded-full bg-red-500"></span>
          </span>
          <!-- End Unread Indicator -->
        </div>
      </template>
    </Card>
    <DfPaginator :list="props.portfolioInquiries" class="md:mt-10" />
  </section>
</template>

<style scoped></style>
