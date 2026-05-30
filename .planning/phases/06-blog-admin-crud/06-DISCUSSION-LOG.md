# Phase 6: blog-admin-crud - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-05-30
**Phase:** 6-blog-admin-crud
**Areas discussed:** Markdown editor UX, Cover image handling, Publish/unpublish model, Slug + SEO continuity
**Mode:** advisor (research-backed comparison tables; calibration tier: standard)

---

## Markdown editor UX

| Option | Description | Selected |
|--------|-------------|----------|
| EasyMDE | Toolbar + live preview, keeps Markdown storage/render/reading-time intact (~15 lines Alpine glue, scope CSS vs Tailwind reset) | ✓ |
| Plain textarea | Zero deps, native wire:model, but requires Markdown fluency | |
| Tiptap WYSIWYG | Best UX but emits HTML — breaks storage format + reading-time + renderer + migration | |

**User's choice:** EasyMDE
**Notes:** Body stays raw Markdown; preserves existing `<x-markdown>` render path and word-count reading_time. Tailwind 4 CSS-scoping flagged as the main integration risk.

---

## Cover image handling

| Option | Description | Selected |
|--------|-------------|----------|
| medialibrary → S3 | Livewire upload, auto-thumbnail, stable getFirstMediaUrl(); uses medialibrary already in stack | ✓ |
| Raw Storage::disk('s3') | Matches existing Passage-photos pattern (§Pitfall 6), but no auto-thumbnail | |
| Manual URL string | Zero infra, but Pierre must self-host images — fails autonomy goal | |

**User's choice:** medialibrary → S3
**Notes:** Intentional divergence from Passage photos (raw S3). Needs `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3` + Scaleway CORS. 3 migrated articles have no cover → og-default.jpg fallback unchanged.

---

## Publish / unpublish model

| Option | Description | Selected |
|--------|-------------|----------|
| enum draft/published + 410 | "brouillon/publié", scopePublished() gates public+sitemap; 410 on unpublish of indexed post, 404 for fresh draft | ✓ |
| published_at timestamp | null=draft, future=scheduled; unintuitive for non-dev | |
| 3-value enum + archived | Overkill for single-author blog | |

**User's choice:** enum draft/published + 410
**Notes:** 3 articles migrate as published. 410 vs 404 distinction is the SEO-critical detail.

---

## Slug + SEO continuity

| Option | Description | Selected |
|--------|-------------|----------|
| Auto + lock on publish | Str::slug from title, editable while draft, read-only once published. Zero redirect infra | ✓ |
| Editable + 301 redirect | Editable anytime, auto-301 via post_redirects table; extra migration + chain risk | |
| Permanently locked | Immutable after first save; typo-forever friction | |

**User's choice:** Auto + lock on publish
**Notes:** Migration is an idempotent seeder reusing BlogRepository parser via updateOrCreate(slug) regardless of slug choice; preserves dates/show_date/author/excerpt; keep .md files as backup behind a config flag.

---

## Claude's Discretion

- CRUD wiring shape — follow established Clients pattern (controllers + Livewire).
- `Post` schema details (timestamps, indexes) beyond named fields.
- Seeder production-guard mechanism (idempotent upsert satisfies re-runnability).

## Deferred Ideas

- Scheduled publishing (future-dated published_at).
- Editable-slug-after-publish + 301 redirect table (post_redirects).
- Categories/tags, comments, blog search, RSS, multi-author.
