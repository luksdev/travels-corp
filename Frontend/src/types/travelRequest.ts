export interface TravelRequest {
  id: string
  destination: string
  departure_date: string
  return_date: string
  status: 'requested' | 'approved' | 'cancelled'
  user: {
    id: string
    name: string
    email: string
    role: string
    email_verified_at: string | null
    created_at: string
    updated_at: string
  }
  created_at: string
  updated_at: string
}

export interface TravelRequestFilters {
  status?: string
  destination?: string
  departure_from?: string
  departure_to?: string
  page?: number
  per_page?: number
}

export interface PaginationMeta {
  total: number[]
  current_page: number
  from: number
  last_page: number
  links: Array<{
    url: string | null
    label: string
    active: boolean
  }>
  path: string
  per_page: number
  to: number
}

export interface PaginationLinks {
  first: string
  last: string
  prev: string | null
  next: string | null
}

export interface TravelRequestResponse {
  data: TravelRequest[]
  meta: PaginationMeta
  links: PaginationLinks
}

export interface TravelRequestDetailResponse {
  data: TravelRequest
}