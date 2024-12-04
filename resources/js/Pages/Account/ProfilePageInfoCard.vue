<script setup lang="ts">
import { type PropType, ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Message from 'primevue/message'
import { faLock, faUnlockAlt } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import DfInputMask from '@/Components/Inputs/DfInputMask.vue'
import type { UserProfile } from '@/Pages/Account/ProfilePage.vue'
import DfDatePicker from '@/Components/Inputs/DfDatePicker.vue'
import DfSelect from '@/Components/Inputs/DfSelect.vue'
import { helpers, minLength, required } from '@vuelidate/validators'
import { mobilePhoneRule, uniqueUserIdentifierRule } from '@/Utils/vuelidate-custom-validators.ts'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm.ts'
import { useDateFormat } from '@vueuse/core'
import { useToast } from 'primevue/usetoast'
import type { SharedPage } from '@/Types/shared-page.ts'

const props = defineProps({
  profile: {
    type: Object as PropType<UserProfile>,
    required: true,
  },
  countryOptions: {
    type: Array<{ id: number; name: string }>,
    required: true,
  },
  checkAvailabilityBaseUrl: {
    type: String,
    required: true,
  },
  updateProfileUrl: {
    type: String,
    required: true,
  },
})
const lockedEditing = ref(true)

type ProfileUpdateForm = {
  username: string
  given_name: string
  family_name: string
  birthday: string | Date | null
  gender: string | null
  mobile_number: string | null
  country_id: number | null
  address_line_1: string | null
  address_line_2: string | null
  address_line_3: string | null
  city_municipality: string | null
  province_state_county: string | null
  postal_code: string | null
}

const form = useForm<ProfileUpdateForm>({
  username: props.profile.username,
  given_name: props.profile.given_name,
  family_name: props.profile.family_name,
  birthday: props.profile.birthday ? new Date(props.profile.birthday) : null,
  gender: props.profile.gender ?? null,
  mobile_number: props.profile.mobile_number ?? null,
  country_id: props.profile.country_id ?? null,
  address_line_1: props.profile.address_line_1 ?? null,
  address_line_2: props.profile.address_line_2 ?? null,
  address_line_3: props.profile.address_line_3 ?? null,
  city_municipality: props.profile.city_municipality ?? null,
  province_state_county: props.profile.province_state_county ?? null,
  postal_code: props.profile.postal_code ?? null,
})

const clientValidationRules = {
  username: {
    required: helpers.withMessage('Username is required.', required),
    minLength: helpers.withMessage('Must be 3 or more characters', minLength(3)),
    unique: helpers.withAsync(
      helpers.withMessage(
        'This username is already taken',
        uniqueUserIdentifierRule(props.checkAvailabilityBaseUrl, 'username', props.profile.id)
      )
    ),
  },
  given_name: {
    required: helpers.withMessage('First name is required.', required),
  },
  family_name: {
    required: helpers.withMessage('Last name is required.', required),
  },
  mobile_number: {
    mobile_number: helpers.withMessage('Must be a valid mobile number', mobilePhoneRule()),
    unique: helpers.withAsync(
      helpers.withMessage(
        'This mobile number is already taken',
        uniqueUserIdentifierRule(props.checkAvailabilityBaseUrl, 'mobile_number', props.profile.id)
      )
    ),
  },
}

const formWithValidation = useClientValidatedForm(clientValidationRules, form)
const toast = useToast()
const page = usePage<SharedPage>()
const submit = function () {
  formWithValidation
    .transform(function (data) {
      data.birthday = data.birthday ? useDateFormat(data.birthday, 'YYYY-MM-DD').value.toString() : null
      return {
        ...data,
      }
    })
    .patch(props.updateProfileUrl, {
      onSuccess: function () {
        if (page.props.flash.CMS_SUCCESS) {
          toast.add({
            severity: 'success',
            summary: 'Profile',
            detail: page.props.flash.CMS_SUCCESS,
            life: 3000,
          })
        }
        lockedEditing.value = true
      },
    })
}
</script>

<template>
  <section class="flex flex-col gap-y-2 dark:gap-y-3">
    <Message
      v-if="page.props.auth.user?.recommend_username_change"
      severity="info"
      icon="pi pi-flag"
      class="w-full dark:!bg-surface-950"
    >
      We recommend that you to personalize the auto-generated <span class="font-bold">username</span> we created for you.
    </Message>
    <Card>
      <template #title>
        <div class="flex items-center justify-between">
          <span class="text-sm font-bold">PROFILE INFORMATION</span>
          <button
            class="flex h-7 w-7 items-center justify-center rounded-full p-1 hover:cursor-pointer"
            :class="`${lockedEditing ? 'bg-surface-500' : 'bg-blue-500'}`"
            @click="lockedEditing = !lockedEditing"
          >
            <FontAwesomeIcon :icon="lockedEditing ? faLock : faUnlockAlt" class="text-sm text-surface-0" />
          </button>
        </div>
      </template>
      <template #content>
        <div class="flex w-full flex-col gap-y-4">
          <p class="mb-3">
            Here, you can edit your personal information. Update your profile to keep your details accurate and up-to-date.
          </p>
          <DfInputText
            v-model="form.username"
            placeholder="Username"
            :invalid="!!form.errors.username"
            :invalid-message="form.errors.username"
            :disabled="lockedEditing || formWithValidation.processing"
            class="w-full"
          >
            <template #icon>
              <i class="pi pi-id-card"></i>
            </template>
          </DfInputText>
          <DfInputText
            v-model="form.given_name"
            placeholder="First Name"
            :invalid="!!form.errors.given_name"
            :invalid-message="form.errors.given_name"
            :disabled="lockedEditing || formWithValidation.processing"
            class="w-full"
          >
            <template #icon>
              <i class="pi pi-user"></i>
            </template>
          </DfInputText>
          <DfInputText
            v-model="form.family_name"
            placeholder="Last Name"
            :invalid="!!form.errors.family_name"
            :invalid-message="form.errors.family_name"
            :disabled="lockedEditing || formWithValidation.processing"
            class="w-full"
          >
            <template #icon>
              <i class="pi pi-user"></i>
            </template>
          </DfInputText>
          <DfDatePicker
            v-model="form.birthday"
            placeholder="Birthday"
            :max-date="new Date()"
            date-format="MM dd, yy"
            :invalid="!!form.errors.birthday"
            :invalid-message="form.errors.birthday"
            :disabled="lockedEditing || formWithValidation.processing"
            class="w-full"
          >
            <template #icon>
              <i class="pi pi-calendar"></i>
            </template>
          </DfDatePicker>
          <DfSelect
            v-model="form.gender"
            placeholder="Gender"
            :options="[
              { name: 'Male', id: 'male' },
              { name: 'Female', id: 'female' },
              { name: 'Other', id: 'other' },
            ]"
            option-label="name"
            option-value="id"
            :invalid="!!form.errors.gender"
            :invalid-message="form.errors.gender"
            :disabled="lockedEditing || formWithValidation.processing"
          >
            <template #icon>
              <i class="pi pi-key"></i>
            </template>
          </DfSelect>
          <DfInputMask
            v-model="form.mobile_number"
            placeholder="Mobile Number"
            :invalid="!!form.errors.mobile_number"
            :invalid-message="form.errors.mobile_number"
            mask="+9999999999999999"
            :auto-clear="false"
            slot-char=" "
            :disabled="lockedEditing || formWithValidation.processing"
          >
            <template #icon>
              <i class="pi pi-phone"></i>
            </template>
          </DfInputMask>
          <DfSelect
            v-model="form.country_id"
            placeholder="Country"
            :options="countryOptions"
            option-label="name"
            option-value="id"
            :invalid="!!form.errors.country_id"
            :invalid-message="form.errors.country_id"
            :disabled="lockedEditing || formWithValidation.processing"
          >
            <template #icon>
              <i class="pi pi-map"></i>
            </template>
          </DfSelect>
          <DfInputText
            v-model="form.city_municipality"
            placeholder="City or Municipality"
            :invalid="!!form.errors.city_municipality"
            :invalid-message="form.errors.city_municipality"
            class="w-full"
            :disabled="lockedEditing || formWithValidation.processing"
          >
            <template #icon>
              <i class="pi pi-map-marker"></i>
            </template>
          </DfInputText>
          <DfInputText
            v-model="form.province_state_county"
            placeholder="Province/State/County"
            :invalid="!!form.errors.province_state_county"
            :invalid-message="form.errors.province_state_county"
            class="w-full"
            :disabled="lockedEditing || formWithValidation.processing"
          >
            <template #icon>
              <i class="pi pi-map-marker"></i>
            </template>
          </DfInputText>
          <DfInputText
            v-model="form.postal_code"
            placeholder="Postal Code"
            :invalid="!!form.errors.postal_code"
            :invalid-message="form.errors.postal_code"
            class="w-full"
            :disabled="lockedEditing || formWithValidation.processing"
          >
            <template #icon>
              <i class="pi pi-map-marker"></i>
            </template>
          </DfInputText>
          <DfInputText
            v-model="form.address_line_1"
            placeholder="Address Line 1"
            :invalid="!!form.errors.address_line_1"
            :invalid-message="form.errors.address_line_1"
            class="w-full"
            :disabled="lockedEditing || formWithValidation.processing"
          >
            <template #icon>
              <i class="pi pi-map-marker"></i>
            </template>
          </DfInputText>
          <DfInputText
            v-model="form.address_line_2"
            placeholder="Address Line 2"
            :invalid="!!form.errors.address_line_2"
            :invalid-message="form.errors.address_line_2"
            class="w-full"
            :disabled="lockedEditing || formWithValidation.processing"
          >
            <template #icon>
              <i class="pi pi-map-marker"></i>
            </template>
          </DfInputText>
          <DfInputText
            v-model="form.address_line_3"
            placeholder="Address Line 3"
            :invalid="!!form.errors.address_line_3"
            :invalid-message="form.errors.address_line_3"
            class="w-full"
            :disabled="lockedEditing || formWithValidation.processing"
            @keydown.enter="submit"
          >
            <template #icon>
              <i class="pi pi-map-marker"></i>
            </template>
          </DfInputText>
        </div>
      </template>
      <template #footer>
        <div class="mt-2 flex justify-end">
          <Button
            icon="pi pi-save"
            label="Update"
            :disabled="lockedEditing || formWithValidation.processing"
            :loading="formWithValidation.processing"
            @click="submit"
          ></Button>
        </div>
      </template>
    </Card>
  </section>
</template>

<style scoped></style>
