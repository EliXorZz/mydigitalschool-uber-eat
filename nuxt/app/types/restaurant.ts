export type Restaurant = {
    id: number
    name: string
    slug?: string
    city?: string
    type: string | { id: number; name: string; created_at: string; updated_at: string }
    description: string
    score: number
    rating?: number
    price_score: number
    price_range?: number
    tags: string[] | null
    features?: string[]
    image?: string
    owner_id: number
    type_id: number
    owner?: { id: number; name: string; email: string }
}