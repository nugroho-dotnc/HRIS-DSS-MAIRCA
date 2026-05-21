<?php

use App\Models\Vacancies;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new  #[Layout('layouts::hr', ['page_title' => 'Vacancies'])] class extends Component
{
    //
    public $search = '';

    public function vacancies(){
        return Vacancies::where('title', 'like',"%".$this->search."%")->orderBy('status', 'desc')->get();
    }
};
?>

<div>
    {{-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison --}}
    <div class="flex flex-1 flex-col gap-4 rounded-xl">
        <div class="flex justify-between items-center">
            <flux:input wire:model.live.debounce.300ms="search" type="text" class="w-full max-w-md" kbd="⌘K" icon="magnifying-glass" placeholder="Search..."/>
            <flux:modal.trigger name="add-positions">
                <flux:button class="cursor-pointer" href="{{ route('hr.vacancies.create') }}" wire:navigate>Tambah</flux:button>
            </flux:modal.trigger>
        </div>

        <livewire:bread-crumbs/>

        @if (count($this->vacancies()) != 0)
            <flux:table>
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
                            <flux:table.cell>Lindsey Aminoff</flux:table.cell>
                            <flux:table.cell>Lindsey Aminoff</flux:table.cell>
                            <flux:table.cell>Lindsey Aminoff</flux:table.cell>
                            <flux:table.cell>Jul 29, 10:45 AM</flux:table.cell>
                            <flux:table.cell><flux:badge color="green" size="sm" inset="top bottom">Paid</flux:badge></flux:table.cell>
                            <flux:table.cell variant="strong">
                                <flux:button icon="pencil" size="sm"></flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:callout inline>
                <flux:callout.heading icon="newspaper">Belum ada Vacancies</flux:callout.heading>

                <flux:callout.text>Tidak ada data lowongan. Silakan tambahkan lowongan baru untuk memulai proses rekrutmen.</flux:callout.text>
            </flux:callout>
        @endif
    </div>
</div>
