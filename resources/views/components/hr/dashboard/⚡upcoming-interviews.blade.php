<?php

use App\Models\InterviewSession;
use Carbon\Carbon;
use Livewire\Component;

new class extends Component
{
    public function interviews()
    {
        return InterviewSession::with(['application.candidate', 'application.vacancy.position', 'interviewer'])
            ->where('interview_date', '>=', now())
            ->orderBy('interview_date')
            ->limit(6)
            ->get();
    }
};
?>

<section class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <flux:heading size="md">Upcoming Interviews</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Jadwal interview terdekat.</flux:text>
        </div>
       <div class="flex items-center justify-center gap-3">
         <flux:badge color="blue" size="sm">{{ $this->interviews()->count() }} jadwal</flux:badge>
        <flux:button icon="arrow-right" size="sm" variant="ghost" href="{{ route('hr.interviews') }}" wire:navigate>Lihat</flux:button>
       </div>
    </div>

    @if($this->interviews()->isNotEmpty())
        <div class="flex flex-col divide-y divide-zinc-200 dark:divide-zinc-700">
            @foreach($this->interviews() as $session)
                <div class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                    <div class="min-w-0">
                        <div class="truncate font-medium text-zinc-900 dark:text-white">{{ $session->application->candidate->name }}</div>
                        <div class="truncate text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $session->application->vacancy->position->position_name ?? '-' }} · {{ $session->interviewer->name ?? 'Interviewer belum tersedia' }}
                        </div>
                        @if($session->notes)
                            <div class="mt-1 line-clamp-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $session->notes }}</div>
                        @endif
                    </div>
                    <div class="shrink-0 text-right">
                        <div class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                            {{ Carbon::parse($session->interview_date)->translatedFormat('d M') }}
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ Carbon::parse($session->interview_date)->format('H:i') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <flux:callout inline align="center">
            <flux:callout.heading icon="calendar-days" class="mx-auto">Tidak ada interview mendatang</flux:callout.heading>
            <flux:callout.text>Jadwal interview yang akan datang akan tampil di sini.</flux:callout.text>
        </flux:callout>
    @endif
</section>
