import api from './api'
import type { 
  TravelRequestResponse, 
  TravelRequestDetailResponse, 
  TravelRequestFilters
} from '../types/travelRequest'

export const travelRequestService = {
  async getAll(filters: TravelRequestFilters = {}): Promise<TravelRequestResponse> {
    const params = new URLSearchParams()
    
    if (filters.status) params.append('status', filters.status)
    if (filters.destination) params.append('destination', filters.destination)
    if (filters.departure_from) params.append('departure_from', filters.departure_from)
    if (filters.departure_to) params.append('departure_to', filters.departure_to)
    if (filters.page) params.append('page', filters.page.toString())
    if (filters.per_page) params.append('per_page', filters.per_page.toString())

    const response = await api.get<TravelRequestResponse>(`/travel-requests?${params.toString()}`)
    return response.data
  },

  async getById(id: string): Promise<TravelRequestDetailResponse> {
    const response = await api.get<TravelRequestDetailResponse>(`/travel-requests/${id}`)
    return response.data
  },

  async create(data: {
    destination: string
    departure_date: string
    return_date: string
  }): Promise<TravelRequestDetailResponse> {
    const response = await api.post<TravelRequestDetailResponse>('/travel-requests', data)
    return response.data
  },

  async updateStatus(id: string, status: 'approved' | 'cancelled'): Promise<void> {
    await api.patch(`/travel-requests/${id}/status`, { status })
  },

  async getStats(): Promise<{ total: number; requested: number; approved: number; cancelled: number }> {
    try {
      const response = await api.get<{ 
        data: { total: number; requested: number; approved: number; cancelled: number }
      }>('/travel-requests/stats')
      return response.data.data
    } catch (error) {
      const response = await this.getAll()
      const requests = response.data
      
      return {
        total: requests.length,
        requested: requests.filter(r => r.status === 'requested').length,
        approved: requests.filter(r => r.status === 'approved').length,
        cancelled: requests.filter(r => r.status === 'cancelled').length
      }
    }
  }
}