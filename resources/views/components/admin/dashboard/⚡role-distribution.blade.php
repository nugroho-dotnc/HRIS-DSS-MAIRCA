<?php

use App\Models\User;
use Livewire\Component;

new class extends Component
{
    public function roles(): array
    {
        $labels = [
            'admin' => ['label' => 'Admin', 'color' => 'bg-blue-500'],
            'hr' => ['label' => 'HR', 'color' => 'bg-emerald-500'],
            'supervisor' => ['label' => 'Supervisor', 'color' => 'bg-amber-500'],
            'employee' => ['label' => 'Employee', 'color' => 'bg-indigo-500'],
            'candidate' => ['label' => 'Candidate', 'color' => 'bg-zinc-500'],
        ];

        $counts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $max = max(1, (int) $counts->max());

        return collect($labels)->map(function ($meta, $role) use ($counts, $max) {
            $value = (int) ($counts[$role] ?? 0);

            return [
                'role' => $role,
                'label' => $meta['label'],
                'color' => $meta['color'],
                'value' => $value,
                'percent' => $value > 0 ? max(8, round(($value / $max) * 100)) : 0,
            ];
        })->values()->all();
    }
};
?>

<section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="mb-5">
        <flux:heading size="md">Role Distribution</flux:heading>
        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Sebaran akun berdasarkan role sistem.</flux:text>
    </div>

    <div class="flex flex-col gap-4">
        @foreach($this->roles() as $role)
            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <div class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $role['label'] }}</div>
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $role['value'] }}</div>
                </div>
                <div class="h-2 rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <div class="{{ $role['color'] }} h-2 rounded-full" style="width: {{ $role['percent'] }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
</section>
