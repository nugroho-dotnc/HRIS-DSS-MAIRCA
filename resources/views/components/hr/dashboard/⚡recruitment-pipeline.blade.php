<?php

use App\Models\Application;
use Livewire\Component;

new class extends Component
{
    public function pipeline(): array
    {
        $labels = [
            'applied' => ['label' => 'Applied', 'color' => 'bg-zinc-500'],
            'screening' => ['label' => 'Screening', 'color' => 'bg-amber-500'],
            'interview_scheduled' => ['label' => 'Interview', 'color' => 'bg-blue-500'],
            'interview_done' => ['label' => 'Interview Done', 'color' => 'bg-indigo-500'],
            'hired' => ['label' => 'Hired', 'color' => 'bg-green-500'],
            'rejected' => ['label' => 'Rejected', 'color' => 'bg-red-500'],
        ];

        $counts = Application::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $max = max(1, (int) $counts->max());

        return collect($labels)->map(function ($meta, $status) use ($counts, $max) {
            $value = (int) ($counts[$status] ?? 0);

            return [
                'status' => $status,
                'label' => $meta['label'],
                'color' => $meta['color'],
                'value' => $value,
                'percent' => $value > 0 ? max(8, round(($value / $max) * 100)) : 0,
            ];
        })->values()->all();
    }

    public function totalApplications(): int
    {
        return Application::count();
    }
};
?>

<section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="md">Recruitment Pipeline</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Sebaran lamaran berdasarkan tahap proses rekrutmen.</flux:text>
        </div>
        <flux:badge color="zinc" size="sm">{{ $this->totalApplications() }} total lamaran</flux:badge>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">
        @foreach($this->pipeline() as $item)
            <div class="flex min-h-40 flex-col justify-end rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                <div class="mb-3 flex h-24 items-end">
                    <div class="{{ $item['color'] }} w-full rounded-t-md transition-all" style="height: {{ $item['percent'] }}%"></div>
                </div>
                <div class="flex items-end justify-between gap-2">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $item['label'] }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ str_replace('_', ' ', $item['status']) }}</div>
                    </div>
                    <div class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $item['value'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
</section>
