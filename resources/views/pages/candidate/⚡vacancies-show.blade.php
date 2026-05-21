<?php

use App\Models\Vacancies;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts::guest')] class extends Component
{
    //
    public $id;
    public $vacancy;

    public function mount(){
        $this->vacancy = Vacancies::with(['Hr', 'Position'])->findOrFail($this->id);
    }
};
?>

<div>
    <div class="flex flex-1 flex-col gap-6 rounded-xl">
        <div class="w-full flex flex-col gap-6">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $vacancy->title }}</h1>
                    <span class="text-sm text-zinc-400">{{ $vacancy->Position->position_name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <flux:badge color="{{ $vacancy->status === 'open' ? 'green' : 'zinc' }}" size="sm" inset="top bottom">
                        {{ ucfirst($vacancy->status) }}
                    </flux:badge>

                </div>
            </div>

            <flux:separator/>

            {{-- Meta info --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Posisi</span>
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ $vacancy->Position->position_name }}
                    </span>
                </div>

                <div class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Deadline</span>
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ \Carbon\Carbon::parse($vacancy->deadline)->translatedFormat('d F Y') }}
                    </span>
                </div>

                <div class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Diposting oleh</span>
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ $vacancy->Hr->name }}
                    </span>
                </div>

                <div class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Sisa Waktu</span>
                    <span class="text-sm font-medium {{ \Carbon\Carbon::parse($vacancy->deadline)->isPast() ? 'text-red-400' : 'text-zinc-700 dark:text-zinc-300' }}">
                        {{ \Carbon\Carbon::parse($vacancy->deadline)->isPast()
                            ? 'Sudah ditutup'
                            : \Carbon\Carbon::parse($vacancy->deadline)->diffForHumans() }}
                    </span>
                </div>
            </div>

            <flux:separator/>

            {{-- Deskripsi --}}
            <div class="flex flex-col gap-2">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Deskripsi Pekerjaan</span>
                <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-line">
                    {{ $vacancy->description }}
                </p>
            </div>

            <flux:separator/>

            {{-- Persyaratan --}}
            <div class="flex flex-col gap-2">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Persyaratan</span>
                <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-line">
                    {{ $vacancy->requirements }}
                </p>
            </div>

            <flux:separator/>

            {{-- CTA bawah --}}
            @if($vacancy->status === 'open')
                <div class="flex items-center justify-between">
                    <flux:text class="text-zinc-400 text-sm">Tertarik dengan posisi ini?</flux:text>
                    <flux:button
                        variant="primary" class="cursor-pointer"
                        size="sm"
                        >
                        Lamar Sekarang
                    </flux:button>
                </div>
            @else
                <flux:callout variant="warning" icon="exclamation-circle" heading="Lowongan Ditutup">
                    <flux:callout.text>Maaf, lowongan ini sudah tidak menerima lamaran.</flux:callout.text>
                </flux:callout>
            @endif

        </div>
    </div>
</div>
