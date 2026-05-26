<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::hr', ['page_title' => 'Dashboard', 'page_description' => 'Ringkasan proses rekrutmen, interview, MAIRCA, dan onboarding'])] class extends Component
{
    //
};
?>

<div class="flex flex-1 flex-col gap-6">
    <livewire:hr.dashboard.header />

    <livewire:hr.dashboard.stats-overview />

    <livewire:hr.dashboard.recruitment-pipeline />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <livewire:hr.dashboard.upcoming-interviews />
        <livewire:hr.dashboard.vacancies-closing-soon />
    </div>

    <livewire:hr.dashboard.latest-applications />
</div>
