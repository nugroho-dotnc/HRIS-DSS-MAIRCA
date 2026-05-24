<?php

use App\Models\Vacancies;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    public function vacancies(){
        return Vacancies::with('Position')->where('status', 'open')->latest()->paginate(3);
    }
};
?>

<div>
    <section class="flex flex-col gap-6 md:gap-8 items-center justify-start py-12 md:py-16" id="vacancies">

        {{-- Section Header --}}
        <div class="text-center flex flex-col gap-2 max-w-xl">
            <span class="text-xs font-semibold uppercase tracking-widest text-indigo-500 dark:text-indigo-400">
                Lowongan Terbaru
            </span>
            <h2 class="text-2xl md:text-3xl font-bold text-zinc-900 dark:text-white">
                Lowongan Pekerjaan di EVoU
            </h2>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm md:text-base">
                Temukan peluang karier yang sesuai dengan minat dan keahlianmu.
            </p>
        </div>

        {{-- Vacancy Cards --}}
        @if(count($this->vacancies()) != 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 w-full">
                @foreach ($this->vacancies() as $vacancy)
                    <a
                        href="{{ route('candidate.vacancies.show', $vacancy->id) }}"
                        aria-label="{{ $vacancy->title }}"
                        wire:navigate
                    >
                        <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors h-full">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex flex-col gap-1">
                                    <flux:heading class="flex items-center gap-2">
                                        {{ $vacancy->title }}
                                        <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro"/>
                                    </flux:heading>
                                    <span class="text-xs text-zinc-400">{{ $vacancy->Position->position_name }}</span>
                                </div>
                            </div>
                            <flux:text class="mt-2 line-clamp-2 text-sm">{{ $vacancy->description }}</flux:text>
                            <div class="flex items-center gap-1 mt-3 text-xs text-zinc-400">
                                <flux:icon name="calendar" variant="micro"/>
                                Deadline: {{ \Carbon\Carbon::parse($vacancy->deadline)->translatedFormat('d F Y') }}
                            </div>
                        </flux:card>
                    </a>
                @endforeach
            </div>

            {{-- CTA See All --}}
            <div class="w-full flex justify-center pt-2">
                <flux:button
                    size="sm"
                    icon:trailing="arrow-long-right"
                    variant="primary"
                    class="cursor-pointer"
                    href="{{ route('candidate.vacancies') }}"
                    wire:navigate
                >
                    Lihat Semua Lowongan
                </flux:button>
            </div>

        @else
            <flux:callout inline class="max-w-xl w-full mx-auto">
                <flux:callout.heading icon="briefcase">Belum Ada Lowongan</flux:callout.heading>
                <flux:callout.text>
                    Saat ini belum ada lowongan yang tersedia. Kunjungi lagi nanti atau pantau halaman lowongan kami.
                </flux:callout.text>
            </flux:callout>
        @endif

    </section>
</div>
