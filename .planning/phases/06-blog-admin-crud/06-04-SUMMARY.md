---
phase: 06-blog-admin-crud
plan: 04
subsystem: blog-admin
tags: [livewire, easymde, markdown, medialibrary, s3, content-01]
requires: ["06-02", "06-03"]
provides:
  - "PostForm write component (create/edit/publish/unpublish)"
  - "EasyMDE Markdown editor wired (postEditor Alpine factory)"
  - "cover upload → Scaleway S3 via medialibrary"
affects:
  - "resources/js/app.js (EasyMDE import + postEditor registration)"
  - "resources/css/app.css (.easymde-wrap token skin)"
  - "blog.index cache (flushed on status change)"
tech-stack:
  added:
    - "easymde ^2.21.0 (npm) — Markdown editor (SimpleMDE successor)"
  patterns:
    - "wire:ignore + Alpine x-data factory + $wire.set(...,false) deferred sync for non-Livewire JS islands"
    - "Livewire WithFileUploads → store('livewire-tmp','s3') → addMediaFromDisk → toMediaCollection (S3 temp-disk safe)"
    - "slug auto-from-title while draft, locked on publish"
key-files:
  created:
    - "resources/js/post-editor.js"
    - "app/Livewire/PostForm.php"
    - "resources/views/livewire/post-form.blade.php"
    - "tests/Feature/PostFormTest.php"
  modified:
    - "resources/js/app.js"
    - "resources/css/app.css"
    - "package.json"
    - "package-lock.json"
    - "tests/Pest.php"
decisions:
  - "Editor body font = Inter (--font-sans), not Fredoka (Fredoka reserved for headings)"
  - "EasyMDE skinned via .easymde-wrap scope in @layer components — preflight NOT disabled globally"
  - "slug-collision resolved with numeric suffix (-2, -3) excluding the current post id"
metrics:
  duration: "~1 task-pair (2 commits)"
  tasks_completed: 2
  files_touched: 9
  completed: 2026-05-30
---

# Phase 6 Plan 04: Blog Editor (PostForm + EasyMDE) Summary

Pierre's publication autonomy slice (CONTENT-01): a full `/admin/blog` create/edit form with an EasyMDE Markdown body, cover upload to Scaleway S3, slug auto/lock-on-publish, a segmented Brouillon/Publié toggle, inline-confirm unpublish, and `blog.index` cache flush on every status change — no code, no git.

## What Was Built

**Task 1 — EasyMDE install + wiring (commit `76b6266`)**
- `npm install easymde ^2.21.0` (legitimacy gate pre-approved by the user; npmjs verified: MIT, no pre/postinstall scripts, canonical repo Ionaru/easy-markdown-editor).
- `resources/js/app.js`: imports `easymde/dist/easymde.min.css` + `EasyMDE` before Alpine, exposes `window.EasyMDE`, registers `Alpine.data('postEditor', postEditor)`.
- `resources/js/post-editor.js`: `postEditor(initialBody)` factory — news up EasyMDE on the `x-ref` textarea, seeds edit-mode content, binds `codemirror.on('change')` → `$wire.set('body', value, false)` (deferred, no per-keystroke network).
- `resources/css/app.css`: `.easymde-wrap` re-skin scoped inside `@layer components` (toolbar navy/sand border + ink icons + azure active; CodeMirror min-height 320px, Inter, sand-50 bg; preview pane tokens). Tailwind preflight left intact globally.
- `npm run build` green (EasyMDE bundles, CSS compiles).

**Task 2 — PostForm + post-form.blade.php (commit `4b90276`, TDD)**
- `app/Livewire/PostForm.php`: `WithFileUploads`; `#[Validate]` on title/slug/body/excerpt/status/cover; `mount(?int $postId)` hydrates everything except cover; `submit()` mirrors ClientForm's try/catch + `Log::error` + `addError('save')`.
  - Slug: while draft → `$this->slug ?: Str::slug($title)` with numeric-suffix dedupe; once the persisted post is `published` → keeps the existing slug (D-04 lock, ignores tampered input).
  - Cover: `store('livewire-tmp','s3')` then `addMediaFromDisk(...,'s3')->toMediaCollection('cover','s3')` (avoids `getRealPath()` which is invalid when the Livewire temp disk is S3 — RESEARCH Open Question 1 RESOLVED).
  - `Cache::forget('blog.index')` on submit; `dispatch('post-saved')` + redirect navigate.
- `resources/views/livewire/post-form.blade.php`: UI-SPEC Surfaces 2-3 — title; conditional slug (editable+prefix while draft / locked pill+lock-icon while published); EasyMDE `wire:ignore .easymde-wrap` block + hidden `wire:model="body"`; excerpt; Livewire cover dropzone with preview/Remplacer/Retirer + `wire:loading` "Envoi…"; status `role="radiogroup"` segmented control + helper copy; inline Alpine confirm Dépublier (no modal, danger only on confirm); submit/cancel + save-error block.
- `tests/Feature/PostFormTest.php`: create, mount-hydrate, slug-lock-on-publish, cache-flush, missing-title/body validation, cover-mime rejection, cover-persist — 8 tests.

## Verification

- `npm run build`: green (exit 0).
- `PostFormTest`: 8/8 green. `--filter "Post|Blog"`: 76/76 green.
- Acceptance greps all matched (`WithFileUploads`, `Cache::forget('blog.index')`, `toMediaCollection('cover','s3')`, blade `wire:ignore` / `role="radiogroup"` / slug `@if` / inline-confirm `x-data`).

### Test execution note (worktree environment limitation)

The Pest feature suite **cannot run in-place from this git worktree**: `pest()->extend(Tests\TestCase::class)` does not bind to worktree-pathed test files (the app boots against the symlinked main-repo vendor, so TestCase/container resolution breaks). Proof: the already-merged `BlogTest` runs **0/12 from the worktree** but **12/12 from main**. This is pre-existing and not specific to this plan.

GREEN was therefore validated by **temporarily copying the 3 new files into the main repo, running pest, then deleting them** — leaving main's working tree pristine and **without dumping the autoloader** (PSR-4 resolves `App\Livewire\PostForm` from `app/` with no dump). Results: PostFormTest 8/8, Post|Blog 76/76. The post-merge test gate re-validates in main.

Because RED could not execute in-place, the conventional RED commit was folded into the GREEN commit; the commit message documents this honestly.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Test bug] Cover-rejection test triggered Livewire preview-mime guard instead of the validation rule**
- **Found during:** Task 2 GREEN run.
- **Issue:** `UploadedFile::fake()->create('virus.pdf', ...)` made Livewire throw "File with extension pdf is not previewable" on `->set('cover',...)`, before `submit()`/validation ran — so the test asserted the wrong layer.
- **Fix:** switched to a `.gif` fake image (previewable, so the upload is accepted) that fails the `mimes:jpg,jpeg,png,webp` rule on submit — exercising the actual `#[Validate]` rule (threat T-06-10).
- **Files modified:** `tests/Feature/PostFormTest.php` (within Task 2 commit `4b90276`).

**2. [Rule 3 - Blocking] Worktree had no `vendor/` and feature tests could not bind**
- **Found during:** Task 2 GREEN.
- **Issue:** the worktree shipped without a `vendor/` symlink; even after symlinking to main's vendor, pest could not bind `Tests\TestCase` to worktree-pathed files.
- **Fix:** symlinked `vendor` → main (gitignored, not committed); added a `STRIP ON MERGE` worktree-only PSR-4 crutch in `tests/Pest.php`; validated GREEN from the main repo as described above. No autoloader dump in either tree.

## Known Stubs

None. The editor is fully wired end-to-end (create/edit/publish/unpublish, cover→S3, cache flush). Live S3 cover upload depends on Scaleway bucket CORS (`user_setup`) — operational config, verified at Checkpoint 2, not a code stub.

## STRIP ON MERGE markers

- `tests/Pest.php` — worktree-only PSR-4 autoloader crutch block (clearly commented `STRIP ON MERGE`). Remove on merge; once `app/` lives at the repo root the normal autoloader resolves `App\` correctly.
- `vendor` symlink in the worktree is gitignored and never committed — nothing to strip from git, but it must not be carried into main.

## Self-Check: PASSED

Files (worktree):
- FOUND: resources/js/post-editor.js
- FOUND: app/Livewire/PostForm.php
- FOUND: resources/views/livewire/post-form.blade.php
- FOUND: tests/Feature/PostFormTest.php

Commits:
- FOUND: 76b6266 (Task 1 — easymde + wiring)
- FOUND: 4b90276 (Task 2 — PostForm + blade + test)

## Status

Tasks 1-2 complete and committed on `worktree-agent-a7d6c2e78dff34445`. **Paused at Checkpoint 2** (full-editor human-verify, `gate="blocking"`) — automation complete, awaiting human verification of the live editor, cover S3 round-trip, slug-lock, and unpublish 410 before the plan is marked done.
