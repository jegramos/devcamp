<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import { faFilter, faPlusCircle, faSearch, faSpinner, faUserAlt } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import DfSelect from '@/Components/Inputs/DfSelect.vue'
import { useForm, usePage } from '@inertiajs/vue3'
import type { SharedPage } from '@/Types/shared-page.ts'
import { useDateFormat, useDebounceFn } from '@vueuse/core'
import { email, helpers, minLength, required, sameAs } from '@vuelidate/validators'
import { mobilePhoneRule, passwordRegex, passwordRule, uniqueUserIdentifierRule } from '@/Utils/vuelidate-custom-validators.ts'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm.ts'
import { useToast } from 'primevue/usetoast'
import DfPassword from '@/Components/Inputs/DfPassword.vue'
import DfDatePicker from '@/Components/Inputs/DfDatePicker.vue'
import DfInputMask from '@/Components/Inputs/DfInputMask.vue'
import DfMultiSelect from '@/Components/Inputs/DfMultiSelect.vue'

const props = defineProps({
  currentFilters: {
    type: Object,
    required: true,
  },
  totalFiltersActive: {
    type: Number,
    required: true,
  },
  checkAvailabilityBaseUrl: {
    type: String,
    required: true,
  },
  storeUserUrl: {
    type: String,
    required: true,
  },
  countryOptions: {
    type: Array<{ id: number; name: string }>,
    required: true,
  },
})

const showFilterModal = ref(false)
const sortByOptions = [
  { label: 'Family Name (ASC)', value: 'family_name' },
  { label: 'Family Name (DESC)', value: '-family_name' },
  { label: 'Given Name (ASC)', value: 'given_name' },
  { label: 'Given Name (DESC)', value: '-given_name' },
  { label: 'Created At (ASC)', value: 'created_at' },
  { label: 'Created At (DESC)', value: '-created_at' },
]

const roleOptions = [
  { value: 'user', label: 'User (Regular)' },
  { value: 'admin', label: 'Admin' },
  { value: 'super_user', label: 'Super User' },
]

const activationFilterOptions = [
  { value: 1, label: 'Active' },
  { value: 0, label: 'Deactivated' },
]

const verifiedFilterOptions = [
  { value: 1, label: 'Verified' },
  { value: 0, label: 'Unverified' },
]

type FilterForm = {
  sort?: string | null
  role?: string | null
  active?: number | null
  verified?: number | null
  q?: string | null
}

const filterForm = useForm<FilterForm>({
  sort: props.currentFilters?.sort || null,
  role: props.currentFilters?.role || null,
  active: props.currentFilters?.active ? Number.parseInt(props.currentFilters.active) : null,
  verified: props.currentFilters?.verified ? Number.parseInt(props.currentFilters.verified) : null,
  q: props.currentFilters?.q || null,
})

const page = usePage<SharedPage>()
const submitFilterForm = function () {
  let url = page.props.pageUris['admin.userManagement']
  filterForm
    .transform(function (data) {
      if (data.sort === null) delete data.sort
      if (data.role === null) delete data.role
      if (data.active === null) delete data.active
      if (data.verified === null) delete data.verified
      if (data.q === null || data.q === '') delete data.q

      return data
    })
    .get(url, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => (showFilterModal.value = false),
    })
}

// Only trigger this function after 0.5 seconds
const debouncedSubmitFilterForm = useDebounceFn(function () {
  submitFilterForm()
}, 500)

watch(
  () => filterForm.q,
  function () {
    debouncedSubmitFilterForm()
  }
)

const clearFilterForm = function () {
  filterForm.sort = null
  filterForm.role = null
  filterForm.active = null
  filterForm.verified = null
}

const showAddUserModal = ref(false)
type UserCreateItem = {
  given_name: string
  family_name: string
  username: string
  email: string
  password: string
  password_confirmation: string
  roles: Array<string>
  mobile_number: string
  gender: string
  birthday: string | null
  country_id: string
  address_line_1: string
  address_line_2: string
  address_line_3: string
  city_municipality: string
  province_state_county: string
  postal_code: string
}
const createUserForm = useForm<UserCreateItem>({
  given_name: '',
  family_name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: [],
  mobile_number: '',
  gender: '',
  birthday: null,
  country_id: '',
  address_line_1: '',
  address_line_2: '',
  address_line_3: '',
  city_municipality: '',
  province_state_county: '',
  postal_code: '',
})

const createUserClientValidationRules = {
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
    sameAsPassword: helpers.withMessage('Must match the password field', sameAs(computed(() => createUserForm.password))),
  },
  mobile_number: {
    mobile_number: helpers.withMessage('Must be a valid mobile number', mobilePhoneRule()),
    unique: helpers.withAsync(
      helpers.withMessage(
        'This mobile number is already taken',
        uniqueUserIdentifierRule(props.checkAvailabilityBaseUrl, 'mobile_number')
      )
    ),
  },
  roles: {
    required: helpers.withMessage('A user must at least have 1 role.', required),
  },
}

const validatedCreateUserForm = useClientValidatedForm(createUserClientValidationRules, createUserForm)

const toast = useToast()
const submitCreateUserForm = function () {
  validatedCreateUserForm
    .transform(function (data) {
      data.birthday = data.birthday ? useDateFormat(data.birthday, 'YYYY-MM-DD').value.toString() : null
      return {
        ...data,
      }
    })
    .post(props.storeUserUrl, {
      onSuccess: function () {
        toast.add({
          severity: 'success',
          summary: 'User Management',
          detail: page.props.flash.CMS_SUCCESS,
          life: 3000,
        })
        showAddUserModal.value = false
        validatedCreateUserForm.reset()
      },
    })
}
</script>

<template>
  <div
    class="my-6 flex w-full items-center justify-between rounded-lg border border-surface-300 p-4 lg:mt-0 dark:border-surface-700"
  >
    <div class="flex items-center space-x-4">
      <!-- Start Search Input -->
      <DfInputText v-model="filterForm.q" placeholder="Search Person" @keydown.enter.prevent="debouncedSubmitFilterForm">
        <template #icon>
          <FontAwesomeIcon v-if="!filterForm.processing" :icon="faSearch" class="text-primary" />
          <FontAwesomeIcon v-else :icon="faSpinner" class="animate-spin text-primary" />
        </template>
      </DfInputText>
      <!-- End Search Input -->
      <!-- Start Filter Modal -->
      <button
        class="relative rounded-lg border border-surface-300 bg-surface-0 px-3.5 py-2 transition-transform hover:scale-105 dark:border-surface-700 dark:bg-surface-950"
        @click="showFilterModal = !showFilterModal"
      >
        <span
          v-if="props.totalFiltersActive > 0"
          class="absolute -right-2 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 p-1 text-xs dark:bg-amber-700"
        >
          {{ props.totalFiltersActive }}
        </span>
        <FontAwesomeIcon :icon="faFilter" class="text-primary" />
      </button>
      <Dialog v-model:visible="showFilterModal" modal class="w-[90%] md:w-[30rem]">
        <template #header>
          <div class="inline-flex items-center justify-center gap-x-2">
            <div class="flex rounded-full border p-1">
              <FontAwesomeIcon :icon="faFilter" />
            </div>
            <span class="font-bold">Filter & Sorting</span>
          </div>
        </template>
        <div class="mt-1 flex flex-col gap-y-4">
          <DfSelect
            v-model="filterForm.sort"
            placeholder="Sort By"
            :options="sortByOptions"
            option-label="label"
            option-value="value"
            show-clear
          >
            <template #icon>
              <i class="pi pi-sort"></i>
            </template>
          </DfSelect>
          <DfSelect
            v-model="filterForm.role"
            placeholder="Role"
            :options="roleOptions"
            option-label="label"
            option-value="value"
            show-clear
          >
            <template #icon>
              <i class="pi pi-user-edit"></i>
            </template>
          </DfSelect>
          <DfSelect
            v-model="filterForm.active"
            placeholder="Activation Status"
            :options="activationFilterOptions"
            option-label="label"
            option-value="value"
            show-clear
          >
            <template #icon>
              <i class="pi pi-power-off"></i>
            </template>
          </DfSelect>
          <DfSelect
            v-model="filterForm.verified"
            placeholder="Verification Status"
            :options="verifiedFilterOptions"
            option-label="label"
            option-value="value"
            show-clear
          >
            <template #icon>
              <i class="pi pi-verified"></i>
            </template>
          </DfSelect>
        </div>
        <template #footer>
          <Button size="small" label="Clear" text severity="secondary" @click="clearFilterForm" />
          <Button
            size="small"
            label="Apply"
            outlined
            severity="secondary"
            :loading="filterForm.processing"
            :disabled="filterForm.processing"
            @click="submitFilterForm"
          />
        </template>
      </Dialog>
      <!-- End Filter Modal -->
    </div>
    <!-- Start Add User Modal -->
    <button
      class="ml-4 rounded-lg border border-surface-300 bg-surface-0 px-4 py-3 text-xs transition-transform hover:scale-105 md:ml-0 dark:border-surface-700 dark:bg-surface-950"
      @click="showAddUserModal = !showAddUserModal"
    >
      <FontAwesomeIcon :icon="faPlusCircle" class="mr-0 text-primary md:mr-1" />
      <span class="hidden font-bold text-primary md:inline-block">ADD USER</span>
    </button>
    <Dialog v-model:visible="showAddUserModal" modal class="w-[90%] md:w-[70%] lg:w-[60%]" maximizable>
      <template #header>
        <div class="inline-flex items-center justify-center gap-x-2">
          <div class="flex rounded-full border p-1">
            <FontAwesomeIcon :icon="faUserAlt" />
          </div>
          <span class="font-bold">Add User</span>
        </div>
      </template>
      <!-- Start Username and Email -->
      <section class="mt-1.5 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
        <DfInputText
          v-model="validatedCreateUserForm.email"
          placeholder="Email"
          :invalid="!!validatedCreateUserForm.errors.email"
          :invalid-message="validatedCreateUserForm.errors.email"
        >
          <template #icon>
            <i class="pi pi-envelope"></i>
          </template>
        </DfInputText>
        <DfInputText
          v-model="validatedCreateUserForm.username"
          placeholder="Username"
          :invalid="!!validatedCreateUserForm.errors.username"
          :invalid-message="validatedCreateUserForm.errors.username"
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
          v-model="validatedCreateUserForm.given_name"
          placeholder="First Name"
          :invalid="!!validatedCreateUserForm.errors.given_name"
          :invalid-message="validatedCreateUserForm.errors.given_name"
        >
          <template #icon>
            <i class="pi pi-id-card"></i>
          </template>
        </DfInputText>
        <DfInputText
          v-model="validatedCreateUserForm.family_name"
          placeholder="Last Name"
          :invalid="!!validatedCreateUserForm.errors.family_name"
          :invalid-message="validatedCreateUserForm.errors.family_name"
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
          v-model="validatedCreateUserForm.password"
          placeholder="Password"
          :invalid="!!validatedCreateUserForm.errors.password"
          :invalid-message="validatedCreateUserForm.errors.password"
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
          v-model="validatedCreateUserForm.password_confirmation"
          placeholder="Confirm Password"
          :invalid="!!validatedCreateUserForm.errors.password_confirmation"
          :invalid-message="validatedCreateUserForm.errors.password_confirmation"
          :feedback="false"
          toggle-mask
        >
          <template #icon>
            <i class="pi pi-lock"></i>
          </template>
        </DfPassword>
      </section>
      <!-- End Password and Confirmation -->
      <!-- Start Roles and Mobile Number -->
      <section class="mt-4 flex w-full flex-col space-y-4 md:flex-row md:space-x-4 md:space-y-0">
        <DfMultiSelect
          v-model="createUserForm.roles"
          placeholder="Roles"
          :options="roleOptions"
          option-label="label"
          option-value="value"
          :invalid="!!createUserForm.errors.roles"
          :invalid-message="createUserForm.errors.roles"
          :max-selected-labels="2"
          :show-toggle-all="false"
        >
          <template #icon>
            <i class="pi pi-book"></i>
          </template>
        </DfMultiSelect>
        <DfInputMask
          v-model="createUserForm.mobile_number"
          placeholder="Mobile Number"
          :invalid="!!createUserForm.errors.mobile_number"
          :invalid-message="createUserForm.errors.mobile_number"
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
          v-model="createUserForm.birthday"
          placeholder="Birthday"
          :max-date="new Date()"
          date-format="MM dd, yy"
          :invalid="!!createUserForm.errors.birthday"
          :invalid-message="createUserForm.errors.birthday"
        >
          <template #icon>
            <i class="pi pi-calendar"></i>
          </template>
        </DfDatePicker>
        <DfSelect
          v-model="createUserForm.gender"
          placeholder="Gender"
          :options="[
            { label: 'Male', value: 'male' },
            { label: 'Female', value: 'female' },
            { label: 'Other', value: 'other' },
          ]"
          option-label="label"
          option-value="value"
          :invalid="!!createUserForm.errors.gender"
          :invalid-message="createUserForm.errors.gender"
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
          v-model="createUserForm.country_id"
          placeholder="Country"
          :options="countryOptions"
          option-label="name"
          option-value="id"
          :invalid="!!createUserForm.errors.country_id"
          :invalid-message="createUserForm.errors.country_id"
        >
          <template #icon>
            <i class="pi pi-map"></i>
          </template>
        </DfSelect>
        <DfInputText
          v-model="createUserForm.city_municipality"
          placeholder="City or Municipality"
          :invalid="!!createUserForm.errors.city_municipality"
          :invalid-message="createUserForm.errors.city_municipality"
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
          v-model="createUserForm.province_state_county"
          placeholder="Province/State/County"
          :invalid="!!createUserForm.errors.province_state_county"
          :invalid-message="createUserForm.errors.province_state_county"
          class="w-full"
        >
          <template #icon>
            <i class="pi pi-map-marker"></i>
          </template>
        </DfInputText>
        <DfInputText
          v-model="createUserForm.postal_code"
          placeholder="Postal Code"
          :invalid="!!createUserForm.errors.postal_code"
          :invalid-message="createUserForm.errors.postal_code"
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
          v-model="createUserForm.address_line_1"
          placeholder="Address Line 1"
          :invalid="!!createUserForm.errors.address_line_1"
          :invalid-message="createUserForm.errors.address_line_1"
          class="w-full"
        >
          <template #icon>
            <i class="pi pi-map-marker"></i>
          </template>
        </DfInputText>
        <DfInputText
          v-model="createUserForm.address_line_2"
          placeholder="Address Line 2"
          :invalid="!!createUserForm.errors.address_line_2"
          :invalid-message="createUserForm.errors.address_line_2"
          class="w-full"
        >
          <template #icon>
            <i class="pi pi-map-marker"></i>
          </template>
        </DfInputText>
      </section>
      <section class="mt-4 flex w-full flex-col space-y-4 md:w-[49%] md:flex-row md:space-x-4 md:space-y-0">
        <DfInputText
          v-model="createUserForm.address_line_3"
          placeholder="Address Line 3"
          :invalid="!!createUserForm.errors.address_line_3"
          :invalid-message="createUserForm.errors.address_line_3"
          class="w-full"
          @keydown.enter="submitCreateUserForm"
        >
          <template #icon>
            <i class="pi pi-map-marker"></i>
          </template>
        </DfInputText>
      </section>
      <!-- End Address Lines -->
      <template #footer>
        <Button
          size="small"
          severity="secondary"
          label="Create"
          outlined
          :loading="validatedCreateUserForm.processing"
          :disabled="validatedCreateUserForm.processing"
          @click="submitCreateUserForm"
        />
      </template>
    </Dialog>
    <!-- End Add User Modal -->
  </div>
</template>
