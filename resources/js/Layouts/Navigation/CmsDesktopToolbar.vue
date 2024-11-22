<script setup lang="ts">
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Toolbar from 'primevue/toolbar'
import Button from 'primevue/button'
import Avatar from 'primevue/avatar'
import Badge from 'primevue/badge'
import Tag from 'primevue/tag'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import InputText from 'primevue/inputtext'
import CmsBreadCrumb from '@/Layouts/Navigation/CmsBreadCrumb.vue'
import { useCmsDesktopSidebar } from '@/Composables/useCmsDesktopSidebar'
import type { SharedPage } from '@/Types/shared-page.ts'

const page = usePage<SharedPage>()
const authenticatedUser = computed(function () {
  return page.props.auth.user
})

/** Avatar Menu */
const avatarMenu = ref()
const avatarMenuItems = ref<MenuItem[]>([
  {
    label: 'Data Entry',
    icon: 'pi pi-book',
  },
  {
    label: 'Need Help',
    icon: 'pi pi-phone',
  },
  {
    label: 'Logout',
    icon: 'pi pi-sign-out',
    noArrow: true,
    command: async () => {
      const page = usePage<{ logoutUrl: string }>()
      router.post(page.props.logoutUrl)
    },
  },
])

// Avatar Menu Toggle
const toggleAvatarMenu = function (event: Event) {
  avatarMenu.value.toggle(event)
}

// Sidebar Toggle
const { toggle: toggleDesktopSidebar } = useCmsDesktopSidebar()
</script>

<template>
  <nav class="flex w-full">
    <Toolbar class="min-h-[4rem] w-full border-none text-sm">
      <template #start>
        <Button
          icon="pi pi-th-large"
          severity="secondary"
          text
          rounded
          aria-label="Menu"
          class="mr-4 hover:cursor-pointer hover:!text-primary"
          @click="toggleDesktopSidebar"
        />
        <CmsBreadCrumb />
      </template>
      <template #end>
        <span class="relative mr-4">
          <InputText placeholder="Search" class="pl-10" />
        </span>

        <!-- Start Avatar Menu -->
        <Avatar
          shape="circle"
          class="cursor-pointer overflow-hidden transition-transform hover:scale-105"
          :image="authenticatedUser?.profile_picture_url ?? undefined"
          :label="`${authenticatedUser?.profile_picture_url ? '' : authenticatedUser?.nameInitials}`"
          aria-haspopup="true"
          aria-controls="avatar-menu"
          @click="toggleAvatarMenu"
        />
        <Menu id="avatar-menu" ref="avatarMenu" :model="avatarMenuItems" :popup="true" class="p-2">
          <template #start>
            <button
              class="p-link relative mb-2 flex w-full items-center overflow-hidden p-2 pl-3 hover:bg-surface-100 dark:hover:bg-surface-400/10"
            >
              <Avatar
                :image="authenticatedUser?.profile_picture_url ?? undefined"
                :label="`${authenticatedUser?.profile_picture_url ? '' : authenticatedUser?.nameInitials}`"
                class="mr-2.5 overflow-hidden"
                size="large"
              />
              <span class="inline-flex flex-col justify-start">
                <span class="mx-1 text-sm uppercase">{{ page.props.auth?.user?.full_name }}</span>
                <span class="mx-1 mt-2 flex flex-wrap gap-1 text-xs">
                  <Tag v-for="role in page.props.auth?.user?.roles" :key="role" class="!text-xs">
                    {{ role }}
                  </Tag>
                </span>
              </span>
            </button>
          </template>
          <template #item="{ item, props }">
            <template v-if="!item.noArrow">
              <a class="flex items-center rounded-lg text-sm" v-bind="props.action">
                <span :class="item.icon" />
                <span class="ml-2 font-normal">{{ item.label }}</span>
                <Badge v-if="item.badge" class="ml-auto" :value="item.badge" />
                <i class="pi pi-angle-right ml-auto text-xs"></i>
              </a>
            </template>
            <template v-else>
              <a
                class="my-1.5 flex transform rounded-lg bg-red-500 text-center text-sm !text-surface-0 hover:cursor-pointer hover:bg-red-600"
                v-bind="props.action"
              >
                <span :class="item.icon" />
                <span class="ml-2 font-normal">{{ item.label }}</span>
                <Badge v-if="item.badge" class="ml-auto" :value="item.badge" />
              </a>
            </template>
          </template>
        </Menu>
        <!-- End Avatar Menu -->
      </template>
    </Toolbar>
  </nav>
</template>

<style scoped></style>
