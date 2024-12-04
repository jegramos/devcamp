<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3'
import AppLogo from '@/Components/AppLogo.vue'
import { useCmsNavLinks } from '@/Composables/useCmsNavLinks'
import type { SharedPage } from '@/Types/shared-page.ts'

const page = usePage<SharedPage>()
const { navItems } = useCmsNavLinks(page)
</script>

<template>
  <div class="h-100% flex flex-col bg-surface-0 dark:bg-surface-900">
    <!-- Start Logo -->
    <div class="flex justify-center border-b border-surface-200 px-12 py-6 dark:border-surface-950">
      <AppLogo></AppLogo>
    </div>
    <!-- End Logo -->
    <!-- Start Nav Items -->
    <aside class="flex flex-col overflow-y-auto bg-surface-0 px-5 pt-4 dark:bg-surface-900">
      <div class="flex flex-1 flex-col justify-between">
        <nav class="-mx-3 space-y-3">
          <div
            v-for="item in navItems"
            :key="item.group"
            class="space-y-3 border-b border-surface-200 pb-3 dark:border-surface-950"
          >
            <label class="px-3 text-xs font-bold uppercase text-primary dark:text-primary">
              {{ item.group }}
            </label>
            <Link
              v-for="link in item.links"
              :key="link.name"
              :href="link.uri"
              class="flex transform items-center rounded-lg px-3 py-2 transition-colors hover:cursor-pointer hover:bg-primary hover:text-primary-contrast dark:text-surface-0 hover:dark:text-surface-0"
              :class="{ 'bg-primary text-primary-contrast dark:text-surface-0': page.url.startsWith(link.uri) }"
              prefetch
            >
              <i :class="`${link.icon} rounded-lg border p-1`"></i>
              <span class="mx-3 text-sm">{{ link.name }}</span>
            </Link>
          </div>
        </nav>
      </div>
    </aside>
    <!-- End Nav Items -->
  </div>
</template>

<style scoped></style>
