export type Role = 'default' | 'owner' | 'admin'

export type Account = {
  id: number
  name: string
  email: string
  role: Role
}
