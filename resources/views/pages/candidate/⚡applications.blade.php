<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts::guest')] class extends Component
{
    //
};
?>

<div>
     <section class="flex flex-col gap-6 items-center justify-start" id="vacancies">
        <h1 class="text-2xl font-bold">Lacak Status Lamaran Anda</h1>
        <p class="text-center w-full max-w-3xl">
            Masukkan kode aplikasi yang Anda terima saat melamar untuk melihat riwayat dan status terbaru dari proses rekrutmen Anda secara cepat dan mudah.
        </p>

        <flux:input wire:model.live.debounce.300ms="search" type="text" class="w-full max-w-md" kbd="⌘K" icon="magnifying-glass" placeholder="Masukkan nama lengkap atau applications code"/>
     </section>
</div>
