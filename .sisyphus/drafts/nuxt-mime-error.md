# Draft: Diagnostic Erreur Nuxt MIME Type

## Symptômes
- Erreur navigateur: Loading module from `http://localhost:3000/_nuxt/Users/dylan/MyDigitalSchool/uber-eat/nuxt/node_modules/nuxt/dist/app/entry.async.js` was blocked because of a disallowed MIME type ("").
- Le chemin du module contient un chemin absolu du filesystem (`/Users/dylan/...`) ce qui est anormal pour une URL `_nuxt/...`.

## Diagnostic Agent Explore (Terminé)

### Cause Racine Identifiée
Vite 7 (via @nuxt/vite-builder) génère des URLs de chunks avec des **chemins absolus filesystem** (`/Users/dylan/MyDigitalSchool/...`) au lieu de chemins relatifs. Le serveur dev Nuxt/Nitro essaie de servir ces chemins comme des URLs `_nuxt/...`, échoue (fichier non trouvé → MIME type vide).

### Preuves
- Fichiers `.nuxt` générés contiennent des chemins absolus :
  ```ts
  // .nuxt/ui-image-component.ts
  export { default } from "/Users/dylan/MyDigitalSchool/uber-eat/nuxt/node_modules/@nuxt/image/dist/runtime/components/NuxtImg.vue";
  ```

### Facteurs Suspects
1. **Compatibilité Nuxt 4.1.3 + Vite 7.1.9** - Vite 7 a changé la résolution des chunks
2. **@vite-pwa/nuxt 1.0.7** - Intercepte le dev server, peut causer des problèmes avec les chunks async
3. **Alias paths tsconfig.app.json** - Mappent `nuxt` → `../node_modules/nuxt` (chemin relatif qui peut mal se résoudre)

### Versions Détectées
| Package | Version |
|---------|---------|
| nuxt | ^4.1.3 |
| vite | ^7.1.9 |
| @vite-pwa/nuxt | ^1.0.7 |

## Questions posées à l'utilisateur (en attente)
1. Qu'as-tu modifié juste avant que l'erreur apparaisse ?
2. As-tu déjà relancé le serveur de dev ?
3. L'erreur est-elle sur toutes les pages ou une seule ?

## Prochaines étapes
- Attendre le rapport de l'agent explore.
- Analyser les réponses de l'utilisateur.
- Créer un plan de résolution ciblé.
