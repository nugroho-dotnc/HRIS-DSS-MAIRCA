<?php

use App\Models\Department;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    public string $search = '';
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function departments(){
        return Department::query()
            ->when($this->search, fn($q) =>
                $q->where('department_name', 'like', "%{$this->search}%")
            )
            ->paginate(6);
    }

    public function openEdit($id){
        $this->dispatch('open-edit', id: $id)->to('admin.departments.edit-form');
        Flux::modal('edit-department')->show();
    }

    #[On('department-updated')]
    public function onDepartmentUpdated(): void{
        Flux::toast('Department berhasil di update!');
        Flux::modal('edit-department')->close();
        $this->resetPage();
    }

    public function delete($id){
        Department::destroy($id);
        Flux::toast('Department berhasil dihapus!');
    }

    public function isActiveToggle($id){
        $department = Department::findOrFail($id);
        $department->update([
            'is_active' => !$department->is_active
        ]);
        $this->resetPage();
    }

    #[On('department-created')]
    public function onDepartmentCreated(): void
    {
        Flux::toast('Department berhasil dibuat!.');
        Flux::modal('add-department')->close();
        $this->resetPage();
    }

};
?>

<div>
    <div class="flex flex-1 flex-col gap-8">
        <div class="flex justify-between items-center">
            <flux:input wire:model.live.debounce.300ms="search" class="w-full max-w-md" kbd="⌘K" icon="magnifying-glass" placeholder="Search..."/>
            <flux:modal.trigger name="add-department">
                <flux:button class="cursor-pointer">Tambah</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:table :paginate="$this->departments()">
            <flux:table.columns>
                <flux:table.column class="w-1/3">Department Name</flux:table.column>
                <flux:table.column class="w-1/3">Status</flux:table.column>
                <flux:table.column class="w-1/3">Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->departments() as $department)
                    <flux:table.row wire:key="department-{{ $department->id }}-{{ $department->is_active }}">
                        <flux:table.cell class="flex items-center gap-3">
                            {{ $department->department_name }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{$department->is_active?'green':'red'}}" size="sm">
                                {{$department->is_active?'active':'inactive'}}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="space-x-4">
                            <flux:switch :checked="$department->is_active" wire:click="isActiveToggle({{ $department->id }})"/>
                            <flux:button icon="trash" size="sm" type="button" variant="danger" wire:click="delete({{$department->id}})" wire:confirm="Are you sure you want to delete this post?"></flux:button>
                            <flux:button class="cursor-pointer" size="sm" icon="pencil" wire:click="openEdit({{$department->id}})"/>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    <flux:modal name="add-department" class="md:w-4xl">
        @livewire('admin.departments.add-form')
    </flux:modal>
    <flux:modal name="edit-department" class="md:w-4xl">
            @livewire('admin.departments.edit-form')
    </flux:modal>
</div>
