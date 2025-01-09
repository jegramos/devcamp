<script setup lang="ts">
import { computed, nextTick, type PropType, type Ref, ref, watch } from 'vue'
import { useIntervalFn } from '@vueuse/core'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import AppAnimatedFloaters from '@/Components/AppAnimatedFloaters.vue'
import TextHighlight from '@/Components/Animated/TextHighlight.vue'
import MarqueeElements from '@/Components/Animated/MarqueeElements.vue'
import BorderBeam from '@/Components/Animated/BorderBeam.vue'
import GlowBorder from '@/Components/Animated/GlowBorder.vue'
import { faGear, faThumbsUp } from '@fortawesome/free-solid-svg-icons'
import TracingBeam from '@/Components/Animated/TracingBeam.vue'
import MeteorShower from '@/Components/Animated/MeteorShower.vue'
import TextSparkle from '@/Components/Animated/TextSparkle.vue'
import GradientButton from '@/Components/Animated/GradientButton.vue'
import { useMapSocialsToIcons } from '@/Composables/useMapSocialsToIcons.ts'
import type { Contact, Service, Social, TechExpertise, Timeline } from '@/Pages/Builder/Resume/ResumePage.vue'
import { formatDateSpan } from '@/Utils/date'
import { email, helpers, required } from '@vuelidate/validators'
import { useClientValidatedForm } from '@/Composables/useClientValidatedForm.ts'
import type { SharedPage } from '@/Types/shared-page.ts'

const props = defineProps({
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
  techExpertise: {
    type: Array<TechExpertise>,
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
    type: Object as PropType<Contact> | null,
    required: true,
  },
  projects: {
    type: Array<{
      title: string
      description: string
      cover: string | null
      links: Array<{ name: string; url: string }>
    }>,
    required: true,
  },
  hideNavigation: {
    type: Boolean,
    required: true,
  },
  showNavigation: {
    type: Object as PropType<{
      techExpertise: boolean
      workTimeline: boolean
      projectHighlights: boolean
      services: boolean
    }>,
    required: true,
  },
  sendMessageUrl: {
    type: String,
    required: true,
  },
})

/** Start Before the Fold */
// Titles
const currentTitle = ref(props.titles[0])
const showTitle = ref(true)
if (props.titles.length > 1) {
  useIntervalFn(function () {
    const currentIndex = props.titles.indexOf(currentTitle.value)
    const nextIndex = (currentIndex + 1) % props.titles.length
    currentTitle.value = props.titles[nextIndex]
    showTitle.value = false
    nextTick(() => (showTitle.value = true))
  }, 4000)
}

// Navigation
const techExpertiseEl = ref<HTMLElement>()
const professionalTimelineEl = ref<HTMLElement>()
const projectHighlightsEl = ref<HTMLElement>()
const servicesEl = ref<HTMLElement>()
const navigationLinks = ref([
  {
    label: 'Tech Expertise',
    action: () => scrollTo(techExpertiseEl),
    show: props.showNavigation.techExpertise,
  },
  {
    label: 'Timeline',
    action: () => scrollTo(professionalTimelineEl),
    show: props.showNavigation.workTimeline,
  },
  {
    label: 'Project Highlights',
    action: () => scrollTo(projectHighlightsEl),
    show: props.showNavigation.projectHighlights,
  },
  {
    label: 'Services',
    action: () => scrollTo(servicesEl),
    show: props.showNavigation.services,
  },
])

// Socials
const socialList = useMapSocialsToIcons(props.socials)

const scrollTo = function (view: Ref<HTMLElement | undefined>) {
  view.value?.scrollIntoView({ behavior: 'smooth' })
}

const openInNewTab = function (url: string) {
  window.open(url, '_blank')
}
/** End Before the Fold */

/** Start Tech Expertise */
const activelyUsedTech = computed(() => props.techExpertise.filter((t) => t.proficiency === 'active'))
const familiarlyUsedTech = computed(() => props.techExpertise.filter((t) => t.proficiency === 'familiar'))
const hideActivelyUsedTech = computed(() => activelyUsedTech.value.length < 1)
const hideFamiliarlyUsedTech = computed(() => familiarlyUsedTech.value.length < 1)
const showOneColumnTechExpertise = computed(() => activelyUsedTech.value.length < 1 || familiarlyUsedTech.value.length < 1)
/** End Tech Expertise */

/** Start Contact Me */
const contactForm = useForm({
  name: '',
  email: '',
  message: '',
})

const contactFormRules = {
  name: {
    required: helpers.withMessage('Your Name is required.', required),
  },
  message: {
    required: helpers.withMessage('Message is required.', required),
  },
  email: {
    required: helpers.withMessage('Email is required.', required),
    email: helpers.withMessage('Must be a valid email address', email),
  },
}

const validatedContactForm = useClientValidatedForm(contactFormRules, contactForm)

// Send Message Lock (30 seconds)
const contactSendMessageButtonIsLocked = ref(false)
const secondsTimer = 30
const lockSecondsRemaining = ref(secondsTimer)
const contactSendMessageButtonLockInterval = useIntervalFn(() => (lockSecondsRemaining.value -= 1), 1000, { immediate: false })

// Unlock the send button when the timer completes
watch(
  () => lockSecondsRemaining.value,
  function (value) {
    if (value > 0) return

    // Reset the lock timer states
    contactSendMessageButtonLockInterval.pause()
    lockSecondsRemaining.value = secondsTimer
    contactSendMessageButtonIsLocked.value = false
  }
)

const showContactMessageBanner = ref(false)
const page = usePage<SharedPage>()
const submitContactForm = function () {
  if (validatedContactForm.processing || contactSendMessageButtonIsLocked.value) return
  showContactMessageBanner.value = false

  validatedContactForm.post(props.sendMessageUrl, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: function () {
      showContactMessageBanner.value = true
      validatedContactForm.reset()
    },
    onFinish: function () {
      contactSendMessageButtonIsLocked.value = true
      contactSendMessageButtonLockInterval.resume()
    },
  })
}
/** End Contact Me */

/** Start Footer */
const showFooter = computed(function () {
  return (
    !props.contact?.show &&
    props.showNavigation?.workTimeline &&
    props.showNavigation?.projectHighlights &&
    props.showNavigation?.services
  )
})
/** End Footer */
</script>
<template>
  <Head title="Portfolio"></Head>
  <!-- Start Header -->
  <section class="flex h-full w-full flex-col items-center justify-center bg-surface-900 text-surface-0">
    <!-- Start Before the Fold (Desktop) -->
    <section class="relative hidden h-[100vh] w-full flex-col items-center justify-center p-1 md:flex lg:p-5">
      <AppAnimatedFloaters class="opacity-30" />
      <div class="grid w-full grid-cols-2 gap-x-10 md:w-[80%]">
        <div class="flex flex-col justify-center">
          <div v-if="hideNavigation" class="group z-10 mb-4 flex gap-x-6 lg:justify-start">
            <button
              v-for="(social, idx) in socialList"
              :key="social.name + '-' + idx"
              class="transition-transform hover:scale-125 hover:cursor-pointer hover:text-surface-0"
              @click="openInNewTab(social.url)"
            >
              <FontAwesomeIcon :icon="social.icon" class="text-lg lg:text-2xl" />
            </button>
          </div>
          <!-- Start Name -->
          <div class="flex w-fit flex-col text-nowrap">
            <span class="font-stylish text-lg font-bold lg:text-5xl">
              <span class="text-orange-400">&lt;</span>
              <span class="mx-2 font-writing text-3xl lg:text-6xl">{{ props.name }}</span>
              <span class="text-orange-500">/&gt;</span>
            </span>
            <h2 class="mt-6 font-stylish text-lg lg:text-left lg:text-2xl">
              <span class="mr-2 text-left">I'm an excellent</span>
              <template v-if="showTitle">
                <TextHighlight
                  class="rounded-lg bg-gradient-to-r from-pink-500 to-orange-500 px-3 py-2 font-writing"
                  :duration="500"
                >
                  {{ currentTitle }}
                </TextHighlight>
              </template>
            </h2>
          </div>
          <!-- End Name -->
        </div>
        <!-- Start Navigation-->
        <div v-if="!hideNavigation" class="z-20 flex h-[80vh] flex-col justify-center gap-y-10 text-right text-surface-400">
          <span
            v-for="links in navigationLinks.filter((l) => l.show)"
            :key="links.label"
            class="transform font-stylish text-lg lg:text-2xl"
          >
            <span
              class="border-orange-500 transition-all hover:cursor-pointer hover:border-r-4 hover:pr-2 hover:text-3xl hover:font-bold hover:text-surface-0"
              @click="links.action"
            >
              {{ links.label }}
            </span>
          </span>
          <div class="flex justify-end gap-x-6">
            <button
              v-for="(social, idx) in socialList"
              :key="social.name + '-' + idx"
              class="transition-transform hover:scale-125 hover:cursor-pointer hover:text-surface-0"
              @click="openInNewTab(social.url)"
            >
              <FontAwesomeIcon :icon="social.icon" class="text-lg lg:text-2xl" />
            </button>
          </div>
        </div>
        <!-- End Navigation -->
      </div>
      <!-- Start Experience Summary -->
      <div class="absolute bottom-0 flex w-full items-center bg-gradient-to-r from-orange-500 to-red-500 py-1">
        <MarqueeElements pause-on-hover class="[--duration:40s]">
          <div
            v-for="experience in experiences"
            :key="experience"
            class="flex font-stylish text-lg font-bold transition-transform hover:scale-105 hover:cursor-pointer"
          >
            {{ experience }}
          </div>
        </MarqueeElements>
      </div>
      <!-- End Experience Summary -->
    </section>
    <!-- End Before the Fold (Desktop) -->
    <!-- Start Before the Fold (Mobile) -->
    <section class="relative flex h-[100vh] w-full flex-col items-center justify-center md:hidden">
      <!-- Start Navigation / Socials -->
      <div v-if="!hideNavigation" class="absolute top-0 z-20 flex flex-wrap justify-center gap-4 py-4 text-xs text-surface-400">
        <span v-for="links in navigationLinks.filter((l) => l.show)" :key="links.label" class="transform font-stylish">
          <span class="transition-all hover:cursor-pointer hover:text-surface-0" @click="links.action">
            {{ links.label }}
          </span>
        </span>
      </div>
      <div v-if="hideNavigation" class="absolute top-0 z-20 flex flex-wrap justify-center gap-4 py-4 text-xs text-surface-400">
        <span v-for="(social, idx) in socialList" :key="social.name + idx" class="transform">
          <a :href="social.url" target="_blank" class="transition-all hover:cursor-pointer hover:text-surface-0">
            {{ social.name }}
          </a>
        </span>
      </div>
      <!-- End Navigation / Socials -->
      <AppAnimatedFloaters class="opacity-30" />
      <span class="font-stylish text-3xl font-bold">
        <span class="text-orange-400">&lt;</span>
        <span class="mx-2 font-writing">{{ name }}</span>
        <span class="text-orange-500">/&gt;</span>
      </span>
      <div class="mt-4 font-stylish text-xl">
        <p class="mb-2 text-center text-sm font-bold text-surface-500">I'm an excellent</p>
        <div class="flex justify-center">
          <template v-if="showTitle">
            <TextHighlight class="rounded-lg bg-gradient-to-r from-pink-500 to-orange-500 p-2 font-writing" :duration="500">
              {{ currentTitle }}
            </TextHighlight>
          </template>
        </div>
      </div>
      <!-- Start Experience Summary -->
      <div class="absolute bottom-0 flex w-full items-center bg-gradient-to-r from-orange-500 to-red-500 py-1">
        <MarqueeElements pause-on-hover class="[--duration:40s]">
          <div
            v-for="experience in experiences"
            :key="experience"
            class="flex font-stylish text-sm font-bold transition-transform hover:scale-105 hover:cursor-pointer"
          >
            {{ experience }}
          </div>
        </MarqueeElements>
      </div>
      <!-- End Experience Summary -->
    </section>
    <!-- End Before the Fold (Mobile) -->
    <!-- Start Tech Expertise -->
    <section
      v-if="props.techExpertise.length > 0"
      ref="techExpertiseEl"
      class="bg-surface-850 relative flex min-h-[100vh] w-full flex-col items-center overflow-hidden pb-8 pt-8"
    >
      <div class="mb-8 flex w-full items-center justify-center text-3xl md:w-[80%] md:text-5xl">
        <span data-aos="fade-right" class="font-stylish text-orange-400">&lt;</span>
        <span data-aos="fade-up" class="mx-1 mb-2 font-writing"> Tech Expertise </span>
        <span data-aos="fade-left" class="font-stylish text-orange-500">/&gt;</span>
      </div>
      <div class="flex flex-col gap-x-16 px-3 md:w-[90%] md:flex-row md:px-0">
        <!-- Start Actively Used Tools -->
        <div v-if="!hideActivelyUsedTech" data-aos="fade-right" class="flex h-full w-full flex-col">
          <h1 class="mb-4 text-left font-stylish text-sm font-black uppercase text-surface-500 lg:text-center">Actively Uses</h1>
          <div
            :class="`grid h-full w-full grid-cols-1 flex-col gap-4 ${showOneColumnTechExpertise ? 'lg:grid-cols-3' : 'xl:grid-cols-2'}`"
          >
            <div
              v-for="(tech, idx) in activelyUsedTech"
              :key="tech.name + '-' + idx"
              class="flex flex-grow rounded-lg transition-transform hover:scale-105"
              :class="{ 'min-h-28': showOneColumnTechExpertise }"
            >
              <GlowBorder
                class="relative flex min-h-24 w-full items-center rounded-lg border border-surface-500 bg-inherit p-3 text-surface-0 md:shadow-xl"
                :color="['#fe7c8b', '#f83c7c', '#ff8200']"
              >
                <template v-if="tech.logo">
                  <img :src="tech.logo" alt="logo" class="object-fit mr-4 h-12 w-12 rounded-sm md:h-6 md:w-6" />
                </template>
                <template v-else>
                  <FontAwesomeIcon :icon="faGear" class="mr-2 h-6 w-6 rounded-lg text-pink-500" />
                </template>
                <div class="flex flex-col flex-wrap">
                  <p class="font-stylish font-bold">{{ tech.name }}</p>
                  <small class="text-sm text-surface-500">{{ tech.description }}</small>
                </div>
              </GlowBorder>
            </div>
          </div>
        </div>
        <!-- End Actively Used Tools -->
        <!-- Start Familiar Tools -->
        <div v-if="!hideFamiliarlyUsedTech" data-aos="fade-left" class="mt-10 flex h-full w-full flex-col md:mt-0">
          <h1 class="mb-4 text-left font-stylish text-sm font-black uppercase text-surface-500 lg:text-center">Familiar with</h1>
          <div
            :class="`grid h-full w-full grid-cols-1 flex-col gap-4 ${showOneColumnTechExpertise ? 'lg:grid-cols-3' : 'xl:grid-cols-2'}`"
          >
            <div
              v-for="(tech, idx) in familiarlyUsedTech"
              :key="tech.name + '-' + idx"
              class="flex flex-grow rounded-lg transition-transform hover:scale-105"
            >
              <GlowBorder
                class="relative flex min-h-24 w-full items-center rounded-lg border border-surface-500 bg-inherit p-3 text-surface-0 md:shadow-xl"
                :color="['#f68210', '#f83c7c', '#ff8200']"
              >
                <template v-if="tech.logo">
                  <img :src="tech.logo" alt="logo" class="object-fit mr-4 h-6 w-6 rounded-sm" />
                </template>
                <template v-else>
                  <FontAwesomeIcon :icon="faGear" class="mr-2 h-6 w-6 rounded-lg text-pink-500" />
                </template>
                <div class="flex flex-col flex-wrap">
                  <p class="font-stylish font-bold">{{ tech.name }}</p>
                  <small class="text-sm text-surface-500">{{ tech.description }}</small>
                </div>
              </GlowBorder>
            </div>
          </div>
        </div>
        <!-- End Familiar Tools -->
      </div>
      <BorderBeam color-from="#ffaa40" color-to="#ec4899" :size="250" :duration="12" :delay="9" :border-width="4" />
    </section>
    <!-- End Tech Expertise -->
    <!-- Start Work Timeline -->
    <section
      v-if="props.timeline.history.length > 0"
      ref="professionalTimelineEl"
      class="relative flex min-h-[100vh] w-full flex-col items-center gap-y-4 pb-8 pt-8"
    >
      <div class="mb:0 flex w-full items-center justify-center text-3xl md:mb-4 md:text-5xl lg:mb-8">
        <span data-aos="fade-right" class="font-stylish text-orange-400">&lt;</span>
        <span data-aos="fade-up" class="mx-1 mb-2 font-writing">My Journey So Far</span>
        <span data-aos="fade-left" class="font-stylish text-orange-500">/&gt;</span>
      </div>
      <div class="w-full items-center justify-center px-3 md:px-8">
        <TracingBeam class="px-0 md:px-6">
          <div v-if="timeline.downloadable" data-aos="fade-up" class="flex w-full justify-start md:justify-end">
            <a
              :href="timeline.downloadable as string"
              target="_blank"
              class="mb-2 rounded-lg border border-surface-500 px-2 py-1.5 text-sm text-surface-500 transition-all hover:scale-105 hover:border-orange-500 hover:text-orange-500"
            >
              <i class="pi pi-file-pdf mr-0.5"></i>
              View File
            </a>
          </div>
          <div class="relative mx-auto w-full max-w-4xl pt-1 antialiased">
            <div
              v-for="(work, idx) in props.timeline.history"
              :key="work.title + '-' + idx"
              data-aos="fade-up"
              class="mb-10 flex rounded-lg bg-surface-800"
            >
              <GlowBorder
                class="relative flex w-full items-start gap-x-4 rounded-lg border border-surface-500 bg-inherit px-2 py-6"
                :color="['#fe7c8b', '#f34223', '#f50303']"
              >
                <img
                  :src="work.logo || 'gradient-company-default-logo.svg'"
                  alt="Company Logo"
                  class="hidden h-12 w-12 object-cover md:block"
                />
                <div class="flex flex-col">
                  <span class="font-stylish text-xl font-bold">{{ work.title }}</span>
                  <span class="">{{ work.company }}</span>
                  <small class="text-surface-500">{{ formatDateSpan(work.period) }}</small>
                  <p class="font-sm mr-1 mt-4 whitespace-pre-wrap">
                    {{ work.description }}
                  </p>
                  <div class="mt-4 flex flex-wrap gap-2">
                    <small
                      v-for="(badge, tagIdx) in work.tags"
                      :key="badge + '-' + tagIdx"
                      class="flex items-center justify-center rounded-lg bg-surface-900 px-2 py-1"
                      >{{ badge }}</small
                    >
                  </div>
                </div>
              </GlowBorder>
            </div>
          </div>
        </TracingBeam>
      </div>
    </section>
    <!-- End Work Timeline -->
    <!-- Start Project Highlights -->
    <section
      v-if="props.projects.length > 0"
      ref="projectHighlightsEl"
      class="relative flex min-h-[100vh] w-full flex-col items-center gap-y-4 px-3 pb-8 pt-8 md:px-0"
    >
      <div class="mb:4 flex w-full items-center justify-center text-3xl md:mb-4 md:text-5xl lg:mb-8">
        <span data-aos="fade-right" class="font-stylish text-orange-400">&lt;</span>
        <span data-aos="fade-up" class="mx-1 mb-2 font-writing">Project Highlights</span>
        <span data-aos="fade-left" class="font-stylish text-orange-500">/&gt;</span>
      </div>
      <div class="mt-4 grid w-full grid-cols-1 gap-4 md:mt-0 md:w-[95%] md:grid-cols-2 lg:w-[80%] lg:grid-cols-3">
        <div
          v-for="(project, idx) in projects"
          :key="project.title + '-' + idx"
          data-aos="fade-up"
          class="py-15 group flex w-full flex-col items-center justify-center"
        >
          <div class="relative h-full w-full transition-all hover:scale-105">
            <div
              class="absolute inset-0 hidden size-full scale-[0.70] rounded-md bg-red-500 bg-gradient-to-r from-pink-500 to-orange-500 blur-3xl transition-all group-hover:block"
            />
            <div
              class="relative flex h-full flex-col items-start overflow-hidden rounded-lg border border-surface-700 bg-surface-800 p-4"
            >
              <h1 class="relative z-10 mb-2 font-stylish text-xl font-bold">{{ project.title }}</h1>
              <img
                :src="project.cover || 'gradient-project-default-cover.jpg'"
                alt="Project Image"
                class="relative z-10 h-32 w-full rounded-lg object-cover md:h-48"
              />
              <p class="relative z-10 my-4 text-base font-normal text-surface-500">
                {{ project.description }}
              </p>

              <div v-if="project.links" class="align-self-end flex h-full w-full flex-wrap items-end justify-end gap-2">
                <a
                  v-for="link in project.links"
                  :key="link.name"
                  :href="link.url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="flex rounded-lg border border-surface-500 px-2 py-1 text-surface-300"
                >
                  <small>{{ link.name }}</small>
                </a>
              </div>
              <MeteorShower class="hidden group-hover:flex" />
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End Project Highlights -->
    <!-- Start Services -->
    <section
      v-if="props.services.length > 0"
      ref="servicesEl"
      class="relative flex min-h-[100vh] w-full flex-col items-center gap-y-4 px-3 pb-8 pt-8 md:w-[95%] md:px-0 lg:w-[90%]"
    >
      <div class="mb:4 flex w-full items-center justify-center text-3xl md:mb-4 md:text-5xl lg:mb-8">
        <span data-aos="fade-right" class="font-stylish text-orange-400">&lt;</span>
        <span data-aos="fade-up" class="mx-1 mb-2 font-writing">Services</span>
        <span data-aos="fade-left" class="font-stylish text-orange-500">/&gt;</span>
      </div>
      <div class="mt-4 grid w-full grid-cols-1 gap-x-4 gap-y-6 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="(service, idx) in props.services"
          :key="service.title + '-' + idx"
          data-aos="fade-up"
          class="border-rounded flex w-full flex-col"
        >
          <GlowBorder
            class="relative flex h-full w-full flex-col items-start gap-x-4 rounded-lg border border-surface-500 bg-inherit px-4 py-6"
            :color="['#fe7c8b', '#f34223', '#f50303']"
          >
            <img
              :src="service.logo || 'gradient-service-default-icon.svg'"
              class="h-10 w-10 rounded-lg grayscale"
              alt="Service Logo"
            />
            <span class="mt-3 text-lg font-bold">{{ service.title }}</span>
            <span class="mt-1 text-surface-500">{{ service.description }}</span>
            <div class="mt-4 flex flex-wrap gap-2">
              <div v-for="(tag, i) in service.tags" :key="tag + '-' + i" class="rounded-lg bg-surface-800">
                <small class="flex px-2 py-1">{{ tag }}</small>
              </div>
            </div>
          </GlowBorder>
        </div>
      </div>
    </section>
    <!-- End Services -->
    <!-- Start Contact Me -->
    <section
      v-if="props.contact?.show"
      class="relative flex min-h-[100vh] w-full flex-col items-center gap-y-4 px-3 pb-8 pt-8 md:w-[80%] md:px-0"
    >
      <div class="mb:2 flex w-full items-center justify-center text-3xl md:mb-4 md:text-5xl lg:mb-4">
        <span data-aos="fade-right" class="font-stylish text-orange-400">&lt;</span>
        <span data-aos="fade-up" class="mx-1 mb-2 font-writing">Contact Me</span>
        <span data-aos="fade-left" class="font-stylish text-orange-500">/&gt;</span>
      </div>
      <p data-aos="fade-up" class="text-center font-stylish text-surface-400 md:text-xl">
        Need something done the right way?
        <TextSparkle class="inline-flex">
          <span class="font-bold text-surface-0">Let's make it happen!</span>
        </TextSparkle>
      </p>
      <GlowBorder
        v-if="showContactMessageBanner"
        data-aos="fade-up"
        class="relative mt-2 flex w-full max-w-screen-lg items-center rounded-lg border border-surface-500 bg-inherit p-3 text-surface-0 md:shadow-xl"
        :color="['#fe7c8b', '#f83c7c', '#ff8200']"
      >
        <div class="flex w-full items-center gap-4 font-stylish text-surface-300">
          <FontAwesomeIcon :icon="faThumbsUp"></FontAwesomeIcon>
          {{ page.props.flash.PORTFOLIO_SUCCESS }}
        </div>
      </GlowBorder>
      <div data-aos="fade-up" class="mt-4 flex w-full max-w-screen-lg flex-col rounded-lg bg-surface-800">
        <div class="flex w-full flex-col gap-4 rounded-lg p-4 md:p-8">
          <p class="mb-1 font-stylish font-bold text-surface-300">
            {{ props.contact.availability_status }}
          </p>
          <div class="flex w-full flex-col gap-4 md:flex-row">
            <div class="flex w-full flex-col">
              <input
                v-model="validatedContactForm.name"
                class="w-full rounded-lg border border-surface-600 bg-transparent px-3 py-2 text-surface-0 shadow-sm transition duration-300 placeholder:text-surface-400 focus:shadow focus:outline-none"
                placeholder="Your name *"
                :class="{ '!border-red-500': validatedContactForm.errors.name }"
              />
              <small class="mt-1 text-red-500">{{ validatedContactForm.errors.name }}</small>
            </div>
            <div class="flex w-full flex-col">
              <input
                v-model="validatedContactForm.email"
                class="w-full rounded-lg border border-surface-600 bg-transparent px-3 py-2 text-surface-0 shadow-sm transition duration-300 placeholder:text-surface-400 focus:shadow focus:outline-none"
                placeholder="Email Address *"
                :class="{ '!border-red-500': validatedContactForm.errors.email }"
              />
              <small class="mt-1 text-red-500">{{ validatedContactForm.errors.email }}</small>
            </div>
          </div>
          <div class="flex w-full">
            <div class="flex w-full flex-col">
              <textarea
                v-model="validatedContactForm.message"
                rows="5"
                class="w-full rounded-lg border border-surface-600 bg-transparent px-3 py-2 text-surface-0 shadow-sm transition duration-300 placeholder:text-surface-400 focus:shadow focus:outline-none"
                placeholder="What do you want to work on?"
                :class="{ '!border-red-500': validatedContactForm.errors.message }"
              />
              <small class="mt-1 text-red-500">{{ validatedContactForm.errors.message }}</small>
            </div>
          </div>
          <div class="flex justify-end md:mt-3">
            <GradientButton bg-color="#27272A" @click="submitContactForm">
              <template v-if="!validatedContactForm.processing">
                <i :class="`${contactSendMessageButtonIsLocked ? 'pi pi-clock' : 'pi pi-send'} mr-2 text-sm`"></i>
                <span class="text-sm">{{
                  contactSendMessageButtonIsLocked ? `Wait for ${lockSecondsRemaining} seconds` : 'SEND'
                }}</span>
              </template>
              <template v-if="validatedContactForm.processing">
                <i class="pi pi-spinner mr-2 animate-spin text-sm"></i>
                <span class="text-sm">SENDING</span>
              </template>
            </GradientButton>
          </div>
        </div>
      </div>
      <p class="mt-4 text-sm text-surface-500">or reach me through</p>
      <div class="mt-1 flex items-center justify-end gap-x-6">
        <button
          v-for="(social, idx) in socialList"
          :key="social.name + '-' + idx"
          class="text-surface-500 transition-transform hover:scale-125 hover:cursor-pointer hover:text-surface-0"
          @click="openInNewTab(social.url)"
        >
          <FontAwesomeIcon :icon="social.icon" class="text-lg lg:text-4xl" />
        </button>
      </div>
    </section>
    <!-- End Contact Me -->
    <!-- Start Footer -->
    <footer v-if="showFooter" class="flex w-full items-center justify-center p-4">
      <small class="text-surface-500">© {{ new Date().getFullYear() }} {{ name }}</small>
    </footer>
    <!-- End Footer -->
  </section>
  <!-- End Header -->
</template>
