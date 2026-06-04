export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthentificationStore()
  const accountRole = authStore.role

  if (!authStore.isAuth)
    return navigateTo({ name: 'auth-login' })

  const requiredRoles = to.meta.roles as string[] | undefined
  if (!requiredRoles) return

  if (!requiredRoles.includes(accountRole ?? ''))
    return navigateTo({ name: 'index' })
})
