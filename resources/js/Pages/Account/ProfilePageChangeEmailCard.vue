<script setup lang="ts">
import { type PropType, ref, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import type { UserProfile } from '@/Pages/Account/ProfilePage.vue'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm.ts'
import { email, helpers, required } from '@vuelidate/validators'
import { uniqueUserIdentifierRule } from '@/Utils/vuelidate-custom-validators.ts'
import { ErrorCode, type SharedPage } from '@/Types/shared-page.ts'
import { useBroadcastChannel, useIntervalFn } from '@vueuse/core'
import { ChannelName } from '@/Types/broadcast-channel.ts'

const props = defineProps({
  profile: {
    type: Object as PropType<UserProfile>,
    required: true,
  },
  sendEmailUpdateConfirmationUrl: {
    type: String,
    required: true,
  },
  checkAvailabilityBaseUrl: {
    type: String,
    required: true,
  },
})

const form = useForm<{ email: string }>({
  email: props.profile.email,
})

const clientValidationRules = {
  email: {
    required: helpers.withMessage('Email is required.', required),
    email: helpers.withMessage('Must be a valid email address', email),
    unique: helpers.withAsync(
      helpers.withMessage(
        'This email is already taken',
        uniqueUserIdentifierRule(props.checkAvailabilityBaseUrl, 'email', props.profile.id)
      )
    ),
  },
}

const validatedForm = useClientValidatedForm(clientValidationRules, form)
const page = usePage<SharedPage>()
const toast = useToast()
const showEmailSendSuccessToast = function () {
  toast.add({
    severity: 'success',
    summary: 'Email Update',
    detail: page.props.flash.CMS_SUCCESS,
    life: 6000,
  })
}

// Send Request Email Update Notification Lock (1 minute)
const requestPasswordUpdateButtonIsLocked = ref(false)
const secondsTimer = 60
const lockSecondsRemaining = ref(secondsTimer)
const requestPasswordUpdateButtonLockInterval = useIntervalFn(() => (lockSecondsRemaining.value -= 1), 1000, { immediate: false })

// Unlock the Request Email Update Lock when the timer completes
watch(
  () => lockSecondsRemaining.value,
  function (value) {
    if (value > 0) return

    // Reset the lock timer states
    requestPasswordUpdateButtonLockInterval.pause()
    lockSecondsRemaining.value = secondsTimer
    requestPasswordUpdateButtonIsLocked.value = false
  }
)

const showRateLimitToast = function () {
  toast.add({
    severity: 'warn',
    summary: 'Email Update',
    detail: 'You can only request an email change request once per minute',
    life: 6000,
  })
}
const submit = function () {
  validatedForm.post(props.sendEmailUpdateConfirmationUrl, {
    onSuccess: function () {
      if (page.props.flash.CMS_SUCCESS) {
        showEmailSendSuccessToast()
      }
    },
    onError: (errors) => {
      if (errors[ErrorCode.TOO_MANY_REQUESTS]) showRateLimitToast()
    },
    onFinish: function () {
      requestPasswordUpdateButtonIsLocked.value = true
      requestPasswordUpdateButtonLockInterval.resume()
    },
  })
}

// Listen from other tabs if email update is confirmed
const emailUpdateConfirmedFromBroadcast = ref(false)
const { isSupported, data, close } = useBroadcastChannel({ name: ChannelName.EMAIL_UPDATE_CONFIRMED })
watch(data, function () {
  if (isSupported.value) {
    emailUpdateConfirmedFromBroadcast.value = true
    close()
  }
})
</script>

<template>
  <Message
    v-if="page.props.flash.CMS_EMAIL_UPDATE_CONFIRMED || emailUpdateConfirmedFromBroadcast"
    severity="success"
    icon="pi pi-check-circle"
    class="w-full"
  >
    You've successfully updated your email.
  </Message>
  <Card>
    <template #title>
      <span class="text-sm font-bold">CHANGE EMAIL</span>
    </template>
    <template #content>
      <div class="flex flex-col">
        <p class="mb-4">
          {{
            page.props.auth?.user?.from_external_account
              ? `You may update your email address at ${page.props.auth?.user?.provider_name}.`
              : 'You will need to re-verify your email address upon changing.'
          }}
        </p>
        <DfInputText
          v-model="validatedForm.email"
          placeholder="Email *"
          :invalid="!!validatedForm.errors.email"
          :invalid-message="validatedForm.errors.email"
          :disabled="page.props.auth?.user?.from_external_account"
        >
          <template #icon>
            <i class="pi pi-envelope"></i>
          </template>
        </DfInputText>
      </div>
    </template>
    <template #footer>
      <div class="mt-2 flex justify-end">
        <Button
          :icon="`${requestPasswordUpdateButtonIsLocked ? 'pi pi-clock' : 'pi pi-envelope'}`"
          :label="`${requestPasswordUpdateButtonIsLocked ? `Wait for ${lockSecondsRemaining} seconds` : 'Request Update'}`"
          :loading="validatedForm.processing"
          :disabled="
            validatedForm.processing ||
            props.profile.email === validatedForm.email ||
            page.props.auth?.user?.from_external_account ||
            requestPasswordUpdateButtonIsLocked
          "
          @click="submit"
        ></Button>
      </div>
    </template>
  </Card>
</template>
