<?php

use App\Models\Vacancies;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new  #[Layout('layouts::hr', ['page_title' => 'Vacancies'])] class extends Component
{
    use WithPagination;
    //
    public $search = '';

    public function vacancies(){
        return Vacancies::where('title', 'like',"%".$this->search."%")
        ->with(['Hr', 'Position'])->orderBy('status', 'desc')
        ->paginate(6);
    }

    public function delete($id){
        $vacancy = Vacancies::findOrFail($id);
        $vacancy->delete();
        Flux::toast('Lowongan: '.$vacancy->title.' Berhasil dihapus!');
        $this->resetPage();
    }

    #[On('vacancies-created')]
    public function onVacanciesCreated(){
        Flux::toast('Lowongan baru berhasil ditambahkan!');
    }
};
?>

<div>
    {{-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison --}}
    <div class="flex flex-1 flex-col gap-8">
        <div class="flex justify-between items-center">
            <flux:input wire:model.live.debounce.300ms="search" type="text" class="w-full max-w-md" kbd="⌘K" icon="magnifying-glass" placeholder="Search..."/>
            <flux:modal.trigger name="add-positions">
                <flux:button class="cursor-pointer" variant="primary" href="{{ route('hr.vacancies.create') }}" wire:navigate>Tambah</flux:button>
            </flux:modal.trigger>
        </div>

        <livewire:bread-crumbs/>

        @if (count($this->vacancies()) != 0)
            <flux:table :paginate="$this->vacancies()">
                <flux:table.columns>
                    <flux:table.column class="2/7">HR</flux:table.column>
                    <flux:table.column class="1/7">Position</flux:table.column>
                    <flux:table.column class="1/7">Title</flux:table.column>
                    <flux:table.column class="1/7">Deadline</flux:table.column>
                    <flux:table.column class="1/7">Status</flux:table.column>
                    <flux:table.column class="1/7">Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->vacancies() as $vac)
                        <flux:table.row>
                            <flux:table.cell>{{$vac->Hr->name}}</flux:table.cell>
                            <flux:table.cell>{{$vac->Position->position_name}}</flux:table.cell>
                            <flux:table.cell>{{$vac->title}}</flux:table.cell>
                            <flux:table.cell>{{$vac->deadline}}</flux:table.cell>
                            <flux:table.cell><flux:badge color="green" size="sm" inset="top bottom">{{$vac->status}}</flux:badge></flux:table.cell>
                            <flux:table.cell variant="strong" class="space-x-2">
                                <flux:button icon="book-open" size="sm" class="cursor-pointer">preview</flux:button>
                                <flux:button icon="trash" size="sm" class="cursor-pointer" wire:click="delete({{$vac->id}})" wire:confirm="yakin ingin menghapus lowongan ini?">delete</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:callout inline align="center">
                <flux:callout.heading icon="newspaper" class="mx-auto">Lowongan tidak ditemukan</flux:callout.heading>

                <flux:callout.text>Tidak ada data lowongan. Silakan tambahkan lowongan baru untuk memulai proses rekrutmen.</flux:callout.text>
            </flux:callout>
        @endif
    </div>
</div>
