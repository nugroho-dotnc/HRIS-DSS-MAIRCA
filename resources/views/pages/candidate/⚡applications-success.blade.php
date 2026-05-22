<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::guest')] class extends Component
{
    public $applicationCode;
    public function mount()
    {
        $this->applicationCode = session('application_code');
    }
};
?>

<div>
    @if($applicationCode)
        <section class="flex flex-col gap-6 items-center justify-center min-h-[60vh]" id="vacancies">
            <h1 class="text-2xl font-bold text-center">
                Lamaran berhasil dikirim!
            </h1>

            <p class="text-center w-full max-w-3xl">
                Terima kasih telah melamar. Simpan kode aplikasi di bawah ini untuk melacak perkembangan proses rekrutmen Anda melalui halaman
                <a class="font-semibold cursor-pointer" href="{{route('candidate.applications')}}">Applications</a>.
            </p>

            <div
                x-data="{ copied: false }"
                class="p-6 border-2 border-teal-600 rounded-xl bg-teal-600/10 flex items-center gap-4"
            >
                <h1 class="text-xl font-bold tracking-[0.2em] text-teal-700 dark:text-teal-400">
                    {{ $this->applicationCode }}
                </h1>

                <flux:button
                    type="button"
                    size="sm"
                    variant="primary"
                    x-on:click="
                        navigator.clipboard.writeText('{{ $this->applicationCode }}');
                        copied = true;

                        setTimeout(() => copied = false, 2000);
                    "
                >
                    <span x-show="!copied">Copy Code</span>
                    <span x-show="copied">Copied!</span>
                </flux:button>
            </div>

            <p class="text-sm text-center opacity-80">
                Kode dapat disalin dan digunakan untuk tracking status lamaran Anda.
            </p>
        </section>
    @else
        <flux:callout variant="warning">
            <flux:callout.heading  icon="exclamation-circle">Anda belum melamar</flux:callout.heading>
            <flux:callout.text>Untuk mendapat applications code, pastikan anda sudah melamar pekerjaan. <a class="font-bold" href="{{route('candidate.vacancies')}}">Lamar Sekarang!</a></flux:callout.text>
        </flux:callout>
    @endif
</div>
