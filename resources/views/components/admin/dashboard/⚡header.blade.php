<?php

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

new class extends Component
{
    public function displayDate(): string
    {
        return Carbon::now()->translatedFormat('l, d F Y');
    }

    public function activeUsers(): int
    {
        return User::where('is_active', true)->count();
    }

    public function inactiveMasterData(): int
    {
        return Department::where('is_active', false)->count()
            + Position::where('is_active', false)->count();
    }
};
?>

<section class="flex flex-col gap-4 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 md:flex-row md:items-center md:justify-between">
    <div class="flex flex-col gap-1">
        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $this->displayDate() }}</flux:text>
        <flux:heading size="lg">Selamat datang, {{ auth()->user()->name }}</flux:heading>
        <flux:text class="max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">
            Pantau user, department, position, dan kelengkapan kriteria DSS rekrutmen dari satu tempat.
        </flux:text>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:min-w-72">
        <div class="rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="flex items-center gap-2 text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">
                <flux:icon name="users" class="size-4" />
                User aktif
            </div>
            <div class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $this->activeUsers() }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Akun dapat login</div>
        </div>

        <div class="rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="flex items-center gap-2 text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">
                <flux:icon name="archive-box-x-mark" class="size-4" />
                Nonaktif
            </div>
            <div class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $this->inactiveMasterData() }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Department/position</div>
        </div>
    </div>
</section>
