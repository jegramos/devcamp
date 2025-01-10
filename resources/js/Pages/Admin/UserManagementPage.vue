<script lang="ts">
import CmsLayout from '@/Layouts/CmsLayout.vue'

export default {
  layout: CmsLayout,
}
</script>

<script lang="ts" setup>
import type { PropType } from 'vue'
import { Head } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faUsersSlash } from '@fortawesome/free-solid-svg-icons'
import UserManagementPageUserCard from '@/Pages/Admin/UserManagementPageUserCard.vue'
import DfPaginator, { type PaginatedList } from '@/Components/DfPaginator.vue'
import UserManagementPageToolbarControls from '@/Pages/Admin/UserManagementPageToolbarControls.vue'

export type UserItem = {
  id: number
  username: string
  email: string
  active: boolean
  given_name: string
  family_name: string
  full_name: string
  name_initials: string
  email_verified: boolean
  mobile_number: string | null
  profile_picture_url: string | null
  gender: 'male' | 'female' | 'other' | null
  birthday: string | null
  country_id: number | null
  city_municipality: string | null
  postal_code: string | null
  province_state_county: string | null
  address_line_1: string | null
  address_line_2: string | null
  address_line_3: string | null
  roles: Array<{ name: string; label: string }>
  portfolio_url: string | null
}

type UsersList = PaginatedList & {
  data: Array<UserItem>
}

const props = defineProps({
  users: {
    type: Object as PropType<UsersList>,
    required: true,
  },
  currentFilters: {
    type: Object,
    required: true,
  },
  totalFiltersActive: {
    type: Number,
    required: true,
  },
  checkAvailabilityBaseUrl: {
    type: String,
    required: true,
  },
  storeUserUrl: {
    type: String,
    required: true,
  },
  updateUserUrl: {
    type: String,
    required: true,
  },
  deleteUserUrl: {
    type: String,
    required: true,
  },
  countryOptions: {
    type: Array<{ id: number; name: string }>,
    required: true,
  },
})
</script>

<template>
  <Head title="Users"></Head>
  <section>
    <!-- Start Filter and Controls Section -->
    <UserManagementPageToolbarControls
      :current-filters="props.currentFilters"
      :total-filters-active="props.totalFiltersActive"
      :check-availability-base-url="props.checkAvailabilityBaseUrl"
      :store-user-url="props.storeUserUrl"
      :country-options="props.countryOptions"
    />
    <!-- End Filter and Controls Section -->
    <template v-if="users.data.length > 0">
      <!-- Start User List Grid -->
      <section v-if="!!props.users?.data" class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <UserManagementPageUserCard
          v-for="user in props.users?.data"
          :key="user.id"
          :user="user"
          :check-availability-base-url="props.checkAvailabilityBaseUrl"
          :update-user-url="props.updateUserUrl"
          :delete-user-url="props.deleteUserUrl"
          :country-options="props.countryOptions"
        />
      </section>
      <!-- End User List Grid -->
      <!-- Start Pagination -->
      <DfPaginator :list="props.users" class="md:mt-10" />
      <!-- End Pagination -->
    </template>
    <template v-else>
      <section
        class="mt-12 flex flex-col items-center justify-center rounded-lg border border-surface-300 p-6 text-2xl text-surface-500 dark:border-surface-700"
      >
        <FontAwesomeIcon :icon="faUsersSlash" class="text-2xl" />
        <span class="mt-2 italic">No users found</span>
      </section>
    </template>
  </section>
</template>

<style scoped></style>
