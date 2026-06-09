<?php

use App\Models\Position;
use App\Models\Vacancies;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts::hr', ['page_title' => 'Preview'])] class extends Component
{
    // initiate variable
    public $id;
    public $isEdit = false;

    // model
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

    // method
    public function mount(){
        $this->loadVacancies();
    }
    public function edit(){
        $this->isEdit = true;
    }
    public function cancel(){
        $this->reset('isEdit');
        $this->loadVacancies();
    }

    public function save(){
        $this->validate();
        $vacancy = Vacancies::findOrFail($this->id);
        $vacancy->update(
            [
                'title' => $this->title,
                'requirements' => $this->requirements,
                'description' => $this->description,
                'deadline' => $this->deadline,
                'position_id' => $this->position_id,
                'status' => $this->status?'open':'closed'
            ]
        );
        $this->reset('isEdit');
        $this->loadVacancies();
        Flux::toast('Lowongan Berhasil Di Update!');
    }


    public function loadVacancies(){
        $vacancies = Vacancies::findOrFail($this->id);
        $this->title = $vacancies->title;
        $this->description = $vacancies->description;
        $this->requirements = $vacancies->requirements;
        $this->position_id = $vacancies->position_id;
        $this->deadline = $vacancies->deadline;
        $this->status = $vacancies->status == 'open'? true : false;
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
            @if($isEdit)
                <div>
                    <flux:input type="text" wire:model="title" placeholder="Tuliskan judul lowongan" class="text-2xl font-bold"/>
                    <flux:error name="title" />
                </div>
            @else
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $title }}</h1>
            @endif
            <div class="flex gap-2">
                @if($isEdit)
                    <flux:button class="cursor-pointer" wire:click="cancel">Batal</flux:button>
                    <flux:button variant="primary" class="cursor-pointer" wire:click="save">Simpan</flux:button>
                @else
                    <flux:button size="sm" icon="pencil" variant="primary" class="cursor-pointer" wire:click="edit">Edit</flux:button>
                @endif
            </div>
        </div>

        <flux:separator/>

        {{-- Meta info --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Posisi</span>
                @if($isEdit)
                    @if(count($this->positions()) == 0)
                        <flux:callout variant="warning" icon="exclamation-circle" heading="Posisi tidak tersedia!" />
                    @else
                        <flux:select wire:model="position_id" placeholder="Pilih posisi...">
                            @foreach($this->positions() as $post)
                                <flux:select.option value="{{ $post->id }}">{{ $post->position_name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="position_id" />
                    @endif
                @else
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ $this->positions()->firstWhere('id', $position_id)?->position_name ?? '-' }}
                    </span>
                @endif
            </div>

            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Deadline</span>
                @if($isEdit)
                    <flux:input type="date" wire:model="deadline" max="2999-12-31"/>
                    <flux:error name="deadline" />
                @else
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ \Carbon\Carbon::parse($deadline)->translatedFormat('d F Y') }}
                    </span>
                @endif
            </div>
        </div>

        <flux:separator/>

        {{-- Deskripsi --}}
        <div class="flex flex-col gap-2">
            <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Deskripsi Pekerjaan</span>
            @if($isEdit)
                <flux:textarea wire:model="description" rows="6"
                    placeholder="Jelaskan gambaran umum posisi ini, tanggung jawab utama, dan lingkungan kerja..."/>
                <flux:error name="description" />
            @else
                <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-line">{{ $description }}</p>
            @endif
        </div>

        <flux:separator/>

        {{-- Persyaratan --}}
        <div class="flex flex-col gap-2">
            <span class="text-xs font-medium text-zinc-400 uppercase tracking-wide">Persyaratan</span>
            @if($isEdit)
                <flux:textarea wire:model="requirements" rows="6"
                    placeholder="Tuliskan kualifikasi yang dibutuhkan untuk posisi ini..."/>
                <flux:error name="requirements" />
            @else
                <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-line">{{ $requirements }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2 mt-1">
            <flux:badge color="{{ $status ? 'green' : 'zinc' }}" size="sm" inset="top bottom">
                {{ $status ? 'Open' : 'Closed' }}
            </flux:badge>
            @if($isEdit)
                <flux:switch wire:model.live="status" align="left"/>
            @endif
        </div>
    </div>
</div>

