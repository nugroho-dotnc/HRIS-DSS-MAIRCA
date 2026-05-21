@props(['page_title' => null, 'page_description' => null, 'title' => null])
<x-layouts::app.employee-sidebar :title="$title ?? null">
    <flux:main class="h-screen overflow-y-auto">
        <flux:heading size="xl">{{$page_title??null}}</flux:heading>
        <flux:subheading>{{$page_description??null}}</flux:subheading>
        <div class="mt-5" />
        {{ $slot }}
    </flux:main>
</x-layouts::app.employee-sidebar>
