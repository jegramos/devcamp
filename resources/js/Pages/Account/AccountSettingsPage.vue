<script lang="ts">
import CmsLayout from '@/Layouts/CmsLayout.vue'

export default {
  layout: CmsLayout,
}
</script>
<script lang="ts" setup>
import { Head, usePage } from '@inertiajs/vue3'
import AccountSettingsPagePasskeysCard from '@/Pages/Account/AccountSettingsPagePasskeysCard.vue'
import AccountSettingsPageThemeCard from '@/Pages/Account/AccountSettingsPageThemeCard.vue'
import type { SharedPage } from '@/Types/shared-page.ts'

export type Passkey = {
  id: number
  name: string
  created_at: string
}

const page = usePage<SharedPage>()

const props = defineProps({
  passkeys: {
    type: Array<Passkey>,
    required: true,
  },
  createPasskeyUrl: {
    type: String,
    required: true,
  },
  destroyPasskeyUrl: {
    type: String,
    required: true,
  },
  currentTheme: {
    type: String,
    required: true,
  },
  storeAccountSettingsUrl: {
    type: String,
    required: true,
  },
})
</script>

<template>
  <Head title="Account Settings"></Head>
  <section class="mb-4 mt-2 flex flex-col gap-2 lg:mt-0 lg:grid lg:grid-cols-2 lg:flex-row dark:gap-3">
    <div v-if="!page.props.auth.user?.from_external_account">
      <AccountSettingsPagePasskeysCard
        :enabled="page.props.accountSettings?.passkeys_enabled || false"
        :passkeys="props.passkeys"
        :create-passkey-url="props.createPasskeyUrl"
        destroy-passkey-url="props.destroyPasskeyUrl"
      />
    </div>
    <div>
      <AccountSettingsPageThemeCard
        :store-account-settings-url="props.storeAccountSettingsUrl"
        :current-theme="page.props.accountSettings?.theme || 'auto'"
      />
    </div>
  </section>
</template>

<style scoped></style>
