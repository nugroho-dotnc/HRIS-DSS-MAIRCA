@props(['page_title' => null, 'page_description' => null, 'title' => null])
<x-layouts::app.guest-navbar :title="$title ?? null">
    <flux:main>
        <div class="w-full max-w-5xl mx-auto px-4 md:px-6 lg:px-8 py-6 md:py-8">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.guest-navbar>
