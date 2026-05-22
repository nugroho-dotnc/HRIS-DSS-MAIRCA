<?php

use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    public int $id;
    public string $user_name;
    public string $user_email;
    public ?string $user_password = null;
    public string $confirm_password = '';
    public string $user_role = '';
    public bool $is_active = true;

    #[Computed]
    public function users()
    {
        return User::all();
    }

    public function save()
    {
        $rules = [
            'user_name' => 'required | max:255',
            'user_email' => 'required | email | max:255 | unique:users,email',
            'user_password' => 'required | min:8',
            'confirm_password' => 'required | same:user_password',
            'user_role' => 'required',
            'is_active' => 'required',
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->user_name,
            'email' => $this->user_email,
            'password' => Hash::make($this->user_password),
            'role' => $this->user_role,
            'is_active' => $this->is_active,
        ];

        User::create($data);

        Flux::toast("User berhasil ditambahkan!");

        $this->dispatch('user-created');

        $this->reset([
            "user_name",
            "user_email",
            "user_password",
            "confirm_password",
            "user_role",
            "is_active"
        ]);
    }
};
?>

<div>
    {{-- Knowing is not enough; we must apply. Being willing is not enough; we must do. - Leonardo da Vinci --}}
    <div class="space-y-6">
        <flux:heading size="lg">Add Users</flux:heading>
        <flux:text class="mt-2">Tambahkan user baru ke basis data.</flux:text>


        {{-- input nama --}}
        <flux:field>
            <flux:label>Nama</flux:label>
            <flux:input wire:model="user_name" type="text" placeholder="Masukkan nama user" />
            <flux:error name="user_name" />
        </flux:field>

        {{-- input email --}}
        <flux:field>
            <flux:label>Email</flux:label>
            <flux:input wire:model="user_email" type="text" placeholder="Masukkan email user" />
            <flux:error name="user_email" />
        </flux:field>

        {{-- input password --}}
        <flux:field>
            <flux:label>Password</flux:label>
            <flux:input wire:model="user_password" type="password" placeholder="Masukkan password user" />
            <flux:error name="user_password" />
        </flux:field>

        {{-- input konfirmasi password --}}
        <flux:field>
            <flux:label>Konfirmasi Password</flux:label>
            <flux:input wire:model="confirm_password" type="password" placeholder="Masukkan konfirmasi password user" />
            <flux:error name="confirm_password" />
        </flux:field>

        {{-- input role --}}
        <flux:label>Role</flux:label>
        <flux:select wire:model="user_role" placeholder="Choose role..." searchable>
            <flux:select.option value="admin">Admin</flux:select.option>
            <flux:select.option value="hr">HR</flux:select.option>
            <flux:select.option value="supervisor">Supervisor</flux:select.option>
            <flux:select.option value="candidate">Candidate</flux:select.option>
            <flux:select.option value="employee">Employee</flux:select.option>
        </flux:select>

        {{-- input is_active --}}
        <flux:field>
            <flux:label>Is Active?</flux:label>
            <flux:switch wire:model="is_active" :checked="$this->is_active" />
            <flux:error name="is_active" />
        </flux:field>


        <div class="flex">
            <flux:spacer />
            <flux:button wire:click="save" variant="primary">Add</flux:button>
        </div>
    </div>
</div>
