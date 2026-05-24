<?php

use App\Models\Application;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts::guest')] class extends Component
{
    public $search = '';

    #[Computed]
    public function applications()
    {

        if (empty(trim($this->search))) {
            return collect();
        }

        return Application::with(['candidate', 'vacancy.position'])
            ->where(function ($query) {
                $query->whereHas('candidate', function ($q) {
                    $q->where('name', 'LIKE', $this->search);
                })
                ->orWhere('application_code', 'LIKE', $this->search);
            })
            ->get();
    }
};
?>

<div>
    <section class="flex flex-col gap-4 md:gap-6 items-center justify-start w-full max-w-2xl mx-auto py-6 md:py-10" id="vacancies">
        <div class="text-center flex flex-col gap-1.5">
            <h1 class="text-xl md:text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">Lacak Status Lamaran</h1>
            <p class="text-zinc-500 dark:text-zinc-400 text-xs md:text-sm">
                Masukkan nama lengkap atau kode aplikasi untuk melihat status rekrutmen terbaru.
            </p>
        </div>

        <div class="w-full">
            <flux:input
                wire:model.live.debounce.300ms="search"
                type="text"
                kbd="⌘K"
                icon="magnifying-glass"
                placeholder="Masukkan nama atau applications code"
            />
        </div>

        <div class="w-full flex flex-col gap-3 mt-2">
            @forelse ($this->applications as $app)
                @php
                    $badgeColor = match($app->status) {
                        'applied' => 'blue',
                        'screening' => 'amber',
                        'interview_scheduled', 'interview_done' => 'purple',
                        'hired' => 'green',
                        'rejected' => 'red',
                        default => 'zinc',
                    };
                @endphp

                <flux:card class="p-4 hover:border-zinc-300 dark:hover:border-zinc-700 transition-colors duration-200">
                    <div class="flex items-start justify-between gap-4">

                        <div class="space-y-1 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <flux:heading size="base" class="font-medium">
                                    {{ $app->vacancy->title }}
                                </flux:heading>
                                <span class="text-xs text-zinc-400 font-mono">
                                    · #{{ $app->application_code ?? 'NO-CODE' }}
                                </span>
                            </div>

                            <flux:subheading size="sm" class="text-indigo-600 dark:text-indigo-400 font-medium">
                                {{ $app->vacancy->position->position_name ?? 'Posisi' }}
                            </flux:subheading>

                            <div class="flex items-center gap-x-3 gap-y-1 flex-wrap pt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                <span class="flex items-center gap-1">
                                    <flux:icon name="user" variant="micro" class="text-zinc-400" />
                                    {{ $app->candidate->name }}
                                </span>
                                <span class="text-zinc-300 dark:text-zinc-700">|</span>
                                <span class="flex items-center gap-1">
                                    <flux:icon name="clock" variant="micro" class="text-zinc-400" />
                                    Aktivitas: {{ $app->updated_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <!-- Status & Link Aksi -->
                        <div class="flex flex-col items-end justify-between h-full min-h-[4.5rem] shrink-0">
                            <!-- Flux Badge Komponen -->
                            <flux:badge :color="$badgeColor" inset="top bottom" size="sm" class="capitalize font-medium">
                                {{ str_replace('_', ' ', $app->status) }}
                            </flux:badge>

                            <flux:button
                                href="{{ route('candidate.vacancies.show', $app->vacancy->id) }}"
                                variant="ghost"
                                size="sm"
                                icon-trailing="arrow-right"
                                class="text-zinc-500 hover:text-zinc-900 dark:hover:text-white -me-2 text-sm"
                            >
                                Detail
                            </flux:button>
                        </div>

                    </div>
                </flux:card>
                @empty
                    @if(!empty(trim($search)))
                        <div class="flex flex-col items-center justify-center py-10 text-zinc-400 text-center">
                            <flux:icon name="magnifying-glass" class="w-6 h-6 mb-2 text-zinc-300" />
                            <p class="text-sm font-medium">Applications tidak ditemukan</p>
                            <p class="text-xs text-zinc-500">Periksa kembali keyword atau kode yang Anda masukkan.</p>
                        </div>
                    @endif
                @endforelse
        </div>
    </section>
</div>
