<div>
    <!-- Simplicity is the essence of happiness. - Cedric Bledsoe -->
 <!-- add form -->
    <flux:table.row>
        <flux:table.cell class="w-1/4 overflow-visible">
            <flux:field>
                <flux:input wire:model="name" type="text" placeholder="Masukkan Nama Criteria" size="sm"/>
                <flux:error name="name" />
            </flux:field>
        </flux:table.cell>

        <flux:table.cell class="w-1/4">
            <flux:field>
                <flux:input wire:model="weight" type="text" placeholder="Masukkan bobot (%)" size="sm"/>
                <flux:error name="weight" />
            </flux:field>
        </flux:table.cell>

        <flux:table.cell class="w-1/4">
            <flux:field>
                <flux:select wire:model="type" placeholder="Choose Type..." size="sm">
                    <flux:select.option value="benefit">
                        benefit
                    </flux:select.option>
                    <flux:select.option value="cost">
                        cost
                    </flux:select.option>
                </flux:select>
                <flux:error name="type" />
            </flux:field>
        </flux:table.cell>

        <flux:table.cell class="w-1/4">
            <flux:field>
                <flux:select wire:model="data_type" placeholder="Choose Data Type..." size="sm">
                    <flux:select.option value="kualitatif">
                        kualitatif
                    </flux:select.option>
                    <flux:select.option value="kuantitatif">
                        kuantitatif
                    </flux:select.option>
                </flux:select>
                <flux:error name="data_type" />
            </flux:field>
        </flux:table.cell>

        <flux:table.cell class="space-x-4 w-1/4">
            <flux:button size="sm" type="button" variant="primary" class="cursor-pointer" wire:click="save">
                Save
            </flux:button>
            @if($this->isEdit)
            <flux:button size="sm" type="button" class="cursor-pointer" wire:click="cancelEdit">
                Cancel
            </flux:button>
            @endif
        </flux:table.cell>

    </flux:table.row>
</div>
