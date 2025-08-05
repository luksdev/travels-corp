<script lang="ts" setup>
import type { ToasterProps } from "vue-sonner"
import { Toaster as Sonner } from "vue-sonner"

const props = defineProps<ToasterProps>()
</script>

<style>
/* Success toast */
[data-type="success"] {
  background-color: var(--success-bg) !important;
  color: var(--success-text) !important;
  border-color: var(--success-border) !important;
}

/* Error toast */
[data-type="error"] {
  background-color: var(--error-bg) !important;
  color: var(--error-text) !important;
  border-color: var(--error-border) !important;
}

/* Warning toast */
[data-type="warning"] {
  background-color: var(--warning-bg) !important;
  color: var(--warning-text) !important;
  border-color: var(--warning-border) !important;
}

/* Info toast */
[data-type="info"] {
  background-color: var(--info-bg) !important;
  color: var(--info-text) !important;
  border-color: var(--info-border) !important;
}

/* Toast icons color adjustment */
[data-type="success"] [data-icon] {
  color: var(--success-text) !important;
}

[data-type="error"] [data-icon] {
  color: var(--error-text) !important;
}

[data-type="warning"] [data-icon] {
  color: var(--warning-text) !important;
}

[data-type="info"] [data-icon] {
  color: var(--info-text) !important;
}

/* Close button color adjustment */
[data-type="success"] [data-close-button] {
  color: var(--success-text) !important;
}

[data-type="error"] [data-close-button] {
  color: var(--error-text) !important;
}

[data-type="warning"] [data-close-button] {
  color: var(--warning-text) !important;
}

[data-type="info"] [data-close-button] {
  color: var(--info-text) !important;
}
</style>

<template>
  <Sonner
    class="toaster group"
    v-bind="props"
    :style="{
      '--normal-bg': 'var(--popover)',
      '--normal-text': 'var(--popover-foreground)',
      '--normal-border': 'var(--border)',
      '--success-bg': '#10b981',
      '--success-text': '#ffffff',
      '--success-border': '#059669',
      '--error-bg': '#ef4444',
      '--error-text': '#ffffff', 
      '--error-border': '#dc2626',
      '--warning-bg': '#f59e0b',
      '--warning-text': '#ffffff',
      '--warning-border': '#d97706',
      '--info-bg': '#3b82f6',
      '--info-text': '#ffffff',
      '--info-border': '#2563eb'
    }"
  />
</template>
