<script setup lang="ts">
import { watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Card from 'primevue/card'
import Button from 'primevue/button'
import { faGlobe, faSave } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { subdomainRegex } from '@/Utils/vuelidate-custom-validators.ts'
import DfInputText from '@/Components/Inputs/DfInputText.vue'

const emit = defineEmits<{
  (e: 'success', value: boolean): void
}>()

const props = defineProps({
  subdomain: {
    type: String,
    required: true,
  },
  storeSubdomainUrl: {
    type: String,
    required: true,
  },
  baseSubdomain: {
    type: String,
    required: true,
  },
})

const checkValidSubdomainKeys = function (event: KeyboardEvent) {
  if (event.key === 'Delete' || event.key === 'Backspace') {
    return
  }

  if (!event.key.match(subdomainRegex) || storeSubdomainForm.subdomain.length >= 25) {
    event.preventDefault()
  }
}

const storeSubdomainForm = useForm({
  subdomain: props.subdomain,
})

watch(
  () => storeSubdomainForm.subdomain,
  function () {
    storeSubdomainForm.subdomain = storeSubdomainForm.subdomain.toLowerCase()
  }
)

const submitStoreSubdomainForm = function () {
  if (!storeSubdomainForm.subdomain) return

  storeSubdomainForm.post(props.storeSubdomainUrl, {
    onSuccess: () => emit('success', true),
  })
}
</script>

<template>
  <section class="mt-4 flex flex-col gap-4">
    <Card class="!rounded-tl-none !rounded-tr-none">
      <template #title>
        <div class="flex items-center">
          <span class="text-sm font-bold">SUBDOMAIN REGISTRATION</span>
        </div>
      </template>
      <template #content>
        <p class="mb-4">
          Enter the subdomain you want to use for your resume. This will be the URL you will share to others. Note that only
          letters, numbers, and dashes are allowed.
        </p>
        <DfInputText
          v-model="storeSubdomainForm.subdomain"
          placeholder="Subdomain"
          label="example"
          :invalid="!!storeSubdomainForm.errors.subdomain"
          :invalid-message="storeSubdomainForm.errors.subdomain"
          @keydown.enter="submitStoreSubdomainForm"
          @keydown="checkValidSubdomainKeys"
        >
          <template #icon>
            <FontAwesomeIcon :icon="faGlobe" />
          </template>
        </DfInputText>
        <div class="flex max-w-72 flex-wrap break-all md:max-w-full">
          <p v-if="storeSubdomainForm.subdomain" class="mt-2">
            Your portfolio will be accessible at
            <code class="mx-1 rounded-lg bg-surface-200 px-2 py-0.5 text-sm dark:bg-surface-800">
              {{ storeSubdomainForm.subdomain + '.' + props.baseSubdomain }}
            </code>
          </p>
        </div>
      </template>
      <template #footer>
        <div class="mt-4 flex w-full justify-end">
          <Button
            label="Save"
            :disabled="!storeSubdomainForm.subdomain || storeSubdomainForm.processing"
            :loading="storeSubdomainForm.processing"
            @click="submitStoreSubdomainForm"
          >
            <template #icon>
              <FontAwesomeIcon :icon="faSave" />
            </template>
          </Button>
        </div>
      </template>
    </Card>
  </section>
</template>

<style scoped></style>
