<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBriefcase, faHeart, faFolderOpen, faBookOpen, faCalendarCheck, faSignInAlt } from '@fortawesome/free-solid-svg-icons'
import { faGithub } from '@fortawesome/free-brands-svg-icons'
import AppLogo from '@/Components/AppLogo.vue'
import AppAnimatedFloaters from '@/Components/AppAnimatedFloaters.vue'
import type { SharedPage } from '@/Types/shared-page'
import { applyTheme } from '@/Utils/theme'

const features = [
  { name: 'Resume Builder', icon: faBriefcase },
  { name: 'Projects Showcase', icon: faFolderOpen },
  { name: 'Write Blogs', icon: faBookOpen },
  { name: 'Calendar System', icon: faCalendarCheck },
]

defineProps({
  loginUrl: {
    type: String,
    required: true,
  },
  registerUrl: {
    type: String,
    required: true,
  },
})

const page = usePage<SharedPage>()

const openGithub = function () {
  window.open('https://github.com/jegramos/devcamp', '_blank')
}

applyTheme()
</script>

<template>
  <section class="relative flex h-screen w-full flex-col bg-primary">
    <AppAnimatedFloaters class="z-10" />
    <div class="flex h-12 w-full items-center justify-center bg-amber-500 px-4 py-1 md:bg-primary md:py-1.5">
      <small class="text-xs font-bold text-surface-200 md:text-sm md:font-medium dark:font-bold">
        <i class="pi pi-sparkles mr-2"></i>
        <span>MVP Version</span>
        <i class="pi pi-sparkles ml-2 -rotate-180"></i>
      </small>
    </div>
    <!-- Start Head Section -->
    <section class="flex w-full items-center justify-between bg-primary-contrast px-4 py-2 md:p-4 dark:bg-surface-950">
      <AppLogo color="primary" icon-size-class="text-lg lg:text-xl" text-size-class="text-xl lg:text-2xl"></AppLogo>
      <div class="z-20 hidden md:block">
        <Button
          label="View on Github"
          icon="pi pi-github"
          size="small"
          class="!z-20 !border-none !bg-surface-950 !text-xs !text-surface-100"
          @click="openGithub"
        ></Button>
      </div>
    </section>
    <!-- End Header Section -->
    <!-- Start (iPad and Up View) Main Content -->
    <section class="mt-28 hidden w-full flex-grow justify-center bg-primary md:flex">
      <div
        class="relative flex w-full flex-col items-center rounded-2xl border-8 border-primary-contrast px-6 py-10 text-center md:w-[85%] lg:w-[60%]"
      >
        <p
          class="absolute -top-6 left-1/2 -translate-x-1/2 whitespace-normal bg-primary px-10 pb-6 font-stylish font-black text-primary-contrast md:whitespace-nowrap md:text-4xl lg:text-5xl"
        >
          Micro-CMS Portfolio Builder
        </p>
        <p class="mt-2 font-stylish text-lg font-bold text-primary-contrast lg:mt-4 lg:text-xl">
          A convenient destination for <i>building</i> and <i>showcasing</i> portfolios.
        </p>
        <p class="mt-2 text-lg text-primary-contrast dark:font-medium dark:leading-normal">
          Create, manage, and display your professional portfolio with ease using
          <span class="font-brand"> {{ page.props.appName }} </span>. Whether you're a freelancer, student, or professional, our
          platform offers a comprehensive suite of tools tailored to your needs.
        </p>
        <div class="absolute -right-2.5 top-1/2 h-[20%] w-[12px] -translate-y-1/2 bg-primary"></div>
        <div class="absolute -left-2.5 top-1/2 h-[20%] w-[12px] -translate-y-1/2 bg-primary"></div>
        <div class="absolute -bottom-2.5 left-1/2 h-[12px] w-[10%] -translate-x-1/2 bg-primary"></div>
        <div class="absolute -bottom-3 left-1/2 h-4 w-4 -translate-x-1/2 rounded-full bg-primary-contrast"></div>
        <div class="mt-6 flex space-x-6">
          <Link :href="registerUrl" class="z-20">
            <button class="z-20 w-60 rounded-lg bg-amber-600 px-6 py-3 font-bold text-primary-contrast dark:bg-amber-600">
              <FontAwesomeIcon :icon="faHeart" class="mr-2"></FontAwesomeIcon>
              Get Started for Free
            </button>
          </Link>
          <Link :href="loginUrl" class="z-20">
            <button
              class="w-60 rounded-lg bg-primary-contrast px-6 py-3 font-bold text-primary dark:bg-surface-950 dark:text-primary-contrast"
            >
              <FontAwesomeIcon :icon="faSignInAlt" class="mr-2"></FontAwesomeIcon>
              Sign in
            </button>
          </Link>
        </div>
      </div>
    </section>
    <!-- End (iPad and Up View) Main Content -->
    <!-- Start (Phone View) Main Content -->
    <section class="flex flex-col px-4 md:hidden">
      <span class="mb-4 mt-8 font-stylish text-3xl font-black text-primary-contrast"> A Micro-CMS Portfolio Builder </span>
      <p class="font-stylish text-sm font-bold leading-loose text-primary-contrast">
        Your Ultimate Destination for Building and Showcasing Portfolios.
      </p>
      <p class="text-sm leading-loose text-primary-contrast dark:font-medium dark:leading-normal">
        Create, manage, and display your professional portfolio with ease using <span class="font-brand">Devfolio</span>. Whether
        you're a freelancer, student, or professional, our platform offers a comprehensive suite of tools tailored to your needs.
      </p>
      <div class="mt-6 flex flex-col space-y-4">
        <Link :href="registerUrl" class="z-20">
          <button
            class="z-20 w-full rounded-lg bg-primary-contrast py-2 text-sm font-bold text-primary dark:bg-surface-950 dark:text-primary"
          >
            <FontAwesomeIcon :icon="faHeart" class="mr-2 text-sm"></FontAwesomeIcon>
            Get Started for Free
          </button>
        </Link>
        <Link :href="loginUrl" class="z-20">
          <button
            class="z-20 w-full rounded-lg bg-primary-contrast py-2 text-sm font-bold text-primary dark:bg-surface-950 dark:text-primary"
          >
            <FontAwesomeIcon :icon="faSignInAlt" class="mr-2 text-sm"></FontAwesomeIcon>
            Sign in
          </button>
        </Link>
      </div>
    </section>
    <!-- End (Phone View) Main Content -->
    <!-- Start Features Section -->
    <section class="h-full w-full items-start justify-center bg-primary md:pt-20 lg:items-end lg:pb-10">
      <!-- Start Phone View -->
      <div class="flex h-full items-end px-4 pb-4 text-xs text-primary-contrast md:hidden">
        <button class="z-20 flex items-center">
          <FontAwesomeIcon :icon="faGithub" class="mr-1"></FontAwesomeIcon>
          <a href="https://github.com/jegramos/devcamp" target="_blank">View on Github</a>
        </button>
      </div>
      <!-- End Phone View -->
      <!-- Start iPad and Up View -->
      <div
        class="hidden w-full items-center justify-evenly rounded-2xl p-6 font-brand text-primary-contrast transition-transform md:flex"
      >
        <button v-for="feature in features" :key="feature.name" class="group z-20 flex h-full flex-col items-center">
          <FontAwesomeIcon
            :icon="feature.icon"
            class="mb-2 rounded-full border-4 border-primary-contrast p-2 transition-transform group-hover:animate-bounce md:text-xl lg:text-3xl"
          >
          </FontAwesomeIcon>
          <span
            class="transition-transform group-hover:scale-110 md:text-base md:group-hover:text-lg lg:text-lg lg:group-hover:text-xl"
          >
            {{ feature.name }}
          </span>
        </button>
      </div>
      <!-- End iPad and Up View -->
    </section>
    <!-- End Features Section -->
  </section>
</template>
