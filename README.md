# 🍔 Uber Eat – MyDigitalSchool

Application pour gérer un restaurant, ses plats et ses commandes.

![CI](https://github.com/EliXorZz/mydigitalschool-uber-eat/actions/workflows/main.yml/badge.svg)

URL DU PROJET : https://uber-eat.mydigitalschool.server.dylanbattig.fr

## 🗣️Utilisateurs

### Role Admin
username: admin
password: admin-mydigitalschool

### Role Restaurateur
username: dylan
password: admin-mydigitalschool

---

## 🚀 Fonctionnalités

- Gestion des restaurants
- Gestion des plats
- Gestion des commandes
- CI/CD via GitHub Actions
- Docker & déploiement automatique sur Harbor
- Possibilité PWA / navigation offline

---

## 📚 Documentation

| Outil | Lien |
|---|---|
| Laravel 13 | https://laravel.com/docs/13.x |
| Nuxt 3 | https://nuxt.com/docs |
| NuxtUI v3 | https://ui.nuxt.com |
| Pinia | https://pinia.vuejs.org |
| Tailwind CSS | https://tailwindcss.com/docs |
| Zod | https://zod.dev |
| Rebing GraphQL | https://github.com/rebing/graphql-laravel |
| Scramble (API docs) | https://scramble.dedoc.co/docs |
| JWT Auth (Laravel) | https://github.com/PHP-Open-Source-Saver/jwt-auth |
| Laravel Echo | https://laravel.com/docs/13.x/broadcasting |
| Vitest | https://vitest.dev |
| Playwright | https://playwright.dev |

---

## 🛠️ Choix techniques

### Backend (API Laravel 13)

| Librairie | Rôle |
|---|---|
| `laravel/framework` | Framework PHP — routing, ORM Eloquent, queues, events |
| `php-open-source-saver/jwt-auth` | Authentification sans état via tokens JWT (Bearer) |
| `rebing/graphql-laravel` | Exposition d'une API GraphQL avec types, queries et mutations |
| `laragraph/utils` | Parser HTTP pour requêtes GraphQL (multipart, JSON, form) |
| `dedoc/scramble` | Génération automatique de la doc OpenAPI depuis les controllers |
| `tymon/jwt-auth` | (alias) Gestion des claims et secrets JWT |

### Frontend (Nuxt 3 + Vue 3)

| Librairie | Rôle |
|---|---|
| `nuxt` | Meta-framework Vue 3 — SSR/SPA, routing, auto-imports |
| `@nuxt/ui` | Composants UI prêts à l'emploi (boutons, tables, toasts…) |
| `@pinia/nuxt` | State management global (auth, panier, commandes) |
| `tailwindcss` | CSS utilitaire pour le styling |
| `zod` | Validation de schémas TypeScript côté formulaires |
| `laravel-echo` | Client WebSocket pour écouter les événements Reverb en temps réel |
| `@nuxtjs/i18n` | Internationalisation FR/EN |
| `vitest` | Tests unitaires |
| `@playwright/test` | Tests end-to-end |

### Infrastructure

- Stack : Nuxt 3 + TypeScript + Vue 3 + Pinia
- UI : NuxtUI + Tailwind
- Validation : Zod pour la saisie des formulaires
- Docker : Build et push automatique sur Harbor privé
- CI/CD : GitHub Actions pour build & push
- Déploiement : FluxCD pour mise à jour automatique