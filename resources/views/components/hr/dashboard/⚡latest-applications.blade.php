<?php

use App\Models\Application;
use Livewire\Component;

new class extends Component
{
    public function applications()
    {
        return Application::with(['candidate', 'vacancy.position'])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();
    }

    public function statusColor(string $status): string
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

    public function statusLabel(string $status): string
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

<section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <flux:heading size="md">Latest Applications</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Lamaran terbaru yang masuk ke sistem.</flux:text>
        </div>
        <flux:button icon="arrow-right" size="sm" variant="ghost" href="{{ route('hr.applications') }}" wire:navigate>Lihat</flux:button>
    </div>

    @if($this->applications()->isNotEmpty())
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Kandidat</flux:table.column>
                <flux:table.column>Lowongan</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Aksi</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->applications() as $app)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $app->candidate->name }}</span>
                                <span class="text-xs text-zinc-400">{{ $app->application_code }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span>{{ $app->vacancy->title }}</span>
                                <span class="text-xs text-zinc-400">{{ $app->vacancy->position->position_name ?? '-' }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $this->statusColor($app->status) }}" size="sm">
                                {{ $this->statusLabel($app->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button icon="eye" size="sm" href="{{ route('hr.applications.show', $app->id) }}" wire:navigate>Detail</flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <flux:callout inline align="center">
            <flux:callout.heading icon="inbox" class="mx-auto">Belum ada lamaran</flux:callout.heading>
            <flux:callout.text>Lamaran terbaru akan tampil di sini.</flux:callout.text>
        </flux:callout>
    @endif
</section>
