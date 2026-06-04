export type Role = 'user' | 'restaurant' | 'admin'

export type Account = {
  id: number
  name: string
  email: string
  avatar?: string | null
  role: Role
}
