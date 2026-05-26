<?php

use App\Models\Department;
use App\Models\Position;
use App\Models\RecruitmentCriteria;
use App\Models\User;
use Livewire\Component;

new class extends Component
{
    public function stats(): array
    {
        return [
            [
                'label' => 'Total Users',
                'value' => User::count(),
                'caption' => User::where('is_active', false)->count().' inactive',
                'icon' => 'users',
                'color' => 'text-blue-600 dark:text-blue-400',
                'href' => route('admin.users'),
            ],
            [
                'label' => 'Departments',
                'value' => Department::count(),
                'caption' => Department::where('is_active', true)->count().' active',
                'icon' => 'building-office-2',
                'color' => 'text-emerald-600 dark:text-emerald-400',
                'href' => route('admin.departments'),
            ],
            [
                'label' => 'Positions',
                'value' => Position::count(),
                'caption' => Position::where('is_active', true)->count().' active',
                'icon' => 'briefcase',
                'color' => 'text-amber-600 dark:text-amber-400',
                'href' => route('admin.positions'),
            ],
            [
                'label' => 'DSS Criteria',
                'value' => RecruitmentCriteria::count(),
                'caption' => Position::doesntHave('recruitment_criteria')->count().' position without criteria',
                'icon' => 'adjustments-horizontal',
                'color' => 'text-indigo-600 dark:text-indigo-400',
                'href' => route('admin.positions'),
            ],
        ];
    }
};
?>

<section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($this->stats() as $stat)
        <a href="{{ $stat['href'] }}" wire:navigate class="rounded-lg border border-zinc-200 bg-white p-5 transition hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600 dark:hover:bg-zinc-800/70">
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
        </a>
    @endforeach
</section>
