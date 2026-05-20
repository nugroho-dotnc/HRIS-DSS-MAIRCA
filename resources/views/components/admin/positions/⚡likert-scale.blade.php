<?php
use App\Models\LikertScale;
use App\Models\RecruitmentCriteria;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public $likerts = [];
    public $recruitmentsCriteria = null;
    public $isEdit = false;

    // Form fields
    public $editingId = null;
    public $label = '';
    public $value = '';

    #[On('likert-form-opened')]
    public function onLikertFormOpened($id)
    {
        $this->likerts = LikertScale::where('recruitment_criterias_id', $id)->orderBy('value')->get();
        $this->recruitmentsCriteria = RecruitmentCriteria::findOrFail($id);
        $this->resetForm();
    }

    public function edit($id)
    {
        $likert = LikertScale::findOrFail($id);
        $this->editingId = $likert->id;
        $this->label = $likert->label;
        $this->value = $likert->value;
        $this->isEdit = true;
    }

    public function save()
    {
        $this->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|numeric|min:1|max:10',
        ]);

        if ($this->isEdit && $this->editingId) {
            LikertScale::findOrFail($this->editingId)->update([
                'label' => $this->label,
                'value' => $this->value,
            ]);
        } else {
            LikertScale::create([
                'recruitment_criterias_id' => $this->recruitmentsCriteria->id,
                'label' => $this->label,
                'value' => $this->value,
            ]);
        }

        $this->refreshLikerts();
        $this->resetForm();
    }

    public function delete($id)
    {
        LikertScale::findOrFail($id)->delete();
        $this->refreshLikerts();
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    private function refreshLikerts()
    {
        $this->likerts = LikertScale::where('recruitment_criterias_id', $this->recruitmentsCriteria->id)
            ->orderBy('value')
            ->get();
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->label = '';
        $this->value = '';
        $this->isEdit = false;
    }
};
?>

<div>
    <div class="p-6 space-y-6">
        <flux:heading>
            Skala Likert Criteria: {{ $recruitmentsCriteria?->name }}
        </flux:heading>

        {{-- Tabel Likert --}}
        @if(count($this->likerts) != 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="1/3">Nilai</flux:table.column>
                    <flux:table.column class="1/3">Label</flux:table.column>
                    <flux:table.column class="1/3">Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->likerts as $likert)
                        <flux:table.row :key="$likert->id">
                            <flux:table.cell>
                                <flux:badge variant="outline">{{ $likert->value }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $likert->label }}</flux:table.cell>
                            <flux:table.cell class="text-right space-x-2">
                                <flux:button
                                    icon="pencil"
                                    size="sm"
                                    wire:click="edit({{ $likert->id }})"
                                />
                                <flux:button
                                    icon="trash"
                                    size="sm"
                                    variant="danger"
                                    wire:click="delete({{ $likert->id }})"
                                    wire:confirm="Hapus skala likert ini?"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:callout icon="information-circle">
                Belum ada skala likert untuk kriteria ini.
            </flux:callout>
        @endif

        {{-- Form Tambah / Edit --}}
        <div class="flex flex-col gap-4">
            <flux:heading size="sm">
                {{ $isEdit ? 'Edit Skala Likert' : 'Tambah Skala Likert' }}
            </flux:heading>

            <div class="flex gap-4 items-end">
                <div class="w-1/4">
                    <flux:input
                        label="Nilai (1–10)"
                        type="number"
                        min="1"
                        max="10"
                        wire:model="value"
                        placeholder="cth: 1"
                        :invalid="$errors->has('value')"
                        error:message=""
                    />
                </div>
                <div class="flex-1">
                    <flux:input
                        label="Label"
                        wire:model="label"
                        placeholder="cth: Sangat Tidak Setuju"
                        :invalid="$errors->has('label')"
                        error:message=""
                    />
                </div>
                <div class="flex gap-2">
                    <flux:button variant="primary" wire:click="save">
                        {{ $isEdit ? 'Update' : 'Tambah' }}
                    </flux:button>
                    @if($isEdit)
                        <flux:button wire:click="cancelEdit">Batal</flux:button>
                    @endif
                </div>
            </div>

            @if($errors->hasAny(['value', 'label']))
                <div class="text-sm text-red-500 space-y-1">
                    @error('value') <p>{{ $message }}</p> @enderror
                    @error('label') <p>{{ $message }}</p> @enderror
                </div>
            @endif
        </div>

    </div>
</div>
