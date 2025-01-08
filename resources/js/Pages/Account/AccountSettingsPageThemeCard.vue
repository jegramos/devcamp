<script setup lang="ts">
import { computed, onUnmounted, type PropType, ref, watch } from 'vue'
import Card from 'primevue/card'
import SelectButton from 'primevue/selectbutton'
import Button from 'primevue/button'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSun, faMoon, faStarHalfAlt } from '@fortawesome/free-regular-svg-icons'
import { useForm, usePage } from '@inertiajs/vue3'
import type { SharedPage } from '@/Types/shared-page.ts'
import { useToast } from 'primevue/usetoast'
import { applyTheme } from '@/Utils/theme.ts'

const props = defineProps({
  currentTheme: {
    type: String as PropType<'light' | 'dark' | 'auto'>,
    required: true,
  },
  storeAccountSettingsUrl: {
    type: String,
    required: true,
  },
})

const optionsRaw = [
  { value: 'light', label: 'Light', icon: faSun },
  { value: 'dark', label: 'Dark', icon: faMoon },
  { value: 'auto', label: 'Auto', icon: faStarHalfAlt },
]

const form = useForm({
  theme: props.currentTheme ?? 'auto',
})

const options = computed(function () {
  return optionsRaw.map((option) => ({
    value: option.value,
    label: option.label,
    icon: option.icon,
    'option-disabled': option.value === form.theme,
  }))
})

watch(
  () => form.theme,
  function (value) {
    if (value === 'dark') {
      return applyTheme(value)
    }

    if (value === 'light') {
      return applyTheme(value)
    }

    applyTheme('auto')
  }
)

const page = usePage<SharedPage>()
const toast = useToast()
const themeSaved = ref(false)
const submit = function () {
  form.post(props.storeAccountSettingsUrl, {
    preserveState: true,
    onSuccess: function () {
      if (page.props.flash.CMS_SUCCESS) {
        toast.add({
          severity: 'success',
          summary: 'Theme Configuration',
          detail: 'App theme changed successfully',
          life: 3000,
        })
      }
      localStorage.setItem('theme', form.theme)
      themeSaved.value = true
    },
  })
}

onUnmounted(function () {
  if (!themeSaved.value && props.currentTheme !== form.theme) {
    applyTheme(props.currentTheme)
    toast.add({
      severity: 'warn',
      summary: 'Theme Configuration',
      detail: 'App theme not saved and has reverted.',
      life: 4000,
    })
  }
})
</script>

<template>
  <Card>
    <template #title>
      <div class="flex items-center justify-between">
        <span class="text-sm font-bold">APP THEME</span>
      </div>
    </template>
    <template #content>
      <div class="flex flex-col gap-y-4">
        <p>Transform the webapp's look and feel with a new theme. Select one below to see the changes.</p>
        <SelectButton
          v-model="form.theme"
          :options="options"
          option-value="value"
          option-label="label"
          option-disabled="option-disabled"
        >
          <template #option="slotProps">
            <div class="flex items-center p-0.5">
              <FontAwesomeIcon :icon="slotProps.option.icon" class="h-4 w-4 md:h-6 md:w-6" />
              <span class="ml-2 uppercase">{{ slotProps.option.value }}</span>
            </div>
          </template>
        </SelectButton>
      </div>
    </template>
    <template #footer>
      <div class="mb-0.5 mt-4 flex justify-end">
        <Button label="Save" icon="pi pi-save" :disabled="form.processing" :loading="form.processing" @click="submit"></Button>
      </div>
    </template>
  </Card>
</template>
