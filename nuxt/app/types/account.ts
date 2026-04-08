export type Role = 'default' | 'owner' | 'admin'

export type Account = {
    email: string
    username: string
    password: string
    role: Role
}