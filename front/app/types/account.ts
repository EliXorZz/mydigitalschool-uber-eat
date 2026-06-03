export type Role = 'default' | 'owner' | 'admin'

export type Account = {
  id: number
  name: string
  email: string
  username: string
  role: Role
}
