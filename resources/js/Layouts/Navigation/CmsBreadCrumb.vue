<script setup lang="ts">
import { ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import Breadcrumb from 'primevue/breadcrumb'
import { useCmsNavLinks } from '@/Composables/useCmsNavLinks'
import type { SharedPage } from '@/Types/shared-page.ts'

// Get the group name
const page = usePage<SharedPage>()
const cmsNavLinks = useCmsNavLinks(page)
let groupName = ref('')
let linkName = ref('')

const items = ref<{ label: string }[]>([])

router.on('navigate', () => {
  for (const item of cmsNavLinks.navItems.value) {
    const matchedGroupLink = item.links.find((link) => page.url.startsWith(link.uri))
    if (matchedGroupLink) {
      groupName.value = item.group
      linkName.value = matchedGroupLink.name
      break
    }
  }
  items.value = [{ label: groupName.value }, { label: linkName.value }]
})
</script>

<template>
  <Breadcrumb :model="items" class="text-sm">
    <template #separator><i class="pi pi-angle-right"></i></template>
  </Breadcrumb>
</template>

<style scoped></style>
