<script setup lang="ts">
import Card from 'primevue/card'
import DfProfilePictureInput from '@/Components/Inputs/DfProfilePictureInput.vue'

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
</script>

<template>
  <Card>
    <template #title>
      <span class="text-sm font-bold">CHANGE PROFILE PICTURE</span>
    </template>
    <template #content>
      <div class="flex justify-between gap-x-6 pb-2">
        <p class="text-wrap">
          Let’s put a face to the name! Upload a photo of yourself to personalize your profile. Please to keep the file size under
          2MB.
        </p>
        <DfProfilePictureInput
          :upload-profile-picture-url="uploadProfilePictureUrl"
          :profile-picture-url="props.profilePictureUrl"
        />
      </div>
    </template>
  </Card>
</template>

<style scoped></style>
