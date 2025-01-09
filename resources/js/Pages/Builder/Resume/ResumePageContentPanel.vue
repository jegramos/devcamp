<script setup lang="ts">
import { computed, nextTick, type PropType, ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useDateFormat } from '@vueuse/core'
import { useSortable } from '@vueuse/integrations/useSortable'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Message from 'primevue/message'
import Divider from 'primevue/divider'
import ToggleSwitch from 'primevue/toggleswitch'
import FileUpload, { type FileUploadSelectEvent } from 'primevue/fileupload'
import { useToast } from 'primevue/usetoast'
import { helpers, maxLength, minLength, required } from '@vuelidate/validators'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBuilding, faExternalLink, faGlobe, faMagicWandSparkles } from '@fortawesome/free-solid-svg-icons'
import DfInputText from '@/Components/Inputs/DfInputText.vue'
import DfSelect from '@/Components/Inputs/DfSelect.vue'
import DfDatePicker from '@/Components/Inputs/DfDatePicker.vue'
import DfTextarea from '@/Components/Inputs/DfTextarea.vue'
import { formatDateSpan } from '@/Utils/date'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm'
import type { SharedPage } from '@/Types/shared-page'
import type { Contact, Project, Service, Social, TechExpertise, Timeline } from '@/Pages/Builder/Resume/ResumePage.vue'

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
    type: Array<Social>,
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
  techExpertise: {
    type: Array<TechExpertise>,
    required: true,
  },
  projects: {
    type: Array<Project>,
    required: true,
  },
  timeline: {
    type: Object as PropType<Timeline>,
    required: true,
  },
  services: {
    type: Array<Service>,
    required: true,
  },
  contact: {
    type: Object as PropType<Contact>,
    required: true,
  },
  previewUrl: {
    type: String,
    required: true,
  },
  canPreview: {
    type: Boolean,
    required: true,
  },
})

type ResumeContent = {
  name: string
  titles: string[]
  experiences: string[]
  socials: Array<{ name: string; url: string }>
  theme_id: number
  tech_expertise: Array<TechExpertise>
  projects: Array<Project>
  work_timeline: Timeline
  services: Array<Service>
  contact: Contact
}

const contentForm = useForm<ResumeContent>({
  name: props.name,
  titles: props.titles,
  experiences: props.experiences,
  socials: props.socials,
  theme_id: props.themeId,
  tech_expertise: props.techExpertise,
  projects: props.projects,
  work_timeline: props.timeline,
  services: props.services,
  contact: props.contact,
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
  tech_expertise: {
    maxLength: helpers.withMessage('Must not exceed 100 tech expertise', maxLength(100)),
  },
  timeline: {
    maxLength: helpers.withMessage('Must not exceed 50 records', maxLength(100)),
  },
  services: {
    maxLength: helpers.withMessage('Must not exceed 50 records', maxLength(6)),
  },
  theme_id: {
    required: helpers.withMessage('Please select a theme.', required),
  },
}

const formWithValidation = useClientValidatedForm(clientValidationRules, contentForm)

const urlIsValid = function (url: string, secured: boolean = true): boolean {
  url = url.toLowerCase().trim()

  try {
    new URL(url)
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
  } catch (error) {
    return false
  }

  return !(secured && !url.startsWith('https://'))
}

const removeFromList = function (idx: number, list: Array<unknown>) {
  list.splice(idx, 1)
}
const limitLength = function (event: KeyboardEvent, limit: number = 100, text: string) {
  if (event.key === 'Delete' || event.key === 'Backspace') {
    return
  }

  if (text.length >= limit) event.preventDefault()
}

/** Start Titles */
const title = ref('')
const titleListEl = ref<HTMLElement | null>(null)
useSortable(titleListEl, formWithValidation.titles)
const addTitleToList = function () {
  formWithValidation.clearErrors('titles')

  if (!title.value) {
    formWithValidation.setError('titles', 'Please provided a title or role.')
  }

  if (title.value.length >= 150) {
    formWithValidation.setError('titles', 'The title or role must not exceed 150 characters.')
  }

  if (formWithValidation.titles.length >= 15) {
    formWithValidation.setError('titles', 'You can only add up to 15 titles or roles.')
  }

  if (formWithValidation.titles.find((t) => t.toLowerCase() === title.value.toLowerCase())) {
    formWithValidation.setError('titles', 'The title or role already exists')
  }

  if (formWithValidation.errors.titles) return

  formWithValidation.titles.push(title.value)
  title.value = ''
  formWithValidation.clearErrors('titles')
}
/** End Titles */

/** Start Tech Expertise */
const experience = ref('')
const experienceListEl = ref<HTMLElement | null>(null)
useSortable(experienceListEl, formWithValidation.experiences)
const addExperienceToList = function () {
  formWithValidation.clearErrors('experiences')

  if (!experience.value) {
    formWithValidation.setError('experiences', 'Please provide a record of work or academic achievement.')
  }

  if (experience.value.length >= 150) {
    formWithValidation.setError('experiences', 'The record must not exceed 150 characters.')
  }

  if (formWithValidation.experiences.length >= 15) {
    formWithValidation.setError('experiences', 'You can only add up to 15 records of work or academic achievement.')
  }

  if (formWithValidation.experiences.find((e) => e.toLowerCase() === experience.value.toLowerCase())) {
    formWithValidation.setError('experiences', 'The record already exists')
  }

  if (formWithValidation.errors.experiences) return

  formWithValidation.experiences.push(experience.value)
  experience.value = ''
  formWithValidation.clearErrors('experiences')
}
/** End Tech Expertise */

/** Start Socials */
const social = ref<{ name: string; url: string }>({ name: '', url: '' })
const socialListEl = ref<HTMLElement | null>(null)
useSortable(socialListEl, formWithValidation.socials)
const addSocialToList = function () {
  formWithValidation.clearErrors('socials')

  if (!social.value.name || !social.value.url) {
    formWithValidation.setError('socials', 'Please provide a name and a URL.')
  }

  if (formWithValidation.socials.length >= 15) {
    formWithValidation.setError('socials', 'You can only add up to 15 social network profiles.')
  }

  if (!urlIsValid(social.value.url)) {
    formWithValidation.setError('socials', 'Please provide a valid and secure URL.')
  }

  if (social.value.url.length >= 255) {
    formWithValidation.setError('socials', 'The URL must not exceed 255 characters.')
  }

  if (formWithValidation.socials.find((s) => s.name.toLowerCase() === social.value.name.toLowerCase())) {
    formWithValidation.setError('socials', "You've already added this social network profile.")
  }

  if (formWithValidation.errors.socials) return

  formWithValidation.socials.push({ name: social.value.name, url: social.value.url })
  social.value.name = ''
  social.value.url = ''
  formWithValidation.clearErrors('socials')
}

/** End Socials */

/** Start Tech Expertise */
const techExpertiseListLimit = ref(6)
const showAllTechExpertise = ref(props.techExpertise.length > techExpertiseListLimit.value)
const techExpertiseListCut = computed(function () {
  if (showAllTechExpertise.value && formWithValidation.tech_expertise.length > 4) {
    return formWithValidation.tech_expertise.slice(0, techExpertiseListLimit.value)
  }

  return formWithValidation.tech_expertise
})
const displayShowMoreTechExpertiseButton = computed(function () {
  return formWithValidation.tech_expertise.length > techExpertiseListLimit.value
})

const techExpertiseListEl = ref<HTMLElement | null>(null)
useSortable(techExpertiseListEl, formWithValidation.tech_expertise)

const techExpertiseName = ref('')
const techExpertiseDescription = ref('')
const techExpertiseProficiency = ref('')
const techExpertiseLogo = ref('')

const addTechExpertiseToList = function () {
  formWithValidation.clearErrors('tech_expertise')

  if (!techExpertiseName.value || !techExpertiseDescription.value || !techExpertiseProficiency.value) {
    formWithValidation.setError('tech_expertise', 'Please provide a name, description, and proficiency.')
  }

  if (techExpertiseName.value.length >= 150 || techExpertiseDescription.value.length >= 150) {
    formWithValidation.setError('tech_expertise', 'The name or description must not exceed 150 characters.')
  }

  if (techExpertiseLogo.value && !urlIsValid(techExpertiseLogo.value)) {
    formWithValidation.setError('tech_expertise', 'Please provide a valid and secure URL.')
  }

  if (techExpertiseLogo.value && techExpertiseLogo.value.length >= 255) {
    formWithValidation.setError('tech_expertise', 'The URL must not exceed 255 characters.')
  }

  if (formWithValidation.tech_expertise.length >= 100) {
    formWithValidation.setError('tech_expertise', 'You can only add up to 100 tech expertise.')
  }

  if (formWithValidation.tech_expertise.find((t) => t.name.toLowerCase() === techExpertiseName.value.toLowerCase())) {
    formWithValidation.setError('tech_expertise', `You've already added "${techExpertiseName.value}" as a tech expertise.`)
  }

  if (formWithValidation.errors.tech_expertise) return

  formWithValidation.tech_expertise.push({
    name: techExpertiseName.value,
    description: techExpertiseDescription.value,
    proficiency: techExpertiseProficiency.value,
    logo: techExpertiseLogo.value,
  })

  techExpertiseName.value = ''
  techExpertiseDescription.value = ''
  techExpertiseProficiency.value = ''
  techExpertiseLogo.value = ''

  formWithValidation.clearErrors('tech_expertise')
}
/** End Tech Expertise */

/** Start Project Highlights */
const projectHighlightUploadEl = ref<HTMLElement | null>(null)
const projectHighlightCoverSrc = ref<string | null>(null)
const projectHighlightCoverFile = ref<File | null>(null)
const projectHighlightTitle = ref('')
const projectHighlightDescription = ref('')
const projectHighlightLinkName = ref('')
const projectHighlightLinkUrl = ref('')
const projectHighlightLinks = ref<Array<{ name: string; url: string }>>([])

const projectHighlightListEl = ref<HTMLElement | null>(null)
useSortable(projectHighlightListEl, formWithValidation.projects)

const projectHighlightLinkListEl = ref<HTMLElement | null>(null)
useSortable(projectHighlightLinkListEl, projectHighlightLinks)

// Re-render PrimeVue upload component
const showProjectHighlightUploadInput = ref(true)
const reRenderProjectHighlightUploadInput = function () {
  showProjectHighlightUploadInput.value = false
  nextTick(() => (showProjectHighlightUploadInput.value = true))
}
const onProjectHighlightCoverSelect = function (event: FileUploadSelectEvent) {
  const file = event.files[0]
  projectHighlightCoverFile.value = file || null

  if (!file) return

  const reader = new FileReader()
  reader.readAsDataURL(file)
  reader.onload = (e) => (projectHighlightCoverSrc.value = e.target?.result?.toString() || null)
}

const addLinkToProject = function () {
  formWithValidation.clearErrors('projects')

  if (projectHighlightLinkName.value && projectHighlightLinkName.value.length > 150) {
    formWithValidation.setError('projects', 'The link name must not exceed 150 characters.')
  }

  if (projectHighlightLinkUrl.value && projectHighlightLinkUrl.value.length > 255) {
    formWithValidation.setError('projects', 'The URL must not exceed 255 characters.')
  }

  if (projectHighlightLinkUrl.value && !urlIsValid(projectHighlightLinkUrl.value)) {
    formWithValidation.setError('projects', 'Please provide a valid and secure URL.')
  }

  if (projectHighlightLinks.value.length >= 3) {
    formWithValidation.setError('projects', 'You can only add up to 3 links.')
  }

  if (projectHighlightLinks.value.find((l) => l.name.toLowerCase() === projectHighlightLinkName.value.toLowerCase())) {
    formWithValidation.setError('projects', "You can't add two links with the same name.")
  }

  if (formWithValidation.errors.projects) return

  projectHighlightLinks.value.push({
    name: projectHighlightLinkName.value,
    url: projectHighlightLinkUrl.value,
  })

  projectHighlightLinkName.value = ''
  projectHighlightLinkUrl.value = ''
}

const addProjectToList = function () {
  formWithValidation.clearErrors('projects')

  if (!projectHighlightTitle.value || !projectHighlightDescription.value) {
    formWithValidation.setError('projects', 'Please provide a title and description.')
  }

  if (projectHighlightTitle.value.length >= 150) {
    formWithValidation.setError('projects', 'The title must not exceed 150 characters.')
  }

  if (projectHighlightDescription.value.length >= 500) {
    formWithValidation.setError('projects', 'The description must not exceed 500 characters.')
  }

  if (formWithValidation.projects.find((p) => p.title.toLowerCase() === projectHighlightTitle.value.toLowerCase())) {
    formWithValidation.setError('projects', 'You already have a project with the same title.')
  }

  if (formWithValidation.errors.projects) return

  formWithValidation.projects.push({
    title: projectHighlightTitle.value,
    description: projectHighlightDescription.value,
    cover: projectHighlightCoverFile.value,
    links: projectHighlightLinks.value,
  })

  projectHighlightTitle.value = ''
  projectHighlightDescription.value = ''
  projectHighlightCoverSrc.value = null
  projectHighlightLinks.value = []
  projectHighlightLinkName.value = ''
  projectHighlightLinkUrl.value = ''

  reRenderProjectHighlightUploadInput()
}
/** End Project Highlights */

/** Start Timeline */
const timelineTitleInput = ref('')
const timelineDateInput = ref<Array<Date | string>>([])
const timelineDescriptionInput = ref('')
const timelineTagInput = ref('')
const timelineLogoUrlInput = ref('')
const timelineCompanyInput = ref('')
const timelineTagsList = ref<Array<string>>([])

const timelineTagsListEl = ref<HTMLElement | null>(null)
useSortable(timelineTagsListEl, timelineTagsList.value)

const timelineListEl = ref<HTMLElement | null>(null)
useSortable(timelineListEl, formWithValidation.work_timeline.history)

const timelineDownloadableFile = ref<File | null>(null)
const clearTimelineDownloadableFile = function () {
  formWithValidation.work_timeline.downloadable = null
  displayTimelineDownloadableFileLink.value = false
}

const displayTimelineDownloadableFileLink = ref<boolean>(!!props.timeline.downloadable)
const onTimelineDownloadableSelect = function (event: FileUploadSelectEvent) {
  const file = event.files[0]
  timelineDownloadableFile.value = file || null

  if (!file) return

  const reader = new FileReader()
  reader.readAsDataURL(file)
  reader.onload = (e) => (projectHighlightCoverSrc.value = e.target?.result?.toString() || null)

  formWithValidation.work_timeline.downloadable = timelineDownloadableFile.value
}

const addTimelineTagToList = function () {
  formWithValidation.clearErrors('work_timeline')

  if (!timelineTagInput.value) {
    return
  }

  if (timelineTagInput.value.length >= 50) {
    formWithValidation.setError('work_timeline', 'The tag must not exceed 50 characters.')
  }

  if (timelineTagsList.value.length >= 25) {
    formWithValidation.setError('work_timeline', 'You can only add up to 25 tags.')
  }

  if (timelineTagsList.value.find((t) => t.toLowerCase() === timelineTagInput.value.toLowerCase())) {
    formWithValidation.setError('work_timeline', 'You already have a tag with the same name.')
  }

  if (formWithValidation.errors.work_timeline) return

  timelineTagsList.value.push(timelineTagInput.value)
  timelineTagInput.value = ''
}

const addTimelineToList = function () {
  formWithValidation.clearErrors('work_timeline')

  if (!timelineTitleInput.value || !timelineDateInput.value || !timelineDescriptionInput.value || !timelineCompanyInput.value) {
    formWithValidation.setError('work_timeline', 'Please provide a title, date, company, and description.')
  }

  if (timelineTitleInput.value.length >= 150) {
    formWithValidation.setError('work_timeline', 'The title must not exceed 150 characters.')
  }

  if (timelineCompanyInput.value.length >= 255) {
    formWithValidation.setError('work_timeline', 'The company must not exceed 150 characters.')
  }

  if (timelineDescriptionInput.value.length >= 5000) {
    formWithValidation.setError('work_timeline', 'The description must not exceed 800 characters.')
  }

  if (timelineLogoUrlInput.value && !urlIsValid(timelineLogoUrlInput.value)) {
    formWithValidation.setError('work_timeline', 'Please provide a valid and secure URL.')
  }

  if (timelineLogoUrlInput.value.length >= 255) {
    formWithValidation.setError('work_timeline', 'The URL must not exceed 255 characters.')
  }

  if (formWithValidation.work_timeline.history.length >= 50) {
    formWithValidation.setError('work_timeline', 'You can only add up to 50 records.')
  }

  if (formWithValidation.errors.work_timeline) return

  // Convert period start and end date from Date instance to string ('YYYY-MM-DD') format
  const datePeriod = []
  if (timelineDateInput.value && timelineDateInput.value.length === 2) {
    datePeriod.push(useDateFormat(timelineDateInput.value[0], 'YYYY-MM-DD').value.toString())

    // The user may just select the first date
    if (timelineDateInput.value[1]) {
      datePeriod.push(useDateFormat(timelineDateInput.value[1], 'YYYY-MM-DD').value.toString())
    }
  }

  formWithValidation.work_timeline.history.push({
    title: timelineTitleInput.value,
    period: datePeriod,
    description: timelineDescriptionInput.value,
    logo: timelineLogoUrlInput.value,
    company: timelineCompanyInput.value,
    tags: timelineTagsList.value,
  })

  timelineTitleInput.value = ''
  timelineDateInput.value = []
  timelineDescriptionInput.value = ''
  timelineLogoUrlInput.value = ''
  timelineCompanyInput.value = ''
  timelineTagsList.value = []
  timelineTagInput.value = ''
}
/** End Timeline */

/** Start Services */
const servicesTitleInput = ref('')
const servicesDescriptionInput = ref('')
const servicesLogoUrlInput = ref('')
const servicesTagInput = ref('')
const servicesTagsList = ref<Array<string>>([])

const servicesTagsListEl = ref<HTMLElement | null>(null)
useSortable(servicesTagsListEl, servicesTagsList.value)

const servicesListEl = ref<HTMLElement | null>(null)
useSortable(servicesListEl, formWithValidation.services)

const addServiceTagToList = function () {
  formWithValidation.clearErrors('services')

  if (!servicesTagInput.value) {
    return
  }

  if (servicesTagInput.value.length >= 50) {
    formWithValidation.setError('services', 'The tag must not exceed 50 characters.')
  }

  if (servicesTagsList.value.length >= 5) {
    formWithValidation.setError('services', 'You can only add up to 5 tags.')
  }

  if (servicesTagsList.value.find((t) => t.toLowerCase() === timelineTagInput.value.toLowerCase())) {
    formWithValidation.setError('services', 'You already have a tag with the same name.')
  }

  if (formWithValidation.errors.services) return

  servicesTagsList.value.push(servicesTagInput.value)
  servicesTagInput.value = ''
}

const addServiceToList = function () {
  formWithValidation.clearErrors('services')

  if (!servicesTitleInput.value || !servicesDescriptionInput.value) {
    formWithValidation.setError('projects', 'Please provide a title and description.')
  }

  if (servicesTitleInput.value.length >= 150) {
    formWithValidation.setError('services', 'The title must not exceed 150 characters.')
  }

  if (servicesDescriptionInput.value.length >= 500) {
    formWithValidation.setError('services', 'The description must not exceed 500 characters.')
  }

  if (formWithValidation.services.find((s) => s.title.toLowerCase() === servicesTitleInput.value.toLowerCase())) {
    formWithValidation.setError('services', 'You already have a service with the same title.')
  }

  if (servicesLogoUrlInput.value && servicesLogoUrlInput.value.length > 255) {
    formWithValidation.setError('services', 'The URL must not exceed 255 characters.')
  }

  if (servicesLogoUrlInput.value && !urlIsValid(servicesLogoUrlInput.value)) {
    formWithValidation.setError('services', 'Please provide a valid and secure URL.')
  }

  if (formWithValidation.errors.services) return

  formWithValidation.services.push({
    title: servicesTitleInput.value,
    description: servicesDescriptionInput.value,
    logo: servicesLogoUrlInput.value,
    tags: servicesTagsList.value,
  })

  servicesTitleInput.value = ''
  servicesDescriptionInput.value = ''
  servicesTagsList.value = []
  servicesTagInput.value = ''
  servicesLogoUrlInput.value = ''
}
/** End Services */

/** Start Form Submission */
const toast = useToast()
const page = usePage<SharedPage>()
const submitForm = function () {
  formWithValidation.clearErrors()

  if (formWithValidation.titles.length < 1) {
    formWithValidation.setError('titles', 'Please provided at least one title or role.')
  }

  if (formWithValidation.experiences.length < 1) {
    formWithValidation.setError('experiences', 'Please provided at least one record of work or academic achievement.')
  }

  if (formWithValidation.socials.length < 1) {
    formWithValidation.setError('socials', 'Please provided at least one social network profile.')
  }

  if (!formWithValidation.contact.availability_status) {
    formWithValidation.setError('contact', 'Please provide your availability status.')
  }

  if (formWithValidation.hasErrors) return window.scroll({ top: 0, behavior: 'smooth' })

  if (typeof formWithValidation.work_timeline.downloadable === 'string') {
    formWithValidation.work_timeline.downloadable = null
  }

  if (!formWithValidation.work_timeline.history) {
    formWithValidation.work_timeline.history = []
  }

  formWithValidation.post(props.storeContentUrl, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: function () {
      toast.add({ severity: 'success', summary: 'Resume Builder', detail: page.props.flash.CMS_SUCCESS, life: 3000 })
      displayTimelineDownloadableFileLink.value = !!props.timeline.downloadable
    },
    onError: function () {
      toast.add({
        severity: 'error',
        summary: 'Resume Builder',
        detail: "We couldn't save your changes. Try again.",
        life: 3000,
      })
    },
    onFinish: function () {
      window.scroll({ top: 0, behavior: 'smooth' })
    },
  })
}
/** End Form Submission */
</script>

<template>
  <Message v-if="formWithValidation.hasErrors" class="mt-4 w-full" severity="error">
    <i class="pi pi-exclamation-triangle mr-1"></i>
    <span>There are some errors in the form. Please correct them and submit again.</span>
  </Message>
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
        <Message v-if="formWithValidation.errors.socials" class="mt-2 w-full" severity="error">
          <i class="pi pi-exclamation-triangle mr-1"></i>
          <span>{{ formWithValidation.errors.socials }}</span>
        </Message>
        <div class="mt-4 flex w-full flex-col gap-4 md:flex-row">
          <div class="flex w-full flex-col">
            <div class="flex w-full flex-col items-start justify-between gap-4 md:flex-row">
              <DfSelect v-model="social.name" :options="props.availableSocials" placeholder="Social Network *" class="w-full">
                <template #icon>
                  <i class="pi pi-globe"></i>
                </template>
              </DfSelect>
              <div class="flex w-full items-start justify-between gap-x-2">
                <DfInputText v-model="social.url" placeholder="Link *" class="w-full" @keydown.enter="addSocialToList" />
                <Button
                  icon="pi pi-plus-circle"
                  class="align-self-start flex-shrink-0"
                  :disabled="formWithValidation.processing || !social.name || !social.url"
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
              class="flex transform items-center justify-center rounded-full transition-transform hover:scale-105"
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
    <div class="flex flex-grow-0 flex-col gap-4 md:flex-row">
      <!-- Start Tech Expertise -->
      <Card class="max-h-fit w-full">
        <template #title>
          <div class="flex items-center">
            <span class="text-sm font-bold">TECH EXPERTISE (OPTIONAL)</span>
          </div>
        </template>
        <template #content>
          <p class="mb-4">
            Enter the current and previous technologies or technical skills you have. (E.g., PHP, Laravel, MySQL, etc.)
          </p>
          <Message v-if="formWithValidation.errors.tech_expertise" severity="error" class="w-full">
            <i class="pi pi-exclamation-triangle mr-1"></i>
            <span>{{ formWithValidation.errors.tech_expertise }}</span>
          </Message>
          <div class="mt-4 flex w-full flex-col gap-4">
            <div class="flex w-full flex-col">
              <div class="flex w-full flex-col items-start justify-between gap-4">
                <DfSelect
                  v-model="techExpertiseProficiency"
                  :options="[
                    { value: 'active', label: 'Actively / Frequently Used' },
                    { value: 'familiar', label: 'Previously / Occasionally Used' },
                  ]"
                  option-label="label"
                  option-value="value"
                  placeholder="Proficiency *"
                >
                  <template #icon><i class="pi pi-star"></i></template>
                </DfSelect>
                <DfInputText
                  v-model="techExpertiseName"
                  placeholder="Name *"
                  @keydown="limitLength($event, 150, techExpertiseName)"
                >
                  <template #icon><i class="pi pi-wrench"></i></template>
                </DfInputText>
                <DfInputText
                  v-model="techExpertiseDescription"
                  placeholder="Description *"
                  @keydown="limitLength($event, 150, techExpertiseDescription)"
                >
                  <template #icon><i class="pi pi-book"></i></template>
                </DfInputText>
                <DfInputText
                  v-model="techExpertiseLogo"
                  placeholder="Logo / Icon Link"
                  @keydown="limitLength($event, 255, techExpertiseLogo)"
                >
                  <template #icon><i class="pi pi-link"></i></template>
                </DfInputText>
                <Button
                  label="ADD"
                  icon="pi pi-plus-circle"
                  class="w-full"
                  :disabled="!techExpertiseName || !techExpertiseDescription || !techExpertiseProficiency"
                  @click="addTechExpertiseToList"
                >
                </Button>
              </div>
              <!-- Start Tech Expertise List -->
              <div ref="techExpertiseListEl" class="mt-4 flex flex-col flex-wrap gap-y-2">
                <div
                  v-for="(t, idx) in techExpertiseListCut"
                  :key="t + '-' + idx"
                  class="flex items-center justify-between gap-x-4 rounded-lg bg-surface-100 px-4 py-3 transition-transform hover:scale-105 hover:cursor-move hover:bg-surface-200 dark:bg-surface-700 hover:dark:bg-surface-800"
                >
                  <template v-if="t.logo">
                    <img :src="t.logo" alt="Logo" class="object-fit h-6 w-6" />
                  </template>
                  <template v-else>
                    <FontAwesomeIcon :icon="faGlobe" class="h-6 w-6" />
                  </template>
                  <div class="flex w-full flex-col flex-wrap break-all">
                    <span class="font-bold">{{ t.name }}</span>
                    <span>{{ t.description }}</span>
                    <small class="mt-1 flex w-fit rounded-lg bg-blue-500 px-2 py-1 text-xs text-surface-0">
                      {{ t.proficiency.toUpperCase() }}
                    </small>
                  </div>
                  <button
                    class="flex h-7 w-7 transform items-center justify-center rounded-full p-2 transition-transform hover:scale-105 hover:bg-surface-200 dark:hover:bg-surface-50 dark:hover:bg-opacity-10"
                    @click="removeFromList(idx, formWithValidation.tech_expertise)"
                  >
                    <i class="pi pi-trash" />
                  </button>
                </div>
                <button
                  v-if="displayShowMoreTechExpertiseButton"
                  class="w-full rounded-lg bg-surface-100 py-2 transition-all hover:scale-105 dark:bg-surface-600"
                  @click="showAllTechExpertise = !showAllTechExpertise"
                >
                  <span v-if="showAllTechExpertise">
                    <i class="pi pi-plus mr-1"></i>
                    Show All
                  </span>
                  <span v-if="!showAllTechExpertise">
                    <i class="pi pi-minus mr-1"></i>
                    Show Less
                  </span>
                </button>
              </div>
              <!-- End Tech Expertise List -->
            </div>
          </div>
        </template>
      </Card>
      <!-- End Tech Expertise -->
      <!-- Start Project Highlights -->
      <Card class="max-h-fit w-full">
        <template #title>
          <div class="flex items-center">
            <span class="text-sm font-bold">PROJECT HIGHLIGHTS (OPTIONAL)</span>
          </div>
        </template>
        <template #content>
          <p class="mb-4">
            Enter the projects you consider to be the most relevant to your portfolio. You may enter a maximum of 6 records.
          </p>
          <Message v-if="formWithValidation.errors.projects" severity="error" class="w-full">
            <i class="pi pi-exclamation-triangle mr-1"></i>
            <span>{{ formWithValidation.errors.projects }}</span>
          </Message>
          <div class="mt-4 flex w-full flex-col gap-4">
            <div class="flex w-full flex-col">
              <div class="flex w-full flex-col items-start justify-between gap-4">
                <DfInputText v-model="projectHighlightTitle" placeholder="Title *">
                  <template #icon><i class="pi pi-star"></i></template>
                </DfInputText>
                <DfInputText v-model="projectHighlightDescription" placeholder="Description *">
                  <template #icon><i class="pi pi-book"></i></template>
                </DfInputText>
                <FileUpload
                  v-if="showProjectHighlightUploadInput"
                  ref="projectHighlightUploadEl"
                  mode="basic"
                  choose-label="Cover Image"
                  accept="image/*"
                  :max-file-size="2097152"
                  choose-icon="pi pi-cloud-upload"
                  @select="onProjectHighlightCoverSelect"
                />
                <div class="flex w-full flex-col gap-4 md:flex-row md:gap-2">
                  <DfInputText v-model="projectHighlightLinkName" placeholder="Link Name *" class="w-full">
                    <template #icon><i class="pi pi-link"></i></template>
                  </DfInputText>
                  <DfInputText
                    v-model="projectHighlightLinkUrl"
                    placeholder="Link URL *"
                    class="w-full"
                    @keydown.enter="addLinkToProject"
                  >
                    <template #icon><i class="pi pi-link"></i></template>
                  </DfInputText>
                  <button
                    class="flex h-full w-full items-center justify-center rounded-md bg-surface-500 py-2 text-surface-0 md:w-10 md:flex-shrink-0 md:bg-primary md:py-3 md:text-primary-contrast"
                    :class="{ 'opacity-50': !projectHighlightLinkName || !projectHighlightLinkUrl }"
                    :disabled="!projectHighlightLinkName || !projectHighlightLinkUrl"
                    @click="addLinkToProject"
                  >
                    <i class="pi pi-plus-circle mr-2 md:mr-0"></i>
                    <span class="inline-block md:hidden">Attach Link</span>
                  </button>
                </div>
                <div ref="projectHighlightLinkListEl" class="flex w-full flex-wrap gap-2">
                  <div
                    v-for="(l, idx) in projectHighlightLinks"
                    :key="l + '-' + idx"
                    class="flex gap-x-2 rounded-lg bg-surface-100 px-3 py-2 transition-transform hover:scale-105 hover:cursor-move hover:bg-surface-200 dark:bg-surface-700 hover:dark:bg-surface-800"
                  >
                    <div class="flex flex-col flex-wrap">
                      <span>{{ l.name }}</span>
                    </div>
                    <button
                      class="flex transform items-center justify-center rounded-full transition-transform hover:scale-105"
                      @click="removeFromList(idx, projectHighlightLinks)"
                    >
                      <i class="pi pi-times-circle" />
                    </button>
                  </div>
                  <Button
                    label="ADD"
                    icon="pi pi-plus-circle"
                    class="w-full"
                    :disabled="!projectHighlightTitle || !projectHighlightDescription"
                    @click="addProjectToList"
                  ></Button>
                  <!-- Start Project List -->
                  <div ref="projectHighlightListEl" class="mt-2 flex w-full flex-col flex-wrap gap-y-2">
                    <div
                      v-for="(p, idx) in formWithValidation.projects"
                      :key="p + '-' + idx"
                      class="flex items-center justify-between gap-x-4 rounded-lg bg-surface-100 px-4 py-3 transition-transform hover:scale-105 hover:cursor-move hover:bg-surface-200 dark:bg-surface-700 hover:dark:bg-surface-800"
                    >
                      <div class="relative flex w-full flex-col flex-wrap overflow-hidden">
                        <span class="font-bold">{{ p.title }}</span>
                        <span>{{ p.description }}</span>
                        <small class="my-2">{{ 'Cover Image: ' + (p.cover ? 'Uploaded' : 'Default') }}</small>
                        <div class="flex flex-wrap gap-2">
                          <small
                            v-for="l in p.links"
                            :key="l + '-' + idx"
                            class="mt-1 flex w-fit rounded-lg bg-blue-500 px-2 py-1 text-xs text-surface-0"
                          >
                            {{ l.name }}
                          </small>
                        </div>
                      </div>
                      <button
                        class="flex h-7 w-7 transform items-center justify-center rounded-full p-2 transition-transform hover:scale-105 hover:bg-surface-200 dark:hover:bg-surface-50 dark:hover:bg-opacity-10"
                        @click="removeFromList(idx, formWithValidation.projects)"
                      >
                        <i class="pi pi-trash" />
                      </button>
                    </div>
                  </div>
                  <!-- End Project List -->
                </div>
              </div>
            </div>
          </div>
        </template>
      </Card>
      <!-- End Project Highlights -->
    </div>
    <!-- Start Timeline -->
    <Card class="w-full">
      <template #title>
        <div class="flex items-center">
          <span class="text-sm font-bold">WORK / EDUCATION TIMELINE (OPTIONAL)</span>
        </div>
      </template>
      <template #content>
        <p class="mb-4">You may upload the PDF version of your resume or work history</p>
        <div class="mb-4 grid w-full grid-cols-1 gap-4 break-all md:grid-cols-2">
          <div class="flex w-full justify-start">
            <FileUpload
              mode="basic"
              :choose-label="`${props.timeline?.downloadable ? 'Change File' : 'Upload File'}`"
              accept="application/pdf"
              :max-file-size="2097152"
              choose-icon="pi pi-cloud-upload"
              @select="onTimelineDownloadableSelect"
            />
          </div>
          <div class="flex w-full justify-start">
            <template v-if="displayTimelineDownloadableFileLink">
              <div class="flex items-center gap-1.5 rounded-md border p-1 px-4">
                <FontAwesomeIcon :icon="faExternalLink"></FontAwesomeIcon>
                <a :href="props.timeline.downloadable as string" target="_blank">View Uploaded File</a>
              </div>
              <Button severity="secondary" icon="pi pi-trash" outlined class="ml-1.5" @click="clearTimelineDownloadableFile">
              </Button>
            </template>
          </div>
        </div>
        <Divider />
        <p class="mb-4">Enter the timeline of your work and/or education. You may enter a maximum of 50 records.</p>
        <Message v-if="formWithValidation.errors.work_timeline" class="mt-2 w-full" severity="error">
          <i class="pi pi-exclamation-triangle mr-1"></i>
          <span>{{ formWithValidation.errors.work_timeline }}</span>
        </Message>
        <div class="mt-4 flex w-full flex-col gap-4">
          <div class="flex w-full flex-col gap-4 md:flex-row">
            <DfInputText
              v-model="timelineTitleInput"
              placeholder="Title or Role *"
              class="w-full"
              @keydown="limitLength($event, 150, timelineTitleInput)"
            >
              <template #icon><i class="pi pi-star"></i></template>
            </DfInputText>
            <DfDatePicker
              v-model="timelineDateInput"
              choose-icon="pi pi-cloud-upload"
              placeholder="Engagement Period *"
              view="month"
              date="mm/yy"
              selection-mode="range"
              :manual-input="false"
              class="w-full"
            >
              <template #icon><i class="pi pi-calendar"></i></template>
            </DfDatePicker>
          </div>
          <div class="flex w-full flex-col gap-4 md:flex-row">
            <DfInputText
              v-model="timelineCompanyInput"
              placeholder="Company or Client *"
              class="w-full"
              @keydown="limitLength($event, 255, timelineCompanyInput)"
            >
              <template #icon><i class="pi pi-building"></i></template>
            </DfInputText>
            <DfInputText
              v-model="timelineLogoUrlInput"
              placeholder="Company Logo Link"
              class="w-full"
              @keydown="limitLength($event, 255, timelineCompanyInput)"
            >
              <template #icon><i class="pi pi-link"></i></template>
            </DfInputText>
          </div>
          <div class="grid w-full grid-cols-1 items-start gap-4 md:grid-cols-2">
            <div class="flex w-full flex-col gap-2">
              <div class="flex gap-2">
                <DfInputText
                  v-model="timelineTagInput"
                  placeholder="Add Tag"
                  @keydown.enter="addTimelineTagToList"
                  @keydown="limitLength($event, 50, timelineTagInput)"
                >
                  <template #icon><i class="pi pi-tag"></i></template>
                </DfInputText>
                <Button
                  icon="pi pi-plus-circle"
                  class="align-self-start flex-shrink-0"
                  :disabled="formWithValidation.processing || !timelineTagInput"
                  :loading="formWithValidation.processing"
                  @click="addTimelineTagToList"
                ></Button>
              </div>
              <!-- Start Tags List -->
              <div v-if="timelineTagsList.length > 0" ref="timelineTagsListEl" class="flex flex-wrap gap-2">
                <div
                  v-for="(t, idx) in timelineTagsList"
                  :key="t + '-' + idx"
                  class="flex gap-x-2 break-all rounded-lg bg-surface-100 px-3 py-2 transition-transform hover:scale-105 hover:cursor-move hover:bg-surface-200 dark:bg-surface-700 hover:dark:bg-surface-800"
                >
                  <div class="flex flex-col flex-wrap">
                    <span>{{ t }}</span>
                  </div>
                  <button
                    class="flex transform items-center justify-center rounded-full transition-transform hover:scale-105"
                    @click="removeFromList(idx, timelineTagsList)"
                  >
                    <i class="pi pi-times-circle" />
                  </button>
                </div>
              </div>
              <!-- End Tags List -->
            </div>
          </div>
          <DfTextarea
            v-model="timelineDescriptionInput"
            placeholder="Description *"
            class="w-full"
            rows="10"
            @keydown="limitLength($event, 5000, timelineDescriptionInput)"
          ></DfTextarea>
          <div class="flex w-full justify-end">
            <Button
              icon="pi pi-plus-circle"
              label="ADD"
              :disabled="
                formWithValidation.processing ||
                !timelineTitleInput ||
                !timelineDateInput ||
                !timelineCompanyInput ||
                !timelineDescriptionInput
              "
              @click="addTimelineToList"
            />
          </div>
          <!-- Start Timeline Record List -->
          <div ref="timelineListEl" class="mt-2 flex w-full flex-col flex-wrap gap-y-2">
            <div
              v-for="(h, idx) in formWithValidation.work_timeline.history"
              :key="h + '-' + idx"
              class="flex items-center justify-between gap-x-4 rounded-lg bg-surface-100 px-4 py-3 transition-transform hover:scale-[101%] hover:cursor-move hover:bg-surface-200 dark:bg-surface-700 hover:dark:bg-surface-800"
            >
              <div class="flex w-full gap-4">
                <template v-if="h.logo">
                  <img :src="h.logo" alt="Logo" class="mt-1 h-6 w-6 object-cover" />
                </template>
                <template v-else>
                  <FontAwesomeIcon :icon="faBuilding" class="mt-1 h-6 w-6" />
                </template>
                <div class="flex w-full justify-between">
                  <div class="relative flex flex-col flex-wrap overflow-hidden whitespace-pre-wrap">
                    <span class="font-bold">
                      {{ h.title }}<span class="mx-1 font-bold text-surface-500 dark:text-surface-400">@ {{ h.company }}</span>
                    </span>
                    <small class="font-bold text-surface-500 dark:text-surface-400">
                      {{ formatDateSpan(h.period) }}
                    </small>
                    <span>{{ h.description }}</span>
                    <div class="mt-2 flex flex-wrap gap-2">
                      <small
                        v-for="l in h.tags"
                        :key="l + '-' + idx"
                        class="mt-1 flex w-fit rounded-lg bg-blue-500 px-2 py-1 text-xs text-surface-0"
                      >
                        {{ l }}
                      </small>
                    </div>
                  </div>
                  <div class="ml-1 flex h-full items-center justify-center">
                    <button
                      class="flex h-7 w-7 transform items-center justify-center rounded-full p-2 transition-transform hover:scale-105 hover:bg-surface-200 dark:hover:bg-surface-50 dark:hover:bg-opacity-10"
                      @click="removeFromList(idx, formWithValidation.work_timeline.history)"
                    >
                      <i class="pi pi-trash" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- End Timeline Record List -->
        </div>
      </template>
    </Card>
    <!-- End Timeline -->
    <div class="flex flex-grow-0 flex-col gap-4 md:flex-row">
      <!-- Start Services -->
      <Card class="max-h-fit w-full">
        <template #title>
          <div class="flex items-center">
            <span class="text-sm font-bold">SERVICES (OPTIONAL)</span>
          </div>
        </template>
        <template #content>
          <p class="mb-4">
            Enter the services you provide to your clients (E.g. Full-Stack Web Development, UI/UX Design, Cloud Migration, etc.).
            You may enter a maximum of 6 records.
          </p>
          <Message v-if="formWithValidation.errors.services" severity="error" class="w-full">
            <i class="pi pi-exclamation-triangle mr-1"></i>
            <span>{{ formWithValidation.errors.services }}</span>
          </Message>
          <div class="flex flex-col gap-4">
            <DfInputText
              v-model="servicesTitleInput"
              placeholder="Title *"
              class="w-full"
              @keydown="limitLength($event, 150, servicesTitleInput)"
            >
              <template #icon><i class="pi pi-star"></i></template>
            </DfInputText>
            <DfInputText
              v-model="servicesDescriptionInput"
              placeholder="Description *"
              class="w-full"
              @keydown="limitLength($event, 500, servicesDescriptionInput)"
            >
              <template #icon><i class="pi pi-book"></i></template>
            </DfInputText>
            <div class="flex gap-2">
              <DfInputText
                v-model="servicesTagInput"
                placeholder="Add Tag"
                class="w-full"
                @keydown.enter="addServiceTagToList"
                @keydown="limitLength($event, 50, servicesTagInput)"
              >
                <template #icon><i class="pi pi-tag"></i></template>
              </DfInputText>
              <Button
                icon="pi pi-plus-circle"
                class="align-self-start flex-shrink-0"
                :disabled="formWithValidation.processing || !servicesTagInput"
                :loading="formWithValidation.processing"
                @click="addServiceTagToList"
              ></Button>
            </div>
            <!-- Start Tags List -->
            <div v-if="servicesTagsList.length > 0" ref="servicesListEl" class="flex flex-wrap gap-2">
              <div
                v-for="(t, idx) in servicesTagsList"
                :key="t + '-' + idx"
                class="flex gap-x-2 break-all rounded-lg bg-surface-100 px-3 py-2 transition-transform hover:scale-105 hover:cursor-move hover:bg-surface-200 dark:bg-surface-700 hover:dark:bg-surface-800"
              >
                <div class="flex flex-col flex-wrap">
                  <span>{{ t }}</span>
                </div>
                <button
                  class="flex transform items-center justify-center rounded-full transition-transform hover:scale-105"
                  @click="removeFromList(idx, servicesTagsList)"
                >
                  <i class="pi pi-times-circle" />
                </button>
              </div>
            </div>
            <!-- End Tags List -->
            <DfInputText v-model="servicesLogoUrlInput" placeholder="Icon Link" class="w-full">
              <template #icon><i class="pi pi-link"></i></template>
            </DfInputText>
            <Button
              label="ADD"
              :disabled="formWithValidation.processing || !servicesTitleInput || !servicesDescriptionInput"
              @click="addServiceToList"
            >
              <template #icon>
                <i class="pi pi-plus-circle"></i>
              </template>
            </Button>
            <!-- Start Services List -->
            <div ref="servicesListEl" class="mt-4 flex flex-col flex-wrap gap-y-2">
              <div
                v-for="(s, idx) in formWithValidation.services"
                :key="s.title + '-' + idx"
                class="flex items-center justify-between gap-x-4 rounded-lg bg-surface-100 px-4 py-3 transition-transform hover:scale-105 hover:cursor-move hover:bg-surface-200 dark:bg-surface-700 hover:dark:bg-surface-800"
              >
                <template v-if="s.logo">
                  <img :src="s.logo" alt="Logo" class="object-fit h-6 w-6" />
                </template>
                <template v-else>
                  <FontAwesomeIcon :icon="faMagicWandSparkles" class="h-6 w-6" />
                </template>
                <div class="flex w-full flex-col flex-wrap">
                  <span class="font-bold">{{ s.title }}</span>
                  <span>{{ s.description }}</span>
                  <div class="flex flex-wrap gap-2">
                    <small
                      v-for="(tag, i) in s.tags"
                      :key="tag + '-' + i"
                      class="mt-1 flex w-fit rounded-lg bg-blue-500 px-2 py-1 text-xs text-surface-0"
                    >
                      {{ tag }}
                    </small>
                  </div>
                </div>
                <button
                  class="flex h-7 w-7 transform items-center justify-center rounded-full p-2 transition-transform hover:scale-105 hover:bg-surface-200 dark:hover:bg-surface-50 dark:hover:bg-opacity-10"
                  @click="removeFromList(idx, formWithValidation.services)"
                >
                  <i class="pi pi-trash" />
                </button>
              </div>
            </div>
            <!-- End Services List -->
          </div>
        </template>
      </Card>
      <!-- End Services -->
      <!-- Start Contact Section -->
      <Card class="max-h-fit w-full">
        <template #title>
          <div class="flex items-center">
            <span class="text-sm font-bold">CONTACT / AVAILABILITY (OPTIONAL)</span>
          </div>
        </template>
        <template #content>
          <p class="mb-4">
            Set your availability status (E.g. "Available for full-time work") and the visibility of the "Contact Me" section.
          </p>
          <div class="flex flex-col gap-4">
            <div class="flex gap-2">
              <ToggleSwitch v-model="formWithValidation.contact.show">
                <template #handle="{ checked }">
                  <i :class="['pi !text-xs', { 'pi-check': checked, 'pi-times': !checked }]" />
                </template>
              </ToggleSwitch>
              <span>Display the "Contact Me" section?</span>
            </div>
            <DfInputText
              v-model="formWithValidation.contact.availability_status"
              placeholder="Availability"
              :invalid="!!formWithValidation.errors.contact"
              :invalid-message="formWithValidation.errors.contact"
            >
              <template #icon><i class="pi pi-calendar-clock"></i></template>
            </DfInputText>
          </div>
        </template>
      </Card>
      <!-- End Contact Section -->
    </div>
    <!-- Start Action Buttons -->
    <div class="mb-4 mt-1 flex w-full flex-col justify-center gap-4 md:mt-4 md:w-auto md:flex-row md:justify-end">
      <a
        :href="'https://' + props.previewUrl"
        target="_blank"
        class="flex items-center justify-center gap-x-2 rounded-md bg-surface-500 p-2.5 text-primary-contrast"
      >
        <i class="pi pi-eye"></i>
        <span class="ml-">Preview</span>
      </a>
      <Button label="Save" :loading="formWithValidation.processing" :disabled="formWithValidation.processing" @click="submitForm">
        <template #icon>
          <i class="pi pi-save"></i>
        </template>
      </Button>
    </div>
    <!-- End Action Buttons -->
  </section>
</template>
