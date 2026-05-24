<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::guest')] class extends Component
{
    //
};
?>

<div class="flex flex-col">
    {{-- Hero Section --}}
    <x-candidate.hero/>

    {{-- Divider --}}
    <div class="w-full border-t border-zinc-100 dark:border-zinc-700"></div>

    {{-- Vacancies Section --}}
    <livewire:candidate.vacancies-section/>
</div>
