@props(['page_title' => null, 'page_description' => null, 'title' => null])
<x-layouts::app.hr-sidebar :title="$title ?? null">
    <flux:main class="h-screen overflow-y-auto">
        <div class="flex items-start justify-between">
            <div>
                <flux:heading size="xl">{{ $page_title ?? null }}</flux:heading>
                @if($page_description)
                    <flux:subheading class="mt-1">{{ $page_description }}</flux:subheading>
                @endif
            </div>
            <div class="hidden lg:block">
                <livewire:notification-bell />
            </div>
        </div>
        <div class="mt-5" />
        {{ $slot }}
    </flux:main>
</x-layouts::app.hr-sidebar>
