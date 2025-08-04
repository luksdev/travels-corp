<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { useVModel, useFocus } from "@vueuse/core"
import { computed, ref } from "vue"
import { cn } from "@/lib/utils"
import { Eye, EyeOff } from "lucide-vue-next"

interface Props {
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes["class"]
  label?: string
  placeholder?: string
  type?: string
  id?: string
  error?: string | boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: "text"
})

const emits = defineEmits<{
  (e: "update:modelValue", payload: string | number): void
}>()

const modelValue = useVModel(props, "modelValue", emits, {
  passive: true,
  defaultValue: props.defaultValue,
})

const inputRef = ref<HTMLInputElement>()
const { focused } = useFocus(inputRef)
const showPassword = ref(false)

const hasValue = computed(() => {
  return modelValue.value !== undefined && modelValue.value !== null && modelValue.value !== ''
})

const isLabelFloating = computed(() => {
  return focused.value || hasValue.value
})

const isPassword = computed(() => {
  return props.type === 'password'
})

const actualType = computed(() => {
  if (isPassword.value) {
    return showPassword.value ? 'text' : 'password'
  }
  return props.type
})

const hasError = computed(() => {
  return !!props.error
})

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}
</script>

<template>
  <div class="space-y-1">
    <div class="relative">
      <input
        ref="inputRef"
        v-model="modelValue"
        :type="actualType"
        :id="id"
        :placeholder="isLabelFloating ? placeholder : ''"
        data-slot="input"
        :class="cn(
          'w-full h-11 px-3 text-xs pt-6 pb-2 border-1 rounded-lg transition-all duration-200 ease-in-out outline-none bg-white',
          isPassword ? 'pr-10' : '',
          hasError 
            ? 'border-red-500 focus:border-red-500 focus:ring-1 focus:ring-red-200 text-red-500'
            : 'border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary/20',
          'placeholder:text-gray-400 placeholder:transition-opacity placeholder:duration-200',
          props.class,
        )"
      />
      
      <label
        v-if="label"
        :for="id"
        :class="cn(
          'absolute left-3 pointer-events-none transition-all duration-200 ease-in-out',
          isLabelFloating 
            ? 'top-1 text-xs font-normal'
            : 'top-1/2 transform -translate-y-1/2 text-xs',
          hasError
            ? 'text-red-500'
            : isLabelFloating 
              ? 'text-primary' 
              : 'text-gray-600'
        )"
      >
        {{ label }}
      </label>

      <button
        v-if="isPassword"
        type="button"
        @click="togglePasswordVisibility"
        :class="cn(
          'absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors z-10',
          'focus:outline-none focus:text-primary'
        )"
      >
        <Eye v-if="showPassword" class="w-4 h-4" />
        <EyeOff v-else class="w-4 h-4" />
      </button>
    </div>

    <span 
      v-if="hasError && typeof error === 'string'" 
      class="block text-red-500 text-xs px-1"
    >
      {{ error }}
    </span>
  </div>
</template>
