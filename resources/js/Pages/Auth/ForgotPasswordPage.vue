<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import { useIntervalFn } from '@vueuse/core'
import { email, helpers, required } from '@vuelidate/validators'
import Toast from 'primevue/toast'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Message from 'primevue/message'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import AppAnimatedFloaters from '@/Components/AppAnimatedFloaters.vue'
import AppLogo from '@/Components/AppLogo.vue'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm.ts'
import { ErrorCode, type SharedPage } from '@/Types/shared-page.ts'
import { applyTheme } from '@/Utils/theme.ts'

const page = usePage<SharedPage>()
const props = defineProps({
  sendResetLinkUrl: {
    type: String,
    required: true,
  },
  loginUrl: {
    type: String,
    required: true,
  },
})

const clientValidationRules = {
  email: {
    required: helpers.withMessage('Email is required', required),
    email: helpers.withMessage('Must be a valid email address', email),
  },
}

const validatedForm = useClientValidatedForm(
  clientValidationRules,
  useForm({
    email: '',
  })
)

// Send Request Password Reset Lock (1 minute)
const requestPasswordResetButtonIsLocked = ref(false)
const secondsTimer = 60
const lockSecondsRemaining = ref(secondsTimer)
const requestPasswordResetButtonLockInterval = useIntervalFn(() => (lockSecondsRemaining.value -= 1), 1000, { immediate: false })

// Unlock the Request Password Reset Lock when the timer completes
watch(
  () => lockSecondsRemaining.value,
  function (value) {
    if (value > 0) return

    // Reset the lock timer states
    requestPasswordResetButtonLockInterval.pause()
    lockSecondsRemaining.value = secondsTimer
    requestPasswordResetButtonIsLocked.value = false
  }
)

const submit = function () {
  validatedForm.post(props.sendResetLinkUrl, {
    onSuccess: function () {
      requestPasswordResetButtonIsLocked.value = true
      requestPasswordResetButtonLockInterval.resume()
    },
    onFinish: () => validatedForm.reset(),
  })
}

applyTheme()
</script>

<template>
  <Head title="Forgot Password"></Head>
  <section :class="`relative flex h-screen w-full flex-col items-center justify-center bg-primary px-2 md:px-0`">
    <Toast />
    <AppAnimatedFloaters />
    <Message
      v-if="!!page.props.flash.CMS_SUCCESS"
      severity="success"
      icon="pi pi-check-circle"
      class="mb-4 w-full md:w-[55%] lg:w-[45%] dark:!bg-surface-900"
    >
      {{ page.props.flash.CMS_SUCCESS }}
    </Message>
    <Message
      v-if="!!page.props.errors[ErrorCode.BAD_REQUEST]"
      severity="error"
      icon="pi pi-ban"
      class="mb-4 w-full md:w-[55%] lg:w-[45%] dark:!bg-surface-900"
    >
      {{ page.props.errors[ErrorCode.BAD_REQUEST] }}
    </Message>
    <Message
      v-if="!!page.props.errors[ErrorCode.TOO_MANY_REQUESTS]"
      severity="warn"
      icon="pi pi-exclamation-triangle"
      class="mb-4 w-full md:w-[55%] lg:w-[45%] dark:!bg-surface-900"
    >
      {{ page.props.errors[ErrorCode.TOO_MANY_REQUESTS] }}
    </Message>
    <Card class="z-10 w-full md:w-[55%] lg:w-[45%]">
      <template #title>
        <div class="flex flex-col">
          <AppLogo />
          <span class="mt-4 text-2xl font-bold">Forgot Password</span>
        </div>
      </template>
      <template #content>
        <div class="flex flex-col">
          <p class="mb-4">
            Please enter the email you've used to sign-in to the application. If you've entered a valid email address, you will
            receive the reset link in your inbox.
          </p>
          <DfInputText
            v-model="validatedForm.email"
            placeholder="Email"
            :invalid="!!validatedForm.errors.email"
            :invalid-message="validatedForm.errors.email"
            @keydown.enter="submit"
          >
            <template #icon>
              <i class="pi pi-user"></i>
            </template>
          </DfInputText>
        </div>
      </template>
      <template #footer>
        <div class="mt-4 flex justify-between">
          <Button
            severity="secondary"
            icon="pi pi-arrow-circle-left"
            label="Back to Login"
            :disabled="validatedForm.processing"
            @click="router.get(props.loginUrl)"
          ></Button>
          <Button
            :icon="`${requestPasswordResetButtonIsLocked ? 'pi pi-clock' : 'pi pi-send'}`"
            :label="`${requestPasswordResetButtonIsLocked ? `Wait for ${lockSecondsRemaining} seconds` : 'Send Reset Link'}`"
            :loading="validatedForm.processing"
            :disabled="!validatedForm.email || requestPasswordResetButtonIsLocked || validatedForm.processing"
            @click="submit"
          ></Button>
        </div>
      </template>
    </Card>
  </section>
</template>

<style scoped></style>
