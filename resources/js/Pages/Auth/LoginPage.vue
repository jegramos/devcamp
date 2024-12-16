<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { useBroadcastChannel } from '@vueuse/core'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import Checkbox from 'primevue/checkbox'
import Message from 'primevue/message'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import AppAnimatedFloaters from '@/Components/AppAnimatedFloaters.vue'
import AppLogo from '@/Components/AppLogo.vue'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import DfPassword from '@/Components/Inputs/DfPassword.vue'
import { ErrorCode, type SharedPage } from '@/Types/shared-page.ts'
import { ChannelName } from '@/Types/broadcast-channel.ts'
import { applyTheme } from '@/Utils/theme.ts'
import { browserSupportsWebAuthn, startAuthentication } from '@simplewebauthn/browser'
import type { PublicKeyCredentialRequestOptionsJSON } from '@simplewebauthn/types'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faFingerprint } from '@fortawesome/free-solid-svg-icons'

const props = defineProps({
  registerUrl: {
    type: String,
    required: true,
  },
  authenticateUrl: {
    type: String,
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
  loginViaPasskeyUrl: {
    type: String,
    required: true,
  },
  resumeBuilderUrl: {
    type: String,
    required: true,
  },
  forgotPasswordUrl: {
    type: String,
    required: true,
  },
  passkeyAuthenticateOptions: {
    type: String,
    required: true,
  },
})

type LoginForm = {
  username?: string
  email?: string
  password: string
  remember: boolean
}

const form = useForm<LoginForm>({
  username: '',
  email: '',
  password: '',
  remember: false,
})

const isValidEmail = function (email?: string) {
  if (!email) return false

  return /^[^@]+@\w+(\.\w+)+\w$/.test(email)
}

const page = usePage<SharedPage>()
const toast = useToast()

const showRateLimitToast = function () {
  toast.add({
    severity: 'warn',
    summary: 'Login',
    detail: 'Too many attempts. Please try again in 1 minute.',
    life: 4000,
  })
}

const submit = function () {
  form
    .transform(function (data) {
      // The user can input either their email or username
      const isEmail = isValidEmail(data.email)
      return {
        username: isEmail ? '' : data.email,
        email: isEmail ? data.email : '',
        password: data.password,
        remember: data.remember,
      }
    })
    .post(props.authenticateUrl, {
      onError: (errors) => {
        form.reset('password', 'remember')
        if (errors[ErrorCode.TOO_MANY_REQUESTS]) showRateLimitToast()
      },
    })
}

const bgColorClass = computed(() => {
  if (page.props.errors[ErrorCode.TOO_MANY_REQUESTS]) return 'bg-amber-700 dark:bg-amber-800'
  else if (page.props.errors[ErrorCode.INVALID_CREDENTIALS]) return 'bg-red-700 dark:bg-red-800'
  else return 'bg-primary/90 dark:bg-primary'
})

// Listen for broadcast from other tabs the user is already logged in
const { isSupported, data, close } = useBroadcastChannel({ name: ChannelName.LOGIN_CHANNEL })
watch(data, function () {
  if (isSupported.value) {
    router.get(props.resumeBuilderUrl)
    close()
  }
})

applyTheme()

const showPasskeyWarningMessage = ref(false)
const loginViaPasskeyForm = useForm({
  answer: '',
})
const authenticateViaPasskey = async function () {
  if (!browserSupportsWebAuthn()) return (showPasskeyWarningMessage.value = true)

  const answer = await startAuthentication({
    optionsJSON: JSON.parse(props.passkeyAuthenticateOptions) as PublicKeyCredentialRequestOptionsJSON,
  })

  loginViaPasskeyForm.answer = JSON.stringify(answer)
  loginViaPasskeyForm.post(props.loginViaPasskeyUrl)
}
</script>

<template>
  <Head title="Login"></Head>
  <section :class="`relative flex h-screen w-full flex-col items-center justify-center px-2 md:px-0 ${bgColorClass}`">
    <Toast />
    <AppAnimatedFloaters />
    <Message
      v-if="!!page.props.flash.CMS_SUCCESS"
      severity="success"
      icon="pi pi-check-circle"
      class="mb-4 w-full md:w-[55%] lg:w-[35%] dark:!bg-surface-900"
    >
      {{ page.props.flash.CMS_SUCCESS }}
    </Message>
    <Message
      v-if="!!page.props.flash.CMS_EMAIL_VERIFIED"
      severity="success"
      icon="pi pi-check-circle"
      class="mb-4 w-full md:w-[55%] lg:w-[35%] dark:!bg-surface-900"
    >
      Your email address has been verified. Please login to continue.
    </Message>
    <Message
      v-if="!!page.props.flash.CMS_EMAIL_UPDATE_CONFIRMED"
      severity="success"
      icon="pi pi-check-circle"
      class="mb-4 w-full md:w-[55%] lg:w-[35%] dark:!bg-surface-900"
    >
      You've confirmed your email update request. Please login to continue.
    </Message>
    <Message
      v-if="!!page.props.errors[ErrorCode.INVALID_CREDENTIALS]"
      severity="error"
      icon="pi pi-exclamation-triangle"
      class="mb-4 w-full animate-shake md:w-[55%] lg:w-[35%] dark:!bg-surface-900"
    >
      {{ page.props.errors[ErrorCode.INVALID_CREDENTIALS] }}
    </Message>
    <Message
      v-if="!!page.props.errors[ErrorCode.EXTERNAL_ACCOUNT_EMAIL_CONFLICT]"
      severity="error"
      icon="pi pi-exclamation-triangle"
      class="mb-4 w-full animate-shake md:w-[55%] lg:w-[35%] dark:!bg-surface-900"
    >
      {{ page.props.errors[ErrorCode.EXTERNAL_ACCOUNT_EMAIL_CONFLICT] }}
    </Message>
    <Message
      v-if="!!page.props.errors[ErrorCode.ACCOUNT_DEACTIVATED]"
      severity="error"
      icon="pi pi-ban"
      class="mb-4 w-full animate-shake md:w-[55%] lg:w-[35%] dark:!bg-surface-900"
    >
      {{ page.props.errors[ErrorCode.ACCOUNT_DEACTIVATED] }}
    </Message>
    <Message
      v-if="!!page.props.errors[ErrorCode.BAD_REQUEST]"
      severity="error"
      icon="pi pi-exclamation-triangle"
      class="mb-4 w-full animate-shake md:w-[55%] lg:w-[35%] dark:!bg-surface-900"
    >
      {{ page.props.errors[ErrorCode.BAD_REQUEST] }}
    </Message>
    <Message
      v-if="showPasskeyWarningMessage"
      severity="warn"
      icon="pi pi-key"
      class="mb-4 w-full animate-shake md:w-[55%] lg:w-[35%] dark:!bg-surface-900"
    >
      Your device or browser does not support <span class="font-bold">Passkeys</span>.
    </Message>
    <Card class="z-10 w-full md:w-[55%] lg:w-[35%]">
      <template #title>
        <div class="flex flex-col">
          <AppLogo />
          <span class="mt-4 text-2xl font-bold">Welcome back</span>
        </div>
      </template>
      <template #subtitle>
        <small>Please enter your details or use your social media account</small>
      </template>
      <template #content>
        <!-- Start Input Form -->
        <section class="mt-4 flex flex-col space-y-4">
          <!-- Start Username or Email -->
          <DfInputText
            v-model="form.email"
            placeholder="Email or Username"
            :invalid="!!form.errors.email || !!form.errors.username"
            :invalid-message="form.errors.email ?? form.errors.username"
          >
            <template #icon>
              <i class="pi pi-user"></i>
            </template>
          </DfInputText>
          <!-- End Username or Email -->
          <!-- Start Password -->
          <DfPassword
            v-model="form.password"
            placeholder="Password"
            input-class="w-full"
            :feedback="false"
            toggle-mask
            :invalid="!!form.errors.password"
            :invalid-message="form.errors.password"
            @keydown.enter="submit"
          >
            <template #icon>
              <i class="pi pi-lock"></i>
            </template>
          </DfPassword>
          <!-- End Password -->
        </section>
        <!-- End Input Form -->
      </template>
      <template #footer>
        <div class="mt-4 flex flex-col space-y-2">
          <!-- Start Remember Me & Forgot Password -->
          <div class="mb-2 flex justify-between">
            <div class="flex items-center">
              <Checkbox v-model="form.remember" binary input-id="rememberMe" />
              <label for="rememberMe" class="ml-2 text-sm"> Remember me </label>
            </div>
            <Link :href="props.forgotPasswordUrl" class="transform text-sm hover:underline hover:underline-offset-4"
              >Forgot password?
            </Link>
          </div>
          <!-- End Remember Me & Forgot Password -->
          <!-- Start Sign in Buttons -->
          <Button
            icon="pi pi-sign-in"
            label="Sign in"
            class="w-full"
            :disabled="form.processing || loginViaPasskeyForm.processing"
            :loading="form.processing"
            @click="submit"
          ></Button>
          <Divider class="!my-1 !py-0">
            <small class="font-thin">or</small>
          </Divider>
          <div class="flex flex-col space-y-2 dark:space-y-3">
            <Button
              label="Sign in with Passkeys"
              class="w-full !border-surface-800 !bg-surface-800 !text-amber-300"
              severity="primary"
              :loading="loginViaPasskeyForm.processing"
              :disabled="loginViaPasskeyForm.processing || form.processing"
              @click="authenticateViaPasskey"
            >
              <template #icon>
                <FontAwesomeIcon :icon="faFingerprint" />
              </template>
            </Button>
            <a :href="props.loginViaGoogleUrl" target="_blank">
              <Button
                :disabled="form.processing || loginViaPasskeyForm.processing"
                icon="pi pi-google"
                label="Sign in with Google"
                class="w-full !border-red-900 !bg-red-900 !text-surface-0"
              />
            </a>
            <a :href="props.loginViaGithubUrl" target="_blank">
              <Button
                :disabled="form.processing || loginViaPasskeyForm.processing"
                icon="pi pi-github"
                label="Sign in with Github"
                class="w-full !border-surface-950 !bg-surface-950 !text-surface-0"
              />
            </a>
          </div>
          <!-- End Sign in Buttons -->
          <div class="pt-2">
            <small class="tracking-wide">
              Don't have an account yet?
              <Link :href="registerUrl">
                <span class="font-bold text-amber-600 underline-offset-4 hover:underline dark:text-amber-300"> Sign up </span>
              </Link>
            </small>
          </div>
        </div>
      </template>
    </Card>
  </section>
</template>

<style scoped></style>
