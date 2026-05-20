<?php

use App\Models\Position;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new  #[Layout('layouts::admin', ['page_title' => 'Positions'])] class extends Component
{
    use WithPagination;
    public $search = '';
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    #[Computed]
    public function positions(){
       return Position::with('department')
        ->when($this->search, fn($q) =>
            $q->where('position_name', 'like', "%{$this->search}%")
        )->paginate(10);
    }
};
?>

<div>
    <div class="flex flex-1 flex-col gap-4 rounded-xl">
        <flux:table class="mt-5" :paginate="$this->positions()">
            <flux:table.columns>
                <flux:table.column>Positions Name</flux:table.column>
                <flux:table.column>Department</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->positions() as $post)
                    <flux:table.row :key="$post->id">
                        <flux:table.cell class="flex items-center gap-3">
                            {{ $post->position_name }}
                        </flux:table.cell>
                        <flux:table.cell>{{$post->department->department_name}}</flux:table.cell>
                        <flux:table.cell>{{$post->is_active?'active':'inactive'}}</flux:table.cell>
                        <flux:table.cell class="space-x-4">
                            <flux:switch :checked="$post->is_active" wire:click="isActiveToggle({{ $post->id }})"/>
                            <flux:button icon="trash" size="sm" type="button" variant="danger" wire:click="delete({{$post->id}})" wire:confirm="Are you sure you want to delete this post?"></flux:button>
                            <flux:modal.trigger :name="'edit-department-'.$post->id">
                                <flux:button class="cursor-pointer" size="sm" icon="pencil"/>
                            </flux:modal.trigger>
                        </flux:table.cell>
                    </flux:table.row>
                    <flux:modal :name="'edit-department-'.$post->id">
                        @livewire('admin.departments.edit-form')
                    </flux:modal>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>
