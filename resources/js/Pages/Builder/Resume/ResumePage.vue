<script lang="ts">
import CmsLayout from '@/Layouts/CmsLayout.vue'

export default {
  layout: CmsLayout,
}
</script>
<script setup lang="ts">
import { ref } from 'vue'
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
          <Tab value="0" as="div" class="flex items-center gap-2">
            <FontAwesomeIcon :icon="faGlobe" class="text-xs md:text-base" />
            <span class="whitespace-nowrap text-xs font-bold md:text-base">Subdomain</span>
          </Tab>
          <Tab value="1" as="div" class="flex items-center gap-2">
            <FontAwesomeIcon :icon="faBookJournalWhills" class="text-xs md:text-base" />
            <span class="whitespace-nowrap text-xs font-bold md:hidden">Content</span>
            <span class="hidden whitespace-nowrap font-bold md:inline-block">Content Management</span>
          </Tab>
          <Tab value="2" as="div" class="flex items-center gap-2">
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
              :available-socials="props.availableSocials"
              :store-content-url="props.storeContentUrl"
              :themes="props.themes"
              :theme-id="props.themeId"
            />
          </TabPanel>
        </TabPanels>
      </Tabs>
    </template>
    <!-- End Builder Steps -->
  </section>
</template>

<style scoped></style>
