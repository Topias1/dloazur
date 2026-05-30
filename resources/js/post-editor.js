/**
 * Alpine.data('postEditor') factory — blog admin Markdown editor (Plan 06-04, CONTENT-01).
 *
 * Wraps EasyMDE inside a `wire:ignore` container so Livewire never re-renders the
 * editor DOM (D-01). The editor stays a pure-DOM island; its value is synced back
 * into the Livewire `body` property on every change via $wire.set(..., false) — the
 * third arg `false` means "no debounce, no network roundtrip" (Livewire 3 deferred
 * model update), so the hidden `wire:model="body"` is always current at submit time
 * without spamming the server on each keystroke.
 *
 * window.EasyMDE is exposed in app.js (imported before Alpine boots).
 *
 * @param {string} initialBody - raw Markdown to seed the editor (edit mode); '' on create.
 */
export function postEditor(initialBody) {
    return {
        editor: null,

        /**
         * @param {HTMLTextAreaElement} textarea - the x-ref="ta" element to mount onto.
         */
        init(textarea) {
            this.editor = new window.EasyMDE({
                element: textarea,
                spellChecker: false,
                autosave: { enabled: false },
                status: false,
                // Minimal toolbar per UI-SPEC Surface 2 — no kitchen-sink buttons.
                toolbar: [
                    'bold',
                    'italic',
                    'heading',
                    '|',
                    'unordered-list',
                    'ordered-list',
                    'link',
                    'image',
                    '|',
                    'preview',
                    'side-by-side',
                    'fullscreen',
                ],
            });

            // Seed edit-mode content. Guard against null/undefined/empty.
            if (initialBody) {
                this.editor.value(initialBody);
            }

            // Sync CodeMirror → Livewire on every change. Third arg `false` =
            // deferred (no immediate network request); the hidden wire:model="body"
            // carries the value to the server on submit.
            this.editor.codemirror.on('change', () => {
                this.$wire.set('body', this.editor.value(), false);
            });
        },
    };
}
