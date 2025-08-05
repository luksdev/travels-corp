<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header with back button -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Button variant="ghost" @click="$router.go(-1)">
            <ArrowLeft class="w-4 h-4 mr-2" />
            Voltar
          </Button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalhes da Viagem</h1>
            <p class="text-gray-600">Informações detalhadas da solicitação</p>
          </div>
        </div>
        <Badge v-if="travelRequest" :variant="getStatusVariant(travelRequest.status)">
          {{ getStatusLabel(travelRequest.status) }}
        </Badge>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        <span class="ml-2">Carregando detalhes...</span>
      </div>

      <!-- Error State -->
      <Card v-else-if="error">
        <CardContent class="text-center py-12">
          <div class="text-red-500 text-4xl mb-4">❌</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Erro ao carregar</h3>
          <p class="text-gray-500 mb-4">{{ error }}</p>
          <Button @click="fetchTravelRequest">Tentar novamente</Button>
        </CardContent>
      </Card>

      <!-- Travel Request Details -->
      <div v-else-if="travelRequest" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Informações da Viagem</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Destino</label>
                  <p class="text-lg font-semibold">{{ travelRequest.destination }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Status</label>
                  <div class="mt-1">
                    <Badge :variant="getStatusVariant(travelRequest.status)">
                      {{ getStatusLabel(travelRequest.status) }}
                    </Badge>
                  </div>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Data de Partida</label>
                  <p class="text-lg">{{ formatDate(travelRequest.departure_date) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Data de Retorno</label>
                  <p class="text-lg">{{ formatDate(travelRequest.return_date) }}</p>
                </div>
              </div>
              
              <div class="pt-4 border-t">
                <label class="text-sm font-medium text-gray-500">Duração da Viagem</label>
                <p class="text-lg">{{ getTripDuration }} dias</p>
              </div>
            </CardContent>
          </Card>

          <!-- Timeline -->
          <Card>
            <CardHeader>
              <CardTitle>Histórico</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex items-start space-x-3">
                  <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                  <div>
                    <p class="font-medium">Solicitação criada</p>
                    <p class="text-sm text-gray-500">{{ formatDateTime(travelRequest.created_at) }}</p>
                  </div>
                </div>
                <div v-if="travelRequest.updated_at !== travelRequest.created_at" class="flex items-start space-x-3">
                  <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                  <div>
                    <p class="font-medium">Última atualização</p>
                    <p class="text-sm text-gray-500">{{ formatDateTime(travelRequest.updated_at) }}</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- User Info -->
          <Card>
            <CardHeader>
              <CardTitle>Solicitante</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex items-center space-x-3 mb-4">
                <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-semibold">
                  {{ getUserInitials(travelRequest.user.name) }}
                </div>
                <div>
                  <p class="font-semibold">{{ travelRequest.user.name }}</p>
                  <p class="text-sm text-gray-500">{{ travelRequest.user.email }}</p>
                </div>
              </div>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-500">Role:</span>
                  <span class="capitalize">{{ travelRequest.user.role }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Membro desde:</span>
                  <span>{{ formatDate(travelRequest.user.created_at) }}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Stats -->
          <Card>
            <CardHeader>
              <CardTitle>Informações Rápidas</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">ID da Solicitação</span>
                <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                  {{ travelRequest.id.slice(-8) }}
                </code>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Criado há</span>
                <span class="text-sm">{{ getTimeAgo(travelRequest.created_at) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Atualizado há</span>
                <span class="text-sm">{{ getTimeAgo(travelRequest.updated_at) }}</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { toast } from 'vue-sonner'
import moment from 'moment'

moment.locale('pt-br')
import AppLayout from '@/components/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { ArrowLeft } from 'lucide-vue-next'
import { travelRequestService } from '@/services/travelRequest'
import type { TravelRequest } from '@/types/travelRequest'

const route = useRoute()
const travelRequest = ref<TravelRequest | null>(null)
const isLoading = ref(false)
const error = ref<string | null>(null)

const getTripDuration = computed(() => {
  if (!travelRequest.value) return 0
  return moment(travelRequest.value.return_date).diff(moment(travelRequest.value.departure_date), 'days')
})

const fetchTravelRequest = async () => {
  try {
    isLoading.value = true
    error.value = null
    
    const id = route.params.id as string
    const response = await travelRequestService.getById(id)
    travelRequest.value = response.data
    
  } catch (err: any) {
    const message = err.response?.data?.message || 'Erro ao carregar detalhes da viagem'
    error.value = message
    toast.error(message)
  } finally {
    isLoading.value = false
  }
}

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'requested':
      return 'secondary'
    case 'approved':
      return 'default'
    case 'cancelled':
      return 'destructive'
    default:
      return 'secondary'
  }
}

const getStatusLabel = (status: string) => {
  switch (status) {
    case 'requested':
      return 'Solicitado'
    case 'approved':
      return 'Aprovado'
    case 'cancelled':
      return 'Cancelado'
    default:
      return status
  }
}

const formatDate = (dateString: string) => {
  return moment(dateString).format('DD/MM/YYYY')
}

const formatDateTime = (dateString: string) => {
  return moment(dateString).format('DD/MM/YYYY HH:mm')
}

const getUserInitials = (name: string) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const getTimeAgo = (dateString: string) => {
  return moment(dateString).fromNow()
}

onMounted(() => {
  fetchTravelRequest()
})
</script>