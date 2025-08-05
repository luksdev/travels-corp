<template>
  <AppLayout>
    <div class="bg-white rounded-lg shadow-sm p-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-4">Viagens</h1>
      <p class="text-gray-600">Gerencie todas as solicitações de viagem da empresa.</p>
      
      <div class="mt-8">
        <div class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">✈️</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma viagem encontrada</h3>
          <p class="text-gray-500">Comece criando uma nova solicitação de viagem.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/components/AppLayout.vue'
</script>