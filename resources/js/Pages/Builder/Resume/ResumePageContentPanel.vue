<script setup lang="ts">
import { ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useSortable } from '@vueuse/integrations/useSortable'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Message from 'primevue/message'
import { helpers, maxLength, minLength, required } from '@vuelidate/validators'
import { useToast } from 'primevue/usetoast'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import DfSelect from '@/Components/Inputs/DfSelect.vue'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm.ts'
import type { SharedPage } from '@/Types/shared-page.ts'

const props = defineProps({
  availableSocials: {
    type: Array<string>,
    required: true,
  },
  storeContentUrl: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  titles: {
    type: Array<string>,
    required: true,
  },
  experiences: {
    type: Array<string>,
    required: true,
  },
  socials: {
    type: Array<{ name: string; url: string }>,
    required: true,
  },
  themes: {
    type: Array<{ id: number; name: string }>,
    required: true,
  },
  themeId: {
    type: Number,
    required: true,
  },
})

type ResumeContent = {
  name: string
  titles: string[]
  experiences: string[]
  socials: Array<{ name: string; url: string }>
  theme_id: number
}

const contentForm = useForm<ResumeContent>({
  name: props.name,
  titles: props.titles,
  experiences: props.experiences,
  socials: props.socials,
  theme_id: props.themeId,
})

const clientValidationRules = {
  name: {
    required: helpers.withMessage('Name is required.', required),
    maxLength: helpers.withMessage('Must not exceed 100 characters', maxLength(100)),
  },
  titles: {
    required: helpers.withMessage('Please provided at least one title or role.', minLength(1)),
    maxLength: helpers.withMessage('Must not exceed 15 titles or roles', maxLength(15)),
  },
  experiences: {
    required: helpers.withMessage('Please provided at least one record of work or academic achievement.', minLength(1)),
    maxLength: helpers.withMessage('Must not exceed 15 records of work or academic achievement', maxLength(15)),
  },
  socials: {
    required: helpers.withMessage('Please provided at least one social network profile.', minLength(1)),
    maxLength: helpers.withMessage('Must not exceed 15 social network profiles', maxLength(15)),
  },
  theme_id: {
    required: helpers.withMessage('Please select a theme.', required),
  },
}

const formWithValidation = useClientValidatedForm(clientValidationRules, contentForm)

const title = ref('')
const titleListEl = ref<HTMLElement | null>(null)
useSortable(titleListEl, contentForm.titles)

const experience = ref('')
const experienceListEl = ref<HTMLElement | null>(null)
useSortable(experienceListEl, contentForm.experiences)

const social = ref<{ name: string; url: string }>({
  name: '',
  url: '',
})

const socialListEl = ref<HTMLElement | null>(null)
useSortable(socialListEl, contentForm.socials)

const limitLength = function (event: KeyboardEvent, limit: number = 100, text: string) {
  if (event.key === 'Delete' || event.key === 'Backspace') {
    return
  }

  if (text.length >= limit) event.preventDefault()
}

const addTitleToList = function () {
  if (!title.value) return

  contentForm.titles.push(title.value)
  title.value = ''
  formWithValidation.clearErrors('titles')
}

const addExperienceToList = function () {
  if (!experience.value) return

  contentForm.experiences.push(experience.value)
  experience.value = ''
  formWithValidation.clearErrors('experiences')
}

const urlIsValid = function (url: string, secured: boolean = true): boolean {
  try {
    new URL(social.value.url)

    // eslint-disable-next-line @typescript-eslint/no-unused-vars
  } catch (error) {
    formWithValidation.setError('socials', 'Please provided a valid link.')
    return false
  }

  if (secured && !url.startsWith('https://')) {
    formWithValidation.setError('socials', 'Please provide a secured link. It should start with "https://"')
    return false
  }

  return true
}

const addSocialToList = function () {
  if (!social.value.name || !social.value.url) return

  if (!urlIsValid(social.value.url)) return

  contentForm.socials.push({ name: social.value.name, url: social.value.url })
  social.value.name = ''
  social.value.url = ''
  formWithValidation.clearErrors('socials')
}

const removeFromList = function (idx: number, list: Array<string | { name: string; url: string }>) {
  list.splice(idx, 1)
}

const toast = useToast()
const page = usePage<SharedPage>()
const submitForm = function () {
  if (formWithValidation.titles.length < 1) {
    formWithValidation.setError('titles', 'Please provided at least one title or role.')
  }

  if (formWithValidation.experiences.length < 1) {
    formWithValidation.setError('experiences', 'Please provided at least one record of work or academic achievement.')
  }

  if (formWithValidation.socials.length < 1) {
    formWithValidation.setError('socials', 'Please provided at least one social network profile.')
  }

  if (formWithValidation.hasErrors) return

  formWithValidation.post(props.storeContentUrl, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: function () {
      toast.add({ severity: 'success', summary: 'Resume Builder', detail: page.props.flash.CMS_SUCCESS, life: 3000 })
    },
  })
}
</script>

<template>
  <section class="mt-4 flex flex-col gap-4">
    <Message severity="info" class="w-full">
      <i class="pi pi-book mr-1"></i>
      <span>
        Manage the content of your portfolio. Add your name, work or education, titles and roles, highlighted projects, social
        networks, and others.
      </span>
    </Message>
    <div class="flex flex-grow-0 flex-col gap-4 md:flex-row">
      <!-- Start Name -->
      <Card class="w-full">
        <template #title>
          <div class="flex items-center">
            <span class="text-sm font-bold">FULL NAME</span>
          </div>
        </template>
        <template #content>
          <p class="mb-4">This will be displayed as your official name</p>
          <div class="flex">
            <!-- Start Name -->
            <DfInputText
              v-model="formWithValidation.name"
              :invalid="!!formWithValidation.errors.name"
              :invalid-message="formWithValidation.errors.name"
              placeholder="Name *"
            >
              <template #icon>
                <i class="pi pi-id-card"></i>
              </template>
            </DfInputText>
          </div>
        </template>
      </Card>
      <!-- End Name -->
      <!-- Start Theme Selection -->
      <Card class="w-full">
        <template #title>
          <div class="flex items-center">
            <span class="text-sm font-bold">THEME</span>
          </div>
        </template>
        <template #content>
          <p class="mb-4">Select the look and feel of your portfolio. More themes coming someday.</p>
          <div class="flex">
            <!-- Start Name -->
            <DfSelect
              v-model="formWithValidation.theme_id"
              :invalid="!!formWithValidation.errors.theme_id"
              :invalid-message="formWithValidation.errors.theme_id"
              :options="props.themes"
              option-label="name"
              option-value="id"
              placeholder="Theme *"
            >
              <template #icon>
                <i class="pi pi-palette"></i>
              </template>
            </DfSelect>
          </div>
        </template>
      </Card>
      <!-- End Theme Selection -->
    </div>
    <div class="flex flex-grow-0 flex-col gap-4 md:flex-row">
      <!-- Start Titles -->
      <Card class="max-h-fit w-full">
        <template #title>
          <div class="flex items-center">
            <span class="text-sm font-bold">TITLES & ROLES</span>
          </div>
        </template>
        <template #content>
          <p class="mb-4">
            Provide at least one job title or your desired roles (E.g., Software Engineer, Back-end Developer, etc.)
          </p>
          <div class="mt-4 flex flex-col gap-4 md:flex-row">
            <div class="flex w-full flex-col">
              <div class="flex w-full items-start justify-between gap-x-2">
                <DfInputText
                  v-model="title"
                  placeholder="Titles / Roles *"
                  :invalid-message="formWithValidation.errors.titles"
                  :invalid="!!formWithValidation.errors.titles"
                  @keydown.enter="addTitleToList"
                  @keydown="limitLength($event, 100, title)"
                >
                  <template #icon>
                    <i class="pi pi-sitemap"></i>
                  </template>
                </DfInputText>
                <Button
                  icon="pi pi-plus-circle"
                  class="align-self-start flex-shrink-0"
                  :disabled="formWithValidation.processing || !title"
                  :loading="formWithValidation.processing"
                  @click="addTitleToList"
                ></Button>
              </div>
              <!-- Start Titles List -->
              <div ref="titleListEl" class="mt-2 flex flex-col flex-wrap gap-y-2">
                <div
                  v-for="(t, idx) in formWithValidation.titles"
                  :key="t + '-' + idx"
                  class="flex items-center justify-between gap-x-4 rounded-lg bg-surface-100 px-4 py-3 transition-transform hover:scale-105 hover:cursor-move hover:bg-surface-200 dark:bg-surface-700 hover:dark:bg-surface-800"
                >
                  <div class="flex w-full flex-col flex-wrap break-all">
                    <span>{{ t }}</span>
                  </div>
                  <button
                    class="flex h-7 w-7 transform items-center justify-center rounded-full p-2 transition-transform hover:scale-105 hover:bg-surface-200 dark:hover:bg-surface-50 dark:hover:bg-opacity-10"
                    :class="[{ 'hover:scale-100 hover:cursor-not-allowed': formWithValidation.titles.length < 2 }]"
                    @click="removeFromList(idx, formWithValidation.titles)"
                  >
                    <i class="pi pi-trash" />
                  </button>
                </div>
              </div>
              <!-- End Title List -->
            </div>
          </div>
        </template>
      </Card>
      <!-- End Titles -->
      <!-- Start Experience -->
      <Card class="max-h-fit w-full">
        <template #title>
          <div class="flex items-center">
            <span class="text-sm font-bold">EXPERIENCE & EDUCATION</span>
          </div>
        </template>
        <template #content>
          <p class="mb-4">
            Enter at least one record of work or academic achievement. (E.g., +7 years of Software Development, Master's Degree in
            Computer Science, etc.')
          </p>
          <div class="mt-4 flex flex-col gap-4 md:flex-row">
            <div class="flex w-full flex-col">
              <div class="flex w-full items-start justify-between gap-x-2">
                <DfInputText
                  v-model="experience"
                  placeholder="Experience & Education *"
                  :invalid-message="formWithValidation.errors.experiences"
                  :invalid="!!formWithValidation.errors.experiences"
                  @keydown="limitLength($event, 100, experience)"
                  @keydown.enter="addExperienceToList"
                >
                  <template #icon>
                    <i class="pi pi-briefcase"></i>
                  </template>
                </DfInputText>
                <Button
                  icon="pi pi-plus-circle"
                  class="align-self-start flex-shrink-0"
                  :disabled="formWithValidation.processing || !experience"
                  :loading="formWithValidation.processing"
                  @click="addExperienceToList"
                ></Button>
              </div>
              <!-- Start Experience List -->
              <div ref="experienceListEl" class="mt-2 flex flex-col gap-y-2">
                <div
                  v-for="(e, idx) in formWithValidation.experiences"
                  :key="e + '-' + idx"
                  class="flex items-center justify-between gap-x-4 rounded-lg bg-surface-100 px-4 py-3 transition-transform hover:scale-105 hover:cursor-move hover:bg-surface-200 dark:bg-surface-700 hover:dark:bg-surface-800"
                >
                  <div class="flex w-full flex-col flex-wrap break-all">
                    <span>{{ e }}</span>
                  </div>
                  <button
                    class="flex h-7 w-7 transform items-center justify-center rounded-full p-2 transition-transform hover:scale-105 hover:bg-surface-200 dark:hover:bg-surface-50 dark:hover:bg-opacity-10"
                    @click="removeFromList(idx, formWithValidation.experiences)"
                  >
                    <i class="pi pi-trash" />
                  </button>
                </div>
              </div>
              <!-- End Experience List -->
            </div>
          </div>
        </template>
      </Card>
      <!-- End Experience -->
    </div>
    <!-- Start Socials -->
    <Card class="w-full">
      <template #title>
        <div class="flex items-center">
          <span class="text-sm font-bold">SOCIAL NETWORKS</span>
        </div>
      </template>
      <template #content>
        <p>Provide at least one of your social network profiles. (E.g., Github: https://github.com/jegramos)</p>
        <div class="mt-4 flex w-full flex-col gap-4 md:flex-row">
          <div class="flex w-full flex-col">
            <div class="flex w-full flex-col items-start justify-between gap-4 md:flex-row">
              <DfSelect
                v-model="social.name"
                :options="props.availableSocials"
                placeholder="Social Network *"
                :invalid-message="formWithValidation.errors.socials"
                :invalid="!!formWithValidation.errors.socials"
                class="w-full"
              >
                <template #icon>
                  <i class="pi pi-globe"></i>
                </template>
              </DfSelect>
              <div class="flex w-full items-start justify-between gap-x-2">
                <DfInputText
                  v-model="social.url"
                  placeholder="Link *"
                  class="w-full"
                  :invalid="!!formWithValidation.errors.socials"
                  @keydown.enter="addSocialToList"
                />
                <Button
                  icon="pi pi-plus-circle"
                  class="align-self-start flex-shrink-0"
                  :disabled="formWithValidation.processing || !social.name || !social.url"
                  :loading="formWithValidation.processing"
                  @click="addSocialToList"
                ></Button>
              </div>
            </div>
          </div>
        </div>
        <!-- Start Socials List -->
        <div ref="socialListEl" class="mt-4 flex flex-wrap gap-2">
          <div
            v-for="(s, idx) in formWithValidation.socials"
            :key="s + '-' + idx"
            class="flex gap-x-2 rounded-lg bg-surface-100 px-3 py-2 transition-transform hover:scale-105 hover:cursor-move hover:bg-surface-200 dark:bg-surface-700 hover:dark:bg-surface-800"
          >
            <div class="flex flex-col flex-wrap">
              <span>{{ s.name }}</span>
            </div>
            <button
              class="flex transform items-center justify-center transition-transform hover:scale-105 hover:bg-surface-200 dark:hover:bg-surface-50 dark:hover:bg-opacity-10"
              @click="removeFromList(idx, formWithValidation.socials)"
            >
              <i class="pi pi-times-circle" />
            </button>
          </div>
        </div>
        <!-- End Socials List -->
      </template>
    </Card>
    <!-- End Socials -->
    <!-- Start Action Buttons -->
    <div class="mb-4 mt-1 flex w-full flex-col justify-center gap-4 md:mt-4 md:w-auto md:flex-row md:justify-end">
      <Button label="Preview">
        <template #icon>
          <i class="pi pi-eye"></i>
        </template>
      </Button>
      <Button label="Save" :loading="formWithValidation.processing" :disabled="formWithValidation.processing" @click="submitForm">
        <template #icon>
          <i class="pi pi-save"></i>
        </template>
      </Button>
    </div>
    <!-- End Action Buttons -->
  </section>
</template>
