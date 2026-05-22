<?php

use App\Models\Position;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new  #[Layout('layouts::admin', ['page_title' => 'Positions'])] class extends Component
{
    use WithPagination;

    public $search = '';
    public array $expandedRows = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleExpand($id): void
    {
        if (in_array($id, $this->expandedRows)) {
            $this->expandedRows = array_filter($this->expandedRows, fn($i) => $i !== $id);
        } else {
            $this->expandedRows[] = $id;
        }
    }

    #[Computed]
    public function positions(){
        return Position::select(['id', 'position_name', 'department_id', 'is_active'])
            ->with('department', 'recruitment_criteria')
            ->withCount('recruitment_criteria')
            ->withSum('recruitment_criteria', 'weight')
            ->when($this->search, fn($q) =>
                $q->where('position_name', 'like', "%{$this->search}%")
            )
            ->orderBy('is_active', 'desc')->paginate(6);
    }

    public function toggleActive($id){
        $position = Position::findOrFail($id);
        $position->update(
            [
                'is_active' => !$position->is_active
            ]
        );
        Flux::toast('Position dengan nama '.$position->position_name.' berhasil di update!');
        $this->resetPage();
    }

    public function delete($id){
        $position = Position::findOrFail($id);
        $position->delete();
        Flux::toast('Position '.$position->position_name.' Berhasil Dihapus!');
        $this->resetPage();
    }

    public function edit($id){
        $this->dispatch('edit-positions', id: $id)->to('admin.positions.edit-form');
        Flux::modal('edit-positions')->show();
    }

    #[On('position-updated')]
    public function onPositionUpdated(): void{
        Flux::modal('edit-positions')->close();
        $this->resetPage();
    }

    #[On('position-created')]
    public function onPositionCreated(): void{
        Flux::modal('add-positions')->close();
        $this->resetPage();
    }

    #[On('close-criterias')]
    public function onCloseCriterias($id){
        $this->toggleExpand($id);
    }
};
?>

<div>
    <div class="flex flex-1 flex-col gap-8">
        <div class="flex justify-between items-center">
            <flux:input wire:model.live.debounce.300ms="search" class="w-full max-w-md" kbd="⌘K" icon="magnifying-glass" placeholder="Search..."/>
            <flux:modal.trigger name="add-positions">
                <flux:button class="cursor-pointer">Tambah</flux:button>
            </flux:modal.trigger>
        </div>
        <flux:table :paginate="$this->positions()">
            <flux:table.columns>
                <flux:table.column>Positions Name</flux:table.column>
                <flux:table.column>Department</flux:table.column>
                <flux:table.column>Kriteria DSS</flux:table.column>
                <flux:table.column>Total bobot</flux:table.column>
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
                        <flux:table.cell>{{ $post->recruitment_criteria_count }}</flux:table.cell>
                        <flux:table.cell>{{ $post->recruitment_criteria_sum_weight??0 }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{$post->is_active?'green':'red'}}" size="sm">
                                {{$post->is_active?'active':'inactive'}}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="space-x-4">
                            <flux:switch :checked="$post->is_active" wire:click="toggleActive({{ $post->id }})"/>
                            <flux:button class="cursor-pointer" size="sm" wire:click="edit({{$post->id}})" icon="pencil"/>
                            <flux:button class="cursor-pointer" size="sm" wire:click="toggleExpand({{$post->id}})" icon="cog-6-tooth"/>
                            <flux:button icon="trash" size="sm" type="button" variant="danger" wire:click="delete({{$post->id}})" wire:confirm="Are you sure you want to delete this post?"></flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                    {{-- Expandable row --}}
                    @if(in_array($post->id, $expandedRows))
                        <flux:table.row :key="'expand-'.$post->id">
                            <flux:table.cell colspan="6" class="bg-zinc-50 dark:bg-zinc-800 p-4">
                                <livewire:admin.positions.criteria-panel
                                    :position-id="$post->id"
                                    :key="'criteria-'.$post->id"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @endif
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    <flux:modal name="add-positions" class="md:w-4xl">
        <livewire:admin.positions.add-form/>
    </flux:modal>

    <flux:modal name="edit-positions" class="md:w-4xl">
        <livewire:admin.positions.edit-form/>
    </flux:modal>
</div>
