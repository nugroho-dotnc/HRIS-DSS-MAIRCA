<?php

use App\Models\Application;
use App\Models\Vacancies;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::hr', ['page_title' => 'Review Lamaran', 'page_description' => 'Kelola dan review lamaran yang masuk'])] class extends Component {
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $filterVacancy = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function updatedFilterStatus()
    {
        $this->resetPage();
    }
    public function updatedFilterVacancy()
    {
        $this->resetPage();
    }

    public function applications()
    {
        $query = Application::with(['candidate', 'vacancy.position'])
            ->orderByDesc('created_at');

        if ($this->search) {
            $search = $this->search;
            $query->whereHas('candidate', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterVacancy) {
            $query->where('vacancy_id', $this->filterVacancy);
        }

        return $query->paginate(10);
    }

    public function vacancies()
    {
        return Vacancies::orderByDesc('created_at')->get();
    }

    public function moveToScreening($id)
    {
        $application = Application::findOrFail($id);

        if ($application->status !== 'applied') {
            Flux::toast("Hanya lamaran berstatus 'applied' yang dapat dipindahkan ke screening.", variant: 'danger');
            return;
        }

        $application->status = 'screening';
        $application->save();

        Flux::toast('Lamaran berhasil dipindahkan ke screening.');
    }

    // public function reject($id)
    // {
    //     $application = Application::findOrFail($id);
    //     $allowed = ['applied', 'screening', 'interview_scheduled'];

    //     if (!in_array($application->status, $allowed)) {
    //         Flux::toast("Lamaran berstatus '{$application->status}' tidak dapat ditolak pada tahap ini.", variant: 'danger');
    //         return;
    //     }

    //     $application->status = 'rejected';
    //     $application->save();

    //     Flux::toast('Lamaran berhasil ditolak.');
    // }

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
            'interview_scheduled' => 'Interview',
            'interview_done' => 'Selesai Interview',
            'hired' => 'Diterima',
            'rejected' => 'Ditolak',
            default => $status,
        };
    }
};
?>

<div>
    <div class="flex flex-1 flex-col gap-8">

        {{-- Header: Search & Filters --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <flux:input wire:model.live.debounce.300ms="search" type="text" class="w-full max-w-md"
                icon="magnifying-glass" placeholder="Cari nama atau email kandidat..." />

            <div class="flex gap-2">
                <flux:select wire:model.live="filterStatus" placeholder="Semua Status" class="min-w-[160px]">
                    <flux:select.option value="">Semua Status</flux:select.option>
                    <flux:select.option value="applied">Applied</flux:select.option>
                    <flux:select.option value="screening">Screening</flux:select.option>
                    <flux:select.option value="interview_scheduled">Interview</flux:select.option>
                    <flux:select.option value="interview_done">Selesai Interview</flux:select.option>
                    <flux:select.option value="hired">Diterima</flux:select.option>
                    <flux:select.option value="rejected">Ditolak</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="filterVacancy" placeholder="Semua Lowongan" class="min-w-[180px]">
                    <flux:select.option value="">Semua Lowongan</flux:select.option>
                    @foreach($this->vacancies() as $vac)
                        <flux:select.option value="{{ $vac->id }}">{{ $vac->title }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        {{-- Table --}}
        @if (count($this->applications()) != 0)
            <flux:table :paginate="$this->applications()">
                <flux:table.columns>
                    <flux:table.column>Kode</flux:table.column>
                    <flux:table.column>Kandidat</flux:table.column>
                    <flux:table.column>Lowongan</flux:table.column>
                    <flux:table.column>Posisi</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Tanggal</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->applications() as $app)
                        <flux:table.row>
                            <flux:table.cell class="font-mono text-xs">{{ $app->application_code }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $app->candidate->name }}</span>
                                    <span class="text-xs text-zinc-400">{{ $app->candidate->email }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $app->vacancy->title }}</flux:table.cell>
                            <flux:table.cell>{{ $app->vacancy->position->position_name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ $this->statusColor($app->status) }}" size="sm" inset="top bottom">
                                    {{ $this->statusLabel($app->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-xs text-zinc-500">
                                {{ \Carbon\Carbon::parse($app->created_at)->translatedFormat('d M Y') }}
                            </flux:table.cell>
                            <flux:table.cell variant="strong" class="space-x-2">
                                <flux:button icon="eye" size="sm" class="cursor-pointer"
                                    href="{{ route('hr.applications.show', $app->id) }}" wire:navigate>Detail</flux:button>

                                {{-- @if($app->status === 'applied' || in_array($app->status, ['screening',
                                'interview_scheduled']))
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="cursor-pointer" />

                                    <flux:menu>
                                        @if($app->status === 'applied')
                                        <flux:menu.item icon="arrow-right-circle" wire:click="moveToScreening({{ $app->id }})"
                                            wire:confirm="Pindahkan lamaran ini ke tahap screening?">
                                            Screening
                                        </flux:menu.item>
                                        @endif
                                        @if(in_array($app->status, ['applied', 'screening', 'interview_scheduled']))
                                        <flux:menu.item icon="x-circle" variant="danger" wire:click="reject({{ $app->id }})"
                                            wire:confirm="Yakin ingin menolak lamaran ini?">
                                            Tolak
                                        </flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                                @endif --}}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:callout inline align="center">
                <flux:callout.heading icon="inbox" class="mx-auto">Tidak ada lamaran</flux:callout.heading>
                <flux:callout.text>Belum ada lamaran yang masuk atau sesuai dengan filter yang dipilih.</flux:callout.text>
            </flux:callout>
        @endif
    </div>
</div>