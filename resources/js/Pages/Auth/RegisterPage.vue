<script setup lang="ts">
import { computed, watch } from 'vue'
import { Link, useForm, usePage, Head, router } from '@inertiajs/vue3'
import { useBroadcastChannel, useMediaQuery } from '@vueuse/core'
import { required, helpers, minLength, email, requiredIf, sameAs } from '@vuelidate/validators'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import Message from 'primevue/message'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faRocket } from '@fortawesome/free-solid-svg-icons'
import AppLogo from '@/Components/AppLogo.vue'
import AppAnimatedFloaters from '@/Components/AppAnimatedFloaters.vue'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import DfPassword from '@/Components/Inputs/DfPassword.vue'
import DfSelect from '@/Components/Inputs/DfSelect.vue'
import { useRecaptcha } from '@/Composables/useRecaptcha'
import { ErrorCode, type SharedPage } from '@/Types/shared-page.ts'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm'
import { passwordRegex, passwordRule, uniqueUserIdentifierRule } from '@/Utils/vuelidate-custom-validators'
import { ChannelName } from '@/Types/broadcast-channel.ts'
import { applyTheme } from '@/Utils/theme.ts'

const props = defineProps({
  loginUrl: {
    type: String,
    required: true,
  },
  processRegistrationUrl: {
    type: String,
    required: true,
  },
  checkAvailabilityBaseUrl: {
    type: String,
    required: true,
  },
  countryOptions: {
    type: Array<{ id: number; name: string }>,
    required: true,
  },
  recaptchaEnabled: {
    type: Boolean,
    required: true,
  },
  recaptchaSiteKey: {
    type: String || null,
    required: true,
  },
  loginViaGoogleUrl: {
    type: String,
    required: true,
  },
  loginViaGithubUrl: {
    type: String,
    required: true,
  },
  resumeBuilderUrl: {
    type: String,
    required: true,
  },
})

const form = useForm({
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
  given_name: '',
  family_name: '',
  country_id: '',
  recaptcha_response_token: '',
})

const clientValidationRules = {
  username: {
    required: helpers.withMessage('Username is required.', required),
    minLength: helpers.withMessage('Must be 3 or more characters', minLength(3)),
    unique: helpers.withAsync(
      helpers.withMessage('This username is already taken', uniqueUserIdentifierRule(props.checkAvailabilityBaseUrl, 'username'))
    ),
  },
  email: {
    required: helpers.withMessage('Email is required.', required),
    email: helpers.withMessage('Must be a valid email address', email),
    unique: helpers.withAsync(
      helpers.withMessage('This email is already taken', uniqueUserIdentifierRule(props.checkAvailabilityBaseUrl, 'email'))
    ),
  },
  given_name: {
    required: helpers.withMessage('First name is required.', required),
  },
  family_name: {
    required: helpers.withMessage('Last name is required.', required),
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
  country_id: {
    required: helpers.withMessage('Select your country.', required),
  },
  recaptcha_response_token: {
    requiredIfRecaptchaEnabled: helpers.withMessage('Verify that your are not a robot.', requiredIf(props.recaptchaEnabled)),
  },
}

const formWithValidation = useClientValidatedForm(clientValidationRules, form)

const submit = async function (event: Event) {
  const target = event.target as HTMLFormElement
  formWithValidation.recaptcha_response_token = target['g-recaptcha-response']?.value || ''

  await formWithValidation.post(props.processRegistrationUrl, {
    onError: () => {
      if (formWithValidation.errors.password) formWithValidation.reset('password', 'password_confirmation')
      // This reloads the Recaptcha widget
      useRecaptcha('recaptcha-container')
    },
  })
}

// Load Recaptcha
let recaptchaTheme = 'light'
if (props.recaptchaEnabled) {
  useRecaptcha('recaptcha-container')
  const isPreferredDark = useMediaQuery('(prefers-color-scheme: dark)')
  recaptchaTheme = isPreferredDark ? 'dark' : 'light'
}

// Change the BG color if there are errors
const page = usePage<SharedPage>()
const bgColorClass = computed(function () {
  if (page.props.errors.TOO_MANY_REQUESTS) return 'bg-amber-700 dark:bg-amber-800'
  if (formWithValidation.hasErrors) return 'bg-red-700 dark:bg-red-900'
  else return 'bg-primary/90 dark:bg-primary'
})

// Listen for broadcast from other tabs the user is already logged in
const { isSupported, data } = useBroadcastChannel({ name: ChannelName.LOGIN_CHANNEL })
if (isSupported.value) {
  watch(data, () => router.get(props.resumeBuilderUrl))
}

applyTheme()
</script>

<template>
  <Head title="Register"></Head>
  <section :class="`relative flex h-screen w-full flex-col items-center justify-center px-2 md:px-0 ${bgColorClass}`">
    <AppAnimatedFloaters />
    <Message
      v-if="!!page.props.errors[ErrorCode.EXTERNAL_ACCOUNT_EMAIL_CONFLICT]"
      severity="error"
      icon="pi pi-exclamation-triangle"
      class="mb-4 w-full animate-shake md:w-[80%] lg:w-[50%] dark:!bg-surface-900"
    >
      {{ page.props.errors[ErrorCode.EXTERNAL_ACCOUNT_EMAIL_CONFLICT] }}
    </Message>
    <Card class="z-10 w-full md:w-[80%] lg:w-[50%]">
      <template #title>
        <div class="flex flex-col">
          <AppLogo />
          <span class="mt-4 text-2xl font-bold">Create your account</span>
        </div>
      </template>
      <template #subtitle>
        <small>Please enter your details or use your social media account</small>
      </template>
      <template #content>
        <!-- Start Input Form -->
        <form @submit.prevent="submit">
          <!-- Start Username and Email -->
          <section class="mt-4 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
            <DfInputText
              v-model="formWithValidation.email"
              placeholder="Email"
              :invalid="!!formWithValidation.errors.email"
              :invalid-message="formWithValidation.errors.email"
            >
              <template #icon>
                <i class="pi pi-envelope"></i>
              </template>
            </DfInputText>
            <DfInputText
              v-model="formWithValidation.username"
              placeholder="Username"
              :invalid="!!formWithValidation.errors.username"
              :invalid-message="formWithValidation.errors.username"
            >
              <template #icon>
                <i class="pi pi-user"></i>
              </template>
            </DfInputText>
          </section>
          <!-- End Username and Email -->
          <!-- Start Firstname and Lastname -->
          <section class="mt-4 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
            <DfInputText
              v-model="formWithValidation.given_name"
              placeholder="First Name"
              :invalid="!!formWithValidation.errors.given_name"
              :invalid-message="formWithValidation.errors.given_name"
            >
              <template #icon>
                <i class="pi pi-id-card"></i>
              </template>
            </DfInputText>
            <DfInputText
              v-model="formWithValidation.family_name"
              placeholder="Last Name"
              :invalid="!!formWithValidation.errors.family_name"
              :invalid-message="formWithValidation.errors.family_name"
            >
              <template #icon>
                <i class="pi pi-id-card"></i>
              </template>
            </DfInputText>
          </section>
          <!-- End Firstname and Lastname -->
          <!-- Start Password and Confirmation -->
          <section class="mt-4 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
            <DfPassword
              v-model="formWithValidation.password"
              placeholder="Password"
              :invalid="!!formWithValidation.errors.password"
              :invalid-message="formWithValidation.errors.password"
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
              v-model="formWithValidation.password_confirmation"
              placeholder="Confirm Password"
              :invalid="!!formWithValidation.errors.password_confirmation"
              :invalid-message="formWithValidation.errors.password_confirmation"
              :feedback="false"
              toggle-mask
            >
              <template #icon>
                <i class="pi pi-lock"></i>
              </template>
            </DfPassword>
          </section>
          <!-- End Password and Confirmation -->
          <!-- Start Country -->
          <section class="mt-4 flex flex-col space-y-4 md:w-[49%] md:flex-row md:space-x-4 md:space-y-0">
            <DfSelect
              v-model="formWithValidation.country_id"
              placeholder="Country"
              :options="countryOptions"
              option-label="name"
              option-value="id"
              :invalid="!!formWithValidation.errors.country_id"
              :invalid-message="formWithValidation.errors.country_id"
            >
              <template #icon>
                <i class="pi pi-map"></i>
              </template>
            </DfSelect>
          </section>
          <!-- End Country -->
          <!-- Start Recaptcha -->
          <div v-if="props.recaptchaEnabled" class="mt-4 flex flex-col items-start md:w-[49%]">
            <div id="recaptcha-container" class="g-recaptcha" :data-sitekey="recaptchaSiteKey" :data-theme="recaptchaTheme"></div>
            <small
              v-if="!!formWithValidation.errors.recaptcha_response_token"
              class="mt-1 animate-shake text-xs text-red-500 dark:text-red-300"
            >
              {{ formWithValidation.errors.recaptcha_response_token }}
            </small>
          </div>
          <!-- End Recaptcha -->
          <Button
            label="Sign Up"
            class="mt-4 w-full"
            :disabled="formWithValidation.processing"
            :loading="formWithValidation.processing"
            type="submit"
          >
            <template #icon>
              <FontAwesomeIcon :icon="faRocket"></FontAwesomeIcon>
            </template>
          </Button>
          <!-- End Input Form -->
        </form>
      </template>
      <template #footer>
        <div class="flex flex-col space-y-2">
          <Divider class="!my-1 !py-0">
            <small class="font-thin">or</small>
          </Divider>
          <div class="flex flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
            <a :href="loginViaGoogleUrl" target="_blank" class="flex w-full">
              <Button
                :disabled="formWithValidation.processing"
                icon="pi pi-google"
                label="Sign up with Google"
                class="w-full !border-red-900 !bg-red-900 !text-surface-0"
              />
            </a>
            <a :href="loginViaGithubUrl" target="_blank" class="flex w-full">
              <Button
                :disabled="formWithValidation.processing"
                icon="pi pi-github"
                label="Sign up with Github"
                class="w-full !border-surface-950 !bg-surface-950 !text-surface-0"
              />
            </a>
          </div>
        </div>
        <div class="pt-2">
          <small class="tracking-wide">
            Already have an account?
            <Link :href="loginUrl">
              <span class="font-bold text-amber-600 underline-offset-4 hover:underline dark:text-amber-300"> Sign in </span>
            </Link>
          </small>
        </div>
      </template>
    </Card>
  </section>
</template>

<style scoped></style>
