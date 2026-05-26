<?php

use App\Models\Position;
use App\Models\Vacancies;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

new #[Layout('layouts::hr', ['page_title' => 'New Vacancies'])] class extends Component
{
    #[Validate('required|min:3|max:100|string')]
    public $title = '';
    #[Validate('required|integer')]
    public $position_id = '';
    #[Validate('required|min:10|string')]
    public $description = '';
     #[Validate('required|min:10|string')]
    public $requirements = '';
    #[Validate('required|date|after:today')]
    public $deadline = '';

    public $status = true;

    public function save(){
        $this->validate();
        Vacancies::create(
            [
                'hr_id' => auth()->user()->id,
                'title' => $this->title,
                'requirements' => $this->requirements,
                'description' => $this->description,
                'deadline' => $this->deadline,
                'position_id' => $this->position_id,
                'status' => $this->status?'open':'closed'
            ]
        );
        $this->reset('title', 'position_id', 'description', 'requirements', 'deadline', 'status');
        $this->dispatch('vacancies-created');
        redirect()->to('hr/vacancies');
    }

    public function positions(){
        return Position::where('is_active', true)->get();
    }
};
?>

<div class="flex flex-1 flex-col gap-6 rounded-xl">
    <livewire:bread-crumbs/>

    <div class="w-full flex flex-col gap-6">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <flux:input type="text" wire:model="title" placeholder="Tuliskan judul lowongan" class="text-2xl font-bold"/>
            <flux:button variant="primary" class="cursor-pointer" wire:click="save">Simpan</flux:button>
        </div>

        <flux:separator/>

        {{-- Meta info --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Posisi</span>
                @if(count($this->positions()) == 0)
                    <flux:callout variant="warning" icon="exclamation-circle" heading="Posisi tidak tersedia!" />
                @else
                    <flux:select wire:model="position_id" placeholder="Pilih posisi...">
                        @foreach($this->positions() as $post)
                            <flux:select.option value="{{ $post->id }}">{{ $post->position_name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif
            </div>

            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Deadline</span>
                <flux:input type="date" wire:model="deadline" max="2999-12-31"/>
            </div>
        </div>

        <flux:separator/>

        {{-- Deskripsi --}}
        <div class="flex flex-col gap-2">
            <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Deskripsi Pekerjaan</span>
            <flux:textarea wire:model="description" rows="6"
                placeholder="Jelaskan gambaran umum posisi ini, tanggung jawab utama, dan lingkungan kerja..."/>
        </div>

        <flux:separator/>

        {{-- Persyaratan --}}
        <div class="flex flex-col gap-2">
            <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Persyaratan</span>
            <flux:textarea wire:model="requirements" rows="6"
                placeholder="Tuliskan kualifikasi yang dibutuhkan untuk posisi ini..."/>
        </div>

        <div class="flex items-center gap-2">
            <flux:badge color="{{ $status ? 'green' : 'zinc' }}" size="sm" inset="top bottom">
                {{ $status ? 'Open' : 'Closed' }}
            </flux:badge>
            <flux:switch wire:model.live="status" align="left"/>
        </div>

    </div>
</div>
