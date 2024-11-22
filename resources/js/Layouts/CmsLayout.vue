<script setup lang="ts">
import { nextTick } from 'vue'
import { useBroadcastChannel } from '@vueuse/core'
import Toast from 'primevue/toast'
import { usePage } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import CmsDesktopSidebar from '@/Layouts/Navigation/CmsDesktopSidebar.vue'
import CmsDesktopToolbar from '@/Layouts/Navigation/CmsDesktopToolbar.vue'
import { useCmsDesktopSidebar } from '@/Composables/useCmsDesktopSidebar'
import CmsMobileToolbar from '@/Layouts/Navigation/CmsMobileToolbar.vue'
import { ChannelName } from '@/Types/broadcast-channel.ts'
import { type SharedPage } from '@/Types/shared-page.ts'
import { applyTheme } from '@/Utils/theme.ts'

const { isMaximized: cmsDesktopSideIsMaximized } = useCmsDesktopSidebar()

// Broadcast to other tabs the user is already logged in if they
// access this layout component (E.g. When users log-in via Google)
const broadcastLogin = function () {
  const { isSupported, post } = useBroadcastChannel({ name: ChannelName.LOGIN_CHANNEL })
  if (isSupported.value) {
    post(true)
  }
}

// Broadcast to the VerifyEmailNoticePage that the email is already verified when
// they access this layout component
const broadcastEmailVerified = function () {
  const { isSupported, post } = useBroadcastChannel({ name: ChannelName.EMAIL_VERIFIED })
  if (isSupported.value) {
    post(true)
  }
}

// Broadcast to the ProfilePage that the email was successful
const broadcastEmailUpdateConfirmed = function () {
  const { isSupported, post } = useBroadcastChannel({ name: ChannelName.EMAIL_UPDATE_CONFIRMED })
  if (isSupported.value) {
    post(true)
  }
}

const page = usePage<SharedPage>()
const toast = useToast()
nextTick(() => {
  if (page.props.flash.CMS_LOGIN_SUCCESS) {
    broadcastLogin()
    toast.add({
      severity: 'success',
      summary: 'Login',
      detail: page.props.flash.CMS_LOGIN_SUCCESS,
      life: 4000,
    })
  }
  if (page.props.flash.CMS_EMAIL_VERIFIED) {
    broadcastEmailVerified()
    toast.add({
      severity: 'success',
      summary: 'Email verified',
      detail: page.props.flash.CMS_EMAIL_VERIFIED,
      life: 4000,
    })
  }
  if (page.props.flash.CMS_EMAIL_VERIFIED) {
    broadcastEmailUpdateConfirmed()
  }
  if (page.props.flash.CMS_ERROR) {
    toast.add({
      severity: 'warn',
      summary: 'Error',
      detail: page.props.flash.CMS_ERROR,
      life: 4000,
    })
  }
})

applyTheme()
</script>

<template>
  <Toast />
  <section class="flex min-h-screen bg-surface-200 text-surface-700 dark:bg-surface-950 dark:text-surface-0">
    <CmsDesktopSidebar
      :class="`${cmsDesktopSideIsMaximized ? 'w-[20%]' : 'w-0 -translate-x-96 transform'}
       hidden overflow-hidden transition-all duration-200 lg:flex`"
    />
    <section class="mx-2 mt-2 flex flex-1 flex-col md:mx-4 md:mt-4 lg:mt-0">
      <CmsDesktopToolbar class="hidden lg:flex" />
      <CmsMobileToolbar class="lg:hidden" />
      <slot></slot>
    </section>
  </section>
</template>

<style scoped></style>
