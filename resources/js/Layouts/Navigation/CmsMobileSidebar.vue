<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Drawer from 'primevue/drawer'
import Button from 'primevue/button'
import Avatar from 'primevue/avatar'
import Tag from 'primevue/tag'
import type { SharedPage } from '@/Types/shared-page.ts'
import { useCmsNavLinks } from '@/Composables/useCmsNavLinks.ts'

const visible = ref(false)
const page = usePage<SharedPage>()
const authenticatedUser = computed(function () {
  return page.props.auth?.user
})

const { navItems } = useCmsNavLinks(page)

const logout = function () {
  router.post(page.props.logoutUrl)
}
</script>

<template>
  <Drawer v-model:visible="visible" position="right">
    <!-- Start Avatar Header -->
    <template #header>
      <div v-if="authenticatedUser" class="flex w-full items-center">
        <div class="flex">
          <Avatar
            :image="authenticatedUser?.profile_picture_url ?? undefined"
            :label="`${authenticatedUser?.profile_picture_url ? '' : authenticatedUser?.nameInitials}`"
            class="mr-2.5 overflow-hidden"
            shape="square"
            size="large"
          />
        </div>
        <div class="flex-3 flex flex-col">
          <span class="text-left font-bold">{{ authenticatedUser.full_name }}</span>
          <span class="flex flex-wrap gap-1">
            <Tag v-for="role in authenticatedUser.roles" :key="role" :value="role" class="mt-0.5 !text-xs"></Tag>
          </span>
        </div>
      </div>
    </template>
    <!-- End Avatar Header -->
    <!-- Start Nav Items -->
    <nav class="-mx-3 space-y-3">
      <div v-for="item in navItems" :key="item.group" class="space-y-2 pb-2">
        <label class="px-1 text-xs font-bold uppercase text-primary">
          {{ item.group }}
        </label>
        <Link
          v-for="link in item.links"
          :key="link.name"
          :href="link.uri"
          class="flex transform items-center rounded-lg px-3 py-2 transition-colors hover:cursor-pointer hover:bg-primary/20 hover:text-primary"
          :class="{ 'bg-primary/20 text-primary': page.url.startsWith(link.uri) }"
        >
          <i :class="`${link.icon} rounded-lg border p-1 ${page.url.startsWith(link.uri) ? 'border-primary' : ''}`"></i>
          <span class="mx-2 text-sm font-medium">{{ link.name }}</span>
        </Link>
      </div>
    </nav>
    <!-- End Nav Items -->
    <!-- Start Logout Button -->
    <div class="mt-2 flex">
      <Button label="Logout" icon="pi pi-sign-out" class="flex-auto" severity="danger" @click="logout"></Button>
    </div>
    <!-- End Logout Button -->
  </Drawer>
  <Button
    icon="pi pi-th-large"
    text
    rounded
    aria-label="Menu"
    class="focus:bg-primary-dark !text-surface-0 focus:!bg-primary"
    @click="visible = !visible"
  />
</template>

<style scoped></style>
