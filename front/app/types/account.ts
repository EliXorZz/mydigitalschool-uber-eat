export type Role = 'user' | 'restaurant' | 'admin'

export type Account = {
  id: number
  name: string
  email: string
  avatar?: string | null
  role: Role
}

export type AuthToken = {
  token: string
  type: string
  expires_in: number
}
