<script lang="ts">
import CmsLayout from '@/Layouts/CmsLayout.vue'

export default {
  layout: CmsLayout,
}
</script>
<script setup lang="ts">
import { type PropType, ref } from 'vue'
import { useToast } from 'primevue/usetoast'
import { Head, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanel from 'primevue/tabpanel'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBookJournalWhills, faFlagCheckered, faGlobe, faShareAlt } from '@fortawesome/free-solid-svg-icons'
import ResumePageSubdomainPanel from '@/Pages/Builder/Resume/ResumePageSubdomainPanel.vue'
import ResumePageContentPanel from '@/Pages/Builder/Resume/ResumePageContentPanel.vue'
import type { SharedPage } from '@/Types/shared-page.ts'

export type TechExpertise = {
  name: string
  description: string
  logo: string
  proficiency: string
}

export type Social = {
  name: string
  url: string
}

export type Project = {
  title: string
  description: string
  cover: File | null
  links: Array<{ name: string; url: string }>
}

export type Timeline = {
  history: Array<{
    title: string
    description: string
    period: Array<string>
    company: string
    logo: string | null
    tags: Array<string>
  }>
  downloadable: File | string | null
}

export type Contact = {
  show: boolean
  availability_status: string
}

export type Service = {
  title: string
  description: string
  logo: string | null
  tags: Array<string>
}

const props = defineProps({
  baseSubdomain: {
    type: String,
    required: true,
  },
  showGetStarted: {
    type: Boolean,
    required: true,
  },
  storeSubdomainUrl: {
    type: String,
    required: true,
  },
  storeContentUrl: {
    type: String,
    required: true,
  },
  availableSocials: {
    type: Array<string>,
    required: true,
  },
  subdomain: {
    type: String,
    required: true,
  },
  hasExistingResume: {
    type: Boolean,
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
  themes: {
    type: Array<{ id: number; name: string }>,
    required: true,
  },
  themeId: {
    type: Number,
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

const toast = useToast()
const page = usePage<SharedPage>()
const showIntroduction = ref(props.showGetStarted)
const tabIndex = ref('0')

/** Start Subdomain */
const handleSubdomainSuccess = function (success: boolean) {
  if (!success) return

  toast.add({
    severity: 'success',
    summary: 'Resume Builder',
    detail: page.props.flash.CMS_SUCCESS,
    life: 3000,
  })

  if (!props.hasExistingResume) tabIndex.value = '1'
}
/** End Subdomain */
</script>

<template>
  <Head title="Resume Builder"></Head>
  <section class="relative mt-4 flex h-full w-full flex-col items-center lg:mt-0">
    <!-- Start Introduction -->
    <Card v-if="showIntroduction" class="flex max-w-4xl flex-col items-center justify-center rounded-lg p-4 text-center md:mt-4">
      <template #content>
        <h1 class="font-stylish text-xl font-bold md:text-2xl">Build Your Landing Page!</h1>
        <p class="mt-4">
          Here, you can add your technical expertise, work history roles, companies, dates, achievements, education degrees,
          institutions, certifications, and skills.
        </p>
      </template>
      <template #footer>
        <Button label="Start Now" class="mt-4 w-full md:w-auto md:px-8" size="large" @click="showIntroduction = false">
          <template #icon>
            <FontAwesomeIcon :icon="faFlagCheckered" />
          </template>
        </Button>
      </template>
    </Card>
    <!-- End Introduction -->
    <!-- Start Builder Steps -->
    <template v-if="!showIntroduction">
      <Tabs :value="tabIndex" class="w-full">
        <TabList>
          <Tab value="0" as="div" class="flex items-center gap-2 hover:cursor-pointer">
            <FontAwesomeIcon :icon="faGlobe" class="text-xs md:text-base" />
            <span class="whitespace-nowrap text-xs font-bold md:text-base">Subdomain</span>
          </Tab>
          <Tab value="1" as="div" class="flex items-center gap-2 hover:cursor-pointer">
            <FontAwesomeIcon :icon="faBookJournalWhills" class="text-xs md:text-base" />
            <span class="whitespace-nowrap text-xs font-bold md:hidden">Content</span>
            <span class="hidden whitespace-nowrap font-bold md:inline-block">Content Management</span>
          </Tab>
          <Tab value="2" as="div" class="flex items-center gap-2 hover:cursor-pointer">
            <FontAwesomeIcon :icon="faShareAlt" class="text-xs md:text-base" />
            <span class="whitespace-nowrap text-xs font-bold md:text-base">Sharing</span>
          </Tab>
        </TabList>
        <TabPanels>
          <!-- Start Subdomain Registration -->
          <TabPanel value="0" class="w-full">
            <ResumePageSubdomainPanel
              :subdomain="props.subdomain"
              :base-subdomain="props.baseSubdomain"
              :store-subdomain-url="props.storeSubdomainUrl"
              @success="handleSubdomainSuccess"
            />
          </TabPanel>
          <!-- End Subdomain Registration -->
          <TabPanel value="1">
            <ResumePageContentPanel
              :name="props.name"
              :titles="props.titles"
              :experiences="props.experiences"
              :socials="props.socials"
              :projects="props.projects"
              :available-socials="props.availableSocials"
              :timeline="props.timeline"
              :services="props.services"
              :contact="props.contact"
              :store-content-url="props.storeContentUrl"
              :themes="props.themes"
              :theme-id="props.themeId"
              :can-preview="props.canPreview"
              :preview-url="props.previewUrl"
              :tech-expertise="props.techExpertise"
            />
          </TabPanel>
          <TabPanel value="2">
            <div class="mx-auto flex h-[60vh] items-center justify-center">
              <div class="flex flex-col items-center">
                <div class="loader"></div>
                <h1 class="mt-4 font-stylish text-lg dark:text-surface-100">Nothing to see here. It's a work in progress.</h1>
              </div>
            </div>
          </TabPanel>
        </TabPanels>
      </Tabs>
    </template>
    <!-- End Builder Steps -->
  </section>
</template>

<style scoped>
.loader {
  display: inline-flex;
  gap: 10px;
}

.loader:before,
.loader:after {
  content: '';
  height: 20px;
  aspect-ratio: 1;
  border-radius: 50%;
  background:
    linear-gradient(#222 0 0) top/100% 40% no-repeat,
    radial-gradient(farthest-side, #000 95%, #0000) 50%/8px 8px no-repeat #fff;
  animation: l7 1.5s infinite alternate ease-in;
}

@keyframes l7 {
  0%,
  70% {
    background-size:
      100% 40%,
      8px 8px;
  }
  85% {
    background-size:
      100% 120%,
      8px 8px;
  }
  100% {
    background-size:
      100% 40%,
      8px 8px;
  }
}
</style>
