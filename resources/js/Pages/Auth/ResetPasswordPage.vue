<script setup lang="ts">
import Button from 'primevue/button'
import AppAnimatedFloaters from '@/Components/AppAnimatedFloaters.vue'
import Card from 'primevue/card'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import Toast from 'primevue/toast'
import AppLogo from '@/Components/AppLogo.vue'
import DfPassword from '@/Components/Inputs/DfPassword.vue'
import { passwordRegex, passwordRule } from '@/Utils/vuelidate-custom-validators.ts'
import { helpers, required, sameAs, email } from '@vuelidate/validators'
import { computed } from 'vue'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm.ts'
import { ErrorCode, type SharedPage } from '@/Types/shared-page.ts'
import Message from 'primevue/message'
import { applyTheme } from '@/Utils/theme.ts'

const page = usePage<SharedPage>()
const props = defineProps({
  resetPasswordUrl: {
    type: String,
    required: true,
  },
  emailParam: {
    type: String,
    required: true,
  },
  tokenParam: {
    type: String,
    required: true,
  },
})

const form = useForm({
  email: props.emailParam,
  password: '',
  password_confirmation: '',
  token: props.tokenParam,
})

const clientValidationRules = {
  email: {
    required: helpers.withMessage('Email is required', required),
    email: helpers.withMessage('Must be a valid email address', email),
  },
  password: {
    required: helpers.withMessage('Password is required.', required),
    password: helpers.withMessage(
      'At least 8 characters, 1 uppercase, 1 lowercase, 1 digit, 1 special character.',
      passwordRule()
    ),
  },
  password_confirmation: {
    required: helpers.withMessage('Confirm your password.', required),
    sameAsPassword: helpers.withMessage('Must match the password field', sameAs(computed(() => form.password))),
  },
}

const validatedForm = useClientValidatedForm(clientValidationRules, form)
const submit = function () {
  validatedForm.patch(props.resetPasswordUrl)
}

applyTheme()
</script>

<template>
  <Head title="Reset Password"></Head>
  <section :class="`relative flex h-screen w-full flex-col items-center justify-center bg-primary px-2 md:px-0`">
    <Toast />
    <AppAnimatedFloaters />
    <Message
      v-if="!!page.props.errors[ErrorCode.BAD_REQUEST]"
      severity="error"
      icon="pi pi-exclamation-triangle"
      class="mb-4 w-full animate-shake md:w-[55%] lg:w-[45%] dark:!bg-surface-900"
    >
      {{ page.props.errors[ErrorCode.BAD_REQUEST] }}
    </Message>
    <Card class="z-10 w-full md:w-[55%] lg:w-[45%]">
      <template #title>
        <div class="flex flex-col">
          <AppLogo />
          <span class="mt-4 text-2xl font-bold">Reset Password</span>
        </div>
      </template>
      <template #content>
        <div class="flex flex-col space-y-4">
          <p class="mb-4">Enter the email you've used to request this link and input your new password.</p>
          <DfInputText
            v-model="validatedForm.email"
            placeholder="Email"
            :invalid="!!validatedForm.errors.email"
            :invalid-message="validatedForm.errors.email"
            :disabled="true"
            class="hover:cursor-not-allowed"
          >
            <template #icon>
              <i class="pi pi-envelope"></i>
            </template>
          </DfInputText>
          <DfPassword
            v-model="validatedForm.password"
            placeholder="New Password"
            :invalid="!!validatedForm.errors.password"
            :invalid-message="validatedForm.errors.password"
            toggle-mask
            :enable-feedback="true"
            feedback-header="Create your password"
            :feedback-helper-list="[
              'At least one lowercase letter',
              'At least one uppercase',
              'At least one numeric',
              'At least one symbol from',
              'Minimum 8 characters',
            ]"
            :strong-regex="passwordRegex"
            medium-label="Almost there"
            strong-label="Perfect!"
          >
            <template #icon>
              <i class="pi pi-lock"></i>
            </template>
          </DfPassword>
          <DfPassword
            v-model="validatedForm.password_confirmation"
            placeholder="Confirm New Password"
            :invalid="!!validatedForm.errors.password_confirmation"
            :invalid-message="validatedForm.errors.password_confirmation"
            :feedback="false"
            toggle-mask
            @keydown.enter="submit"
          >
            <template #icon>
              <i class="pi pi-lock"></i>
            </template>
          </DfPassword>
        </div>
      </template>
      <template #footer>
        <div class="mt-4 flex justify-end">
          <Button
            icon="pi pi-save"
            label="Save"
            :disabled="validatedForm.processing"
            :loading="validatedForm.processing"
            @click="submit"
          ></Button>
        </div>
      </template>
    </Card>
  </section>
</template>

<style scoped></style>
