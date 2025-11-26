<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent, AuthFormField } from '@nuxt/ui'

definePageMeta({
  layout: 'background',
  ssr: false
})

const fields: AuthFormField[] = [
  {
    name: 'username',
    type: 'text',
    label: 'Nom d\'utilisateur',
    placeholder: 'Entrez votre nom d\'utilisateur',
    help: 'admin',
    required: true
  },
  {
    name: 'password',
    type: 'password',
    label: 'Mot de passe',
    placeholder: 'Entrez votre mot de passe',
    help: 'admin-mydigitalschool',
    required: true
  }
]

const schema = z.object({
  username: z.string('Nom d\'utilisateur obligatoire'),
  password: z.string('Mot de passe obligatoire')
})

type Schema = z.output<typeof schema>

const router = useRouter()
const toast = useToast()

const authStore = useAuthentificationStore()

async function onSubmit(payload: FormSubmitEvent<Schema>) {
  const { username, password } = payload.data
  const success = await authStore.login(username, password)

  if (!success) {
    toast.add({
      title: 'Système d\'authentification',
      description: `Le nom d'utilisateur et/ou votre mot de passe sont incorrect.`,
      icon: 'i-lucide-user-lock',
      color: 'error'
    })

    return
  }

  toast.add({
    title: 'Système d\'authentification',
    description: `Vous avez été connecté avec succès.`,
    icon: 'i-lucide-user-lock',
    color: 'success'
  })

  router.push({ name: 'index', replace: true })
}
</script>

<template>
  <AuthForm>
    <template #body>
      <UAuthForm
          :schema="schema"
          title="Connexion"
          description="Entrez vos identifiants pour accèder a votre compte."
          icon="i-lucide-user"
          :submit="{ label: 'Se connecter' }"
          :fields="fields"
          @submit="onSubmit"
      />
    </template>

    <template #footer>
      Vous n'avez pas de compte ? <NuxtLink :to="{ name: 'auth-register' }"  class="text-primary">Enregistrez vous</NuxtLink>
    </template>
  </AuthForm>
</template>