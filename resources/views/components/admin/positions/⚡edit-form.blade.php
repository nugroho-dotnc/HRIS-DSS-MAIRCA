<?php

use App\Models\Department;
use App\Models\Position;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public $id = 0;

    #[Validate('required | min:3')]
    public string $position_name;

    #[Validate('required')]
    public $department_id = 0;

    public $is_active = true;

    #[Computed]
    public function departments(){
        return Department::all();
    }

    #[On('edit-positions')]
    public function _editPositions($id){
       $position = Position::findOrFail($id);
       $this->id = $id;
       $this->position_name = $position->position_name;
       $this->department_id = $position->department_id;
       $this->is_active = $position->is_active;
    }

    public function update(){
        $this->validate();
        $position = Position::findOrFail($this->id);
        $position->update(
            [
                'position_name'=> $this->position_name,
                'department_id'=> $this->department_id,
                'is_active' => $this->is_active,
            ]
        );
        Flux::toast('Posisi '.$position->position_name.' Berhasil di update!');
        $this->dispatch('position-updated');
    }
};
?>

<div>
    {{-- Knowing is not enough; we must apply. Being willing is not enough; we must do. - Leonardo da Vinci --}}
    <div class="space-y-6">
        <flux:heading size="lg">Edit Positions</flux:heading>
        <flux:text class="mt-2">Perbarui posisi baru ke basis data.</flux:text>

        <flux:field>
            <flux:label>Position Name</flux:label>
            <flux:input wire:model="position_name" type="text" placeholder="Masukkan nama posisi" />
            <flux:error name="position_name" />
        </flux:field>

        <flux:field>
            <flux:label>Department</flux:label>
            <flux:select wire:model="department_id" placeholder="Choose industry..." searchable>
                @forelse ($this->departments() as $dept)
                <flux:select.option value="{{ $dept->id }}" wire:key='{{ $dept->id }}'>
                        {{ $dept->department_name }}
                </flux:select.option>
                @empty
                <flux:select.option selected disabled>
                        Tidak ada department
                </flux:select.option>
                @endforelse
            </flux:select>
            <flux:error name="department_id" />
        </flux:field>

        <flux:field>
                <flux:label>Is Active?</flux:label>
                <flux:switch wire:model="is_active" :checked="$this->is_active"/>
                <flux:error name="is_active" />
        </flux:field>

        <div class="flex">
            <flux:spacer />
            <flux:button wire:click="update" variant="primary">Save changes</flux:button>
        </div>
    </div>
</div>
