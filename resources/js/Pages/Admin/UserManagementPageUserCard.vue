<script setup lang="ts">
import { type PropType, ref } from 'vue'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import type { UserItem } from '@/Pages/Admin/UserManagementPage.vue'
import { faUserEdit } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const props = defineProps({
  user: {
    type: Object as PropType<UserItem>,
    required: true,
  },
})

const showUserDetailsDialog = ref(false)
</script>

<template>
  <button
    class="relative flex gap-x-4 rounded-lg bg-surface-0 p-3 transition-transform hover:scale-105 hover:ring-2 hover:ring-primary dark:bg-surface-900"
    @click="showUserDetailsDialog = true"
  >
    <!-- Start Avatar -->
    <div class="h-24 w-24 shrink-0">
      <img
        v-if="props.user.profile_picture_url"
        :src="props.user.profile_picture_url"
        alt="Profile Picture"
        class="flex h-full w-full rounded-lg bg-primary object-cover"
      />
      <div
        v-if="!props.user.profile_picture_url"
        class="flex h-full w-full items-center justify-center rounded-lg bg-primary font-stylish text-2xl text-primary-contrast"
      >
        {{ props.user.name_initials }}
      </div>
    </div>
    <!-- End Avatar -->
    <!-- Start name and other info -->
    <div class="gap flex flex-col items-start">
      <p class="text-wrap text-left uppercase">{{ props.user.full_name }}</p>
      <p class="text-left text-sm dark:text-surface-400">@{{ props.user.username }}</p>
      <div class="mt-2 flex flex-wrap gap-1">
        <Tag v-for="role in props.user.roles" :key="role.name" severity="secondary" class="!text-xs">
          {{ role.label }}
        </Tag>
      </div>
    </div>
    <!-- End name and other info -->
    <!-- Start Email Verified Icon -->
    <div v-if="!props.user.email_verified" class="flex flex-grow justify-end">
      <button v-tooltip="'Email Not Verified'" class="h-7 w-7 rounded-full border border-amber-500">
        <i class="pi pi-exclamation-triangle text-amber-500" />
      </button>
    </div>
    <!-- End Email Verified Icon -->
    <!-- Start Deactivated Banner -->
    <div
      v-if="!props.user.active"
      class="absolute bottom-3 w-24 transform items-center justify-center rounded-b-lg bg-amber-500 px-2 py-0.5 text-surface-0 opacity-90"
    >
      <small class="text-xs">Deactivated</small>
    </div>
    <!-- End Deactivated Banner -->
  </button>
  <Dialog v-model:visible="showUserDetailsDialog" modal class="w-[90%] md:w-[70%] lg:w-[60%]" maximizable>
    <template #header>
      <div class="inline-flex items-center justify-center gap-x-2">
        <div class="flex items-center justify-end rounded-full border p-1">
          <FontAwesomeIcon :icon="faUserEdit" class="h-4 w-4" />
        </div>
        <span class="font-bold">Edit User</span>
      </div>
    </template>
  </Dialog>
</template>

<style scoped></style>
