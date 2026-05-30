---
status: partial
phase: 06-blog-admin-crud
source: [06-VERIFICATION.md]
started: 2026-05-30
updated: 2026-05-30
---

## Current Test

[awaiting human testing]

## Tests

### 1. EasyMDE editor rendering & validation-error DOM survival
expected: On `/admin/blog/create`, the EasyMDE toolbar renders sand/azure-skinned (not unstyled); writing Markdown + toggling preview works; submitting with an empty body then refilling does NOT blank the editor (the `wire:ignore` island survives the Livewire validation re-render).
result: [pending]

### 2. Cover upload via Scaleway S3 CORS
expected: Uploading a landscape cover shows a preview and persists to the `cover` media collection on Scaleway S3. Requires the Scaleway bucket CORS to allow browser PUT to the `livewire-tmp/` prefix — if CORS is unset the upload spinner hangs.
result: [pending]

### 3. Slug lock visual + publish round-trip
expected: Typing a title auto-fills the slug with the `dloazurpiscines.com/blog/` prefix; switching status to "Publié" and saving renders the slug as a locked pill with a lock icon (not a disabled input); the post is then live at `GET /blog/{slug}` (200).
result: [pending]

### 4. Unpublish inline confirm → 410
expected: Clicking "Dépublier" shows an inline Alpine confirm block (no modal); confirming + saving flips status to "Brouillon" in `/admin/blog` and `GET /blog/{slug}` returns 410 Gone.
result: [pending]

## Summary

total: 4
passed: 0
issues: 0
pending: 4
skipped: 0
blocked: 0

## Gaps
