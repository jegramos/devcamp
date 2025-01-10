<script setup lang="ts">
import { type PropType, ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import { faUserEdit } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { email, helpers, minLength, required } from '@vuelidate/validators'
import { useDateFormat } from '@vueuse/core'
import { mobilePhoneRule, uniqueUserIdentifierRule } from '@/Utils/vuelidate-custom-validators.ts'
import DfSelect from '@/Components/Inputs/DfSelect.vue'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm.ts'
import type { UserItem } from '@/Pages/Admin/UserManagementPage.vue'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import DfMultiSelect from '@/Components/Inputs/DfMultiSelect.vue'
import DfInputMask from '@/Components/Inputs/DfInputMask.vue'
import DfDatePicker from '@/Components/Inputs/DfDatePicker.vue'
import type { SharedPage } from '@/Types/shared-page.ts'

const props = defineProps({
  user: {
    type: Object as PropType<UserItem>,
    required: true,
  },
  checkAvailabilityBaseUrl: {
    type: String,
    required: true,
  },
  updateUserUrl: {
    type: String,
    required: true,
  },
  deleteUserUrl: {
    type: String,
    required: true,
  },
  countryOptions: {
    type: Array<{ id: number; name: string }>,
    required: true,
  },
})

const showUserDetailsDialog = ref(false)
const roleOptions = [
  { value: 'user', label: 'User (Regular)' },
  { value: 'admin', label: 'Admin' },
  { value: 'super_user', label: 'Super User' },
]

const activationOptions = [
  { value: true, label: 'Active' },
  { value: false, label: 'Deactivated' },
]

const verifiedOptions = [
  { value: true, label: 'Verified' },
  { value: false, label: 'Unverified' },
]

type UpdateUserItem = {
  active: boolean
  verified: boolean
  given_name: string
  family_name: string
  username: string
  email: string
  roles: Array<string>
  mobile_number: string
  gender: string
  birthday: string | Date | null
  country_id: number | null
  address_line_1: string
  address_line_2: string
  address_line_3: string
  city_municipality: string
  province_state_county: string
  postal_code: string
}
const updateUserForm = useForm<UpdateUserItem>({
  active: props.user.active,
  verified: props.user.email_verified,
  given_name: props.user.given_name,
  family_name: props.user.family_name,
  username: props.user.username,
  email: props.user.email,
  roles: props.user.roles.map((role) => role.name),
  mobile_number: props.user.mobile_number || '',
  gender: props.user.gender || '',
  birthday: props.user.birthday ? new Date(props.user.birthday) : null,
  country_id: props.user.country_id || null,
  address_line_1: props.user.address_line_1 || '',
  address_line_2: props.user.address_line_2 || '',
  address_line_3: props.user.address_line_3 || '',
  city_municipality: props.user.city_municipality || '',
  province_state_county: props.user.province_state_county || '',
  postal_code: props.user.postal_code || '',
})

const updateUserClientValidationRules = {
  active: {
    required: helpers.withMessage('Activation Status is required.', required),
  },
  verified: {
    required: helpers.withMessage('Verification Status is required.', required),
  },
  username: {
    required: helpers.withMessage('Username is required.', required),
    minLength: helpers.withMessage('Must be 3 or more characters', minLength(3)),
    unique: helpers.withAsync(
      helpers.withMessage(
        'This username is already taken',
        uniqueUserIdentifierRule(props.checkAvailabilityBaseUrl, 'username', props.user.id)
      )
    ),
  },
  email: {
    required: helpers.withMessage('Email is required.', required),
    email: helpers.withMessage('Must be a valid email address', email),
    unique: helpers.withAsync(
      helpers.withMessage(
        'This email is already taken',
        uniqueUserIdentifierRule(props.checkAvailabilityBaseUrl, 'email', props.user.id)
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
        uniqueUserIdentifierRule(props.checkAvailabilityBaseUrl, 'mobile_number', props.user.id)
      )
    ),
  },
  roles: {
    required: helpers.withMessage('A user must at least have 1 role.', required),
  },
}

const toast = useToast()
const page = usePage<SharedPage>()
const validateUpdateUserForm = useClientValidatedForm(updateUserClientValidationRules, updateUserForm)
const submitUpdateUserForm = function () {
  validateUpdateUserForm
    .transform(function (data) {
      data.birthday = data.birthday ? useDateFormat(data.birthday, 'YYYY-MM-DD').value.toString() : null
      return {
        ...data,
      }
    })
    .patch(props.updateUserUrl + '/' + props.user.id, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: function () {
        toast.add({
          severity: 'success',
          summary: 'User Update',
          detail: page.props.flash.CMS_SUCCESS,
          life: 3000,
        })
        showUserDetailsDialog.value = false
      },
    })
}

const deleteUser = function () {
  router.delete(props.deleteUserUrl + '/' + props.user.id, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: function () {
      toast.add({
        severity: 'success',
        summary: 'User Deleted',
        detail: page.props.flash.CMS_SUCCESS,
        life: 3000,
      })
    },
  })
}

const confirm = useConfirm()
const confirmDeleteUser = function () {
  return confirm.require({
    message: 'Are you sure you want to delete ' + props.user.full_name + "'s account?",
    header: 'Delete User',
    icon: 'pi pi-exclamation-triangle',
    accept: () => {
      deleteUser()
    },
    rejectProps: {
      label: 'Cancel',
      severity: 'secondary',
      outlined: true,
      size: 'small',
    },
    acceptProps: {
      label: 'Continue',
      severity: 'danger',
      icon: 'pi pi-trash',
      outlined: true,
      size: 'small',
    },
    reject: () => {},
  })
}

const viewPortfolio = function () {
  window.open(props.user.portfolio_url || '', '_blank')
}
</script>

<template>
  <button
    class="relative flex gap-x-4 rounded-lg bg-surface-0 p-3 transition-transform hover:scale-105 hover:ring-2 hover:ring-primary dark:bg-surface-900"
    @click="showUserDetailsDialog = true"
  >
    <!-- Start Avatar -->
    <div class="h-24 w-24 shrink-0">
      <img
        v-if="props.user.profile_picture_url"
        :src="props.user.profile_picture_url"
        alt="Profile Picture"
        class="flex h-full w-full rounded-lg bg-primary object-cover"
      />
      <div
        v-if="!props.user.profile_picture_url"
        class="flex h-full w-full items-center justify-center rounded-lg bg-primary font-stylish text-2xl text-primary-contrast"
      >
        {{ props.user.name_initials }}
      </div>
    </div>
    <!-- End Avatar -->
    <!-- Start name and other info -->
    <div class="gap flex flex-col items-start">
      <p class="text-wrap text-left uppercase">{{ props.user.full_name }}</p>
      <p class="text-left text-sm dark:text-surface-400">@{{ props.user.username }}</p>
      <div class="mt-2 flex flex-wrap gap-1">
        <Tag v-for="role in props.user.roles" :key="role.name" severity="secondary" class="!text-xs">
          {{ role.label }}
        </Tag>
      </div>
    </div>
    <!-- End name and other info -->
    <!-- Start Email Verified Icon -->
    <div v-if="!props.user.email_verified" class="flex flex-grow justify-end">
      <button v-tooltip="'Email Not Verified'" class="h-7 w-7 rounded-full border border-amber-500">
        <i class="pi pi-exclamation-triangle text-amber-500" />
      </button>
    </div>
    <!-- End Email Verified Icon -->
    <!-- Start Deactivated Banner -->
    <div
      v-if="!props.user.active"
      class="absolute bottom-3 w-24 transform items-center justify-center rounded-b-lg bg-amber-500 px-2 py-0.5 text-surface-0 opacity-90"
    >
      <small class="text-xs">Deactivated</small>
    </div>
    <!-- End Deactivated Banner -->
  </button>
  <Dialog v-model:visible="showUserDetailsDialog" modal class="w-[90%] md:w-[70%] lg:w-[60%]" maximizable>
    <template #header>
      <div class="inline-flex items-center justify-center gap-x-2">
        <div class="flex items-center justify-end rounded-full border p-1">
          <FontAwesomeIcon :icon="faUserEdit" class="h-4 w-4" />
        </div>
        <span class="font-bold">Edit User</span>
      </div>
    </template>
    <!-- Start Verification and Activation Status -->
    <section class="mt-1.5 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
      <DfSelect
        v-model="validateUpdateUserForm.active"
        placeholder="Activation Status"
        :options="activationOptions"
        :invalid="!!validateUpdateUserForm.errors.active"
        :invalid-message="validateUpdateUserForm.errors.active"
        option-label="label"
        option-value="value"
      >
        <template #icon>
          <i class="pi pi-power-off"></i>
        </template>
      </DfSelect>
      <DfSelect
        v-model="validateUpdateUserForm.verified"
        placeholder="Verification Status"
        :options="verifiedOptions"
        :invalid="!!validateUpdateUserForm.errors.verified"
        :invalid-message="validateUpdateUserForm.errors.verified"
        option-label="label"
        option-value="value"
      >
        <template #icon>
          <i class="pi pi-verified"></i>
        </template>
      </DfSelect>
    </section>
    <!-- End Verification and Activation Status -->
    <!-- Start Email and Username -->
    <section class="mt-4 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
      <DfInputText
        v-model="validateUpdateUserForm.email"
        placeholder="Email"
        :invalid="!!validateUpdateUserForm.errors.email"
        :invalid-message="validateUpdateUserForm.errors.email"
      >
        <template #icon>
          <i class="pi pi-envelope"></i>
        </template>
      </DfInputText>
      <DfInputText
        v-model="validateUpdateUserForm.username"
        placeholder="Username"
        :invalid="!!validateUpdateUserForm.errors.username"
        :invalid-message="validateUpdateUserForm.errors.username"
      >
        <template #icon>
          <i class="pi pi-user"></i>
        </template>
      </DfInputText>
    </section>
    <!-- End Email and Username -->
    <!-- Start Firstname and Lastname -->
    <section class="mt-4 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
      <DfInputText
        v-model="validateUpdateUserForm.given_name"
        placeholder="First Name"
        :invalid="!!validateUpdateUserForm.errors.given_name"
        :invalid-message="validateUpdateUserForm.errors.given_name"
      >
        <template #icon>
          <i class="pi pi-id-card"></i>
        </template>
      </DfInputText>
      <DfInputText
        v-model="validateUpdateUserForm.family_name"
        placeholder="Last Name"
        :invalid="!!validateUpdateUserForm.errors.family_name"
        :invalid-message="validateUpdateUserForm.errors.family_name"
      >
        <template #icon>
          <i class="pi pi-id-card"></i>
        </template>
      </DfInputText>
    </section>
    <!-- End Firstname and Lastname -->
    <!-- Start Roles and Mobile Number -->
    <section class="mt-4 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
      <DfMultiSelect
        v-model="validateUpdateUserForm.roles"
        placeholder="Roles"
        :options="roleOptions"
        option-label="label"
        option-value="value"
        :invalid="!!validateUpdateUserForm.errors.roles"
        :invalid-message="validateUpdateUserForm.errors.roles"
        :max-selected-labels="2"
        :show-toggle-all="false"
      >
        <template #icon>
          <i class="pi pi-book"></i>
        </template>
      </DfMultiSelect>
      <DfInputMask
        v-model="validateUpdateUserForm.mobile_number"
        placeholder="Mobile Number"
        :invalid="!!validateUpdateUserForm.errors.mobile_number"
        :invalid-message="validateUpdateUserForm.errors.mobile_number"
        mask="+9999999999999999"
        :auto-clear="false"
        slot-char=" "
      >
        <template #icon>
          <i class="pi pi-phone"></i>
        </template>
      </DfInputMask>
    </section>
    <!-- End Roles and Mobile Number -->
    <!-- Start Birthday and Gender -->
    <section class="mt-4 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
      <DfDatePicker
        v-model="validateUpdateUserForm.birthday"
        placeholder="Birthday"
        :max-date="new Date()"
        date-format="MM dd, yy"
        :invalid="!!validateUpdateUserForm.errors.birthday"
        :invalid-message="validateUpdateUserForm.errors.birthday"
      >
        <template #icon>
          <i class="pi pi-calendar"></i>
        </template>
      </DfDatePicker>
      <DfSelect
        v-model="validateUpdateUserForm.gender"
        placeholder="Gender"
        :options="[
          { label: 'Male', value: 'male' },
          { label: 'Female', value: 'female' },
          { label: 'Other', value: 'other' },
        ]"
        option-label="label"
        option-value="value"
        :invalid="!!validateUpdateUserForm.errors.gender"
        :invalid-message="validateUpdateUserForm.errors.gender"
      >
        <template #icon>
          <i class="pi pi-key"></i>
        </template>
      </DfSelect>
    </section>
    <!-- End Birthday and Gender -->
    <!-- Start Country and City/Municipality -->
    <section class="mt-4 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
      <DfSelect
        v-model="validateUpdateUserForm.country_id"
        placeholder="Country"
        :options="props.countryOptions"
        option-label="name"
        option-value="id"
        :invalid="!!validateUpdateUserForm.errors.country_id"
        :invalid-message="validateUpdateUserForm.errors.country_id"
      >
        <template #icon>
          <i class="pi pi-map"></i>
        </template>
      </DfSelect>
      <DfInputText
        v-model="validateUpdateUserForm.city_municipality"
        placeholder="City or Municipality"
        :invalid="!!validateUpdateUserForm.errors.city_municipality"
        :invalid-message="validateUpdateUserForm.errors.city_municipality"
        class="w-full"
      >
        <template #icon>
          <i class="pi pi-map-marker"></i>
        </template>
      </DfInputText>
    </section>
    <!-- End Country and City/Municipality -->
    <!-- Start State and Postal Code -->
    <section class="mt-4 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
      <DfInputText
        v-model="validateUpdateUserForm.province_state_county"
        placeholder="Province/State/County"
        :invalid="!!validateUpdateUserForm.errors.province_state_county"
        :invalid-message="validateUpdateUserForm.errors.province_state_county"
        class="w-full"
      >
        <template #icon>
          <i class="pi pi-map-marker"></i>
        </template>
      </DfInputText>
      <DfInputText
        v-model="validateUpdateUserForm.postal_code"
        placeholder="Postal Code"
        :invalid="!!validateUpdateUserForm.errors.postal_code"
        :invalid-message="validateUpdateUserForm.errors.postal_code"
        class="w-full"
      >
        <template #icon>
          <i class="pi pi-map-marker"></i>
        </template>
      </DfInputText>
    </section>
    <!-- End State and Postal Code -->
    <!-- Start Address Lines -->
    <section class="mt-4 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
      <DfInputText
        v-model="validateUpdateUserForm.address_line_1"
        placeholder="Address Line 1"
        :invalid="!!validateUpdateUserForm.errors.address_line_1"
        :invalid-message="validateUpdateUserForm.errors.address_line_1"
        class="w-full"
      >
        <template #icon>
          <i class="pi pi-map-marker"></i>
        </template>
      </DfInputText>
      <DfInputText
        v-model="validateUpdateUserForm.address_line_2"
        placeholder="Address Line 2"
        :invalid="!!validateUpdateUserForm.errors.address_line_2"
        :invalid-message="validateUpdateUserForm.errors.address_line_2"
        class="w-full"
      >
        <template #icon>
          <i class="pi pi-map-marker"></i>
        </template>
      </DfInputText>
    </section>
    <section class="mt-4 flex w-full flex-col space-y-4 md:w-[49%] md:flex-row md:space-x-4 md:space-y-0">
      <DfInputText
        v-model="validateUpdateUserForm.address_line_3"
        placeholder="Address Line 3"
        :invalid="!!validateUpdateUserForm.errors.address_line_3"
        :invalid-message="validateUpdateUserForm.errors.address_line_3"
        class="w-full"
        @keydown.enter="submitUpdateUserForm"
      >
        <template #icon>
          <i class="pi pi-map-marker"></i>
        </template>
      </DfInputText>
    </section>
    <!-- End Address Lines -->
    <template #footer>
      <div class="flex w-full justify-between">
        <Button
          v-if="props.user.portfolio_url"
          size="small"
          icon="pi pi-external-link"
          text
          severity="secondary"
          label="Portfolio"
          @click="viewPortfolio"
        >
        </Button>
        <div class="flex gap-4" :class="{'w-full justify-end' : !props.user.portfolio_url}">
          <Button size="small" label="Delete" outlined severity="danger" @click="confirmDeleteUser" />
          <Button
            size="small"
            label="Update"
            outlined
            icon="pi pi-save"
            severity="secondary"
            :loading="validateUpdateUserForm.processing"
            :disabled="validateUpdateUserForm.processing"
            @click="submitUpdateUserForm"
          />
        </div>
      </div>
    </template>
  </Dialog>
</template>

<style scoped></style>
