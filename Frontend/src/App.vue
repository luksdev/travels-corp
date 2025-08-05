<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner'
import 'vue-sonner/style.css'
</script>

<template>
  <div id="app" class="font-poppins">
    <Toaster />

    <router-view />
  </div>
</template>