export type Restaurant = {
    id: number
    name: string
    slug: string
    city: string
    type: string
    description: string
    rating: number
    price_range: number
    features: string[]
    image: string
}

export type RestaurantsResponse = Restaurant[]