<template>
  <AppLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
          <p class="text-gray-600 mt-1">Visão geral das solicitações de viagem</p>
        </div>
        <Dialog v-model:open="isCreateDialogOpen">
          <DialogTrigger as-child v-if="!isAdmin">
            <Button>
              <Plus class="w-4 h-4 mr-2"/>
              Nova Solicitação
            </Button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-md">
            <DialogHeader>
              <DialogTitle>Nova Solicitação de Viagem</DialogTitle>
              <DialogDescription>
                Preencha os dados para criar uma nova solicitação de viagem.
              </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleCreateSubmit" class="space-y-4">
              <div class="space-y-2">
                <label class="text-sm font-medium">Destino</label>
                <Input
                    v-model="createForm.destination"
                    placeholder="Ex: São Paulo, SP"
                    required
                    class="!h-12"
                />
              </div>

              <div class="space-y-2">
                <label class="text-sm font-medium">Data de Partida</label>
                <DatePicker
                    v-model="createForm.departure_date"
                    placeholder="Selecione a data de partida"
                    :min-date="new Date()"
                />
              </div>

              <div class="space-y-2">
                <label class="text-sm font-medium">Data de Retorno</label>
                <DatePicker
                    v-model="createForm.return_date"
                    placeholder="Selecione a data de retorno"
                    :min-date="createForm.departure_date || undefined"
                />
              </div>

              <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    @click="resetCreateForm"
                >
                  Cancelar
                </Button>
                <Button
                    type="submit"
                    :disabled="!isCreateFormValid || isLoading"
                >
                  <span v-if="isLoading">Criando...</span>
                  <span v-else>Criar Solicitação</span>
                </Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div v-if="isLoading" v-for="i in 4" :key="i" class="border rounded-lg p-4">
          <Skeleton class="h-4 w-20 mb-2"/>
          <Skeleton class="h-8 w-12 mb-1"/>
          <Skeleton class="h-3 w-24"/>
        </div>

        <div v-else class="border rounded-lg p-4">
          <p class="text-sm text-gray-600 mb-1">Total</p>
          <p class="text-2xl font-semibold">{{ stats.total }}</p>
          <p class="text-xs text-gray-500">Solicitações</p>
        </div>

        <div v-if="!isLoading" class="border rounded-lg p-4">
          <p class="text-sm text-gray-600 mb-1">Pendentes</p>
          <p class="text-2xl font-semibold text-amber-600">{{ stats.requested }}</p>
          <p class="text-xs text-gray-500">Aguardando</p>
        </div>

        <div v-if="!isLoading" class="border rounded-lg p-4">
          <p class="text-sm text-gray-600 mb-1">Aprovadas</p>
          <p class="text-2xl font-semibold text-green-600">{{ stats.approved }}</p>
          <p class="text-xs text-gray-500">Confirmadas</p>
        </div>

        <div v-if="!isLoading" class="border rounded-lg p-4">
          <p class="text-sm text-gray-600 mb-1">Canceladas</p>
          <p class="text-2xl font-semibold text-red-600">{{ stats.cancelled }}</p>
          <p class="text-xs text-gray-500">Negadas</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="border rounded-lg p-6">
        <div class="mb-4">
          <h3 class="text-lg font-medium">Filtros</h3>
          <p class="text-sm text-gray-600">Filtre as solicitações de viagem</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="space-y-2">
            <Select v-model="filters.status">
              <SelectTrigger class="w-full">
                <SelectValue placeholder="Todos os status"/>
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Todos</SelectItem>
                <SelectItem value="requested">Solicitado</SelectItem>
                <SelectItem value="approved">Aprovado</SelectItem>
                <SelectItem value="cancelled">Cancelado</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Input
                v-model="filters.destination"
                placeholder="Digite o destino"
                class="h-11"
            />
          </div>

          <div class="space-y-2">
            <DatePicker
                v-model="departureFromDate"
                placeholder="Data inicial"
            />
          </div>

          <div class="space-y-2">
            <DatePicker
                v-model="departureToDate"
                placeholder="Data final"
                :min-date="departureFromDate || undefined"
            />
          </div>
        </div>

        <div class="flex gap-2 mt-4">
          <Button @click="applyFilters" :disabled="isLoading" class="h-9">
            <Search class="w-4 h-4 mr-2"/>
            Filtrar
          </Button>
          <Button class="h-9" variant="outline" @click="clearFilters" :disabled="isLoading">
            <X class="w-4 h-4 mr-2"/>
            Limpar
          </Button>
        </div>
      </div>

      <!-- Table -->
      <div class="border rounded-lg">
        <div class="p-6 border-b">
          <h3 class="text-lg font-medium">Solicitações de Viagem</h3>
          <p class="text-sm text-gray-600 mt-1">{{ totalItems }} solicitações encontradas</p>
        </div>

        <div class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Solicitante</TableHead>
                <TableHead>Destino</TableHead>
                <TableHead>Partida</TableHead>
                <TableHead>Retorno</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Criado</TableHead>
                <TableHead class="text-right">Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <!-- Loading Skeleton -->
              <TableRow v-if="isLoading" v-for="i in 5" :key="i">
                <TableCell>
                  <div>
                    <Skeleton class="h-4 w-24 mb-1"/>
                    <Skeleton class="h-3 w-32"/>
                  </div>
                </TableCell>
                <TableCell>
                  <Skeleton class="h-4 w-20"/>
                </TableCell>
                <TableCell>
                  <Skeleton class="h-4 w-16"/>
                </TableCell>
                <TableCell>
                  <Skeleton class="h-4 w-16"/>
                </TableCell>
                <TableCell>
                  <Skeleton class="h-5 w-16 rounded-full"/>
                </TableCell>
                <TableCell>
                  <Skeleton class="h-4 w-16"/>
                </TableCell>
                <TableCell class="text-right">
                  <Skeleton class="h-8 w-8 rounded ml-auto"/>
                </TableCell>
              </TableRow>

              <!-- Empty State -->
              <TableRow v-else-if="travelRequests.length === 0">
                <TableCell colspan="7" class="text-center py-12">
                  <div class="text-gray-500">
                    <p class="text-lg mb-2">Nenhuma solicitação encontrada</p>
                    <p class="text-sm">Tente ajustar os filtros ou criar uma nova solicitação</p>
                  </div>
                </TableCell>
              </TableRow>

              <!-- Data Rows -->
              <TableRow v-else v-for="request in travelRequests" :key="request.id" class="hover:bg-gray-50">
                <TableCell class="font-medium">
                  <div>
                    <p class="font-medium">{{ request.user?.name || 'N/A' }}</p>
                    <p class="text-sm text-gray-500">{{ request.user?.email || 'N/A' }}</p>
                  </div>
                </TableCell>
                <TableCell>{{ request.destination }}</TableCell>
                <TableCell>{{ formatDate(request.departure_date) }}</TableCell>
                <TableCell>{{ formatDate(request.return_date) }}</TableCell>
                <TableCell>
                  <Badge :variant="getStatusVariant(request.status)" class="text-xs">
                    {{ getStatusLabel(request.status) }}
                  </Badge>
                </TableCell>
                <TableCell class="text-sm text-gray-500">
                  {{ formatDate(request.created_at) }}
                </TableCell>
                <TableCell class="text-right">
                  <div class="flex items-center justify-end gap-1">
                    <!-- Admin Actions -->
                    <template v-if="isAdmin && request.status === 'requested'">
                      <Button
                          variant="ghost"
                          size="sm"
                          @click="handleApprove(request.id)"
                          class="text-green-600 hover:text-green-700 hover:bg-green-50"
                          title="Aprovar solicitação"
                      >
                        <Check class="w-4 h-4"/>
                      </Button>
                      <Button
                          variant="ghost"
                          size="sm"
                          @click="handleReject(request.id)"
                          class="text-red-600 hover:text-red-700 hover:bg-red-50"
                          title="Rejeitar solicitação"
                      >
                        <Ban class="w-4 h-4"/>
                      </Button>
                    </template>

                    <!-- View Button -->
                    <Button variant="ghost" size="sm" @click="viewRequest(request.id)" title="Ver detalhes">
                      <Eye class="w-4 h-4"/>
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-between p-4 border-t">
          <div class="text-sm text-gray-500">
            Página {{ currentPage }} de {{ totalPages }} ({{ totalItems }} total)
          </div>
          <div class="flex gap-1">
            <Button
                variant="outline"
                size="sm"
                :disabled="currentPage <= 1 || isLoading"
                @click="goToPage(currentPage - 1)"
            >
              <ChevronLeft class="w-4 h-4"/>
            </Button>

            <Button
                v-for="page in getPaginationPages"
                :key="page"
                :variant="page === currentPage ? 'default' : 'outline'"
                size="sm"
                :disabled="isLoading"
                @click="goToPage(page)"
            >
              {{ page }}
            </Button>

            <Button
                variant="outline"
                size="sm"
                :disabled="currentPage >= totalPages || isLoading"
                @click="goToPage(currentPage + 1)"
            >
              <ChevronRight class="w-4 h-4"/>
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import {computed, onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'
import {Table, TableBody, TableCell, TableHead, TableHeader, TableRow} from '@/components/ui/table'
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from '@/components/ui/select'
import {Input} from '@/components/ui/input'
import {Button} from '@/components/ui/button'
import {Badge} from '@/components/ui/badge'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger
} from '@/components/ui/dialog'
import {DatePicker} from '@/components/ui/date-picker'
import {Skeleton} from '@/components/ui/skeleton'
import {Search, X, Eye, ChevronLeft, ChevronRight, Plus, Check, Ban} from 'lucide-vue-next'
import {useTravelRequests} from '@/composables/useTravelRequests'
import {useAuth} from '@/composables/useAuth'

const router = useRouter()
const {isAdmin} = useAuth()

const {
  travelRequests,
  isLoading,
  currentPage,
  totalPages,
  totalItems,
  stats,
  filters,
  departureFromDate,
  departureToDate,
  fetchTravelRequests,
  fetchStats,
  applyFilters,
  clearFilters,
  createTravelRequest,
  updateRequestStatus,
  goToPage,
  getStatusVariant,
  getStatusLabel,
  formatDate
} = useTravelRequests()

const isCreateDialogOpen = ref(false)
const createForm = ref({
  destination: '',
  departure_date: null as Date | null,
  return_date: null as Date | null
})

const isCreateFormValid = computed(() => {
  return createForm.value.destination.trim() !== '' &&
      createForm.value.departure_date !== null &&
      createForm.value.return_date !== null
})

const resetCreateForm = () => {
  createForm.value = {
    destination: '',
    departure_date: null,
    return_date: null
  }
  isCreateDialogOpen.value = false
}

const handleCreateSubmit = async () => {
  if (!isCreateFormValid.value) return

  try {
    await createTravelRequest({
      destination: createForm.value.destination,
      departure_date: createForm.value.departure_date!,
      return_date: createForm.value.return_date!
    })
    resetCreateForm()
  } catch (error) {
  }
}

const getPaginationPages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

const viewRequest = (id: string) => {
  router.push(`/trips/${id}`)
}

const handleApprove = async (requestId: string) => {
  try {
    await updateRequestStatus(requestId, 'approved')
  } catch (error) {
    console.error('Erro ao aprovar solicitação:', error)
  }
}

const handleReject = async (requestId: string) => {
  try {
    await updateRequestStatus(requestId, 'cancelled')
  } catch (error) {
    console.error('Erro ao rejeitar solicitação:', error)
  }
}

onMounted(() => {
  fetchTravelRequests()
  fetchStats()
})
</script>