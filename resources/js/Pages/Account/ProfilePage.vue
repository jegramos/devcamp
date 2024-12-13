<script lang="ts">
import CmsLayout from '@/Layouts/CmsLayout.vue'

export default {
  layout: CmsLayout,
}
</script>

<script lang="ts" setup>
import { Head, usePage } from '@inertiajs/vue3'
import ProfilePagePictureCard from '@/Pages/Account/ProfilePagePictureCard.vue'
import ProfilePageInfoCard from '@/Pages/Account/ProfilePageInfoCard.vue'
import type { PropType } from 'vue'
import ProfilePageChangeEmailCard from '@/Pages/Account/ProfilePageChangeEmailCard.vue'
import ProfilePageChangePasswordCard from '@/Pages/Account/ProfilePageChangePasswordCard.vue'
import type { SharedPage } from '@/Types/shared-page.ts'

export type UserProfile = {
  id: string
  username: string
  email: string
  given_name: string
  family_name: string
  mobile_number?: string
  birthday: string | Date | null
  gender: string | null
  country_id: number | null
  address_line_1: string | null
  address_line_2: string | null
  address_line_3: string | null
  city_municipality: string | null
  province_state_county: string | null
  postal_code: string | null
  profile_picture_url: string | null
}

const page = usePage<SharedPage>()
const props = defineProps({
  profile: {
    type: Object as PropType<UserProfile>,
    required: true,
  },
  countryOptions: {
    type: Array<{ id: number; name: string }>,
    required: true,
  },
  checkAvailabilityBaseUrl: {
    type: String,
    required: true,
  },
  updateProfileUrl: {
    type: String,
    required: true,
  },
  uploadProfilePictureUrl: {
    type: String,
    required: true,
  },
  sendEmailUpdateConfirmationUrl: {
    type: String,
    required: true,
  },
  changePasswordUrl: {
    type: String,
    required: true,
  },
})
</script>

<template>
  <Head title="Profile"></Head>
  <section class="mb-4 mt-2 flex flex-col gap-2 lg:mt-0 lg:grid lg:grid-cols-2 dark:gap-3">
    <!-- Start Profile Information -->
    <div class="order-last flex flex-col lg:order-first">
      <ProfilePageInfoCard
        :profile="props.profile"
        :country-options="countryOptions"
        :check-availability-base-url="props.checkAvailabilityBaseUrl"
        :update-profile-url="props.updateProfileUrl"
        class="w-full"
      />
    </div>
    <!-- End Profile Information -->
    <!-- Start Profile Picture & Change Password -->
    <div class="order-first flex flex-col gap-2 lg:order-last dark:gap-3">
      <ProfilePagePictureCard
        :upload-profile-picture-url="props.uploadProfilePictureUrl"
        :profile-picture-url="props.profile.profile_picture_url"
      />
      <ProfilePageChangeEmailCard
        :profile="props.profile"
        :send-email-update-confirmation-url="props.sendEmailUpdateConfirmationUrl"
        :check-availability-base-url="props.checkAvailabilityBaseUrl"
      />
      <ProfilePageChangePasswordCard
        v-if="!page.props.auth.user?.from_external_account"
        :change-password-url="props.changePasswordUrl"
      />
    </div>
    <!-- End Profile Picture & Change Password -->
  </section>
</template>

<style scoped></style>
