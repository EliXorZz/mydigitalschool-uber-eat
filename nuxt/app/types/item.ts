export type Item = {
    id: number
    name: string
    price: number
    description: string
    image: string
}

export type ItemsResponse = {
    id_restaurant: number
    items: Item[]
}[]