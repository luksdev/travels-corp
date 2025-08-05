import { ref, computed } from 'vue'
import { toast } from 'vue-sonner'
import { travelRequestService } from '@/services/travelRequest'
import type { TravelRequest, TravelRequestFilters } from '@/types/travelRequest'

export function useTravelRequests() {
  const travelRequests = ref<TravelRequest[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const totalPages = ref(1)
  const totalItems = ref(0)
  const stats = ref({
    total: 0,
    requested: 0,
    approved: 0,
    cancelled: 0
  })

  const filters = ref<TravelRequestFilters>({
    status: 'all',
    destination: '',
    departure_from: '',
    departure_to: '',
    page: 1,
    per_page: 15
  })

  const departureFromDate = ref<Date | null>(null)
  const departureToDate = ref<Date | null>(null)

  const fetchTravelRequests = async () => {
    try {
      isLoading.value = true
      error.value = null
      
      if (departureFromDate.value) {
        filters.value.departure_from = departureFromDate.value.toISOString().split('T')[0]
      } else {
        filters.value.departure_from = ''
      }
      
      if (departureToDate.value) {
        filters.value.departure_to = departureToDate.value.toISOString().split('T')[0]
      } else {
        filters.value.departure_to = ''
      }

      const cleanFilters = Object.fromEntries(
        Object.entries({
          ...filters.value,
          page: currentPage.value
        }).filter(([key, value]) => {
          if (key === 'status' && (value === 'all' || value === '')) return false
          return value !== '' && value !== null && value !== undefined
        })
      )
      
      console.log('Applying filters:', cleanFilters) // Debug log
      
      const response = await travelRequestService.getAll(cleanFilters)
      
      travelRequests.value = response.data
      currentPage.value = response.meta.current_page
      totalPages.value = response.meta.last_page
      totalItems.value = response.meta.total[0] || 0
      
    } catch (err: any) {
      const message = err.response?.data?.message || 'Erro ao carregar solicitações'
      error.value = message
      toast.error(message)
    } finally {
      isLoading.value = false
    }
  }

  const fetchStats = async () => {
    try {
      stats.value = await travelRequestService.getStats()
    } catch (err: any) {
      console.error('Erro ao carregar estatísticas:', err)
    }
  }

  const applyFilters = () => {
    currentPage.value = 1
    fetchTravelRequests()
  }

  const clearFilters = () => {
    filters.value = {
      status: 'all',
      destination: '',
      departure_from: '',
      departure_to: '',
      page: 1,
      per_page: 15
    }
    departureFromDate.value = null
    departureToDate.value = null
    currentPage.value = 1
    fetchTravelRequests()
  }

  const createTravelRequest = async (data: {
    destination: string
    departure_date: Date
    return_date: Date
  }) => {
    try {
      isLoading.value = true
      error.value = null

      const formattedData = {
        destination: data.destination,
        departure_date: data.departure_date.toISOString().split('T')[0],
        return_date: data.return_date.toISOString().split('T')[0]
      }

      const response = await travelRequestService.create(formattedData)
      
      toast.success('Solicitação de viagem criada com sucesso!')

      await fetchTravelRequests()
      await fetchStats()
      
      return response
      
    } catch (err: any) {
      const message = err.response?.data?.message || 'Erro ao criar solicitação'
      error.value = message
      toast.error(message)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const goToPage = (page: number) => {
    currentPage.value = page
    fetchTravelRequests()
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
    return new Date(dateString).toLocaleDateString('pt-BR')
  }

  const updateRequestStatus = async (requestId: string, status: 'approved' | 'cancelled') => {
    try {
      isLoading.value = true
      error.value = null

      await travelRequestService.updateStatus(requestId, status)
      
      toast.success(`Solicitação ${status === 'approved' ? 'aprovada' : 'cancelada'} com sucesso!`)

      await fetchTravelRequests()
      await fetchStats()
      
    } catch (err: any) {
      const message = err.response?.data?.message || 'Erro ao atualizar status'
      error.value = message
      toast.error(message)
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    travelRequests: computed(() => travelRequests.value),
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),
    currentPage: computed(() => currentPage.value),
    totalPages: computed(() => totalPages.value),
    totalItems: computed(() => totalItems.value),
    stats: computed(() => stats.value),
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
  }
}