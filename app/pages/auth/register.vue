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
    required: true
  },
  {
    name: 'email',
    type: 'email',
    label: 'Adresse mail',
    placeholder: 'Entrez votre email',
    required: true
  },
  {
    name: 'password',
    type: 'password',
    label: 'Mot de passe',
    placeholder: 'Entrez votre mot de passe',
    required: true
  },
  {
    name: 'confirmPassword',
    type: 'password',
    label: 'Confirmation mot de passe',
    placeholder: 'Entrez votre mot de passe',
    required: true
  }
]

const schema = z.object({
  username: z.string('Nom d\'utilisateur invalide'),
  email: z.email('Adresse email invalide'),
  password: z.string('Mot de passe obligatoire').min(8, 'Votre mot de passe doit faire plus de 8 caractères'),
  confirmPassword: z.string('Mot de passe obligatoire')
}).refine((data) => data.password === data.confirmPassword, {
  message: "Les mots de passes ne correpondent pas.",
  path: ["confirmPassword"],
});

type Schema = z.output<typeof schema>

function onSubmit(payload: FormSubmitEvent<Schema>) {
  console.log('Submitted', payload)
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
          :fields="fields"
          :submit="{ label: 'S\'enregistrer' }"
          @submit="onSubmit"
      />
    </template>

    <template #footer>
      Vous avez déjà un compte ? <NuxtLink :to="{ name: 'auth-login' }" class="text-primary">Connectez vous</NuxtLink>
    </template>
  </AuthForm>
</template>