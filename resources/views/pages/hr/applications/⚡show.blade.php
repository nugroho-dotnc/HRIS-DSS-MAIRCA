<?php

use App\Models\Application;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::hr', ['page_title' => 'Detail Lamaran'])] class extends Component
{
    public $id;

    public function application()
    {
        return Application::with([
            'candidate',
            'vacancy.position.department',
            'interviewSessions.interviewer',
            'result',
        ])->findOrFail($this->id);
    }

    public function moveToScreening()
    {
        $application = Application::findOrFail($this->id);

        if ($application->status !== 'applied') {
            Flux::toast("Hanya lamaran berstatus 'applied' yang dapat dipindahkan ke screening.", variant: 'danger');
            return;
        }

        $application->status = 'screening';
        $application->save();

        Flux::toast('Lamaran berhasil dipindahkan ke screening.');
    }

    public function reject()
    {
        $application = Application::findOrFail($this->id);
        $allowed = ['applied', 'screening', 'interview_scheduled'];

        if (!in_array($application->status, $allowed)) {
            Flux::toast("Lamaran berstatus '{$application->status}' tidak dapat ditolak pada tahap ini.", variant: 'danger');
            return;
        }

        $application->status = 'rejected';
        $application->save();

        Flux::toast('Lamaran berhasil ditolak.');
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

<div class="flex flex-1 flex-col gap-6 rounded-xl">
    <livewire:bread-crumbs/>

    @php $app = $this->application(); @endphp

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $app->candidate->name }}</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Kode Lamaran: <span class="font-mono">{{ $app->application_code }}</span></p>
        </div>
        <div class="flex gap-2">
            @if($app->status === 'applied')
                <flux:button icon="arrow-right-circle" variant="primary" class="cursor-pointer" wire:click="moveToScreening" wire:confirm="Pindahkan lamaran ini ke tahap screening?">
                    Screening
                </flux:button>
            @endif
            @if(in_array($app->status, ['applied', 'screening', 'interview_scheduled']))
                <flux:button icon="x-circle" variant="danger" class="cursor-pointer" wire:click="reject" wire:confirm="Yakin ingin menolak lamaran ini?">
                    Tolak
                </flux:button>
            @endif
            <flux:button icon="arrow-left" href="{{ route('hr.applications') }}" wire:navigate class="cursor-pointer">
                Kembali
            </flux:button>
        </div>
    </div>

    <flux:separator/>

    {{-- Status Badge --}}
    <div class="flex items-center gap-3">
        <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Status Saat Ini</span>
        <flux:badge color="{{ $this->statusColor($app->status) }}" size="sm">
            {{ $this->statusLabel($app->status) }}
        </flux:badge>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Card: Informasi Kandidat --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col gap-4 bg-white dark:bg-zinc-800/50">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                <flux:icon name="user" class="size-4"/> Data Kandidat
            </h2>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-zinc-400">Nama Lengkap</span>
                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $app->candidate->name }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-zinc-400">Email</span>
                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $app->candidate->email }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-zinc-400">No. Telepon</span>
                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $app->candidate->phone }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-zinc-400">Jenis Kelamin</span>
                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $app->candidate->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-zinc-400">Kota</span>
                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $app->candidate->city }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-zinc-400">Kode Pos</span>
                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $app->candidate->zip_code }}</span>
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <span class="text-xs text-zinc-400">Alamat Lengkap</span>
                <p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-line">{{ $app->candidate->complete_address }}</p>
            </div>

            <flux:separator/>

            <div class="flex flex-col gap-1">
                <span class="text-xs text-zinc-400">Pengalaman</span>
                <p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-line leading-relaxed">{{ $app->candidate->experience }}</p>
            </div>
        </div>

        {{-- Card: Informasi Lamaran --}}
        <div class="flex flex-col gap-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col gap-4 bg-white dark:bg-zinc-800/50">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                    <flux:icon name="briefcase" class="size-4"/> Informasi Lamaran
                </h2>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs text-zinc-400">Lowongan</span>
                        <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $app->vacancy->title }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs text-zinc-400">Posisi</span>
                        <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $app->vacancy->position->position_name ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs text-zinc-400">Departemen</span>
                        <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $app->vacancy->position->department->department_name ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs text-zinc-400">Tanggal Melamar</span>
                        <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            {{ \Carbon\Carbon::parse($app->created_at)->translatedFormat('d F Y, H:i') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Card: Dokumen --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col gap-4 bg-white dark:bg-zinc-800/50">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                    <flux:icon name="document" class="size-4"/> Dokumen
                </h2>

                <div class="flex flex-col gap-3">
                    @if($app->candidate->cv_path)
                        <a href="{{ asset('storage/' . $app->candidate->cv_path) }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            <flux:icon name="document-arrow-down" class="size-4"/>
                            Download CV
                        </a>
                    @else
                        <span class="text-sm text-zinc-400 italic">CV belum diunggah</span>
                    @endif

                    @if($app->candidate->portofolio_path)
                        <a href="{{ asset('storage/' . $app->candidate->portofolio_path) }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            <flux:icon name="document-arrow-down" class="size-4"/>
                            Download Portofolio
                        </a>
                    @else
                        <span class="text-sm text-zinc-400 italic">Portofolio belum diunggah</span>
                    @endif

                    @if($app->candidate->web_portofolio_url)
                        <a href="{{ $app->candidate->web_portofolio_url }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            <flux:icon name="globe-alt" class="size-4"/>
                            Portofolio Web
                        </a>
                    @else
                        <span class="text-sm text-zinc-400 italic">Tidak ada link portofolio web</span>
                    @endif
                </div>
            </div>

            {{-- Card: Interview Sessions (jika ada) --}}
            @if($app->interviewSessions->count() > 0)
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col gap-4 bg-white dark:bg-zinc-800/50">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                        <flux:icon name="chat-bubble-left-right" class="size-4"/> Sesi Interview
                    </h2>

                    <div class="flex flex-col gap-3">
                        @foreach($app->interviewSessions as $session)
                            <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-zinc-700/50 px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $session->interviewer->name ?? 'N/A' }}
                                    </span>
                                    <span class="text-xs text-zinc-400">
                                        {{ $session->interview_date ? \Carbon\Carbon::parse($session->interview_date)->translatedFormat('d M Y, H:i') : 'Belum dijadwalkan' }}
                                    </span>
                                </div>
                                @if($session->notes)
                                    <flux:badge color="blue" size="sm">Ada Catatan</flux:badge>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
