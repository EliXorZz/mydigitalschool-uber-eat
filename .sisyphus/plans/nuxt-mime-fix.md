# Fix Nuxt Dev Server - Absolute Path Module URLs

## TL;DR

> **Quick Summary**: Fix the Nuxt 4.1.3 dev server that generates absolute filesystem paths in module URLs (e.g. `/_nuxt/Users/dylan/...`), causing all JS modules to fail with "disallowed MIME type" errors.
>
> **Deliverables**:
> - Diagnosed root cause (cache corruption vs Nuxt core bug)
> - Fixed dev server serving modules with correct relative paths
> - Verified HMR works and no absolute paths leak into module URLs
>
> **Estimated Effort**: Short (15-30 min execution, mostly waiting for installs/rebuilds)
> **Parallel Execution**: NO - sequential diagnostic → fix → verification
> **Critical Path**: T1 (Diagnostic) → T2 (Fix) → T3 (Verification) → F1-F3 (Final Review)

---

## Context

### Original Request
User reported: "Loading module from `http://localhost:3000/_nuxt/Users/dylan/MyDigitalSchool/uber-eat/nuxt/node_modules/nuxt/dist/app/entry.async.js` was blocked because of a disallowed MIME type ('')". Same error for `/_nuxt/@vite/client`. The dev server is completely broken.

### Interview Summary
**Key Discussions**:
- Error affects ALL modules, not a single file (systemic Vite dev server issue)
- URL contains absolute filesystem path `/Users/dylan/...` instead of relative path
- User recently modified `app/pages/index.vue`
- User doesn't know what triggered the error

**Research Findings**:
- Nuxt 4.1.3 + Vite 7.1.9 installed
- `@vite-pwa/nuxt` 1.0.7 present but likely a red herring (Metis assessment)
- `.nuxt/` generated files contain absolute filesystem paths
- This is a known Nuxt core bug in `vite-node.ts` (PR #34303, #34810) fixed in Nuxt 4.4.0+
- Cache corruption is also a possible cause

### Metis Review
**Identified Gaps** (addressed in plan):
- Missing diagnostic steps: `npx nuxt cleanup`, SSR setting check, reverse proxy check
- Risk of scope creep: upgrading unrelated deps, adding custom Vite config, modifying app code
- Need for agent-executable acceptance criteria (curl/grep commands, not manual testing)
- Edge case: Windows path normalization, SSR disabled scenario, PWA service worker cache

---

## Work Objectives

### Core Objective
Restore the Nuxt dev server so it serves JS modules with relative URLs (not absolute filesystem paths), eliminating the MIME type errors and making HMR functional again.

### Concrete Deliverables
- Dev server starts without MIME type console errors
- `http://localhost:3000/_nuxt/entry.async.js` returns HTTP 200 with `application/javascript` MIME type
- `http://localhost:3000/_nuxt/@vite/client` returns HTTP 200
- No absolute filesystem paths (`/Users/dylan/...`) appear in served HTML or module URLs
- HMR works after touching a Vue file

### Definition of Done
- [ ] All verification curl commands return expected results
- [ ] Browser loads the app without module loading errors
- [ ] `npx nuxt dev` starts cleanly with no errors

### Must Have
- [ ] Zero-impact fix (no app code changes)
- [ ] Reversible if fix causes other issues
- [ ] Verification of fix with automated checks

### Must NOT Have (Guardrails)
- [ ] MUST NOT modify `node_modules/` files directly
- [ ] MUST NOT add custom Vite configuration unless minimal fixes fail
- [ ] MUST NOT remove `@vite-pwa/nuxt` unless proven it's the root cause
- [ ] MUST NOT upgrade unrelated dependencies (nuxt/ui, i18n, etc.) beyond compatibility requirements
- [ ] MUST NOT modify app code (`app/pages/index.vue`, components, etc.)
- [ ] MUST NOT add error boundaries, fallbacks, or documentation beyond this fix

---

## Verification Strategy (MANDATORY)

> **ZERO HUMAN INTERVENTION** - ALL verification is agent-executed. No exceptions.

### Test Decision
- **Infrastructure exists**: NO (this is a dev server fix, not a feature requiring unit tests)
- **Automated tests**: NO
- **Agent-Executed QA**: YES - every task includes curl/grep verification commands

### QA Policy
Every task MUST include agent-executed QA scenarios with exact commands, expected outputs, and evidence capture.

- **API/Backend**: Use Bash (curl) - Send requests, assert status + headers
- **File inspection**: Use Bash (grep) - Search for absolute paths in generated files
- **Dev server**: Use Bash - Start server, check logs for errors

---

## Execution Strategy

### Parallel Execution Waves

```
Wave 1 (Sequential Diagnostic → Fix → Verify):
├── Task 1: Diagnostic - nuxt cleanup + reproduction check [quick]
├── Task 2: Fix - Upgrade Nuxt to 4.4.0+ or apply workaround [quick]
└── Task 3: Verification - Automated checks + HMR test [quick]

Wave FINAL (After ALL tasks — 3 parallel reviews):
├── Task F1: Plan compliance audit (oracle)
├── Task F2: Code quality + build check (unspecified-high)
└── Task F3: Real manual QA (unspecified-high)
-> Present results -> Get explicit user okay

Critical Path: T1 → T2 → T3 → F1-F3 → user okay
Parallel Speedup: N/A (sequential diagnostic required)
Max Concurrent: 3 (Wave FINAL only)
```

### Dependency Matrix

- **T1**: None → Blocks T2
- **T2**: T1 → Blocks T3
- **T3**: T2 → Blocks F1-F3
- **F1-F3**: T3 → None (final review)

### Agent Dispatch Summary

- **Wave 1**: **3** - T1 → `quick`, T2 → `quick`, T3 → `quick`
- **FINAL**: **3** - F1 → `oracle`, F2 → `unspecified-high`, F3 → `unspecified-high`

---

## TODOs

- [ ] 1. Diagnostic — `nuxt cleanup` + Reproduction Check

  **What to do**:
  1. Stop any running dev server (`pkill -f "nuxt dev"`)
  2. Run `npx nuxt cleanup` in `/Users/dylan/MyDigitalSchool/uber-eat/nuxt/`
  3. Delete `node_modules/.vite/` if it exists
  4. Check `nuxt.config.ts` for `ssr` setting (grep for `ssr:`)
  5. Start dev server: `npx nuxt dev`
  6. Open browser to `http://localhost:3000` and check DevTools console
  7. Capture the result: does the MIME type error still reproduce?

  **Must NOT do**:
  - MUST NOT modify any source files during diagnostic
  - MUST NOT run `npm install` or `pnpm install` during this step
  - MUST NOT disable `@vite-pwa/nuxt` yet (red herring per Metis)

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Diagnostic steps are fast, no complex logic
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: NO (must complete before T2)
  - **Parallel Group**: Sequential (Wave 1)
  - **Blocks**: T2
  - **Blocked By**: None

  **References**:
  - `nuxt.config.ts` - Check for `ssr` setting
  - `.nuxt/dev/index.mjs` - Inspect for `file://` URLs after cleanup
  - Metis finding: `npx nuxt cleanup` is the zero-cost first step

  **Acceptance Criteria**:
  - [ ] `npx nuxt cleanup` completes successfully
  - [ ] Dev server starts after cleanup
  - [ ] Result captured: ERROR REPRODUCES or ERROR FIXED

  **QA Scenarios**:

  ```
  Scenario: Verify cleanup completes and dev server starts
    Tool: Bash
    Preconditions: Dev server stopped
    Steps:
      1. cd /Users/dylan/MyDigitalSchool/uber-eat/nuxt && npx nuxt cleanup
      2. rm -rf node_modules/.vite 2>/dev/null; echo "vite cache cleared"
      3. npx nuxt dev &
      4. sleep 10
      5. curl -s http://localhost:3000/ > /tmp/nuxt-home.html
    Expected Result: HTTP 200 returned, HTML saved to /tmp/nuxt-home.html
    Failure Indicators: Dev server fails to start, port 3000 not responding
    Evidence: .sisyphus/evidence/task-1-server-start.log

  Scenario: Check for absolute paths in served HTML
    Tool: Bash (grep)
    Preconditions: /tmp/nuxt-home.html exists from previous scenario
    Steps:
      1. grep -c "Users/dylan" /tmp/nuxt-home.html || echo "0"
    Expected Result: "0" (no absolute paths found)
    Failure Indicators: Count > 0 means absolute paths still leak
    Evidence: .sisyphus/evidence/task-1-absolute-paths-check.log
  ```

  **Evidence to Capture**:
  - [ ] Dev server startup logs
  - [ ] HTML page source showing script src URLs
  - [ ] Screenshot of browser console errors if any

  **Commit**: NO (diagnostic only)

---

- [ ] 2. Fix — Upgrade Nuxt to 4.4.0+ (or Workaround if Upgrade Blocked)

  **What to do**:
  1. If T1 shows ERROR FIXED after cleanup → SKIP this task, mark as done
  2. If T1 shows ERROR REPRODUCES → This is the known Nuxt core bug
  3. Check current Nuxt version in `package.json`
  4. Upgrade Nuxt to `^4.4.0` (or latest stable): `npm install nuxt@^4.4.0`
  5. If `npm install` fails due to peer dependency conflicts:
     - Check `@nuxt/ui`, `@nuxtjs/i18n` compatibility with Nuxt 4.4+
     - If conflicts exist, try `npm install nuxt@latest --legacy-peer-deps`
     - If still blocked, apply workaround instead of upgrade
  6. Workaround (if upgrade blocked): Add minimal `vite.server.fs.allow` config to `nuxt.config.ts` to force relative resolution:
     ```ts
     export default defineNuxtConfig({
       // ...existing config
       vite: {
         server: {
           fs: {
             allow: ['..'] // Force relative path resolution
           }
         }
       }
     })
     ```
     **WARNING**: Only use workaround if upgrade is genuinely blocked. This is a last resort.
  7. Run `npx nuxt cleanup` again after changes
  8. Start dev server and verify fix

  **Must NOT do**:
  - MUST NOT upgrade `@nuxt/ui`, `@nuxtjs/i18n`, or other modules unless strictly required for compatibility
  - MUST NOT modify app code to "work around" the bug
  - MUST NOT add broad custom Vite configuration beyond the minimal workaround
  - MUST NOT downgrade Vite (this is a Nuxt bug, not a Vite bug)

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Package upgrade or minimal config change
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: NO (depends on T1 result)
  - **Parallel Group**: Sequential (Wave 1)
  - **Blocks**: T3
  - **Blocked By**: T1

  **References**:
  - `package.json` - Current Nuxt version to check
  - Nuxt PR #34303, #34810 - Known fix for vite-node.ts absolute path bug
  - Metis directive: Start with cleanup → version check → minimal config → targeted workaround

  **Acceptance Criteria**:
  - [ ] Nuxt upgraded to 4.4.0+ OR workaround applied
  - [ ] `npm install` completes without unresolvable errors
  - [ ] Dev server starts after fix application

  **QA Scenarios**:

  ```
  Scenario: Verify Nuxt upgrade succeeds
    Tool: Bash
    Preconditions: T1 completed, bug reproduces after cleanup
    Steps:
      1. cd /Users/dylan/MyDigitalSchool/uber-eat/nuxt
      2. npm install nuxt@^4.4.0 2>&1 | tee /tmp/nuxt-upgrade.log
      3. grep -E "(ERR|error|failed)" /tmp/nuxt-upgrade.log | head -5
    Expected Result: No critical errors in install log
    Failure Indicators: Peer dependency conflicts that can't be resolved
    Evidence: .sisyphus/evidence/task-2-upgrade.log

  Scenario: Verify fix resolves absolute paths
    Tool: Bash (curl + grep)
    Preconditions: Dev server restarted after upgrade
    Steps:
      1. curl -s http://localhost:3000/ > /tmp/nuxt-fixed.html
      2. grep -c "Users/dylan" /tmp/nuxt-fixed.html || echo "0"
      3. grep -c "_nuxt/entry" /tmp/nuxt-fixed.html || echo "0"
    Expected Result: "0" absolute paths, ">0" relative _nuxt paths
    Failure Indicators: Absolute paths still present
    Evidence: .sisyphus/evidence/task-2-fixed-paths.log
  ```

  **Evidence to Capture**:
  - [ ] `npm install` output log
  - [ ] Updated `package.json` and lockfile diff
  - [ ] HTML source after fix showing relative paths

  **Commit**: YES
  - Message: `fix(nuxt): upgrade to 4.4.0+ to resolve dev server absolute path bug`
  - Files: `nuxt/package.json`, `nuxt/package-lock.json` (or `pnpm-lock.yaml` / `yarn.lock`)
  - Pre-commit: `cd /Users/dylan/MyDigitalSchool/uber-eat/nuxt && npx nuxt cleanup`

---

- [ ] 3. Verification — Automated Checks + HMR Test

  **What to do**:
  1. Verify dev server is running on `localhost:3000`
  2. Run all verification curl commands from Success Criteria section
  3. Test HMR: touch a Vue file and verify browser console shows `[vite] hot updated`
  4. Check `.nuxt/dev/index.mjs` for any remaining `file://` URLs
  5. Capture all evidence

  **Must NOT do**:
  - MUST NOT skip any verification step
  - MUST NOT declare fix complete if any check fails

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Verification commands, no complex logic
  - **Skills**: []

  **Parallelization**:
  - **Can Run In Parallel**: NO (depends on T2)
  - **Parallel Group**: Sequential (Wave 1)
  - **Blocks**: F1-F3
  - **Blocked By**: T2

  **References**:
  - Success Criteria section in this plan for exact curl commands

  **Acceptance Criteria**:
  - [ ] `curl -I http://localhost:3000/_nuxt/entry.async.js` → HTTP 200 + `Content-Type: application/javascript`
  - [ ] `curl -I http://localhost:3000/_nuxt/@vite/client` → HTTP 200
  - [ ] `grep -c "Users/dylan" /tmp/nuxt-fixed.html` → 0
  - [ ] HMR works after touching `app/pages/index.vue`

  **QA Scenarios**:

  ```
  Scenario: Verify entry.async.js loads correctly
    Tool: Bash (curl)
    Preconditions: Dev server running after fix
    Steps:
      1. curl -I http://localhost:3000/_nuxt/entry.async.js 2>&1 | grep -E "(HTTP|Content-Type)"
    Expected Result: "HTTP/1.1 200 OK" and "Content-Type: application/javascript"
    Failure Indicators: 404, 500, or missing/wrong Content-Type
    Evidence: .sisyphus/evidence/task-3-entry-async.log

  Scenario: Verify Vite client loads
    Tool: Bash (curl)
    Preconditions: Dev server running
    Steps:
      1. curl -I http://localhost:3000/_nuxt/@vite/client 2>&1 | grep -E "(HTTP|Content-Type)"
    Expected Result: "HTTP/1.1 200 OK"
    Failure Indicators: Any non-200 status
    Evidence: .sisyphus/evidence/task-3-vite-client.log

  Scenario: Verify HMR works
    Tool: Bash
    Preconditions: Dev server running (background process from T1/T2)
    Steps:
      1. touch /Users/dylan/MyDigitalSchool/uber-eat/nuxt/app/pages/index.vue
      2. sleep 3
      3. ps aux | grep "nuxt dev" | grep -v grep | awk '{print $2}' | head -1 > /tmp/nuxt-pid.txt
      4. lsof -p $(cat /tmp/nuxt-pid.txt) 2>/dev/null | head -1 || echo "checking logs"
      5. tail -n 50 /tmp/nuxt-dev.log 2>/dev/null | grep -i "hot updated\|hmr" | head -3
    Expected Result: Log output contains "[vite] hot updated" or "hmr" messages
    Failure Indicators: No HMR-related messages in logs
    Evidence: .sisyphus/evidence/task-3-hmr.log
  ```

  **Evidence to Capture**:
  - [ ] All curl response headers
  - [ ] Browser console screenshot showing zero module errors
  - [ ] HMR log output

  **Commit**: NO (verification only)

---

## Final Verification Wave (MANDATORY — after ALL implementation tasks)

> 3 review agents run in PARALLEL. ALL must APPROVE. Present consolidated results to user and get explicit "okay" before completing.

- [ ] F1. **Plan Compliance Audit** — `oracle`
  Read the plan end-to-end. For each "Must Have": verify implementation exists (check package.json for nuxt version ≥ 4.4.0, or check nuxt.config.ts for workaround). For each "Must NOT Have": search codebase for forbidden patterns — reject with file:line if found. Check evidence files exist in `.sisyphus/evidence/`. Compare deliverables against plan.
  Output: `Must Have [N/N] | Must NOT Have [N/N] | Tasks [N/N] | VERDICT: APPROVE/REJECT`

- [ ] F2. **Code Quality + Build Check** — `unspecified-high`
  Run `npm audit` to check for new vulnerabilities introduced by upgrade. Verify `package.json` is valid JSON. Check that only `nuxt` version changed (no accidental upgrades of unrelated packages).
  Output: `Audit [PASS/FAIL] | package.json valid [YES/NO] | Scope clean [YES/NO] | VERDICT`

- [ ] F3. **Real Manual QA** — `unspecified-high`
  Start from clean state. Execute EVERY QA scenario from T3 — follow exact steps, capture evidence. Test cross-browser (if possible): Chrome, Safari, Firefox. Test edge case: open app in incognito mode to bypass any service worker cache.
  Output: `Scenarios [N/N pass] | Browsers [N/N] | Incognito [PASS/FAIL] | VERDICT`

---

## Commit Strategy

- **T1-T3**: `fix(nuxt): resolve dev server absolute path module URLs` - nuxt/package.json, nuxt/package-lock.json (or equivalent)

---

## Success Criteria

### Verification Commands
```bash
# Dev server starts without errors
cd /Users/dylan/MyDigitalSchool/uber-eat/nuxt && npx nuxt dev &
# EXPECT: No console errors containing "disallowed MIME type"

# Module URLs use relative paths
curl -s http://localhost:3000/ | grep -o 'src="[^"]*"' | grep -E '(Users|/Users|file://)' || echo "PASS: No absolute paths found"
# EXPECT: "PASS: No absolute paths found"

# entry.async.js loads successfully
curl -I http://localhost:3000/_nuxt/entry.async.js
# EXPECT: HTTP/1.1 200 OK with Content-Type: application/javascript

# Vite client loads successfully
curl -I http://localhost:3000/_nuxt/@vite/client
# EXPECT: HTTP/1.1 200 OK

# No absolute paths in .nuxt/dev/index.mjs
grep -c "file:///Users" /Users/dylan/MyDigitalSchool/uber-eat/nuxt/.nuxt/dev/index.mjs || echo "0"
# EXPECT: 0
```

### Final Checklist
- [ ] All "Must Have" present
- [ ] All "Must NOT Have" absent
- [ ] Dev server starts cleanly
- [ ] Browser loads app without module errors
- [ ] HMR works after file change
