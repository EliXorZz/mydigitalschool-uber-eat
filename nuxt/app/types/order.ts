import type { Dish } from '~/types/item'

export type Order = {
    id: number
    state: string
    total: number
    restaurant_id: number
    user_id: number
    created_at: string
    updated_at: string
    dishes?: Dish[]
    user?: {
        id: number
        name: string
        email: string
    }
}
