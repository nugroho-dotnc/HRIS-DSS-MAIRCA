@props(['page_title' => null, 'page_description' => null, 'title' => null])
<x-layouts::app.guest-navbar :title="$title ?? null">
    <flux:main>
        <div class="xl:max-w-5xl mx-auto px-6">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.guest-navbar>
