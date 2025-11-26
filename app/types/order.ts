import type {Item} from "~/types/item";

export type Order = {
    id: number
    name: string
    date: string
    total: number
    items: Item[]
}