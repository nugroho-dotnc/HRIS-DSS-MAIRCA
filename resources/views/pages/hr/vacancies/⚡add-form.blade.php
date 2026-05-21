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

<div class="flex flex-1 flex-col gap-4 rounded-xl">
    <livewire:bread-crumbs/>
    <div class="w-full max-w-4xl flex flex-col gap-4 mt-5">
        <flux:input type="text" label="Title" wire:model="title" placeholder="Tuliskan judul lowongan"/>
        @if(count($this->positions()) == 0)
            <flux:callout variant="warning" icon="exclamation-circle" heading="Posisi tidak tersedia!" />
        @endif
        <flux:select wire:model="position_id" placeholder="Choose positions..." label="Position">
            @foreach($this->positions() as $post)
                <flux:select.option value="{{$post->id}}">{{$post->position_name}}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:textarea
            wire:model="description"
            label="Deskripsi Pekerjaan"
            placeholder="Jelaskan gambaran umum posisi ini, tanggung jawab utama, dan lingkungan kerja. Contoh: Kami mencari seorang Backend Developer yang akan bergabung dengan tim engineering untuk membangun dan mengembangkan sistem internal perusahaan..."
            rows="5"
        />

        <flux:textarea
            wire:model="requirements"
            label="Persyaratan"
            placeholder="Tuliskan kualifikasi yang dibutuhkan untuk posisi ini. Contoh: Pendidikan minimal D3/S1 Teknik Informatika, menguasai PHP dan Laravel, berpengalaman minimal 1 tahun di bidang yang relevan..."
            rows="5"
        />
        <flux:input type="date" wire:model="deadline" max="2999-12-31" label="Deadline" />
        <flux:field align="end">
            <flux:label>Status</flux:label>
             <flux:switch label="{{$this->status?'open':'closed'}}" wire:model.live="status" align="left"/>
        </flux:field>
        <flux:button variant="primary" class="cursor-pointer" wire:click="save">
            Simpan
        </flux:button>
    </div>
</div>
