<?php

use App\Models\Position;
use Livewire\Component;

new class extends Component
{
    public function positions()
    {
        return Position::with('department')
            ->withCount('recruitment_criteria')
            ->withSum('recruitment_criteria', 'weight')
            ->orderBy('recruitment_criteria_count')
            ->limit(6)
            ->get();
    }

    public function weightColor($weight): string
    {
        $value = (float) $weight;

        return match (true) {
            $value >= 0.99 && $value <= 1.01 => 'green',
            $value <= 0 => 'red',
            default => 'amber',
        };
    }
};
?>

<section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <flux:heading size="md">Positions & DSS Criteria</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Posisi dengan jumlah kriteria paling sedikit ditampilkan lebih dulu.</flux:text>
        </div>
        <flux:button icon="arrow-right" size="sm" variant="ghost" href="{{ route('admin.positions') }}" wire:navigate>Lihat</flux:button>
    </div>

    @if($this->positions()->isNotEmpty())
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Position</flux:table.column>
                <flux:table.column>Department</flux:table.column>
                <flux:table.column>Criteria</flux:table.column>
                <flux:table.column>Weight</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->positions() as $position)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $position->position_name }}</span>
                                <span class="text-xs text-zinc-400">{{ $position->is_active ? 'active' : 'inactive' }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $position->department->department_name ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $position->recruitment_criteria_count }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $this->weightColor($position->recruitment_criteria_sum_weight ?? 0) }}" size="sm">
                                {{ $position->recruitment_criteria_sum_weight ?? 0 }}
                            </flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <flux:callout inline align="center">
            <flux:callout.heading icon="briefcase" class="mx-auto">Belum ada posisi</flux:callout.heading>
            <flux:callout.text>Data posisi dan kriteria DSS akan tampil di sini.</flux:callout.text>
        </flux:callout>
    @endif
</section>
