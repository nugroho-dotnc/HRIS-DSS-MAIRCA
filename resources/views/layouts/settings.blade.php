@props(['page_title' => 'Settings', 'page_description' => null, 'title' => null])

@php
    $role = auth()->user()?->role ?? 'employee';
@endphp

@if ($role === 'admin')
    <x-layouts::app.admin-sidebar :title="$title ?? null">
        <flux:main class="h-screen overflow-y-auto">
            {{ $slot }}
        </flux:main>
    </x-layouts::app.admin-sidebar>
@elseif ($role === 'hr')
    <x-layouts::app.hr-sidebar :title="$title ?? null">
        <flux:main class="h-screen overflow-y-auto">
            {{ $slot }}
        </flux:main>
    </x-layouts::app.hr-sidebar>
@else
    <x-layouts::app.employee-sidebar :title="$title ?? null">
        <flux:main class="h-screen overflow-y-auto">
            {{ $slot }}
        </flux:main>
    </x-layouts::app.employee-sidebar>
@endif
