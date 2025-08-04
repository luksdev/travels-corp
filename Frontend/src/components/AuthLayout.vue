<template>
  <div class="min-h-screen flex flex-col md:flex-row bg-white">
    <div
        class="flex-1 md:flex-1 min-h-[40vh] md:min-h-auto flex items-end justify-end relative overflow-hidden">
      <img
          src="/image-login.jpg"
          alt="image login"
          class="max-w-full h-[93.5%] w-auto min-w-[300px] min-h-[200px] rounded-t-3xl object-cover shadow-2xl transition-all duration-300 ease-in-out"
          style="max-width: min(710px, 90vw); aspect-ratio: auto;"
      />
    </div>

    <div class="flex-1 flex items-center justify-center">
      <slot />
    </div>
  </div>
</template>


<script setup lang="ts">
</script>