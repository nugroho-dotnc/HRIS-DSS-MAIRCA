<?php

use App\Models\Vacancies;
use Carbon\Carbon;
use Livewire\Component;

new class extends Component
{
    public function vacancies()
    {
        return Vacancies::with(['position.department'])
            ->withCount('applications')
            ->where('status', 'open')
            ->whereDate('deadline', '>=', today())
            ->orderBy('deadline')
            ->limit(6)
            ->get();
    }
};
?>

<section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <flux:heading size="md">Vacancies Closing Soon</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Lowongan aktif dengan deadline terdekat.</flux:text>
        </div>
        <flux:button icon="arrow-right" size="sm" variant="ghost" href="{{ route('hr.vacancies') }}" wire:navigate>Lihat</flux:button>
    </div>

    @if($this->vacancies()->isNotEmpty())
        <div class="flex flex-col divide-y divide-zinc-200 dark:divide-zinc-700">
            @foreach($this->vacancies() as $vacancy)
                <div class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                    <div class="min-w-0">
                        <div class="truncate font-medium text-zinc-900 dark:text-white">{{ $vacancy->title }}</div>
                        <div class="truncate text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $vacancy->position->position_name ?? '-' }} · {{ $vacancy->position->department->department_name ?? '-' }}
                        </div>
                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $vacancy->applications_count }} pelamar
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <flux:badge color="{{ Carbon::parse($vacancy->deadline)->diffInDays(today()) <= 7 ? 'red' : 'amber' }}" size="sm">
                            {{ Carbon::parse($vacancy->deadline)->translatedFormat('d M Y') }}
                        </flux:badge>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <flux:callout inline align="center">
            <flux:callout.heading icon="briefcase" class="mx-auto">Tidak ada deadline dekat</flux:callout.heading>
            <flux:callout.text>Lowongan aktif dengan deadline terdekat akan tampil di sini.</flux:callout.text>
        </flux:callout>
    @endif
</section>
