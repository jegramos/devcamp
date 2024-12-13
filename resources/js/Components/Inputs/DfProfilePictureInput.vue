<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useImage } from '@vueuse/core'
import Skeleton from 'primevue/skeleton'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import { mimeTypeRule, maxFileSizeRule } from '@/Utils/vuelidate-custom-validators.ts'
import type { SharedPage } from '@/Types/shared-page.ts'

const page = usePage<SharedPage>()

const props = defineProps({
  profilePictureUrl: {
    type: [String, null],
    required: true,
  },
  uploadProfilePictureUrl: {
    type: String,
    required: true,
  },
})

const imageSource = ref<string | null>(props.profilePictureUrl)
const { isLoading } = useImage({ src: imageSource.value || '' })

/** Image Selection */
const file = ref<HTMLInputElement | null>(null)
const handleBrowseImages = () => {
  file.value?.click()
}

/** Handle Image Change and Validation */
const form = useForm<{ photo: File | null }>({
  photo: null,
})

const toast = useToast()
const handleOnChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target?.files?.[0]
  form.photo = file || null

  if (!file) return

  // Check if the file is an image
  if (!mimeTypeRule(['image/jpeg', 'image/jpg', 'image/svg', 'image/png', 'image/bmp'])(file)) {
    form.photo = null
    toast.add({ severity: 'error', summary: 'Profile Picture', detail: 'Please select a valid image file', life: 8000 })
    return
  }

  // Image must not exceed 5Mb
  if (!maxFileSizeRule(5)(file)) {
    form.photo = null
    toast.add({ severity: 'error', summary: 'Profile Picture', detail: 'The image must not exceed 5MB', life: 8000 })
    return
  }

  // read the file to display it
  const reader = new FileReader()
  reader.readAsDataURL(file)
  reader.onload = (e) => (imageSource.value = e.target?.result?.toString() || null)
}

// We show the upload action button when the image is changed
const showActionButton = ref(false)
watch(
  () => form.photo,
  () => {
    if (form.photo) showActionButton.value = true
  }
)

/** Handle Image Uploading */
const handleImageUpload = () => {
  if (!form.photo) return

  form.post(props.uploadProfilePictureUrl, {
    onSuccess: function () {
      if (page.props.flash.CMS_SUCCESS) {
        toast.add({
          severity: 'success',
          summary: 'Profile',
          detail: page.props.flash.CMS_SUCCESS,
          life: 3000,
        })
      }
      showActionButton.value = false
    },
  })
}
</script>

<template>
  <div class="relative flex flex-col">
    <div class="group rounded-md drop-shadow-sm transition-all hover:scale-105">
      <input ref="file" type="file" accept="image/*" class="hidden" @change="handleOnChange" />
      <!-- Start Avatar Input -->
      <div v-if="!isLoading" class="relative h-24 w-24 lg:h-32 lg:w-32">
        <img
          v-if="imageSource"
          :src="imageSource"
          alt="Profile Picture"
          class="h-full w-full rounded-lg bg-primary-500 object-cover shadow-md"
        />
        <div
          v-if="!imageSource"
          class="flex h-full w-full items-center justify-center rounded-lg bg-primary text-3xl text-surface-0"
        >
          {{ page.props.auth.user?.nameInitials }}
        </div>
        <button
          class="absolute inset-0 hidden h-full w-full items-center justify-center rounded-lg bg-surface-900 opacity-80 group-hover:flex group-hover:cursor-pointer"
          @click="handleBrowseImages"
        >
          <i class="pi pi-camera text-xl text-surface-0"></i>
        </button>
      </div>
      <!-- End Avatar Input -->
      <!-- Start Avatar Loader -->
      <div v-if="isLoading" class="h-24 w-24 lg:h-32 lg:w-32">
        <Skeleton height="100%" width="100%" />
      </div>
      <!-- End Avatar Loader -->
    </div>
    <!-- Start action button -->
    <div v-if="showActionButton" class="flex w-full justify-end">
      <Button
        class="mt-4"
        :label="form.processing ? 'Loading...' : 'Upload'"
        :disabled="form.processing"
        icon="pi pi-cloud-upload"
        :loading="form.processing"
        @click="handleImageUpload"
      >
      </Button>
      <!-- End action button -->
    </div>
  </div>
</template>
