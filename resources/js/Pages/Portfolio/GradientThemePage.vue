<script setup lang="ts">
import { nextTick, type Ref, ref } from 'vue'
import { useIntervalFn } from '@vueuse/core'
import { Head } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faLinkedin, faGithub, faYoutubeSquare, faFacebook, faWhatsapp, faViber } from '@fortawesome/free-brands-svg-icons'
import AppAnimatedFloaters from '@/Components/AppAnimatedFloaters.vue'
import TextHighlight from '@/Components/Animated/TextHighlight.vue'
import MarqueeElements from '@/Components/Animated/MarqueeElements.vue'
import BorderBeam from '@/Components/Animated/BorderBeam.vue'
import GlowBorder from '@/Components/Animated/GlowBorder.vue'
import { faEnvelope, faGear } from '@fortawesome/free-solid-svg-icons'
import TracingBeam from '@/Components/Animated/TracingBeam.vue'
import MeteorShower from '@/Components/Animated/MeteorShower.vue'
import TextSparkle from '@/Components/Animated/TextSparkle.vue'
import GradientButton from '@/Components/Animated/GradientButton.vue'

/** Start Before the Fold */
const name = ref('Jego Carlo Ramos')

// Titles
const titles = ref(['Technology Leader', 'Software Engineer', 'Web Architect', 'Professional Consultant'])
const currentTitle = ref(titles.value[0])
const showTitle = ref(true)
useIntervalFn(function () {
  const currentIndex = titles.value.indexOf(currentTitle.value)
  const nextIndex = (currentIndex + 1) % titles.value.length
  currentTitle.value = titles.value[nextIndex]
  showTitle.value = false
  nextTick(() => {
    showTitle.value = true
  })
}, 4000)

// Navigation
const techExpertiseEl = ref<HTMLElement>()
const professionalTimelineEl = ref<HTMLElement>()
const projectHighlightsEl = ref<HTMLElement>()
const servicesEl = ref<HTMLElement>()
const navigationLinks = ref([
  { label: 'Tech Expertise', href: '#', action: () => scrollTo(techExpertiseEl) },
  { label: 'Timeline', href: '#', action: () => scrollTo(professionalTimelineEl) },
  { label: 'Project Highlights', href: '#', action: () => scrollTo(projectHighlightsEl) },
  { label: 'Services', href: '#', action: () => scrollTo(servicesEl) },
])

// Socials
const socials = ref([
  { label: 'LinkedIn', logo: faLinkedin },
  { label: 'Github', logo: faGithub },
  { label: 'Email', logo: faEnvelope },
  { label: 'Youtube', logo: faYoutubeSquare },
  { label: 'Facebook', logo: faFacebook },
  { label: 'WhatsApp', logo: faWhatsapp },
  { label: 'Viber', logo: faViber },
])

// Experience Summary
const experienceSummary = ref([
  '7+ years of Full-Stack Software Engineering',
  '6+ years of Leading & Building Agile Teams',
  'Works remotely with people from all over the world',
  'AWS SAA Certified',
  'Built CI/CD pipelines with Docker, Kubernetes, and Gitlab CI',
  'Your Make-It-Happen Guy',
])

const scrollTo = function (view: Ref<HTMLElement | undefined>) {
  view.value?.scrollIntoView({ behavior: 'smooth' })
}

/** End Before the Fold */

/** Start Tech Expertise */
type Technology = {
  id: number
  name: string
  description: string
  logo?: string
  type: string
}
const techStack = {
  current: <Technology[]>[
    {
      id: 1,
      name: 'Laravel',
      description: 'Fullstack PHP framework',
      logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Laravel.svg/1969px-Laravel.svg.png',
      type: 'Framework',
    },
    {
      id: 2,
      name: 'PHP',
      description: 'Server-side programming language',
      type: 'Language',
      logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/27/PHP-logo.svg/2560px-PHP-logo.svg.png',
    },
    {
      id: 3,
      name: 'VueJS',
      description: 'JavaScipt Framework for building SPAs',
      type: 'Framework',
      logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/95/Vue.js_Logo_2.svg/1200px-Vue.js_Logo_2.svg.png',
    },
    {
      id: 4,
      name: 'JavaScript & TypeScript',
      description: 'Front-end Programming Language',
      type: 'Language',
      logo: 'https://skillforge.com/wp-content/uploads/2020/10/javascript.png',
    },
    {
      id: 5,
      name: 'MySQL',
      description: 'Relational Database Management System',
      type: 'Database',
      logo: 'https://www.vectorlogo.zone/logos/mysql/mysql-official.svg',
    },
    {
      id: 6,
      name: 'Redis',
      description: 'In-memory Data Store',
      type: 'Database',
      logo: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRS0K1pid1KZGG8O7heTiW4Hsck66g3Nsc0qg&s',
    },
    {
      id: 7,
      name: 'AWS',
      description: 'Leading Cloud Computing Service',
      type: 'Cloud Platform',
      logo: 'https://kineticit.com.au/wp-content/uploads/2022/10/AWS_logo-600x600.png',
    },
    {
      id: 8,
      name: 'Docker',
      description: 'Ships and runs apps using containers',
      type: 'Containerization',
      logo: 'https://cdn4.iconfinder.com/data/icons/logos-and-brands/512/97_Docker_logo_logos-512.png',
    },
    {
      id: 9,
      name: 'Kubernetes',
      description: 'Automated container orchestrator',
      type: 'Container Orchestration',
      logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/39/Kubernetes_logo_without_workmark.svg/2109px-Kubernetes_logo_without_workmark.svg.png',
    },
    {
      id: 10,
      name: 'Gitlab CI',
      description: "Gitlab's module for CI/CD",
      type: 'CI/CD',
      logo: 'https://cdn.iconscout.com/icon/free/png-256/free-gitlab-logo-icon-download-in-svg-png-gif-file-formats--company-brand-world-logos-vol-4-pack-icons-282507.png?f=webp&w=256',
    },
    {
      id: 11,
      name: 'TailwindCSS',
      description: 'Utility-first CSS Framework',
      type: 'Framework',
      logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d5/Tailwind_CSS_Logo.svg/2560px-Tailwind_CSS_Logo.svg.png',
    },
    {
      id: 17,
      name: 'Pusher',
      description: 'Platform for bi-directional communication',
      type: 'Platform',
      logo: 'https://cdn-images-1.medium.com/max/1200/1*ndvP5QZDfkgN91YDW-NaRw.png',
    },
  ],
  previous: <Technology[]>[
    {
      id: 12,
      name: 'NodeJS',
      description: 'JavaScript Run-time Engine (or whatever the hell it is now)',
      type: 'Run-time Engine',
      logo: 'https://static-00.iconduck.com/assets.00/node-js-icon-1817x2048-g8tzf91e.png',
    },
    {
      id: 13,
      name: 'ExpressJS',
      description: 'JavaScript Micro-framework',
      type: 'Framework',
      logo: 'https://1.bp.blogspot.com/-jkSmywQ57sA/Wer3KKSqgaI/AAAAAAAACc4/07TexMsBBI4v7WlVKo76YvxM3TvrMxIdwCLcBGAs/s640/express.js.png',
    },
    {
      id: 14,
      name: 'MongoDB',
      description: 'No-SQL Database',
      type: 'Database',
      logo: 'https://miro.medium.com/v2/resize:fit:512/1*doAg1_fMQKWFoub-6gwUiQ.png',
    },
    {
      id: 15,
      name: 'Python',
      description: 'General Purpose Programming Language',
      type: 'Language',
      logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0a/Python.svg/1024px-Python.svg.png',
    },
    {
      id: 16,
      name: 'Django',
      description: 'Python FullStack Web Framework',
      type: 'Framework',
      logo: 'https://www.svgrepo.com/show/353657/django-icon.svg',
    },
    {
      id: 18,
      name: 'Android',
      description: 'Native Android Development',
      type: 'Framework',
      logo: 'https://www.freeiconspng.com/thumbs/android-icon/green-android-icon-31.png',
    },
    {
      id: 19,
      name: 'Java',
      description: 'General Purpose Programming Language',
      type: 'Language',
      logo: 'https://static-00.iconduck.com/assets.00/java-original-icon-756x1024-j3tx11wk.png',
    },
    {
      id: 20,
      name: 'Kotlin',
      description: 'Programming Language based on Java',
      type: 'Language',
      logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Kotlin_Icon.svg/1200px-Kotlin_Icon.svg.png',
    },
    {
      id: 21,
      name: 'Selenium',
      description: 'Web UI automation framework',
      type: 'Framework',
      logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d5/Selenium_Logo.png/1200px-Selenium_Logo.png',
    },
    {
      id: 22,
      name: 'Appium',
      description: 'App UI automation framework',
      type: 'Framework',
      logo: 'https://static-00.iconduck.com/assets.00/appium-icon-2044x2048-8eq3vjix.png',
    },
    {
      id: 23,
      name: 'Flutter',
      description: 'Hybrid Mobile App Framework',
      type: 'Framework',
      logo: 'https://storage.googleapis.com/cms-storage-bucket/0dbfcc7a59cd1cf16282.png',
    },
    {
      id: 24,
      name: 'Socket.IO',
      description: 'Bi-directional communication library',
      type: 'Library',
      logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/96/Socket-io.svg/1024px-Socket-io.svg.png',
    },
  ],
}
/** End Tech Expertise */
/** Start Work Timeline */
const workTimeline = [
  {
    id: 1,
    title: 'Software Engineer & Architect',
    employment: 'Consultant',
    period: 'May 2023 - October 2024',
    company: 'Philippine Department of Social Welfare and Development',
    companyLogoUrl: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQH33vBvvKVcOFq5XQYZP6EPQPQ9EX_gDX7pg&s',
    description: [
      'Sit duis est minim proident non nisi velit non consectetur. Esse adipisicing laboris consectetur enim ipsum reprehenderit eu deserunt Lorem ut aliqua anim do. Duis cupidatat qui irure cupidatat incididunt incididunt enim magna id est qui sunt fugiat. Laboris do duis pariatur fugiat Lorem aute sit ullamco. Qui deserunt non reprehenderit dolore nisi velit exercitation Lorem qui do enim culpa. Aliqua eiusmod in occaecat reprehenderit laborum nostrud fugiat voluptate do Lorem culpa officia sint labore. Tempor consectetur excepteur ut fugiat veniam commodo et labore dolore commodo pariatur.',
      'Dolor minim irure ut Lorem proident. Ipsum do pariatur est ad ad veniam in commodo id reprehenderit adipisicing. Proident duis exercitation ad quis ex cupidatat cupidatat occaecat adipisicing.',
      'Tempor quis dolor veniam quis dolor. Sit reprehenderit eiusmod reprehenderit deserunt amet laborum consequat adipisicing officia qui irure id sint adipisicing. Adipisicing fugiat aliqua nulla nostrud. Amet culpa officia aliquip deserunt veniam deserunt officia adipisicing aliquip proident officia sunt.',
    ],
    badges: ['Laravel', 'VueJS', 'Agile', 'Scrum', 'Integration'],
  },
  {
    id: 1,
    title: 'Software Engineer & Architect',
    employment: 'Consultant',
    period: 'May 2023 - October 2024',
    company: 'Philippine Department of Social Welfare and Development',
    companyLogoUrl: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQH33vBvvKVcOFq5XQYZP6EPQPQ9EX_gDX7pg&s',
    description: [
      'Sit duis est minim proident non nisi velit non consectetur. Esse adipisicing laboris consectetur enim ipsum reprehenderit eu deserunt Lorem ut aliqua anim do. Duis cupidatat qui irure cupidatat incididunt incididunt enim magna id est qui sunt fugiat. Laboris do duis pariatur fugiat Lorem aute sit ullamco. Qui deserunt non reprehenderit dolore nisi velit exercitation Lorem qui do enim culpa. Aliqua eiusmod in occaecat reprehenderit laborum nostrud fugiat voluptate do Lorem culpa officia sint labore. Tempor consectetur excepteur ut fugiat veniam commodo et labore dolore commodo pariatur.',
      'Dolor minim irure ut Lorem proident. Ipsum do pariatur est ad ad veniam in commodo id reprehenderit adipisicing. Proident duis exercitation ad quis ex cupidatat cupidatat occaecat adipisicing.',
      'Tempor quis dolor veniam quis dolor. Sit reprehenderit eiusmod reprehenderit deserunt amet laborum consequat adipisicing officia qui irure id sint adipisicing. Adipisicing fugiat aliqua nulla nostrud. Amet culpa officia aliquip deserunt veniam deserunt officia adipisicing aliquip proident officia sunt.',
    ],
    badges: [
      'Laravel',
      'VueJS',
      'Agile',
      'Scrum',
      'Inter-Agency System Integration',
      'Agile Development',
      'Technical Workshops',
      'Project Management',
      'Requirements Analysis',
    ],
  },
  {
    id: 2,
    title: 'Software Engineer & Architect',
    employment: 'Consultant',
    period: 'May 2023 - October 2024',
    company: 'Philippine Department of Social Welfare and Development',
    companyLogoUrl: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQH33vBvvKVcOFq5XQYZP6EPQPQ9EX_gDX7pg&s',
    description: [
      'Sit duis est minim proident non nisi velit non consectetur. Esse adipisicing laboris consectetur enim ipsum reprehenderit eu deserunt Lorem ut aliqua anim do. Duis cupidatat qui irure cupidatat incididunt incididunt enim magna id est qui sunt fugiat. Laboris do duis pariatur fugiat Lorem aute sit ullamco. Qui deserunt non reprehenderit dolore nisi velit exercitation Lorem qui do enim culpa. Aliqua eiusmod in occaecat reprehenderit laborum nostrud fugiat voluptate do Lorem culpa officia sint labore. Tempor consectetur excepteur ut fugiat veniam commodo et labore dolore commodo pariatur.',
      'Dolor minim irure ut Lorem proident. Ipsum do pariatur est ad ad veniam in commodo id reprehenderit adipisicing. Proident duis exercitation ad quis ex cupidatat cupidatat occaecat adipisicing.',
      'Tempor quis dolor veniam quis dolor. Sit reprehenderit eiusmod reprehenderit deserunt amet laborum consequat adipisicing officia qui irure id sint adipisicing. Adipisicing fugiat aliqua nulla nostrud. Amet culpa officia aliquip deserunt veniam deserunt officia adipisicing aliquip proident officia sunt.',
    ],
    badges: ['Laravel', 'VueJS', 'Agile', 'Scrum', 'Integration'],
  },
  {
    id: 3,
    title: 'Software Engineer & Architect',
    employment: 'Consultant',
    period: 'May 2023 - October 2024',
    company: 'Philippine Department of Social Welfare and Development',
    companyLogoUrl: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQH33vBvvKVcOFq5XQYZP6EPQPQ9EX_gDX7pg&s',
    description: [
      'Sit duis est minim proident non nisi velit non consectetur. Esse adipisicing laboris consectetur enim ipsum reprehenderit eu deserunt Lorem ut aliqua anim do. Duis cupidatat qui irure cupidatat incididunt incididunt enim magna id est qui sunt fugiat. Laboris do duis pariatur fugiat Lorem aute sit ullamco. Qui deserunt non reprehenderit dolore nisi velit exercitation Lorem qui do enim culpa. Aliqua eiusmod in occaecat reprehenderit laborum nostrud fugiat voluptate do Lorem culpa officia sint labore. Tempor consectetur excepteur ut fugiat veniam commodo et labore dolore commodo pariatur.',
      'Dolor minim irure ut Lorem proident. Ipsum do pariatur est ad ad veniam in commodo id reprehenderit adipisicing. Proident duis exercitation ad quis ex cupidatat cupidatat occaecat adipisicing.',
      'Tempor quis dolor veniam quis dolor. Sit reprehenderit eiusmod reprehenderit deserunt amet laborum consequat adipisicing officia qui irure id sint adipisicing. Adipisicing fugiat aliqua nulla nostrud. Amet culpa officia aliquip deserunt veniam deserunt officia adipisicing aliquip proident officia sunt.',
    ],
    badges: ['Laravel', 'VueJS', 'Agile', 'Scrum', 'Integration'],
  },
]
/** End Work Timeline */

/** Start Highlighted Projects */
const projects = ref([
  {
    id: 1,
    name: 'DevCamp',
    description:
      'A convenient destination for tech professions to showcase their portfolios, write blogs, and schedule client appointments',
    image: 'https://i.ytimg.com/vi/tTeKW4vhmGU/maxresdefault.jpg',
    links: [
      {
        label: 'Live',
        url: 'https://app.devcamp.site',
      },
      { label: 'Github', url: 'https://github.com/jegramos/devcamp' },
    ],
    badges: ['Laravel', 'VueJS', 'InertiaJS', 'TailwindCSS', 'TypeScript'],
  },
  {
    id: 2,
    name: 'DSWD Webkits',
    description:
      'Pre-built API and SPA components designed for the Digital Transformation (DX) Team to accelerate secure and consistent digital service development',
    image: 'https://journal.com.ph/wp-content/uploads/2024/02/4Ps-digital-financial-literacy-1200x800.jpg',
    links: [
      {
        label: 'Learn More',
        url: 'https://www.pna.gov.ph/articles/1201723',
      },
    ],
    badges: ['Laravel', 'VueJS', 'InertiaJS', 'TailwindCSS', 'TypeScript'],
  },
  {
    id: 7,
    name: 'Software Engineering Workshops',
    description: 'Conducted software development workshops for the different field offices of DSWD all over the Philippines',
    image: 'https://devcamp-prod-storage-public.s3.ap-southeast-1.amazonaws.com/viber_image_2024-12-22_12-07-44-388.jpg',
    links: [
      {
        label: 'Learn More',
        url: 'https://www.dswd.gov.ph/dswd-pioneers-digital-transformation-in-government-agencies/',
      },
    ],
    badges: ['Workshop', 'Mentorship'],
  },
  {
    id: 5,
    name: 'HousePass',
    description: 'A complex-wide access control system for the House of Representatives of the Philippines',
    image: 'https://upload.wikimedia.org/wikipedia/commons/c/c8/2011_Philippine_State_of_the_Nation_Address.jpg',
    links: [
      { label: 'Live', url: 'https://housepass.hrep.online/login' },
      {
        label: 'Learn More',
        url: 'https://politiko.com.ph/2023/05/24/house-issues-memo-on-marcos-second-sona-second-regular-session-opening/daily-feed/',
      },
    ],
    badges: ['Laravel', 'VueJS', 'Raspberry Pi', 'Printing', 'Scanning', 'Integrations'],
  },
  {
    id: 4,
    name: 'Aloware Integrations',
    description: 'Robust third-party platform integration features for Aloware',
    image: 'https://aloware.com/wp-content/uploads/2022/01/Aloware-x-HubSpot-reviews-thumb.png',
    links: [{ label: 'Learn More', url: 'https://aloware.com/integrations/' }],
    badges: ['Laravel', 'VueJS', 'Integrations'],
  },
  {
    id: 3,
    name: 'DSWD CI/CD Infrastructure',
    description:
      'Complete CI/CD pipeline built with Gitlab CI, Docker, and Kubernetes on AWS (EKS, ECR, Secrets Manager, and more)',
    image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRKWtp7wHnE5xdFlBeVwgi7lPow29H_9JtsTw&s',
    badges: ['Laravel', 'VueJS', 'Raspberry Pi', 'Printing', 'Scanning', 'Integrations'],
  },
])
/** End Highlighted Projects */
/** Start Services Section */
const services = [
  {
    id: 1,
    title: 'FullStack Web Development',
    description:
      "I've developed APIs, SPAs, and full stack web applications as an employee and consultant for various companies and government agencies",
    badges: ['API', 'SPA', 'Monolith', 'Microservices', 'Testing'],
    iconUrl: 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Laravel.svg/1200px-Laravel.svg.png',
  },
  {
    id: 2,
    title: 'Web Application Architecture',
    description:
      "I've been helping companies implement secure and maintainable codebases as a tech lead, senior software engineer, and software architect",
    badges: ['Clean Code', 'Distributed Systems', 'AWS'],
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/5339/5339181.png',
  },
  {
    id: 3,
    title: 'DevOps & Automation',
    description:
      "I've been recently been dabbling with DevOps and have deployed complete CI/CD pipelines for government agencies",
    badges: ['Docker', 'Kubernetes', 'Gitlab CI'],
    iconUrl: 'https://cdn4.iconfinder.com/data/icons/logos-and-brands/512/97_Docker_logo_logos-512.png',
  },
  {
    id: 4,
    title: 'Automated UI Testing',
    description:
      "I've started my career as QA Automation Engineer. My earliest projects where UI automation scripts and farms for Samsung PH and private start-ups",
    badges: ['Selenium', 'Appium', 'UI Automator'],
    iconUrl: 'https://static-00.iconduck.com/assets.00/appium-icon-2044x2048-8eq3vjix.png',
  },
  {
    id: 5,
    title: 'Technical Workshops',
    description:
      "I've been conducting programming and software management workshops for DSWD, Samsung PH, and other private companies",
    badges: ['Training', 'Mentorship'],
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/5339/5339181.png',
  },
]
/** End Services Section */
</script>
<template>
  <Head title="Portfolio"></Head>
  <!-- Start Header -->
  <section class="flex h-full w-full flex-col items-center justify-center bg-surface-900 text-surface-0">
    <!-- Start Before the Fold (Desktop) -->
    <section class="relative hidden h-[100vh] w-full flex-col items-center justify-center p-1 md:flex lg:p-5">
      <AppAnimatedFloaters class="opacity-30" />
      <div class="grid w-full grid-cols-2 gap-x-10 md:w-[80%]">
        <!-- Start Name -->
        <div class="flex flex-col justify-center">
          <span class="font-stylish text-lg font-bold lg:text-5xl">
            <span class="text-orange-400">&lt;</span>
            <span class="mx-2 font-writing text-3xl lg:text-6xl">{{ name }}</span>
            <span class="text-orange-500">/&gt;</span>
          </span>
          <h2 class="mt-6 font-stylish text-lg lg:ml-10 lg:text-left lg:text-2xl">
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
        <!-- Start Navigation -->
        <div class="z-20 flex h-[80vh] flex-col justify-center gap-y-10 text-right text-surface-400">
          <span v-for="links in navigationLinks" :key="links.label" class="transform font-stylish text-lg lg:text-2xl">
            <span
              class="border-orange-500 transition-all hover:cursor-pointer hover:border-r-4 hover:pr-2 hover:text-3xl hover:font-bold hover:text-surface-0"
              @click="links.action"
            >
              {{ links.label }}
            </span>
          </span>
          <div class="flex justify-end gap-x-6">
            <button
              v-for="social in socials"
              :key="social.label"
              class="transition-transform hover:scale-125 hover:cursor-pointer hover:text-surface-0"
            >
              <FontAwesomeIcon :icon="social.logo" class="text-lg lg:text-2xl" />
            </button>
          </div>
        </div>
        <!-- End Navigation -->
      </div>
      <!-- Start Experience Summary -->
      <div class="absolute bottom-0 flex w-full items-center bg-gradient-to-r from-orange-500 to-red-500 py-1">
        <MarqueeElements pause-on-hover class="[--duration:40s]">
          <div
            v-for="experience in experienceSummary"
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
      <!-- Start Navigation -->
      <div class="absolute top-0 z-20 flex flex-wrap justify-center gap-4 py-4 text-xs text-surface-400">
        <span v-for="links in navigationLinks" :key="links.label" class="transform font-stylish">
          <span class="transition-all hover:cursor-pointer hover:text-surface-0" @click="links.action">
            {{ links.label }}
          </span>
        </span>
      </div>
      <!-- End Navigation -->

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
            v-for="experience in experienceSummary"
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
      ref="techExpertiseEl"
      class="bg-surface-850 relative flex min-h-[100vh] w-full flex-col items-center overflow-hidden pb-8 pt-8"
    >
      <div class="mb-8 flex w-full items-center justify-center text-3xl md:w-[80%] md:text-5xl">
        <span data-aos="fade-right" class="font-stylish text-orange-400">&lt;</span>
        <span data-aos="fade-up" class="mx-1 mb-2 font-writing"> Tech Expertise </span>
        <span data-aos="fade-left" class="font-stylish text-orange-500">/&gt;</span>
      </div>
      <div class="flex flex-col gap-x-16 px-3 md:w-[90%] md:flex-row md:px-0">
        <!-- Start Current Tools -->
        <div data-aos="fade-right" class="flex h-full w-full flex-col">
          <h1 class="mb-4 text-left font-stylish text-sm font-black uppercase text-surface-500 lg:text-center">Currently Uses</h1>
          <div class="grid h-full w-full grid-cols-1 flex-col gap-4 lg:grid-cols-2">
            <div
              v-for="tech in techStack.current"
              :key="tech.id"
              class="flex flex-grow rounded-lg transition-transform hover:scale-105"
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
        <!-- End Current Tools -->
        <!-- Start Previous Tools -->
        <div data-aos="fade-left" class="mt-10 flex h-full w-full flex-col md:mt-0">
          <h1 class="mb-4 text-left font-stylish text-sm font-black uppercase text-surface-500 lg:text-center">
            Previously used
          </h1>
          <div class="grid h-full w-full grid-cols-1 flex-col gap-4 md:px-0 lg:grid-cols-2">
            <div
              v-for="tech in techStack.previous"
              :key="tech.id"
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
        <!-- End Previous Tools -->
      </div>
      <BorderBeam color-from="#ffaa40" color-to="#ec4899" :size="250" :duration="12" :delay="9" :border-width="4" />
    </section>
    <!-- End Tech Expertise -->
    <!-- Start Work Timeline -->
    <section ref="professionalTimelineEl" class="relative flex min-h-[100vh] w-full flex-col items-center gap-y-4 pb-8 pt-8">
      <div class="mb:0 flex w-full items-center justify-center text-3xl md:mb-4 md:text-5xl lg:mb-8">
        <span data-aos="fade-right" class="font-stylish text-orange-400">&lt;</span>
        <span data-aos="fade-up" class="mx-1 mb-2 font-writing">My Journey So Far</span>
        <span data-aos="fade-left" class="font-stylish text-orange-500">/&gt;</span>
      </div>
      <div class="w-full items-center justify-center px-3 md:px-8">
        <TracingBeam class="px-0 md:px-6">
          <div data-aos="fade-up" class="flex w-full justify-start md:justify-end">
            <button
              class="mb-2 rounded-lg border border-surface-500 px-2 py-1 text-sm text-surface-500 transition-all hover:scale-105 hover:border-orange-500 hover:text-orange-500"
            >
              <i class="pi pi-cloud-download mr-1"></i>
              Download
            </button>
          </div>
          <div class="relative mx-auto w-full max-w-4xl pt-1 antialiased">
            <div v-for="work in workTimeline" :key="work.id" data-aos="fade-up" class="mb-10 flex rounded-lg bg-surface-800">
              <GlowBorder
                class="relative flex w-full items-start gap-x-4 rounded-lg border border-surface-500 bg-inherit px-2 py-6"
                :color="['#fe7c8b', '#f34223', '#f50303']"
              >
                <img :src="work.companyLogoUrl" alt="Company Logo" class="hidden h-12 w-12 object-cover md:block" />
                <div class="flex flex-col">
                  <span class="font-stylish text-xl font-bold">{{ work.title }}</span>
                  <span class="">{{ work.company }}</span>
                  <small class="text-surface-500">{{ work.period }}</small>
                  <p v-for="(desc, idx) in work.description" :key="idx" class="font-sm mt-4">
                    {{ desc }}
                  </p>
                  <div class="mt-4 flex flex-wrap gap-2">
                    <small
                      v-for="(badge, idx) in work.badges"
                      :key="idx"
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
      ref="projectHighlightsEl"
      class="relative flex min-h-[100vh] w-full flex-col items-center gap-y-4 px-3 pb-8 pt-8 md:w-[80%] md:px-0"
    >
      <div class="mb:4 flex w-full items-center justify-center text-3xl md:mb-4 md:text-5xl lg:mb-8">
        <span data-aos="fade-right" class="font-stylish text-orange-400">&lt;</span>
        <span data-aos="fade-up" class="mx-1 mb-2 font-writing">Project Highlights</span>
        <span data-aos="fade-left" class="font-stylish text-orange-500">/&gt;</span>
      </div>
      <div class="mt-4 grid grid-cols-1 gap-4 md:mt-0 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="project in projects"
          :key="project.id"
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
              <h1 class="relative z-10 mb-2 font-stylish text-xl font-bold">{{ project.name }}</h1>
              <img :src="project.image" alt="Project Image" class="relative z-10 h-32 w-full rounded-lg object-cover md:h-48" />
              <p class="relative z-10 my-4 text-base font-normal text-surface-500">
                {{ project.description }}
              </p>

              <div v-if="project.links" class="align-self-end flex h-full w-full flex-wrap items-end justify-end gap-2">
                <a
                  v-for="link in project.links"
                  :key="link.label"
                  :href="link.url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="flex rounded-lg border border-surface-500 px-2 py-1 text-surface-300"
                >
                  <small>{{ link.label }}</small>
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
      ref="servicesEl"
      class="relative flex min-h-[100vh] w-full flex-col items-center gap-y-4 px-3 pb-8 pt-8 md:w-[80%] md:px-0"
    >
      <div class="mb:4 flex w-full items-center justify-center text-3xl md:mb-4 md:text-5xl lg:mb-8">
        <span data-aos="fade-right" class="font-stylish text-orange-400">&lt;</span>
        <span data-aos="fade-up" class="mx-1 mb-2 font-writing">Services</span>
        <span data-aos="fade-left" class="font-stylish text-orange-500">/&gt;</span>
      </div>
      <div class="mt-4 grid grid-cols-1 gap-x-4 gap-y-6 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="service in services" :key="service.id" data-aos="fade-up" class="border-rounded flex flex-col">
          <GlowBorder
            class="relative flex h-full w-full flex-col items-start gap-x-4 rounded-lg border border-surface-500 bg-inherit px-4 py-6"
            :color="['#fe7c8b', '#f34223', '#f50303']"
          >
            <img :src="service.iconUrl" class="w-10 grayscale" alt="Icon" />
            <span class="mt-3 text-lg font-bold">{{ service.title }}</span>
            <span class="mt-1 text-surface-500">{{ service.description }}</span>
            <div class="mt-4 flex flex-wrap gap-2">
              <div v-for="badge in service.badges" :key="badge" class="rounded-lg bg-surface-800">
                <small class="flex px-2 py-1">{{ badge }}</small>
              </div>
            </div>
          </GlowBorder>
        </div>
      </div>
    </section>
    <!-- End Services -->
    <!-- Start Contact Me -->
    <section class="relative flex min-h-[100vh] w-full flex-col items-center gap-y-4 px-3 pb-8 pt-8 md:w-[80%] md:px-0">
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
      <div data-aos="fade-up" class="mt-4 flex w-full max-w-screen-lg flex-col rounded-lg bg-surface-800">
        <div class="flex w-full flex-col gap-4 rounded-lg p-4 md:p-8">
          <div class="flex w-full flex-col gap-4 md:flex-row">
            <input
              class="w-full rounded-lg border border-surface-600 bg-transparent px-3 py-2 text-surface-0 shadow-sm transition duration-300 placeholder:text-surface-400 focus:shadow focus:outline-none"
              placeholder="Your name *"
            />
            <input
              class="w-full rounded-lg border border-surface-600 bg-transparent px-3 py-2 text-surface-0 shadow-sm transition duration-300 placeholder:text-surface-400 focus:shadow focus:outline-none"
              placeholder="Email Address *"
            />
          </div>
          <div class="flex w-full">
            <textarea
              rows="5"
              class="w-full rounded-lg border border-surface-600 bg-transparent px-3 py-2 text-surface-0 shadow-sm transition duration-300 placeholder:text-surface-400 focus:shadow focus:outline-none"
              placeholder="What do you want to work on?"
            />
          </div>
          <div class="flex justify-end md:mt-3">
            <GradientButton bg-color="#27272A">
              <i class="pi pi-send mr-2 text-sm"></i>
              <span class="text-sm">SEND</span>
            </GradientButton>
          </div>
        </div>
      </div>
      <p class="mt-4 text-sm text-surface-500">or reach me through</p>
      <div class="mt-1 flex items-center justify-end gap-x-6">
        <button
          v-for="social in socials"
          :key="social.label"
          class="text-surface-500 transition-transform hover:scale-125 hover:cursor-pointer hover:text-surface-0"
        >
          <FontAwesomeIcon :icon="social.logo" class="text-lg lg:text-4xl" />
        </button>
      </div>
    </section>
    <!-- End Contact Me -->
    <!-- Start Footer -->
    <footer class="flex w-full items-center justify-center p-4">
      <small class="text-surface-500">© {{ new Date().getFullYear() }} {{ name }}</small>
    </footer>
    <!-- End Footer -->
  </section>
  <!-- End Header -->
</template>
