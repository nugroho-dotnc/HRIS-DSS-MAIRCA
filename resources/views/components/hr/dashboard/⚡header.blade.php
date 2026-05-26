<?php

use App\Models\Application;
use App\Models\InterviewSession;
use Carbon\Carbon;
use Livewire\Component;

new class extends Component
{
    public function todayApplications(): int
    {
        return Application::whereDate('created_at', today())->count();
    }

    public function todayInterviews(): int
    {
        return InterviewSession::whereDate('interview_date', today())->count();
    }

    public function displayDate(): string
    {
        return Carbon::now()->translatedFormat('l, d F Y');
    }
};
?>

<section class="flex flex-col gap-4 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 md:flex-row md:items-center md:justify-between">
    <div class="flex flex-col gap-1">
        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $this->displayDate() }}</flux:text>
        <flux:heading size="lg">Selamat datang, {{ auth()->user()->name }}</flux:heading>
        <flux:text class="max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">
            Pantau lowongan, lamaran, jadwal interview, dan kandidat yang siap diproses dengan MAIRCA.
        </flux:text>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:min-w-72">
        <div class="rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="flex items-center gap-2 text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">
                <flux:icon name="document-plus" class="size-4" />
                Hari ini
            </div>
            <div class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $this->todayApplications() }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Lamaran baru</div>
        </div>

        <div class="rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="flex items-center gap-2 text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">
                <flux:icon name="calendar-days" class="size-4" />
                Jadwal
            </div>
            <div class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $this->todayInterviews() }}</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Interview hari ini</div>
        </div>
    </div>
</section>
