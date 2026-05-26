<?php

use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

new class extends Component
{
    public function users()
    {
        return User::select(['id', 'name', 'email', 'role', 'is_active', 'created_at'])
            ->latest()
            ->limit(6)
            ->get();
    }

    public function roleColor(string $role): string
    {
        return match ($role) {
            'admin' => 'blue',
            'hr' => 'green',
            'supervisor' => 'amber',
            'employee' => 'indigo',
            'candidate' => 'zinc',
            default => 'zinc',
        };
    }
};
?>

<section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <flux:heading size="md">Latest Users</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Akun terbaru yang terdaftar di sistem.</flux:text>
        </div>
        <flux:button icon="arrow-right" size="sm" variant="ghost" href="{{ route('admin.users') }}" wire:navigate>Lihat</flux:button>
    </div>

    @if($this->users()->isNotEmpty())
        <flux:table>
            <flux:table.columns>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Tanggal</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->users() as $user)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $user->name }}</span>
                                <span class="text-xs text-zinc-400">{{ $user->email }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $this->roleColor($user->role) }}" size="sm">{{ $user->role }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $user->is_active ? 'green' : 'red' }}" size="sm">
                                {{ $user->is_active ? 'active' : 'inactive' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ Carbon::parse($user->created_at)->translatedFormat('d M Y') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <flux:callout inline align="center">
            <flux:callout.heading icon="users" class="mx-auto">Belum ada user</flux:callout.heading>
            <flux:callout.text>User terbaru akan tampil di sini.</flux:callout.text>
        </flux:callout>
    @endif
</section>
