<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
    </head>
    <body class=" bg-white dark:bg-zinc-800">
        <div class="w-full fixed top-0 z-99 bg-white dark:bg-zinc-800">
            <div class="w-full max-w-5xl mx-auto h-24 flex items-center justify-between px-6 ">
                <flux:heading size="xl" href="{{ route('home') }}" wire:navigate class="cursor-pointer">
                    EVoU
                </flux:heading>

                <flux:navbar>
                    <flux:navbar.item href="{{ route('home') }}">Home</flux:navbar.item>
                    <flux:navbar.item href="{{ route('candidate.vacancies') }}">Vacancies</flux:navbar.item>
                    <flux:navbar.item href="{{ route('candidate.applications') }}">Applications</flux:navbar.item>

                    {{-- <flux:dropdown>
                        <flux:navbar.item icon:trailing="chevron-down">Account</flux:navbar.item>

                        <flux:navmenu>
                            <flux:navmenu.item href="#">Profile</flux:navmenu.item>
                            <flux:navmenu.item href="#">Settings</flux:navmenu.item>
                            <flux:navmenu.item href="#">Billing</flux:navmenu.item>
                        </flux:navmenu>
                    </flux:dropdown> --}}
                </flux:navbar>

                <div class="flex gap-2">
                    <flux:radio.group x-data size="sm" variant="segmented" x-model="$flux.appearance">
                        <flux:radio value="light" icon="sun"/>
                        <flux:radio value="dark" icon="moon"/>
                    </flux:radio.group>
                    <flux:button size="sm" variant="primary" wire:navigate href="{{ route('login') }}">SIGN IN</flux:button>
                </div>
            </div>
        </div>
        <div class="pt-24">
            {{ $slot }}
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
