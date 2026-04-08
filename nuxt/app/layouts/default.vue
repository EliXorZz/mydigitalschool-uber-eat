<script setup lang="ts">
import { fr, en } from '@nuxt/ui/locale'

import type {NavigationMenuItem} from "#ui/components/NavigationMenu.vue";
import type {DropdownMenuItem} from "#ui/components/DropdownMenu.vue";

const { locale, setLocale } = useI18n()

const toaster = { position: 'bottom-center' }
const toast = useToast()

const authStore = useAuthentificationStore()
const { isAuth, role } = storeToRefs(authStore)

const cartStore = useCartStore()

const items = computed<NavigationMenuItem[]>(() => [])

const profileItems = computed<DropdownMenuItem[]>(() => {
  return [
    [
      {
        label: $t('profile.myProfile'),
        icon: 'i-lucide-user',
        class: 'cursor-pointer',
        to: '/profile'
      },
      {
        label: $t('profile.myOrders'),
        icon: 'i-lucide-receipt',
        class: 'cursor-pointer',
        to: '/profile/orders'
      },
      ...(role.value === 'admin' || role.value === 'owner'
          ? [{
            label: $t('profile.adminPanel'),
            icon: 'i-lucide-layout-dashboard',
            class: 'cursor-pointer',
            to: role.value === 'admin' ? '/admin' : '/admin/owner'
          }]
          : []),
      {
        label: $t('profile.logout'),
        icon: 'i-lucide-log-out',
        class: 'cursor-pointer',
        onSelect: async () => {
          await authStore.logout()
          toast.add({
            title: $t('profile.logoutToastTitle'),
            description: $t('profile.logoutToastDescription'),
            icon: 'i-lucide-user-lock',
            color: 'success'
          })
        }
      },
    ],
  ]
})
</script>

<template>
  <UApp :toaster="toaster">
    <UHeader>
      <template #title>
        <h1 class="text-2xl font-bold text-primary">{{ $t('app.title') }}</h1>
      </template>

      <UNavigationMenu :items="items" />

      <template #right>
        <div class="flex items-center gap-4">
          <USlideover v-if="isAuth">
            <UChip :text="cartStore.quantity" size="3xl" variant="sts">
              <UButton class="cursor-pointer" icon="i-lucide-shopping-basket" size="md" color="primary" variant="solid">
                {{ $t('cart.button') }}
              </UButton>
            </UChip>

            <template #content>
              <div class="flex flex-col gap-4 p-10">
                <h2 class="text-lg font-semibold">{{ $t('cart.title') }}</h2>

                <div v-if="cartStore.items.length > 0">
                  <div v-for="item in cartStore.items" :key="item.id" class="flex items-center gap-3 bg-gray-50 px-2 pr-10 rounded-lg">
                    <img :src="item.image" alt="" class="w-16 h-16 object-cover rounded-md"/>
                    <div class="flex-1 flex flex-col gap-1">
                      <span class="font-medium">{{ item.name }}</span>
                      <div class="flex gap-3">
                        <UButton class="cursor-pointer" icon="i-lucide:plus" size="xs" color="primary" variant="outline" @click="cartStore.incrementQuantity(item)"/>
                        {{ cartStore.getQuantityOfItem(item) }}
                        <UButton class="cursor-pointer" icon="i-lucide:minus" size="xs" color="primary" variant="outline" @click="cartStore.decrementQuantity(item)"/>
                      </div>
                    </div>
                    <div class="font-bold text-primary">{{ cartStore.getQuantityOfItem(item) * item.price }}€</div>
                  </div>

                  <div class="flex justify-between font-semibold mt-2">
                    <span>{{ $t('cart.totalLabel') }}</span>
                    <span>{{ cartStore.total }}€</span>
                  </div>

                  <div class="flex gap-2 mt-4">
                    <UButton class="flex-1 justify-center cursor-pointer" variant="subtle" @click="cartStore.clear">
                      {{ $t('cart.clear') }}
                    </UButton>
                    <UButton class="flex-1 justify-center cursor-pointer" trailing-icon="i-lucide-arrow-right">
                      {{ $t('cart.checkout') }}
                    </UButton>
                  </div>
                </div>

                <div v-else class="mt-40 w-full flex flex-col gap-6 justify-center items-center">
                  <UIcon name="i-lucide-shopping-basket" size="100"/>
                  <p>{{ $t('cart.empty') }}</p>
                </div>
              </div>
            </template>
          </USlideover>

          <UDropdownMenu v-if="isAuth" :items="profileItems" :content="{ align: 'center', side: 'bottom', sideOffset: 8 }">
            <UUser class="cursor-pointer" :avatar="{ src: 'https://i.pravatar.cc/150?u=john-doe', icon: 'i-lucide-image' }"/>
          </UDropdownMenu>
          <NuxtLink v-else :to="{ name: 'auth-login' }">
            {{ $t('auth.login') }}
          </NuxtLink>

          <ULocaleSelect
              :model-value="locale"
              :locales="[fr, en]"
              @update:model-value="setLocale($event)"
          />
        </div>
      </template>
    </UHeader>

    <slot/>
  </UApp>
</template>