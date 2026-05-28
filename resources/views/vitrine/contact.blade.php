@extends('layouts.app', [
    'title'       => 'Nous contacter · Dlo Azur Piscines',
    'description' => 'Une question ? Un devis ? Pierre vous répond sous 48h — par email ou directement sur WhatsApp.',
])

@section('content')
<div class="max-w-lg mx-auto py-16 px-5">
    <h1 class="font-display font-bold text-3xl text-ink-950 mb-2">Nous contacter</h1>
    <p class="text-ink-500 mb-8">Pierre répond en général sous 48h. Pour une urgence, contactez-le directement sur WhatsApp.</p>

    <div class="mt-8">
        <livewire:contact-form />
    </div>
</div>
@endsection
