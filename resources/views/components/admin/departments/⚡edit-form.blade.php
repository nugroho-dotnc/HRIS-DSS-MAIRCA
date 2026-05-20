<?php

use App\Models\Department;
use Illuminate\Support\Facades\Date;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public $id = null;

    #[Validate('required | min:3')]
    public $department_name = null;

    public $is_active = true;

    #[On('open-edit')]
    public function onOpenEdit($id){
        $department = Department::findOrFail($id);
        $this->id = $department->id;
        $this->department_name = $department->department_name;
        $this->is_active = $department->is_active;
    }

    public function save(){
        $this->validate();
        $department = Department::findOrFail($this->id);
        $department->update(
            [
                'department_name' => $this->department_name,
                'is_active' => $this->is_active,
            ]
        );
        $this->dispatch('department-updated')->to('admin.departments.table');
    }
};
?>

<div>
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tambah Department</flux:heading>
                <flux:text class="mt-2">Tambahkan department baru ke basis data.</flux:text>
            </div>

            <flux:field>
                <flux:label>Department Name</flux:label>
                <flux:input wire:model="department_name" type="text" placeholder="Masukkan nama departement" />
                <flux:error name="department_name" />
            </flux:field>

            <flux:field>
                <flux:label>Is Active?</flux:label>
                <flux:switch wire:model="is_active"/>
                <flux:error name="is_active" />
            </flux:field>

            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="save" variant="primary">Save changes</flux:button>
            </div>
        </div>
</div>
