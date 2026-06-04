export type Paginator<T> = {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export type ApiResponse<T> = {
  data: T
}
