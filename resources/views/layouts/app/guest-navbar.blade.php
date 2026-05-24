<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-white dark:bg-zinc-800">

        {{-- Navbar --}}
        <div class="w-full fixed top-0 z-50 bg-white/90 dark:bg-zinc-800/90 backdrop-blur-md border-b border-zinc-100 dark:border-zinc-700">
            <div class="w-full max-w-5xl mx-auto h-16 md:h-20 flex items-center justify-between px-4 md:px-6">

                {{-- Logo --}}
                <flux:heading size="xl" href="{{ route('home') }}" wire:navigate class="cursor-pointer">
                    <span class="font-extrabold text-lg md:text-xl">EVoU</span>
                </flux:heading>

                {{-- Desktop Nav --}}
                <flux:navbar class="hidden md:flex">
                    <flux:navbar.item href="{{ route('home') }}">Home</flux:navbar.item>
                    <flux:navbar.item href="{{ route('candidate.vacancies') }}">Vacancies</flux:navbar.item>
                    <flux:navbar.item href="{{ route('candidate.applications') }}">Applications</flux:navbar.item>
                </flux:navbar>

                {{-- Desktop Actions --}}
                <div class="hidden md:flex gap-2 items-center">
                    <flux:radio.group x-data size="sm" variant="segmented" x-model="$flux.appearance">
                        <flux:radio value="light" icon="sun"/>
                        <flux:radio value="dark" icon="moon"/>
                    </flux:radio.group>
                    <flux:button size="sm" variant="primary" wire:navigate href="{{ route('login') }}">SIGN IN</flux:button>
                </div>

                {{-- Mobile: theme toggle + hamburger --}}
                <div class="flex md:hidden items-center gap-2" x-data="{ open: false }">
                    <flux:radio.group x-data size="sm" variant="segmented" x-model="$flux.appearance">
                        <flux:radio value="light" icon="sun"/>
                        <flux:radio value="dark" icon="moon"/>
                    </flux:radio.group>

                    {{-- Hamburger Button --}}
                    <button
                        @click="open = !open"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                        aria-label="Toggle navigation menu"
                        id="mobile-menu-button"
                    >
                        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    {{-- Mobile Drawer Backdrop --}}
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="open = false"
                        class="fixed inset-0 top-16 bg-black/30 z-40 md:hidden"
                        aria-hidden="true"
                    ></div>

                    {{-- Mobile Drawer Panel --}}
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-250"
                        x-transition:enter-start="opacity-0 -translate-y-3"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-3"
                        class="absolute top-16 left-0 right-0 z-50 bg-white dark:bg-zinc-800 border-b border-zinc-100 dark:border-zinc-700 shadow-lg md:hidden"
                        id="mobile-drawer"
                    >
                        <nav class="flex flex-col px-4 py-4 gap-1">
                            <a
                                href="{{ route('home') }}"
                                wire:navigate
                                @click="open = false"
                                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                                id="mobile-nav-home"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Home
                            </a>
                            <a
                                href="{{ route('candidate.vacancies') }}"
                                wire:navigate
                                @click="open = false"
                                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                                id="mobile-nav-vacancies"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Vacancies
                            </a>
                            <a
                                href="{{ route('candidate.applications') }}"
                                wire:navigate
                                @click="open = false"
                                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                                id="mobile-nav-applications"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Applications
                            </a>

                            <div class="pt-2 mt-1 border-t border-zinc-100 dark:border-zinc-700">
                                <flux:button size="sm" variant="primary" wire:navigate href="{{ route('login') }}" class="w-full justify-center">
                                    SIGN IN
                                </flux:button>
                            </div>
                        </nav>
                    </div>
                </div>

            </div>
        </div>

        {{-- Content offset for fixed navbar --}}
        <div class="pt-16 md:pt-20">
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
