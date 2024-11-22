<script setup lang="ts">
/**
 * Extended version of 2 Input Components
 * @see https://primevue.org/datepicker/#api
 * @see https://primevue.org/inputgroup/
 *
 *
 * Guide for Vue3's attribute inheritance feature
 * @see https://vuejs.org/guide/components/attrs
 */
import { computed, useSlots } from 'vue'
import DatePicker from 'primevue/datepicker'
import InputGroup from 'primevue/inputgroup'
import InputGroupAddon from 'primevue/inputgroupaddon'
import FloatLabel from 'primevue/floatlabel'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps({
  invalidMessage: {
    type: String,
    default: '',
  },
})

const slots = useSlots()
const renderAsInputGroup = computed(() => !!slots.icon)
</script>

<template>
  <template v-if="renderAsInputGroup">
    <div class="flex w-full flex-col">
      <InputGroup class="group">
        <InputGroupAddon>
          <slot name="icon"></slot>
        </InputGroupAddon>
        <FloatLabel variant="on">
          <DatePicker :input-id="$.uid.toString()" v-bind="$attrs" :placeholder="undefined" />
          <label :for="$.uid.toString()">{{ $attrs.placeholder }}</label>
        </FloatLabel>
      </InputGroup>
      <small v-if="$attrs.invalid" class="mt-1 animate-shake text-xs text-red-500 dark:text-red-300">
        {{ props.invalidMessage }}
      </small>
    </div>
  </template>

  <template v-else>
    <div class="flex w-full flex-col">
      <FloatLabel variant="on">
        <DatePicker :input-id="$.uid.toString()" v-bind="$attrs" :placeholder="undefined" />
        <label :for="$.uid.toString()">{{ $attrs.placeholder }}</label>
      </FloatLabel>
      <small v-if="$attrs.invalid" class="mt-1 animate-shake text-xs text-red-500 dark:text-red-300">
        {{ props.invalidMessage }}
      </small>
    </div>
  </template>
</template>

<style scoped></style>
