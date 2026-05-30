<form wire:submit.prevent="submit" class="space-y-5">

    {{-- Titre --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">
            Titre <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <input
            wire:model="title"
            type="text"
            maxlength="160"
            class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none"
            placeholder="Titre de l’article">
        @error('title')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Slug : éditable en brouillon, verrouillé une fois publié (D-04) --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Adresse de l’article</label>
        @if ($status === 'published')
            {{-- Pill verrouillé — pas d'input désactivé grisé (UI-SPEC Surface 2) --}}
            <div class="flex items-center gap-2 rounded-xl bg-sand-100 ring-1 ring-sand-200 px-4 h-12 text-ink-700">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <span class="text-sm">dloazurpiscines.com/blog/<span class="font-medium text-ink-900">{{ $slug }}</span></span>
            </div>
            <p class="mt-1 text-sm text-ink-500">Verrouillé après publication (préserve le référencement).</p>
        @else
            <div class="flex items-center rounded-xl bg-sand-50 ring-1 ring-sand-200 focus-within:ring-2 focus-within:ring-azure-500 overflow-hidden h-12">
                <span class="pl-4 pr-1 text-sm text-ink-500 select-none whitespace-nowrap">dloazurpiscines.com/blog/</span>
                <input
                    wire:model="slug"
                    type="text"
                    class="flex-1 h-full pr-4 bg-transparent outline-none text-ink-900"
                    placeholder="genere-depuis-le-titre">
            </div>
            <p class="mt-1 text-sm text-ink-500">Laissez vide pour générer depuis le titre.</p>
        @endif
        @error('slug')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Contenu / éditeur Markdown — wire:ignore obligatoire (D-01) --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">
            Contenu <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <div
            wire:ignore
            class="easymde-wrap"
            x-data="postEditor(@js($body))"
            x-init="init($refs.ta)">
            <textarea x-ref="ta" aria-label="Contenu de l’article en Markdown"></textarea>
        </div>
        {{-- Champ miroir : post-editor.js y pousse la valeur via $wire.set('body', …) --}}
        <input type="hidden" wire:model="body">
        @error('body')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Extrait --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Extrait</label>
        <textarea
            wire:model="excerpt"
            rows="3"
            maxlength="300"
            class="w-full rounded-xl bg-sand-50 ring-1 ring-sand-200 p-3 focus:ring-2 focus:ring-azure-500 outline-none resize-none"
            placeholder="Court résumé affiché dans la liste du blog et les partages."></textarea>
        @error('excerpt')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Image de couverture — dropzone Livewire (UI-SPEC Surface 2) --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Image de couverture</label>
        @if ($cover)
            <div class="space-y-2">
                <img src="{{ $cover->temporaryUrl() }}" alt="Aperçu de la couverture"
                    class="rounded-xl ring-1 ring-navy-900/8 w-full aspect-[1200/630] object-cover">
                <div class="flex items-center gap-3 text-sm">
                    <label for="cover-input" class="text-azure-600 hover:text-azure-700 cursor-pointer font-medium">Remplacer</label>
                    <button type="button" wire:click="$set('cover', null)" class="text-ink-500 hover:text-ink-900">Retirer</button>
                </div>
                <input id="cover-input" type="file" wire:model="cover" accept="image/*" class="sr-only">
            </div>
        @else
            <label for="cover-input"
                class="rounded-xl border-2 border-dashed border-sand-200 bg-sand-50 p-6 text-center hover:border-azure-300 transition-colors cursor-pointer block text-ink-500">
                Glissez une image ou cliquez pour choisir
            </label>
            <input id="cover-input" type="file" wire:model="cover" accept="image/*" class="sr-only">
        @endif
        <div wire:loading wire:target="cover" aria-live="polite" class="mt-1 text-sm text-ink-500">Envoi…</div>
        @error('cover')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Statut — segmented control role=radiogroup (PAS de toggle iOS) (D-03) --}}
    <div>
        <label class="block text-sm font-semibold text-ink-900 mb-1.5">Statut</label>
        <div role="radiogroup" aria-label="Statut de l’article"
            class="inline-flex rounded-xl bg-sand-100 ring-1 ring-sand-200 p-1 gap-1">
            <button type="button" role="radio"
                :aria-checked="$wire.status === 'draft'"
                aria-checked="{{ $status === 'draft' ? 'true' : 'false' }}"
                wire:click="$set('status', 'draft')"
                @class([
                    'h-10 px-4 rounded-lg text-sm font-medium transition-colors',
                    'bg-white text-ink-900 shadow-xs' => $status === 'draft',
                    'text-ink-500 hover:text-ink-900' => $status !== 'draft',
                ])>
                Brouillon
            </button>
            <button type="button" role="radio"
                :aria-checked="$wire.status === 'published'"
                aria-checked="{{ $status === 'published' ? 'true' : 'false' }}"
                wire:click="$set('status', 'published')"
                @class([
                    'h-10 px-4 rounded-lg text-sm font-medium transition-colors',
                    'bg-azure-500 text-white shadow-xs' => $status === 'published',
                    'text-ink-500 hover:text-ink-900' => $status !== 'published',
                ])>
                Publié
            </button>
        </div>
        <p class="mt-1 text-sm text-ink-500">
            @if ($status === 'published')
                Visible immédiatement sur le blog public.
            @else
                Invisible sur le blog public.
            @endif
        </p>
        @error('status')
            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    {{-- Dépublier — confirm inline Alpine (PAS de modale), uniquement si publié --}}
    @if ($status === 'published' && $postId)
        <div x-data="{ confirming: false }" class="rounded-xl bg-sand-50 ring-1 ring-sand-200 p-4">
            <template x-if="!confirming">
                <button type="button" @click="confirming = true"
                    class="text-sm font-medium text-ink-500 hover:text-ink-900">
                    Dépublier
                </button>
            </template>
            <div x-show="confirming" x-cloak class="space-y-3">
                <p class="text-sm text-ink-900">Dépublier cet article ? Il disparaîtra du blog public.</p>
                <div class="flex items-center gap-3">
                    <button type="button"
                        wire:click="$set('status', 'draft')"
                        @click="confirming = false"
                        class="h-10 px-4 rounded-xl bg-danger text-white text-sm font-semibold hover:opacity-90 transition-opacity">
                        Dépublier
                    </button>
                    <button type="button" @click="confirming = false"
                        class="h-10 px-4 rounded-xl bg-sand-100 ring-1 ring-sand-200 text-sm font-medium text-ink-700 hover:text-ink-900">
                        Garder publié
                    </button>
                </div>
                <p class="text-xs text-ink-500">Pensez à enregistrer pour appliquer le changement.</p>
            </div>
        </div>
    @endif

    {{-- Erreur d’enregistrement --}}
    @error('save')
        <p class="rounded-xl bg-danger/10 ring-1 ring-danger/30 px-4 py-3 text-sm text-danger">{{ $message }}</p>
    @enderror

    {{-- Actions --}}
    <div class="flex items-center gap-4 pt-2">
        <button
            type="submit"
            class="h-12 px-6 rounded-xl bg-azure-500 text-white font-semibold hover:bg-azure-600 transition-colors"
            wire:loading.attr="disabled"
            wire:loading.class="opacity-60">
            <span wire:loading.remove>Enregistrer</span>
            <span wire:loading>Enregistrement…</span>
        </button>
        <a href="{{ route('admin.blog.index') }}" wire:navigate
            class="text-sm font-medium text-ink-500 hover:text-ink-900">Annuler</a>
    </div>

</form>
