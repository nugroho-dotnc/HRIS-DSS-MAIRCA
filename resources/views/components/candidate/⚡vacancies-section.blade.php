<?php

use App\Models\Vacancies;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    public function vacancies(){
        return Vacancies::where('status', 'open')->paginate(3);
    }
};
?>

<div>
    {{-- Smile, breathe, and go slowly. - Thich Nhat Hanh --}}
    <section class="flex flex-col gap-6 items-center justify-center min-h-screen" id="vacancies">
        <h1 class="text-2xl font-bold">
            Lowongan pekerjaan di EVoU
        </h1>
        <p class="text-center w-full max-w-3xl">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempora fugiat accusamus ad veritatis, doloribus quas delectus voluptates recusandae eligendi voluptatem maiores? Ducimus, nam nulla porro quod assumenda architecto repellat ut!
        </p>

            @if(count($this->vacancies()) != 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3  gap-4 items-center justify-center mt-12">
                    @foreach ($this->vacancies() as $vacancy)
                        <a
                            href="{{ route('candidate.vacancies.show', $vacancy->id) }}"
                            aria-label="{{ $vacancy->title }}">
                            <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
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
                <div class="w-full flex justify-end items-center ">
                    <flux:button size="sm" icon:trailing="arrow-long-right" variant="primary" class="cursor-pointer" href="{{route('candidate.vacancies')}}" wire:navigate>See all</flux:button>
                </div>
            @else
            @endif
    </section>
</div>
