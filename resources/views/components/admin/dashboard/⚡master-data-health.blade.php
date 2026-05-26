<?php

use App\Models\Department;
use App\Models\Position;
use Livewire\Component;

new class extends Component
{
    public function items(): array
    {
        return [
            [
                'label' => 'Active Departments',
                'value' => Department::where('is_active', true)->count(),
                'total' => Department::count(),
                'icon' => 'building-office-2',
                'color' => 'green',
            ],
            [
                'label' => 'Inactive Departments',
                'value' => Department::where('is_active', false)->count(),
                'total' => Department::count(),
                'icon' => 'archive-box-x-mark',
                'color' => 'red',
            ],
            [
                'label' => 'Active Positions',
                'value' => Position::where('is_active', true)->count(),
                'total' => Position::count(),
                'icon' => 'briefcase',
                'color' => 'green',
            ],
            [
                'label' => 'Positions Without Criteria',
                'value' => Position::doesntHave('recruitment_criteria')->count(),
                'total' => Position::count(),
                'icon' => 'exclamation-triangle',
                'color' => 'amber',
            ],
        ];
    }
};
?>

<section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="mb-5">
        <flux:heading size="md">Master Data Health</flux:heading>
        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Status kelengkapan data dasar untuk proses HRIS.</flux:text>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @foreach($this->items() as $item)
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <flux:icon :name="$item['icon']" class="size-5 text-zinc-500 dark:text-zinc-400" />
                    <flux:badge color="{{ $item['color'] }}" size="sm">{{ $item['value'] }}</flux:badge>
                </div>
                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $item['label'] }}</div>
                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Dari {{ $item['total'] }} data terkait</div>
            </div>
        @endforeach
    </div>
</section>
