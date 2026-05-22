<?php

use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin', ['page_title' => 'Users'])] class extends Component {
    use WithPagination;

    public $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        return User::select(['id', 'name', 'email', 'role', 'is_active'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('is_active', 'desc')
            ->paginate(6);
    }

    // function untuk toggle is_active
    // public function toggleActive($id)
    // {
    //     $user = User::findOrFail($id);
    //     $user->update([
    //         'is_active' => !$user->is_active,
    //     ]);
    //     Flux::toast('User berhasil diupdate');
    //     $this->resetPage();
    // }

    // function untuk menghapus data
    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        Flux::toast("User berhasil dihapus!");
        $this->resetPage();
    }

    // function untuk edit data
    public function edit($id)
    {
        $this->dispatch('edit-users', id: $id)->to('admin.users.edit-form');
        Flux::modal('edit-users')->show();
    }

    // fungsi akan langsung dijalankan setelah user diupdate
    #[On('user-updated')]
    public function onUserUpdated(): void
    {
        Flux::modal('edit-users')->close();
        Flux::toast('User Berhasil diupdate!');
        $this->resetPage();
    }

    // fungsi akan langsung dijalankan setelah user dibuat
    #[On("user-created")]
    public function onUserCreated(): void
    {
        Flux::modal('add-users')->close();
        $this->resetPage();
    }
};
?>

<div>
    <div class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <flux:input wire:model.live.debounce.300ms="search" class="w-full max-w-md" icon="magnifying-glass"
                placeholder="Search user..." />
            <flux:modal.trigger name="add-users">
                <flux:button class="cursor-pointer">Tambah</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:table :paginate="$this->users()">
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->users() as $user)
                    <flux:table.row :key="$user['id']">
                        <flux:table.cell>{{ $user['name'] }}</flux:table.cell>
                        <flux:table.cell>{{ $user['email'] }}</flux:table.cell>
                        <flux:table.cell>{{ $user['role'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $user->is_active ? 'green' : 'red' }}" size="sm">
                                {{ $user->is_active ? 'active' : 'inactive' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="space-x-4">
                            {{-- <flux:switch :checked="$user->is_active" wire:click="toggleActive({{ $user->id }})" /> --}}
                            <flux:button class="cursor-pointer" size="sm" wire:click="edit({{ $user->id }})"
                                icon="pencil" />
                            <flux:button icon="trash" size="sm" type="button" variant="danger"
                                wire:click="delete({{ $user->id }})"
                                wire:confirm="Are you sure you want to delete this user?"></flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- modal --}}
    <flux:modal name="add-users" class="md:w-4xl">
        <livewire:admin.users.add-form />
    </flux:modal>

    <flux:modal name="edit-users" class="md:w-4xl">
        <livewire:admin.users.edit-form />
    </flux:modal>
</div>
