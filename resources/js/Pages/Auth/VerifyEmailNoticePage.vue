<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { usePage, router, Head } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { useBroadcastChannel, useIntervalFn } from '@vueuse/core'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import Toast from 'primevue/toast'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faEnvelopeOpenText, faPaperPlane, faClock, faSignOutAlt, faTriangleExclamation } from '@fortawesome/free-solid-svg-icons'
import AppAnimatedFloaters from '@/Components/AppAnimatedFloaters.vue'
import { ErrorCode, type SharedPage } from '@/Types/shared-page.ts'
import { ChannelName } from '@/Types/broadcast-channel.ts'
import { applyTheme } from '@/Utils/theme.ts'

const page = usePage<SharedPage>()
const props = defineProps({
  sendEmailVerificationUrl: {
    type: String,
    required: true,
  },
  emailVerificationExpiration: {
    type: String,
    required: true,
  },
  resumeBuilderUrl: {
    type: String,
    required: true,
  },
})

// Send Email Verification Button Lock (1 minute)
const sendEmailButtonIsLocked = ref(false)
const secondsTimer = 60
const lockSecondsRemaining = ref(secondsTimer)
const sendEmailButtonLockInterval = useIntervalFn(() => (lockSecondsRemaining.value -= 1), 1000, { immediate: false })

const toast = useToast()
const showButtonIsLockedMessage = ref(false)
const sendEmailError = ref(false)
const emailSendingInProgress = ref(false)
const sendEmailVerification = function () {
  router.visit(props.sendEmailVerificationUrl, {
    method: 'post',
    onStart: () => (emailSendingInProgress.value = true),
    onSuccess: function () {
      showButtonIsLockedMessage.value = true
      sendEmailButtonIsLocked.value = true
      sendEmailButtonLockInterval.resume()
    },
    onError: function () {
      sendEmailError.value = true
      if (page.props.errors[ErrorCode.TOO_MANY_REQUESTS]) {
        toast.add({
          severity: 'warn',
          summary: 'Verify Email',
          detail: 'You may resend another email after 1 minute.',
          life: 4000,
        })
      }
    },
    onFinish: () => {
      emailSendingInProgress.value = false
    },
    preserveState: true,
  })
}

// Unlock the send email button when the timer completes
watch(
  () => lockSecondsRemaining.value,
  function (value) {
    if (value > 0) return

    // Reset the lock timer states
    sendEmailButtonLockInterval.pause()
    lockSecondsRemaining.value = secondsTimer
    sendEmailButtonIsLocked.value = false
    showButtonIsLockedMessage.value = false
  }
)

const bgColorClass = computed(function () {
  if (page.props.errors[ErrorCode.TOO_MANY_REQUESTS]) return 'bg-amber-700 dark:bg-amber-700'
  if (page.props.errors[ErrorCode.EMAIL_ALREADY_VERIFIED]) return 'bg-red-700 dark:bg-red-900'
  return 'bg-primary'
})

const sendButtonColorClass = computed(function () {
  if (page.props.errors[ErrorCode.TOO_MANY_REQUESTS]) return '!bg-primary-contrast !text-amber-700 !border-amber-700'
  if (page.props.errors[ErrorCode.EMAIL_ALREADY_VERIFIED]) return '!bg-primary-contrast !text-red-700 !border-red-700'
  return '!bg-primary-contrast !text-primary'
})

// Listen for broadcast from other tabs the email is already verified
const { isSupported, data, close } = useBroadcastChannel({ name: ChannelName.EMAIL_VERIFIED })
watch(data, function () {
  if (isSupported.value) {
    router.get(props.resumeBuilderUrl)
    close()
  }
})

applyTheme()
</script>

<template>
  <Head title="Verify Email"></Head>
  <Toast />
  <section
    :class="`flex h-screen w-full flex-col items-center justify-center px-2 text-primary-contrast md:px-0 ${bgColorClass}`"
  >
    <AppAnimatedFloaters />
    <!-- Start Messages -->
    <div
      v-if="showButtonIsLockedMessage"
      class="mb-4 w-full rounded-lg border-4 border-primary-contrast p-4 md:w-[70%] lg:w-[60%]"
    >
      <FontAwesomeIcon :icon="faClock" class="mr-2"></FontAwesomeIcon>
      Email Sent! You can send again after <span class="font-bold">{{ lockSecondsRemaining }} seconds</span> seconds.
    </div>
    <div
      v-if="page.props.errors[ErrorCode.EMAIL_ALREADY_VERIFIED]"
      class="mb-4 w-full rounded-lg border-4 border-primary-contrast p-4 md:w-[70%] lg:w-[60%]"
    >
      <FontAwesomeIcon :icon="faTriangleExclamation" class="mr-2"></FontAwesomeIcon>
      Your email address was already verified.
    </div>
    <!-- End Messages -->
    <div
      class="z-10 flex flex-col items-center justify-center rounded-lg border-4 border-primary-contrast p-6 md:w-[70%] lg:w-[60%]"
    >
      <div class="flex w-full flex-col items-center md:flex-row">
        <div class="mb-4 mr-4 rounded-full border-2 border-primary-contrast p-2">
          <FontAwesomeIcon :icon="faEnvelopeOpenText" class="text-4xl" />
        </div>
        <h1 class="mb-4 text-center font-stylish text-2xl font-bold md:text-left md:text-4xl">
          You must verify your email address
        </h1>
      </div>
      <p class="text-center leading-relaxed md:text-left md:text-xl">
        We've sent a verification link to
        <span class="text-warn-400 mx-1 font-medium underline underline-offset-4">{{ page.props.auth?.user?.email }}</span>
        to verify your email address and activate your account. The link in the email will expire in
        <b>{{ emailVerificationExpiration }}</b
        >. You may need to check your spam folder if you can't find the email in your inbox.
      </p>
      <!-- Start Action Buttons -->
      <div class="mt-6 flex w-full justify-between">
        <Link :href="page.props.logoutUrl" method="post">
          <Button text label="Logout" class="!text-primary-contrast underline-offset-4 hover:!bg-primary hover:underline">
            <template #icon>
              <FontAwesomeIcon :icon="faSignOutAlt" />
            </template>
          </Button>
        </Link>
        <Button
          label="Resend Email"
          :class="`${sendButtonColorClass} ${sendEmailButtonIsLocked || emailSendingInProgress ? 'hover:cursor-not-allowed' : ''}`"
          :loading="emailSendingInProgress"
          :disabled="emailSendingInProgress || sendEmailButtonIsLocked"
          @click="sendEmailVerification"
        >
          <template #icon>
            <FontAwesomeIcon :icon="faPaperPlane" />
          </template>
        </Button>
      </div>
    </div>
    <!-- End Action Buttons -->
  </section>
</template>

<style scoped></style>
