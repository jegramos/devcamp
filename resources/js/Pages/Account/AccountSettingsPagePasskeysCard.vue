<script setup lang="ts">
import { ref, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import Card from 'primevue/card'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import { browserSupportsWebAuthn, startRegistration } from '@simplewebauthn/browser'
import type { PublicKeyCredentialCreationOptionsJSON } from '@simplewebauthn/types'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faXmarksLines } from '@fortawesome/free-solid-svg-icons'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import type { Passkey } from '@/Pages/Account/AccountSettingsPage.vue'
import type { SharedPage } from '@/Types/shared-page'

const props = defineProps({
  enabled: {
    type: Boolean,
    required: true,
  },
  storeAccountSettingsUrl: {
    type: String,
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
  passkeys: {
    type: Array<Passkey>,
    required: true,
  },
  passkeyRegisterOptions: {
    type: String,
    required: true,
  },
})

const page = usePage<SharedPage>()
const togglePasskeyForm = useForm({
  passkeys_enabled: Boolean(page.props.accountSettings?.passkeys_enabled),
})

const toast = useToast()
const showPasskeyDisabledToastMessage = function (enabled: boolean) {
  toast.add({
    severity: 'info',
    summary: 'Passkeys',
    detail: enabled ? 'Passkey login has been enabled.' : 'Passkey login has been disabled.',
    life: 3000,
  })
}

watch(
  () => togglePasskeyForm.passkeys_enabled,
  function (enabled) {
    if (enabled && props.passkeys.length < 1) return

    togglePasskeyForm.post(props.storeAccountSettingsUrl, {
      preserveScroll: true,
      onSuccess: function () {
        if (page.props.flash.CMS_SUCCESS) showPasskeyDisabledToastMessage(enabled)
      },
    })
  }
)

const createForm = useForm({
  name: '',
  passkey: '',
})

const showSuccessToast = function () {
  if (page.props.flash.CMS_SUCCESS) {
    toast.add({
      severity: 'success',
      summary: 'Passkeys',
      detail: page.props.flash.CMS_SUCCESS,
      life: 3000,
    })
  }
}

// Check if passkey is supported
const passkeySupported = ref(browserSupportsWebAuthn())
const submitCreateForm = async function () {
  if (!createForm.name) return createForm.setError('name', 'The passkey name is required.')

  try {
    const passkey = await startRegistration({
      optionsJSON: JSON.parse(props.passkeyRegisterOptions) as PublicKeyCredentialCreationOptionsJSON,
    })

    createForm.passkey = JSON.stringify(passkey)
  } catch (err) {
    console.error(err)
    createForm.setError('name', 'Passkey creation failed. Please try again.')
    return
  }

  createForm.post(props.createPasskeyUrl, {
    preserveScroll: true,
    onSuccess: function () {
      showSuccessToast()
      createForm.reset()

      // Enable Passkeys Login in Account Settings
      if (!page.props.accountSettings?.passkeys_enabled) {
        togglePasskeyForm.post(props.storeAccountSettingsUrl, {
          preserveScroll: true,
          onError: function () {
            console.error('Something went wrong at enabling passkey login.')
          },
        })
      }
    },
  })
}

const submitDeleteForm = function (id: number) {
  if (props.passkeys?.length < 2) return

  const url = `${props.createPasskeyUrl}/${id}`
  router.delete(url, {
    preserveScroll: true,
    onSuccess: () => showSuccessToast(),
  })
}
</script>

<template>
  <Card>
    <template #title>
      <div class="flex items-center">
        <span class="text-sm font-bold">PASSKEYS</span>
      </div>
    </template>
    <template #content>
      <div class="flex min-h-28 flex-col gap-y-4">
        <template v-if="passkeySupported">
          <p>
            Experience a more secure and convenient login with <b>passkeys</b>. Say goodbye to passwords and hello to seamless
            logins. You can have up to <b>5 active passkeys</b> at a time, and <b>password login will be disabled</b>
            once enabled.
          </p>
          <!-- Start Passkey Registration -->
          <template v-if="togglePasskeyForm.passkeys_enabled">
            <div class="flex items-start justify-between gap-x-2">
              <DfInputText
                v-model="createForm.name"
                placeholder="Name"
                :invalid="!!createForm.errors.name"
                :invalid-message="createForm.errors.name"
                @keydown.enter="submitCreateForm"
              >
                <template #icon>
                  <i class="pi pi-key"></i>
                </template>
              </DfInputText>
              <Button
                icon="pi pi-plus-circle"
                class="align-self-start flex-shrink-0"
                :disabled="createForm.processing || !submitCreateForm.name"
                :loading="createForm.processing"
                @click="submitCreateForm"
              ></Button>
            </div>
            <!-- Start Passkey List -->
            <div class="flex flex-col gap-y-3">
              <div
                v-for="passkey in props.passkeys"
                :key="passkey.id"
                class="flex items-center justify-between gap-x-4 rounded-lg bg-surface-100 px-4 py-3 dark:bg-surface-700"
              >
                <div class="flex w-full flex-col">
                  <span>{{ passkey.name }}</span>
                  <small>{{ passkey.created_at }}</small>
                </div>
                <button
                  class="flex h-7 w-7 transform items-center justify-center rounded-full p-2 transition-transform hover:scale-105 hover:bg-surface-200 dark:hover:bg-surface-50 dark:hover:bg-opacity-10"
                  :class="[{ 'hover:scale-100 hover:cursor-not-allowed': props.passkeys?.length < 2 }]"
                  :disabled="props.passkeys?.length < 2"
                  @click="submitDeleteForm(passkey.id)"
                >
                  <i class="pi pi-trash" />
                </button>
              </div>
            </div>
            <!-- End Passkey List -->
          </template>
          <!-- End Passkey Registration -->
        </template>
        <template v-if="!passkeySupported">
          <div class="flex flex-col items-center">
            <FontAwesomeIcon :icon="faXmarksLines" class="mb-1 mt-6 text-2xl" />
            <p class="italic">
              Your browser does not support support
              <span class="font-bold"> Passkeys</span>.
            </p>
          </div>
        </template>
      </div>
    </template>
    <template v-if="passkeySupported" #footer>
      <div class="mt-4 flex justify-end">
        <Button
          :icon="`${togglePasskeyForm.passkeys_enabled ? 'pi pi-ban' : 'pi pi-check-circle'}`"
          :label="`${togglePasskeyForm.passkeys_enabled ? 'Disable' : 'Enable'}`"
          :severity="togglePasskeyForm.passkeys_enabled ? 'danger' : 'primary'"
          :disabled="togglePasskeyForm.processing"
          :loading="togglePasskeyForm.processing"
          @click="togglePasskeyForm.passkeys_enabled = !togglePasskeyForm.passkeys_enabled"
        ></Button>
      </div>
    </template>
  </Card>
</template>

<style scoped></style>
