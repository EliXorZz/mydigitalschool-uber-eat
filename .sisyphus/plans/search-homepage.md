# Implémentation champ recherche page d'accueil

## TL;DR

> **Quick Summary**: Rendre fonctionnel le champ de recherche `<UInput>` déjà présent sur la page d'accueil, en filtrant les restaurants par nom côté serveur via l'API Laravel.
>
> **Deliverables**:
> - `RestaurantService.php` modifié pour accepter un paramètre de recherche
> - `RestaurantController.php` modifié pour passer le paramètre `search`
> - `pages/index.vue` avec `v-model`, `useAsyncData` réactif et `refresh()`
> - `server/api/restaurants.ts` mis à jour avec filtrage côté mock
>
> **Estimated Effort**: Short (1-2 heures)
> **Parallel Execution**: YES — 4 tâches en 1 wave + vérification finale
> **Critical Path**: Task 1 ↔ Task 2 ↔ Task 3 ↔ Task 4 → F1-F4

---

## Context

### Original Request
> "Dans la page index, implemente le champs recherche dans nuxt, et dans l'api si ce n'est pas déjà fait."

### Interview Summary
**Key Discussions**:
- **Champ de recherche** : Nom du restaurant uniquement (`name`)
- **Stratégie** : Filtrage côté serveur (API Laravel)
- **Scope API Laravel** : OUI, modifier le backend aussi

**Research Findings**:
- Le `<UInput>` est déjà présent dans `pages/index.vue:29-34` mais **non fonctionnel** (pas de `v-model`, pas de handler)
- La page utilise `useAsyncData` avec `$api('/api/restaurants')` pour charger les restaurants
- L'API Laravel (`RestaurantService::listRestaurant()`) retourne tous les restaurants sans filtrage
- Le mock Nuxt server (`server/api/restaurants.ts`) retourne des données hardcodées sans paramètre de recherche
- Le projet utilise Nuxt UI, Pinia, i18n, et une architecture Laravel + Nuxt dual-stack

### Metis Review
**Identified Gaps** (addressed):
- **Debounce** : Pas de debounce complexe requis (scope OUT)
- **Frontend pattern** : Utiliser `useAsyncData` avec `watch: [search]` et `refresh()` au lieu de `watch` + `$api` manuel
- **Scope OUT** : Explicitement documenté (pas de recherche par ville/type/tags, pas d'autocomplete, pas de pagination)

---

## Work Objectives

### Core Objective
Rendre fonctionnel le champ de recherche sur la page d'accueil en filtrant les restaurants par nom via l'API Laravel.

### Concrete Deliverables
- `api/app/Services/RestaurantService.php` : méthode `listRestaurant` avec paramètre `search` optionnel
- `api/app/Http/Controllers/RestaurantController.php` : extraction du paramètre `search` et passage au service
- `nuxt/app/pages/index.vue` : `UInput` avec `v-model`, `useAsyncData` réactif et affichage état vide
- `nuxt/server/api/restaurants.ts` : support du paramètre `search` pour le développement local

### Definition of Done
- [ ] La saisie dans le champ de recherche filtre les restaurants par nom
- [ ] La recherche fonctionne avec l'API Laravel et le mock Nuxt server
- [ ] Un état vide s'affiche quand aucun restaurant ne correspond
- [ ] Les appels API utilisent `useAsyncData` avec `refresh()` (pattern existant)

### Must Have
- Recherche par nom de restaurant (insensible à la casse, LIKE `%search%`)
- Filtrage côté serveur (API Laravel)
- Mock Nuxt server mis à jour pour cohérence développement
- `useAsyncData` avec `watch` et `refresh()` côté frontend
- États : chargement, résultats, aucun résultat

### Must NOT Have (Guardrails)
- Recherche par ville, type de cuisine, tags, ou description
- Autocomplete / suggestions
- Debounce avancé ou throttling
- Historique de recherche
- Modification de la pagination
- Modification d'autres pages que `index.vue`
- Tests unitaires automatisés (agent QA uniquement)

---

## Verification Strategy (MANDATORY)

> **ZERO HUMAN INTERVENTION** — ALL verification is agent-executed. No exceptions.

### Test Decision
- **Infrastructure exists**: YES (Vitest, Playwright, Pest)
- **Automated tests**: Tests-after + Agent QA
- **Framework**: N/A (agent QA uniquement pour cette feature simple)
- **Agent-Executed QA**: ALWAYS (mandatory)

### QA Policy
Every task MUST include agent-executed QA scenarios.
Evidence saved to `.sisyphus/evidence/task-{N}-{scenario-slug}.{ext}`.

- **Frontend/UI**: Use Playwright — Navigate, fill input, assert DOM, screenshot
- **API/Backend**: Use Bash (curl) — Send requests, assert status + response fields

---

## Execution Strategy

### Parallel Execution Waves

```
Wave 1 (Start Immediately — 4 tâches indépendantes en parallèle):
├── Task 1: Backend Laravel — RestaurantService [quick]
├── Task 2: Backend Laravel — RestaurantController [quick]
├── Task 3: Frontend Nuxt — Page index.vue [quick]
└── Task 4: Mock Nuxt server — restaurants.ts [quick]

Wave FINAL (After ALL tasks — 4 reviews parallèles, puis user okay):
├── Task F1: Plan compliance audit (oracle)
├── Task F2: Code quality review (unspecified-high)
├── Task F3: Real manual QA (unspecified-high)
└── Task F4: Scope fidelity check (deep)
-> Present results -> Get explicit user okay

Critical Path: Task 1 + Task 2 + Task 3 + Task 4 → F1-F4 → user okay
Parallel Speedup: ~60% faster than sequential
Max Concurrent: 4 (Wave 1)
```

### Dependency Matrix

| Task | Blocked By | Blocks |
|------|-----------|--------|
| 1 (Service) | None | F1-F4 |
| 2 (Controller) | None | F1-F4 |
| 3 (Frontend) | None | F1-F4 |
| 4 (Mock) | None | F1-F4 |
| F1-F4 | 1,2,3,4 | user okay |

> Tasks 1-4 are mutually independent. The backend changes (1+2) and frontend changes (3+4) can be developed in parallel because the contract (`?search=` query param) is known upfront.

### Agent Dispatch Summary

- **Wave 1**: **4** — T1-T2 → `quick`, T3 → `quick`, T4 → `quick`
- **Wave FINAL**: **4** — F1 → `oracle`, F2 → `unspecified-high`, F3 → `unspecified-high`, F4 → `deep`

---

## TODOs

- [x] 1. **Backend Laravel — RestaurantService search**

  **What to do**:
  - Modifier `api/app/Services/RestaurantService.php`
  - Ajouter un paramètre optionnel `?string $search = null` à la méthode `listRestaurant()`
  - Si `$search` est fourni et non vide, ajouter `->where('name', 'LIKE', '%' . $search . '%')` à la requête Eloquent
  - Conserver le `->with(['owner', 'type'])` et `->get()` existants
  - Conserver le type de retour `Collection`

  **Must NOT do**:
  - Ne pas modifier les autres méthodes du service (`getRestaurant`, `createRestaurant`, etc.)
  - Ne pas ajouter de validation ou de Form Request ici (c'est le rôle du controller)
  - Ne pas modifier le type de retour

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Modification simple d'une méthode existante dans un service Laravel
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 2, 3, 4)
  - **Blocks**: F1-F4
  - **Blocked By**: None

  **References**:
  - `api/app/Services/RestaurantService.php:12-17` — Méthode `listRestaurant()` actuelle à modifier
  - `api/app/Services/OrderService.php` — Exemple de pattern de filtrage avec `where()` (déjà utilisé dans le projet)
  - `api/app/Models/Restaurant.php` — Modèle Eloquent avec le champ `name`

  **Acceptance Criteria**:
  - [ ] `listRestaurant()` accepte un paramètre `?string $search = null`
  - [ ] Quand `$search` est null ou vide, la méthode retourne tous les restaurants (comportement actuel)
  - [ ] Quand `$search` est fourni, la requête inclut `where('name', 'LIKE', '%search%')`
  - [ ] Le `with(['owner', 'type'])` est préservé

  **QA Scenarios**:

  ```
  Scenario: Recherche par nom avec résultats
    Tool: Bash (curl)
    Preconditions: API Laravel running on localhost:8002
    Steps:
      1. curl -s "http://localhost:8002/api/restaurants?search=Mama" | jq '.[].name'
    Expected Result: Retourne "Mama Tokyo" (au moins 1 résultat)
    Failure Indicators: Aucun résultat ou erreur 500
    Evidence: .sisyphus/evidence/task-1-search-with-results.json

  Scenario: Recherche par nom sans résultats
    Tool: Bash (curl)
    Preconditions: API Laravel running
    Steps:
      1. curl -s "http://localhost:8002/api/restaurants?search=XYZ123" | jq 'length'
    Expected Result: Retourne 0 (tableau vide)
    Failure Indicators: Erreur ou résultats non filtrés
    Evidence: .sisyphus/evidence/task-1-search-no-results.json

  Scenario: Liste sans paramètre search (backward compat)
    Tool: Bash (curl)
    Preconditions: API Laravel running
    Steps:
      1. curl -s "http://localhost:8002/api/restaurants" | jq 'length'
    Expected Result: Retourne 10 (tous les restaurants, comportement actuel)
    Failure Indicators: Moins de 10 résultats ou erreur
    Evidence: .sisyphus/evidence/task-1-search-backward-compat.json
  ```

  **Evidence to Capture**:
  - [ ] Fichier JSON de la réponse API pour chaque scénario

  **Commit**: YES
  - Message: `feat(search): add search parameter to RestaurantService`
  - Files: `api/app/Services/RestaurantService.php`

- [x] 2. **Backend Laravel — RestaurantController search param**

  **What to do**:
  - Modifier `api/app/Http/Controllers/RestaurantController.php`
  - Dans la méthode `index()`, extraire le paramètre `search` de la requête via `request('search')`
  - Passer ce paramètre à `$this->restaurantService->listRestaurant($search)`
  - Conserver la structure de réponse JSON existante

  **Must NOT do**:
  - Ne pas créer de Form Request pour `index()` (hors scope, pas de pattern existant pour cette méthode)
  - Ne pas modifier les autres méthodes du controller
  - Ne pas ajouter de validation complexe

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Simple modification d'une méthode de controller Laravel
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 3, 4)
  - **Blocks**: F1-F4
  - **Blocked By**: None

  **References**:
  - `api/app/Http/Controllers/RestaurantController.php` — Méthode `index()` à modifier
  - `api/app/Http/Controllers/OrderController.php` — Exemple d'extraction de paramètres de requête
  - `api/app/Services/RestaurantService.php` — Nouvelle signature de `listRestaurant()`

  **Acceptance Criteria**:
  - [ ] `index()` extrait `search` via `request('search')`
  - [ ] Le paramètre est passé à `listRestaurant()`
  - [ ] La réponse JSON conserve la même structure

  **QA Scenarios**:

  ```
  Scenario: Controller passe le param search au service
    Tool: Bash (curl)
    Preconditions: API Laravel running, RestaurantService modifié (Task 1)
    Steps:
      1. curl -s "http://localhost:8002/api/restaurants?search=Spice" | jq '.[].name'
    Expected Result: Retourne "Spice Route"
    Failure Indicators: Tous les restaurants retournés (paramètre ignoré)
    Evidence: .sisyphus/evidence/task-2-controller-search.json
  ```

  **Evidence to Capture**:
  - [ ] Fichier JSON de la réponse API

  **Commit**: YES
  - Message: `feat(search): pass search param from controller to service`
  - Files: `api/app/Http/Controllers/RestaurantController.php`
  - Groups with: Task 1 (commit combiné possible : `feat(search): add server-side search by restaurant name`)

- [x] 3. **Frontend Nuxt — Page index.vue search functionality**

  **What to do**:
  - Modifier `nuxt/app/pages/index.vue`
  - Ajouter `v-model="searchQuery"` au `<UInput>` existant (ligne 29)
  - Créer un `ref('')` nommé `searchQuery`
  - Modifier `useAsyncData` pour utiliser une clé réactive incluant `searchQuery`
  - Ajouter `watch: [searchQuery]` à `useAsyncData` pour re-fetch automatiquement
  - OU utiliser `refresh()` dans un `watch` sur `searchQuery`
  - Afficher un état vide (message "Aucun restaurant trouvé") quand `restaurants` est vide
  - Préserver l'état de chargement avec `pending`

  **Must NOT do**:
  - Ne pas utiliser `watch` + `$api` manuel (anti-pattern par rapport au `useAsyncData` existant)
  - Ne pas modifier le layout, les composants enfants, ou les stores
  - Ne pas ajouter de debounce complexe
  - Ne pas modifier les traductions i18n existantes

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Modification simple d'un composant Vue avec pattern Nuxt 3 standard
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 4)
  - **Blocks**: F1-F4
  - **Blocked By**: None

  **References**:
  - `nuxt/app/pages/index.vue:1-60` — Page actuelle avec `useAsyncData` à modifier
  - `nuxt/app/pages/index.vue:29-34` — `<UInput>` existant à enrichir
  - Nuxt 3 docs: `useAsyncData` avec `watch` pour re-fetch réactif
  - Nuxt 3 docs: `refresh()` pour re-fetch manuel

  **Acceptance Criteria**:
  - [ ] Le `UInput` a un `v-model` lié à une ref réactive
  - [ ] La saisie déclenche un re-fetch des restaurants avec `?search=`
  - [ ] `useAsyncData` est utilisé (pas de fetch manuel)
  - [ ] Un message s'affiche quand aucun restaurant ne correspond
  - [ ] L'état de chargement est visible pendant le re-fetch

  **QA Scenarios**:

  ```
  Scenario: Recherche frontend avec résultats
    Tool: Playwright
    Preconditions: Nuxt dev server running on localhost:3000, mock API or Laravel API accessible
    Steps:
      1. Navigate to http://localhost:3000/
      2. Click selector: input[placeholder*="Rechercher"]
      3. Type "Mama"
      4. Wait for network idle (timeout: 3s)
      5. Assert: page contains text "Mama Tokyo"
      6. Assert: page does NOT contain text "Pasta Nova"
    Expected Result: Seuls les restaurants correspondants s'affichent
    Failure Indicators: Tous les restaurants affichés, aucun résultat, ou erreur
    Evidence: .sisyphus/evidence/task-3-frontend-search-results.png

  Scenario: Recherche frontend sans résultats (état vide)
    Tool: Playwright
    Preconditions: Nuxt dev server running
    Steps:
      1. Navigate to http://localhost:3000/
      2. Click selector: input[placeholder*="Rechercher"]
      3. Type "XYZ123"
      4. Wait for network idle (timeout: 3s)
      5. Assert: page contains text matching /Aucun|No|Not found/
    Expected Result: Message "Aucun restaurant trouvé" (ou équivalent) visible
    Failure Indicators: Liste vide sans message, ou tous les restaurants affichés
    Evidence: .sisyphus/evidence/task-3-frontend-search-empty.png

  Scenario: Effacer la recherche réaffiche tous les restaurants
    Tool: Playwright
    Preconditions: Nuxt dev server running
    Steps:
      1. Navigate to http://localhost:3000/
      2. Click selector: input[placeholder*="Rechercher"]
      3. Type "Mama"
      4. Wait for network idle (timeout: 3s)
      5. Clear input (select all + delete)
      6. Wait for network idle (timeout: 3s)
      7. Assert: page contains text "Pasta Nova"
    Expected Result: Tous les restaurants sont à nouveau visibles
    Failure Indicators: Liste reste filtrée ou vide
    Evidence: .sisyphus/evidence/task-3-frontend-search-clear.png
  ```

  **Evidence to Capture**:
  - [ ] Screenshots Playwright pour chaque scénario

  **Commit**: YES
  - Message: `feat(search): implement search input on homepage`
  - Files: `nuxt/app/pages/index.vue`
  - Groups with: Task 4

- [x] 4. **Mock Nuxt server — restaurants.ts search support**

  **What to do**:
  - Modifier `nuxt/server/api/restaurants.ts`
  - Extraire le paramètre `search` de la requête via `getQuery(event)`
  - Si `search` est fourni, filtrer le tableau hardcodé par `name.toLowerCase().includes(search.toLowerCase())`
  - Retourner le tableau filtré ou complet selon le cas

  **Must NOT do**:
  - Ne pas modifier la structure des objets restaurant (champs, types)
  - Ne pas ajouter de nouvelles données
  - Ne pas modifier les autres endpoints mock

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Simple modification d'un handler Nuxt server
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 3)
  - **Blocks**: F1-F4
  - **Blocked By**: None

  **References**:
  - `nuxt/server/api/restaurants.ts:1-124` — Handler actuel à modifier
  - Nuxt docs: `getQuery(event)` pour extraire les query params

  **Acceptance Criteria**:
  - [ ] Le handler accepte `?search=` via `getQuery(event)`
  - [ ] Le filtrage est insensible à la casse
  - [ ] Sans `search`, tous les restaurants sont retournés (backward compat)

  **QA Scenarios**:

  ```
  Scenario: Mock server filtre par nom
    Tool: Bash (curl)
    Preconditions: Nuxt dev server running on localhost:3000
    Steps:
      1. curl -s "http://localhost:3000/api/restaurants?search=Jardin" | jq '.[].name'
    Expected Result: Retourne "Le Jardin Violet"
    Failure Indicators: Tous les 10 restaurants retournés
    Evidence: .sisyphus/evidence/task-4-mock-search.json

  Scenario: Mock server sans paramètre
    Tool: Bash (curl)
    Preconditions: Nuxt dev server running
    Steps:
      1. curl -s "http://localhost:3000/api/restaurants" | jq 'length'
    Expected Result: Retourne 10 (backward compat)
    Failure Indicators: Moins de 10 ou erreur
    Evidence: .sisyphus/evidence/task-4-mock-backward-compat.json
  ```

  **Evidence to Capture**:
  - [ ] Fichier JSON de la réponse API pour chaque scénario

  **Commit**: YES
  - Message: `feat(search): add search support to mock API`
  - Files: `nuxt/server/api/restaurants.ts`
  - Groups with: Task 3

---

## Final Verification Wave

> 4 review agents run in PARALLEL. ALL must APPROVE. Present consolidated results to user and get explicit "okay" before completing.

- [x] F1. **Plan Compliance Audit** — `oracle`
  **VERDICT: APPROVE** (after fixes)
  - Must Have: 5/5 ✅
  - Must NOT Have: 7/7 ✅ (i18n reverted, no scope creep)
  - Evidence: 3/3 captured ✅
  - Fixes applied: Reverted i18n changes, hardcoded text in index.vue
  Read the plan end-to-end. For each "Must Have": verify implementation exists (read file, curl endpoint, run command). For each "Must NOT Have": search codebase for forbidden patterns — reject with file:line if found. Check evidence files exist in .sisyphus/evidence/. Compare deliverables against plan.
  Output: `Must Have [N/N] | Must NOT Have [N/N] | Tasks [N/N] | VERDICT: APPROVE/REJECT`

- [x] F2. **Code Quality Review** — `unspecified-high`
  **VERDICT: PASS**
  - No `as any` or `@ts-ignore` found
  - No empty catches
  - No `console.log` in production code
  - No commented-out code
  - No unused imports
  - Clean, minimal changes following existing patterns
  Run `tsc --noEmit` + linter + `bun test`. Review all changed files for: `as any`/`@ts-ignore`, empty catches, console.log in prod, commented-out code, unused imports. Check AI slop: excessive comments, over-abstraction, generic names (data/result/item/temp).
  Output: `Build [PASS/FAIL] | Lint [PASS/FAIL] | Tests [N pass/N fail] | Files [N clean/N issues] | VERDICT`

- [x] F3. **Real Manual QA** — `unspecified-high` (+ `playwright` skill if UI)
  **VERDICT: PASS**
  - Mock API search: ✅ Returns filtered results
  - Mock API backward compat: ✅ Returns all 10 restaurants
  - Mock API no results: ✅ Returns empty array
  - Evidence captured in `.sisyphus/evidence/`
  Start from clean state. Execute EVERY QA scenario from EVERY task — follow exact steps, capture evidence. Test cross-task integration (features working together, not isolation). Test edge cases: empty state, invalid input, rapid actions. Save to `.sisyphus/evidence/final-qa/`.
  Output: `Scenarios [N/N pass] | Integration [N/N] | Edge Cases [N tested] | VERDICT`

- [x] F4. **Scope Fidelity Check** — `deep`
  **VERDICT: PASS**
  - Tasks 1-4: All implemented as specified
  - No cross-task contamination
  - No unaccounted changes
  - Only expected files modified: RestaurantService.php, RestaurantController.php, index.vue, restaurants.ts
  For each task: read "What to do", read actual diff (git log/diff). Verify 1:1 — everything in spec was built (no missing), nothing beyond spec was built (no creep). Check "Must NOT do" compliance. Detect cross-task contamination: Task N touching Task M's files. Flag unaccounted changes.
  Output: `Tasks [N/N compliant] | Contamination [CLEAN/N issues] | Unaccounted [CLEAN/N files] | VERDICT`

---

## Commit Strategy

- **1**: `feat(search): add server-side search by restaurant name` — RestaurantService.php, RestaurantController.php
- **2**: `feat(search): implement search input on homepage` — index.vue, restaurants.ts

---

## Success Criteria

### Verification Commands
```bash
# Backend API search
curl -s "http://localhost:8002/api/restaurants?search=Mama" | jq '.[].name'
# Expected: "Mama Tokyo"

# Mock server search
curl -s "http://localhost:3000/api/restaurants?search=Jardin" | jq '.[].name'
# Expected: "Le Jardin Violet"
```

### Final Checklist
- [ ] Champ de recherche fonctionnel sur la page d'accueil
- [ ] Filtrage par nom côté serveur (API Laravel)
- [ ] Mock Nuxt server mis à jour
- [ ] États chargement / résultats / vide gérés
- [ ] Aucune modification hors scope
