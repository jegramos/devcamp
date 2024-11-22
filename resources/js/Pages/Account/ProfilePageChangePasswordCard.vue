<script setup lang="ts">
import Card from 'primevue/card'
import Button from 'primevue/button'
import DfPassword from '@/Components/Inputs/DfPassword.vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { helpers, required, sameAs } from '@vuelidate/validators'
import { passwordRegex, passwordRule } from '@/Utils/vuelidate-custom-validators.ts'
import { computed } from 'vue'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm.ts'
import { useToast } from 'primevue/usetoast'
import type { SharedPage } from '@/Types/shared-page.ts'

const props = defineProps({
  changePasswordUrl: {
    type: String,
    required: true,
  },
})

const form = useForm({
  password: '',
  password_confirmation: '',
  old_password: '',
})

const clientValidationRules = {
  old_password: {
    required: helpers.withMessage('Old password is required.', required),
  },
  password: {
    required: helpers.withMessage('New password is required.', required),
    password: helpers.withMessage(
      'At least 8 characters, 1 uppercase, 1 lowercase, 1 digit, 1 special character.',
      passwordRule()
    ),
  },
  password_confirmation: {
    required: helpers.withMessage('Confirm your new password.', required),
    sameAsPassword: helpers.withMessage('Must match the password field', sameAs(computed(() => form.password))),
  },
}

const validatedForm = useClientValidatedForm(clientValidationRules, form)

const page = usePage<SharedPage>()
const toast = useToast()
const submit = function () {
  validatedForm.patch(props.changePasswordUrl, {
    onSuccess: function () {
      if (page.props.flash.CMS_SUCCESS) {
        toast.add({
          severity: 'success',
          summary: 'Change Password',
          detail: page.props.flash.CMS_SUCCESS,
          life: 3000,
        })
        validatedForm.reset()
      }
    },
    onError: function () {
      validatedForm.reset('password', 'password_confirmation')
    },
  })
}
</script>

<template>
  <Card>
    <template #title>
      <span class="text-sm font-bold">CHANGE PASSWORD</span>
    </template>
    <template #content>
      <div class="flex flex-col">
        <p class="mb-4">Remember to regularly update your password.</p>
        <div class="mt-4 flex w-full flex-col space-y-4">
          <DfPassword
            v-model="validatedForm.old_password"
            placeholder="Old Password"
            input-class="w-full"
            :feedback="false"
            toggle-mask
            :invalid="!!form.errors.old_password"
            :invalid-message="form.errors.old_password"
          >
            <template #icon>
              <i class="pi pi-lock"></i>
            </template>
          </DfPassword>
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
      </div>
    </template>
    <template #footer>
      <div class="mt-2 flex justify-end">
        <Button icon="pi pi-save" label="Update" @click="submit"></Button>
      </div>
    </template>
  </Card>
</template>

<style scoped></style>
