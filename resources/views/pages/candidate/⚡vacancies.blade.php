<?php

use App\Models\Vacancies;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

new #[Layout('layouts::guest')] class extends Component
{
    use WithPagination;
    public $search = '';

    #[Computed]
    public function vacancies(){
        return Vacancies::with('Position')
            ->where('status', 'open')
            ->where('title', 'like', "%" . $this->search . "%")
            ->orderBy('created_at', 'desc')
            ->paginate(6);
    }
};
?>

<div>
    <section class="flex flex-col gap-6 items-center justify-start" id="vacancies">
        <h1 class="text-2xl font-bold">Lowongan pekerjaan di EVoU</h1>
        <p class="text-center w-full max-w-3xl">
            Temukan peluang karier terbaik sesuai minat dan keahlianmu. Gunakan fitur pencarian untuk menjelajahi lowongan dari berbagai perusahaan dan mulai langkah barumu hari ini.
        </p>

        <flux:input wire:model.live.debounce.300ms="search" type="text" class="w-full max-w-md" kbd="⌘K" icon="magnifying-glass" placeholder="Search..."/>

        @if($this->vacancies->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 w-full mt-6">
                @foreach ($this->vacancies as $vacancy)
                    <a href="{{ route('candidate.vacancies.show', $vacancy->id) }}" aria-label="{{ $vacancy->title }}">
                        <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors h-full">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex flex-col gap-1">
                                    <flux:heading class="flex items-center gap-2">
                                        {{ $vacancy->title }}
                                        <flux:icon name="arrow-up-right" class="ml-auto text-zinc-400" variant="micro"/>
                                    </flux:heading>
                                    <span class="text-xs text-zinc-400">{{ $vacancy->Position->position_name }}</span>
                                </div>
                            </div>
                            <flux:text class="mt-2 line-clamp-2">{{ $vacancy->description }}</flux:text>
                            <div class="flex items-center gap-1 mt-3 text-xs text-zinc-400">
                                <flux:icon name="calendar" variant="micro"/>
                                Deadline: {{ \Carbon\Carbon::parse($vacancy->deadline)->translatedFormat('d F Y') }}
                            </div>
                        </flux:card>
                    </a>
                @endforeach
            </div>

            <div class="flex justify-start items-center w-full">
                <flux:pagination :paginator="$this->vacancies" />
            </div>
        @else
            <flux:callout inline class="max-w-xl w-full mx-auto mt-12 ">
                <flux:callout.heading icon="briefcase">Lowongan Tidak Ditemukan</flux:callout.heading>
                <flux:callout.text>
                    Belum ada lowongan yang sesuai dengan pencarian Anda. Coba gunakan kata kunci lain atau jelajahi peluang kerja lainnya yang tersedia.
                </flux:callout.text>
            </flux:callout>
        @endif
    </section>
</div>
