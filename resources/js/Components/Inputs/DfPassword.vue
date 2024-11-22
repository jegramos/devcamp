<script setup lang="ts">
/**
 * Extended version of 2 Input Components
 * @see https://primevue.org/password/#api
 * @see https://primevue.org/inputgroup/
 *
 *
 * Guide for Vue3's attribute inheritance feature
 * @see https://vuejs.org/guide/components/attrs
 */
import { computed, useSlots } from 'vue'
import Password from 'primevue/password'
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
  enableFeedback: {
    type: Boolean,
    default: false,
  },
  feedbackHeader: {
    type: String,
    default: '',
  },
  feedbackHelperList: {
    type: Array<string>,
    default: [],
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
          <Password
            v-bind="$attrs"
            :input-id="$.uid.toString()"
            :placeholder="undefined"
            :class="{ 'cursor-not-allowed': $attrs.disabled }"
          >
            <template v-if="props.enableFeedback || $attrs.feedback" #header>
              <div class="text-xm mb-4 text-xs font-semibold md:text-sm">{{ props.feedbackHeader }}</div>
            </template>
            <template v-if="props.enableFeedback || $attrs.feedback" #footer>
              <Divider />
              <ul class="my-0 ml-2 mt-2 pl-2 text-xs leading-normal md:text-sm">
                <li v-for="item in props.feedbackHelperList" :key="item">
                  {{ item }}
                </li>
              </ul>
            </template>
          </Password>
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
        <Password
          v-bind="$attrs"
          :input-id="$.uid.toString()"
          :placeholder="undefined"
          :class="{ 'cursor-not-allowed': $attrs.disabled }"
        >
          <template v-if="props.enableFeedback || $attrs.feedback" #header>
            <div class="text-xm mb-4 text-xs font-semibold md:text-sm">{{ props.feedbackHeader }}</div>
          </template>
          <template v-if="props.enableFeedback || $attrs.feedback" #footer>
            <Divider />
            <ul class="my-0 ml-2 mt-2 pl-2 text-xs leading-normal md:text-sm">
              <li v-for="item in props.feedbackHelperList" :key="item">
                {{ item }}
              </li>
            </ul>
          </template>
        </Password>
        <label :for="$.uid.toString()">{{ $attrs.placeholder }}</label>
      </FloatLabel>
      <small v-if="$attrs.invalid" class="mt-1 animate-shake text-xs text-red-500 dark:text-red-300">
        {{ props.invalidMessage }}
      </small>
    </div>
  </template>
</template>

<style scoped></style>
