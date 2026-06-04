<?php

use App\Models\InterviewSession;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::hr', ['page_title' => '', 'page_description' => ''])] class extends Component
{
    // 'today' | 'past' | 'all'
    public string $filterDate = 'all';

    public function updatedFilterDate(): void
    {
        // reaktif, tidak perlu reset paginasi karena tidak menggunakan paginasi di sini
    }

    public function sessions()
    {
        $query = InterviewSession::with([
            'application.candidate',
            'application.vacancy.position.department',
        ]);

        if ($this->filterDate === 'today') {
            $query->whereDate('interview_date', today())->orderBy('interview_date');
        } elseif ($this->filterDate === 'past') {
            $query->whereDate('interview_date', '<', today())->orderBy('interview_date');
        } else {
            // 'all' → urutkan dari jadwal interview yang terbaru
            $query->orderByDesc('interview_date');
        }

        return $query->get();
    }

    public function statusColor($status)
    {
        return match ($status) {
            'applied' => 'zinc',
            'screening' => 'amber',
            'interview_scheduled' => 'blue',
            'interview_done' => 'indigo',
            'hired' => 'green',
            'rejected' => 'red',
            default => 'zinc',
        };
    }

    public function statusLabel($status)
    {
        return match ($status) {
            'applied' => 'Applied',
            'screening' => 'Screening',
            'interview_scheduled' => 'Interview Dijadwalkan',
            'interview_done' => 'Selesai Interview',
            'hired' => 'Diterima',
            'rejected' => 'Ditolak',
            default => $status,
        };
    }
};
?>

<div class="flex flex-1 flex-col gap-8">

    {{-- Header & Filter --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-zinc-900 dark:text-white">Jadwal Interview</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Daftar sesi interview kandidat yang terjadwal</p>
        </div>

        <flux:radio.group wire:model.live="filterDate" variant="segmented">
            <flux:radio value="all" label="Semua" />
            <flux:radio value="today" label="Hari Ini" />
            <flux:radio value="past" label="Sudah Lewat" />
        </flux:radio.group>
    </div>

    {{-- Table --}}
    @php $sessions = $this->sessions(); @endphp

    @if($sessions->isNotEmpty())
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Kode Lamaran</flux:table.column>
                <flux:table.column>Nama Kandidat</flux:table.column>
                <flux:table.column>Departemen</flux:table.column>
                <flux:table.column>Posisi Dilamar</flux:table.column>
                <flux:table.column>Jadwal Interview</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($sessions as $session)
                    @php
                        $app = $session->application;
                        $dept = $app->vacancy->position->department->department_name ?? '-';
                        $pos  = $app->vacancy->position->position_name ?? '-';
                    @endphp
                    <flux:table.row :key="$session->id">
                        <flux:table.cell class="font-mono text-xs">
                            {{ $app->application_code }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $app->candidate->name }}</span>
                                <span class="text-xs text-zinc-400">{{ $app->candidate->email }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $dept }}</flux:table.cell>
                        <flux:table.cell>{{ $pos }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium">
                                    {{ \Carbon\Carbon::parse($session->interview_date)->translatedFormat('d M Y') }}
                                </span>
                                <span class="text-xs text-zinc-400">
                                    {{ \Carbon\Carbon::parse($session->interview_date)->format('H:i') }} WIB
                                </span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $this->statusColor($app->status) }}" size="sm">
                                {{ $this->statusLabel($app->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($app->status === 'interview_scheduled')
                                <flux:button
                                    icon="play"
                                    size="sm"
                                    variant="primary"
                                    class="cursor-pointer whitespace-nowrap"
                                    href="{{ route('hr.interviews.dss', $session->id) }}"
                                    wire:navigate
                                >
                                    Mulai Sesi Interview
                                </flux:button>
                            @elseif($app->status === 'interview_done')
                                <flux:button
                                    icon="pencil-square"
                                    size="sm"
                                    variant="filled"
                                    class="cursor-pointer whitespace-nowrap"
                                    href="{{ route('hr.interviews.dss', $session->id) }}"
                                    wire:navigate
                                >
                                    Edit Nilai DSS
                                </flux:button>
                            @else
                                <flux:button
                                    icon="eye"
                                    size="sm"
                                    variant="ghost"
                                    class="cursor-pointer whitespace-nowrap"
                                    href="{{ route('hr.interviews.dss', $session->id) }}"
                                    wire:navigate
                                >
                                    Lihat Nilai DSS
                                </flux:button>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <flux:callout inline align="center">
            <flux:callout.heading icon="calendar" class="mx-auto">
                Tidak ada jadwal interview
            </flux:callout.heading>
            <flux:callout.text>
                @if($this->filterDate === 'today')
                    Tidak ada jadwal interview untuk hari ini.
                @elseif($this->filterDate === 'past')
                    Tidak ada jadwal interview yang sudah lewat.
                @else
                    Belum ada jadwal interview sama sekali.
                @endif
            </flux:callout.text>
        </flux:callout>
    @endif
</div>
