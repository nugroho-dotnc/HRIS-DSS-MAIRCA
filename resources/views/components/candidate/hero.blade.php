<div id="hero" class="relative z-0 py-10 md:py-16 lg:py-24">
    <div class="flex flex-col-reverse lg:flex-row items-center gap-8 lg:gap-12">

        {{-- Text --}}
        <div class="w-full lg:w-1/2 flex flex-col items-center lg:items-start text-center lg:text-left gap-5">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Human Resource Information System
            </span>

            <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold leading-tight text-zinc-900 dark:text-white">
                Temukan Karier
                <span class="text-indigo-600 dark:text-indigo-400">Terbaik</span>
                <br class="hidden md:block">
                Bersama EVoU
            </h1>

            <p class="text-zinc-500 dark:text-zinc-400 text-base md:text-lg leading-relaxed max-w-lg">
                Jelajahi ribuan lowongan kerja terpilih. Lamar secara langsung dan pantau status rekrutmen Anda dengan mudah melalui platform kami.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                <flux:button
                    variant="primary"
                    icon="briefcase"
                    href="{{ route('candidate.vacancies') }}"
                    wire:navigate
                    class="w-full sm:w-auto"
                >
                    Lihat Lowongan
                </flux:button>
                <flux:button
                    variant="ghost"
                    icon="document-text"
                    href="{{ route('candidate.applications') }}"
                    wire:navigate
                    class="w-full sm:w-auto"
                >
                    Lacak Lamaran
                </flux:button>
            </div>

            {{-- Stats --}}
            <div class="flex items-center gap-6 pt-2">
                <div class="flex flex-col">
                    <span class="text-xl font-bold text-zinc-900 dark:text-white">100+</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Posisi Tersedia</span>
                </div>
                <div class="w-px h-8 bg-zinc-200 dark:bg-zinc-700"></div>
                <div class="flex flex-col">
                    <span class="text-xl font-bold text-zinc-900 dark:text-white">50+</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Departemen</span>
                </div>
                <div class="w-px h-8 bg-zinc-200 dark:bg-zinc-700"></div>
                <div class="flex flex-col">
                    <span class="text-xl font-bold text-zinc-900 dark:text-white">500+</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Pelamar Aktif</span>
                </div>
            </div>
        </div>

        {{-- Illustration --}}
        <div class="w-full sm:w-3/4 md:w-2/3 lg:w-1/2 flex justify-center">
            <img
                src="{{ asset('images/hero/hero.svg') }}"
                class="w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-full h-auto drop-shadow-xl"
                alt="Ilustrasi rekrutmen EVoU"
            >
        </div>

    </div>
</div>
