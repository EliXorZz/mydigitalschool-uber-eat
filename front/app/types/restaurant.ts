export type RestaurantType = {
  id: number
  name: string
}

export type Restaurant = {
  id: number
  name: string
  type: RestaurantType
  description: string
  city: string
  score: number
  price_score: number
  tags?: string[]
  features?: string[]
  image: string
  owner_id: number
  type_id: number
  owner?: { id: number, name: string, email: string }
}

export type RestaurantsResponse = {
  data: Restaurant[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
