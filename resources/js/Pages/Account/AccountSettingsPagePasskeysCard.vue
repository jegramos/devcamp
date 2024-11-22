<script setup lang="ts">
import { ref } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import type { Passkey } from '@/Pages/Account/AccountSettingsPage.vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import type { SharedPage } from '@/Types/shared-page.ts'
import { useToast } from 'primevue/usetoast'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm.ts'
import { helpers, required } from '@vuelidate/validators'

const props = defineProps({
  enabled: {
    type: Boolean,
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
})

const enabled = ref(props.enabled)

const createForm = useForm({
  name: '',
})

const validatedCreateForm = useClientValidatedForm(
  {
    name: {
      required: helpers.withMessage("Enter the Passkey's name", required),
    },
  },
  createForm
)

const page = usePage<SharedPage>()
const toast = useToast()
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

const submitCreateForm = function () {
  validatedCreateForm.post(props.createPasskeyUrl, {
    preserveScroll: true,
    onSuccess: function () {
      showSuccessToast()
      validatedCreateForm.reset()
    },
  })
}

const submitDeleteForm = function (id: number) {
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
        <p>
          Experience a more secure and convenient login with <b>passkeys</b>. Say goodbye to passwords and hello to seamless
          logins. You can have up to <b>5 active passkeys</b> at a time, and <b>password login will be disabled</b>
          once enabled.
        </p>
        <!-- Start Passkey Registration -->
        <template v-if="enabled">
          <div class="flex items-start justify-between gap-x-2">
            <DfInputText
              v-model="validatedCreateForm.name"
              placeholder="Name"
              :invalid="!!validatedCreateForm.errors.name"
              :invalid-message="validatedCreateForm.errors.name"
              @keydown.enter="submitCreateForm"
            >
              <template #icon>
                <i class="pi pi-key"></i>
              </template>
            </DfInputText>
            <Button
              icon="pi pi-plus-circle"
              class="align-self-start flex-shrink-0"
              :disabled="validatedCreateForm.processing || !submitCreateForm.name"
              :loading="validatedCreateForm.processing"
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
                @click="submitDeleteForm(passkey.id)"
              >
                <i class="pi pi-trash" />
              </button>
            </div>
          </div>
          <!-- End Passkey List -->
        </template>
        <!-- End Passkey Registration -->
      </div>
    </template>
    <template #footer>
      <div class="mt-4 flex justify-end">
        <Button
          :icon="`${enabled ? 'pi pi-ban' : 'pi pi-check-circle'}`"
          :label="`${enabled ? 'Disable' : 'Enable'}`"
          :severity="enabled ? 'danger' : 'primary'"
          @click="enabled = !enabled"
        ></Button>
      </div>
    </template>
  </Card>
</template>

<style scoped></style>
