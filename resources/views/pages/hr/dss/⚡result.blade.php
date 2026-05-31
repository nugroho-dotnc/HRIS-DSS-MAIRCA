<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Services\MAIRCA;

new #[Layout('layouts::hr', [
    'page_title' => 'Hasil Perhitungan MAIRCA',
    'page_description' => 'Detail perangkingan kandidat pelamar berdasarkan metode DSS MAIRCA.'
])] class extends Component
{
    public int $vacancyId;

    public function result(): array|string
    {
        try {
            $service = new MAIRCA();
            return $service->calculate($this->vacancyId);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
};
?>

<div class="flex flex-1 flex-col gap-6">
    <livewire:bread-crumbs/>

    @php $result = $this->result(); @endphp

    @if(is_string($result))
        <div class="flex flex-col items-center justify-center p-12 bg-white dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm text-center">
            <div class="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-full mb-4">
                <flux:icon name="exclamation-triangle" class="size-10 text-amber-500" />
            </div>
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Perhitungan Tidak Dapat Dilakukan</h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2 max-w-md">
                {{ $result }}
            </p>
            <flux:button class="mt-8 cursor-pointer" href="{{ route('hr.dss') }}" wire:navigate icon="arrow-left" variant="primary">
                Kembali ke Daftar DSS
            </flux:button>
        </div>
    @else
        {{-- Header / Top Actions --}}
        <div class="flex items-center justify-between gap-4 bg-zinc-50 dark:bg-zinc-800/20 px-4 py-3 rounded-xl border border-zinc-200/50 dark:border-zinc-700/50">
            <div class="flex items-center gap-2">
                <flux:icon name="briefcase" class="size-4 text-zinc-400 dark:text-zinc-500" />
                <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Lowongan:</span>
                <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $result['vacancy'] }}</span>
            </div>
            <flux:button icon="arrow-left" size="sm" href="{{ route('hr.dss') }}" wire:navigate class="cursor-pointer">
                Kembali
            </flux:button>
        </div>

        <flux:separator/>

        {{-- Info Umum --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-5 py-4 flex flex-col gap-1">
                <span class="text-xs text-zinc-400 uppercase tracking-wide">Posisi</span>
                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $result['position'] }}</span>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-5 py-4 flex flex-col gap-1">
                <span class="text-xs text-zinc-400 uppercase tracking-wide">Departemen</span>
                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $result['department'] }}</span>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-5 py-4 flex flex-col gap-1">
                <span class="text-xs text-zinc-400 uppercase tracking-wide">Deadline</span>
                <span class="font-medium text-zinc-800 dark:text-zinc-200">
                    {{ \Carbon\Carbon::parse($result['deadline'])->translatedFormat('d M Y') }}
                </span>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 px-5 py-4 flex flex-col gap-1">
                <span class="text-xs text-zinc-400 uppercase tracking-wide">Preferensi (Pi)</span>
                <span class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $result['Pi'] }}</span>
            </div>
        </div>

        {{-- Info Kriteria --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-zinc-400 flex items-center gap-2">
                <flux:icon name="clipboard-document-list" class="size-4 text-zinc-400" />
                Kriteria Penilaian & Bobot
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($result['criteria'] as $index => $criteriaName)
                    @php
                        $isBenefit = $result['types'][$index] === 'benefit';
                    @endphp
                    <div class="relative group overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 p-4 transition-all duration-300 hover:shadow-md hover:border-zinc-300 dark:hover:border-zinc-600 flex flex-col justify-between gap-3">
                        <div class="flex flex-col gap-1.5">
                            <span class="text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest">
                                Kriteria {{ $index + 1 }}
                            </span>
                            <h3 class="font-semibold text-zinc-800 dark:text-zinc-100 text-sm line-clamp-2 min-h-[2.5rem]" title="{{ $criteriaName }}">
                                {{ $criteriaName }}
                            </h3>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-2.5 border-t border-zinc-100 dark:border-zinc-700/60">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 bg-zinc-100 dark:bg-zinc-700 px-2 py-0.5 rounded font-mono">
                                {{ number_format($result['weights'][$index] * 100, 0) }}%
                            </span>
                            <flux:badge size="sm" color="{{ $isBenefit ? 'green' : 'red' }}" class="capitalize font-medium">
                                {{ $result['types'][$index] }}
                            </flux:badge>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tabel Ranking --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                <flux:icon name="trophy" class="size-4"/>
                Peringkat Kandidat
            </h2>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Peringkat</flux:table.column>
                    <flux:table.column>Nama Kandidat</flux:table.column>
                    @foreach($result['criteria'] as $criteriaName)
                        <flux:table.column>{{ $criteriaName }}</flux:table.column>
                    @endforeach
                    <flux:table.column>Total Qi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($result['ranking'] as $row)
                        <flux:table.row :key="$row['rank']">

                            {{-- Peringkat dengan warna medal --}}
                            <flux:table.cell>
                                @if($row['rank'] === 1)
                                    <flux:badge color="yellow" size="sm" icon="trophy">🥇 Ke-1</flux:badge>
                                @elseif($row['rank'] === 2)
                                    <flux:badge color="zinc" size="sm">🥈 Ke-2</flux:badge>
                                @elseif($row['rank'] === 3)
                                    <flux:badge color="zinc" size="sm">🥉 Ke-3</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Ke-{{ $row['rank'] }}</flux:badge>
                                @endif
                            </flux:table.cell>

                            {{-- Nama kandidat --}}
                            <flux:table.cell>
                                <span class="font-medium {{ $row['rank'] === 1 ? 'text-amber-600 dark:text-amber-400' : '' }}">
                                    {{ $row['candidate_name'] }}
                                </span>
                            </flux:table.cell>

                            {{-- Gap per kriteria --}}
                            @foreach($row['gap_details'] as $gap)
                                <flux:table.cell>
                                    <span class="font-mono text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ number_format($gap, 6) }}
                                    </span>
                                </flux:table.cell>
                            @endforeach

                            {{-- Total Qi --}}
                            <flux:table.cell>
                                <span class="font-mono font-semibold {{ $row['rank'] === 1 ? 'text-green-600 dark:text-green-400' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    {{ number_format($row['qi_score'], 6) }}
                                </span>
                            </flux:table.cell>

                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <p class="text-xs text-zinc-400 dark:text-zinc-500">
                * Nilai Qi (Total Gap) yang lebih kecil menunjukkan kandidat yang lebih baik.
            </p>
        </div>
    @endif
</div>

</div>
