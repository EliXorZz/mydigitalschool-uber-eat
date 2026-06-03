import type { Dish } from '~/types/dish'

export type Order = {
  id: number
  state: string
  state_name?: string
  allowed_transitions?: { value: string; label: string }[]
  total: number
  restaurant_id: number
  user_id: number
  created_at: string
  updated_at: string
  dishes?: Dish[]
  total_items?: number
  items?: { id: number; name: string; price: number; quantity: number }[]
  user?: {
    id: number
    name: string
    email: string
  }
}
