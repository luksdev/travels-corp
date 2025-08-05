<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { useField } from 'vee-validate'
import { computed, ref, onMounted, onUnmounted } from "vue"
import { cn } from "@/lib/utils"
import { Eye, EyeOff } from "lucide-vue-next"

interface Props {
  name: string
  label?: string
  placeholder?: string
  type?: string
  id?: string
  class?: HTMLAttributes["class"]
}

const props = withDefaults(defineProps<Props>(), {
  type: "text"
})

const { value, errorMessage, handleChange, handleBlur } = useField(() => props.name)

const showPassword = ref(false)

const hasValue = computed(() => {
  return value.value !== undefined && value.value !== null && value.value !== ''
})

const hasFocused = ref(false)

const isLabelFloating = computed(() => {
  return hasValue.value || hasFocused.value
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
  return !!errorMessage.value
})

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}
</script>

<template>
  <div class="space-y-1">
    <div class="relative">
      <input
        :value="value"
        @input="handleChange"
        @blur="handleBlur"
        @focus="hasFocused = true"
        @focusout="hasFocused = false"
        :type="actualType"
        :id="id"
        :name="name"
        :placeholder="isLabelFloating ? placeholder : ''"
        data-slot="input"
        :class="cn(
          'w-full h-11 px-3 text-xs pt-6 pb-2 border-1 rounded-lg transition-all duration-200 ease-in-out outline-none bg-white',
          isPassword ? 'pr-10' : '',
          hasError 
            ? 'border-rose-400 focus:border-rose-400 focus:ring-1 focus:ring-red-200 text-rose-400'
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
            ? 'text-rose-400'
            : isLabelFloating 
              ? 'text-primary' 
              : 'text-gray-600'
        )"
      >
        {{ label }}
      </label>

      <!-- Password Toggle Button -->
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

    <!-- Error Message -->
    <span 
      v-if="errorMessage" 
      class="block text-rose-400 text-xs px-1"
    >
      {{ errorMessage }}
    </span>
  </div>
</template>