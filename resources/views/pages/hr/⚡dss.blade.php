<?php

use App\Models\Vacancies;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::hr', ['page_title' => 'Hasil DSS', 'page_description' => 'Lowongan berikut sudah melewati masa deadline. Klik Lihat Hasil untuk melihat peringkat kandidat berdasarkan perhitungan MAIRCA.'])] class extends Component
{
    public function vacancies()
    {
        // Ambil vacancy yang deadline-nya hari ini atau sudah lewat
        return Vacancies::with(['position.department'])
            ->whereDate('deadline', '<=', today())
            ->orderByDesc('deadline')
            ->get();
    }
};
?>

<div class="flex flex-1 flex-col gap-8">
    <livewire:bread-crumbs/>

    @php $vacancies = $this->vacancies(); @endphp

    @if($vacancies->isNotEmpty())
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Judul Lowongan</flux:table.column>
                <flux:table.column>Posisi</flux:table.column>
                <flux:table.column>Departemen</flux:table.column>
                <flux:table.column>Deadline</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($vacancies as $vacancy)
                    @php
                        $isToday  = \Carbon\Carbon::parse($vacancy->deadline)->isToday();
                        $isPast   = \Carbon\Carbon::parse($vacancy->deadline)->isPast() && !$isToday;
                    @endphp
                    <flux:table.row :key="$vacancy->id">
                        <flux:table.cell>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $vacancy->title }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $vacancy->position->position_name ?? '-' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $vacancy->position->department->department_name ?? '-' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium">
                                    {{ \Carbon\Carbon::parse($vacancy->deadline)->translatedFormat('d M Y') }}
                                </span>
                                @if($isToday)
                                    <span class="text-xs text-amber-500 font-medium">Deadline hari ini</span>
                                @else
                                    <span class="text-xs text-zinc-400">
                                        {{ \Carbon\Carbon::parse($vacancy->deadline)->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $vacancy->status === 'open' ? 'green' : 'zinc' }}" size="sm">
                                {{ $vacancy->status === 'open' ? 'Open' : 'Closed' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button
                                icon="chart-bar"
                                size="sm"
                                variant="primary"
                                class="cursor-pointer whitespace-nowrap"
                                href="{{ route('hr.dss.result', $vacancy->id) }}"
                                wire:navigate
                            >
                                Lihat Hasil
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <flux:callout inline align="center">
            <flux:callout.heading icon="clock" class="mx-auto">
                Belum ada lowongan yang melewati deadline
            </flux:callout.heading>
            <flux:callout.text>
                Halaman ini akan menampilkan lowongan yang deadline-nya sudah tiba atau sudah lewat,
                sehingga perhitungan MAIRCA dapat dilakukan.
            </flux:callout.text>
        </flux:callout>
    @endif
</div>
