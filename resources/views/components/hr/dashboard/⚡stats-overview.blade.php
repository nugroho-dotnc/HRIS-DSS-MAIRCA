<?php

use App\Models\Application;
use App\Models\InterviewSession;
use App\Models\Vacancies;
use Livewire\Component;

new class extends Component
{
    public function primaryStats(): array
    {
        return [
            [
                'label' => 'Open Vacancies',
                'value' => Vacancies::where('status', 'open')->count(),
                'icon' => 'briefcase',
                'color' => 'text-blue-600 dark:text-blue-400',
                'caption' => 'Lowongan aktif',
            ],
            [
                'label' => 'New Applications',
                'value' => Application::whereDate('created_at', today())->count(),
                'icon' => 'document-plus',
                'color' => 'text-emerald-600 dark:text-emerald-400',
                'caption' => 'Masuk hari ini',
            ],
            [
                'label' => 'Need Screening',
                'value' => Application::where('status', 'applied')->count(),
                'icon' => 'clipboard-document-check',
                'color' => 'text-amber-600 dark:text-amber-400',
                'caption' => 'Menunggu review HR',
            ],
            [
                'label' => 'Upcoming Interviews',
                'value' => InterviewSession::where('interview_date', '>=', now())->count(),
                'icon' => 'calendar-days',
                'color' => 'text-indigo-600 dark:text-indigo-400',
                'caption' => 'Jadwal mendatang',
            ],
        ];
    }

};
?>

<section class="flex flex-col gap-4">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($this->primaryStats() as $stat)
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</flux:text>
                        <div class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $stat['value'] }}</div>
                    </div>
                    <div class="{{ $stat['color'] }} rounded-lg bg-zinc-100 p-2 dark:bg-zinc-800">
                        <flux:icon :name="$stat['icon']" class="size-5" />
                    </div>
                </div>
                <flux:text class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">{{ $stat['caption'] }}</flux:text>
            </div>
        @endforeach
    </div>

</section>
