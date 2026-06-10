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

        return Application::with(['candidate', 'vacancy.position', 'notifications' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])
            ->where(function ($query) {
                $query->whereHas('candidate', function ($q) {
                    $q->where('name', 'LIKE', $this->search);
                })
                ->orWhere('application_code', 'LIKE', $this->search);
            })
            ->get();
    }

    public function markNotificationAsRead($id)
    {
        $notification = \App\Models\Notification::find($id);
        if ($notification && $notification->recipient_type === 'candidate') {
            $notification->update(['is_read' => true]);
        }
    }
};
?>

<div>
    <section class="flex flex-col gap-4 md:gap-6 items-center justify-start w-full max-w-2xl mx-auto py-6" id="vacancies">
        <h1 class="text-xl md:text-2xl lg:text-3xl font-bold text-center">Lacak Status Lamaran</h1>
        <p class="text-center w-full max-w-3xl text-sm md:text-base text-zinc-500 dark:text-zinc-400">
            Masukkan nama lengkap atau kode aplikasi untuk melihat status rekrutmen terbaru.
        </p>

        <flux:input wire:model.live.debounce.300ms="search" type="text" class="w-full max-w-xs md:max-w-md" kbd="⌘K" icon="magnifying-glass" placeholder="Masukkan nama atau applications code"/>

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

                    <!-- Notifikasi Section -->
                    @if($app->notifications->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                            <flux:heading size="sm" class="mb-3">Notifikasi Terkait</flux:heading>
                            <div class="space-y-2">
                                @foreach($app->notifications as $notif)
                                    <div
                                        class="p-3 rounded-lg flex items-start gap-3 {{ $notif->is_read ? 'bg-zinc-50 dark:bg-zinc-800/50' : 'bg-indigo-50/50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800' }}"
                                        @if(!$notif->is_read) wire:click="markNotificationAsRead({{ $notif->id }})" role="button" @endif
                                    >
                                        <flux:icon name="bell" variant="mini" class="mt-0.5 shrink-0 {{ $notif->is_read ? 'text-zinc-400' : 'text-indigo-500' }}" />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium {{ $notif->is_read ? 'text-zinc-600 dark:text-zinc-300' : 'text-indigo-900 dark:text-indigo-100' }}">
                                                {{ $notif->title }}
                                                @if(!$notif->is_read)
                                                    <span class="inline-block w-2 h-2 ml-1 bg-indigo-500 rounded-full"></span>
                                                @endif
                                            </p>
                                            <p class="text-xs mt-0.5 {{ $notif->is_read ? 'text-zinc-500' : 'text-indigo-700 dark:text-indigo-300' }}">
                                                {{ $notif->body }}
                                            </p>
                                            <p class="text-[10px] mt-1 {{ $notif->is_read ? 'text-zinc-400' : 'text-indigo-400' }}">
                                                {{ $notif->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
