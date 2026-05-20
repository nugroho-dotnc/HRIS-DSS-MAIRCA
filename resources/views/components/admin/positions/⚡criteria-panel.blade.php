<?php

use App\Models\Position;
use App\Models\RecruitmentCriteria;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public int $positionId;
    public bool $isEdit = false;
    public int $criteriaId = 0;

    #[Validate('required|min:3|max:50')]
    public $name = '';

    #[Validate('required|numeric|min:0|max:100')]
    public $weight = '';

    #[Validate('required|in:benefit,cost')]
    public string $type = '';

    #[Validate('required|in:kualitatif,kuantitatif')]
    public string $data_type = '';

    public function position(){
        return Position::findOrFail($this->positionId);
    }

    public function criteriaByPositionId(){
        return RecruitmentCriteria::where('position_id', $this->positionId)->get();
    }

    public function edit($id): void{
        $criteria = RecruitmentCriteria::findOrFail($id);
        $this->criteriaId = $criteria->id;
        $this->name = $criteria->name;
        $this->weight = $criteria->weight;
        $this->type = $criteria->type;
        $this->data_type = $criteria->data_type;
        $this->isEdit = true;
    }

    public function cancelEdit(): void{
        $this->reset('criteriaId', 'name', 'weight', 'type', 'data_type', 'isEdit');
    }


    public function save(): void{
        $this->validate();

        if(!$this->checkTotalWeightCriteria($this->weight)){
            return;
        }

        if($this->isEdit){
            $criteria = RecruitmentCriteria::findOrFail($this->criteriaId);
            $criteria->update(
                 [
                    'position_id' => $this->positionId,
                    'name' => $this->name,
                    'weight' => $this->weight,
                    'type'  => $this->type,
                    'data_type' => $this->data_type
                ]
            );
            $this->reset('criteriaId', 'name', 'weight', 'type', 'data_type', 'isEdit');
            Flux::toast('Criteria berhasil di update!');
            $this->dispatch('$refresh');
            return;
        }

        RecruitmentCriteria::create(
            [
                'position_id' => $this->positionId,
                'name' => $this->name,
                'weight' => $this->weight,
                'type'  => $this->type,
                'data_type' => $this->data_type
            ]
        );
        $this->reset('name', 'weight', 'type', 'data_type');
        Flux::toast('Criteria baru berhasil ditambahkan!');
        $this->dispatch('$refresh');
    }

    public function checkTotalWeightCriteria($w):bool {
        $specificWeight  = 0;
        if($this->isEdit){
            $criteria = RecruitmentCriteria::findOrFail($this->criteriaId);
            $specificWeight = $criteria->weight;
        }
        $totalWeight = RecruitmentCriteria::where('position_id', $this->positionId)->sum('weight')??0;
        $newWeight = $totalWeight - $specificWeight + $w;
        if($newWeight <= 100){
            return true;
        }
        Flux::toast('Gagal, Jumlah bobot lebih besar 100%!');
        return false;
    }
    public function delete($id): void{
        $criteria = RecruitmentCriteria::findOrFail($id);
        $criteria->delete();
        Flux::toast('Criteria '.$criteria->name.' berhasil dihapus!');
        $this->dispatch('$refresh');
    }

    public function close(){
        $this->dispatch('close-criterias', id: $this->positionId);
    }
};
?>

<div>
    {{-- Simplicity is an acquired taste. - Katharine Gerould --}}
    <div class="space-y-6 p-4 overflow-visible">
        <flux:heading class="flex gap-4 items-center justify-between">
            <div class="flex gap-4 items-center">
                Recruitment Criterias: <flux:badge color="green">{{ $this->position()->position_name }}</flux:badge>
            </div>
            <flux:button size="sm" wire:click="close" class="cursor-pointer">
                Close
            </flux:button>
        </flux:heading>
        <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Weight</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Data Type</flux:table.column>
                    <flux:table.column>Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->criteriaByPositionId() as $criteria)
                        @if($this->criteriaId == $criteria->id)
                            <x-admin.positions.add-criteria/>
                        @else
                            <flux:table.row :key="$criteria->id">
                                <flux:table.cell class="w-1/4">
                                    {{ $criteria->name }}
                                </flux:table.cell>
                                <flux:table.cell class="w-1/4">{{$criteria->weight}}</flux:table.cell>
                                <flux:table.cell class="w-1/4">{{$criteria->type }}</flux:table.cell>
                                <flux:table.cell class="w-1/4">{{$criteria->data_type }}</flux:table.cell>
                                <flux:table.cell class="space-x-4 w-1/4">
                                    <flux:button icon="trash" size="sm" type="button" variant="danger" wire:click="delete({{$criteria->id}})" wire:confirm="Are you sure you want to delete this criteria?"></flux:button>
                                    <flux:button class="cursor-pointer" size="sm" wire:click="edit({{$criteria->id}})" icon="pencil"/>
                                </flux:table.cell>
                            </flux:table.row>
                        @endif
                    @endforeach
                   @if(!$this->isEdit)
                    <x-admin.positions.add-criteria/>
                   @endif
                </flux:table.rows>
        </flux:table>
    </div>
</div>
