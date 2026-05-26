<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts::admin', ['page_title' => 'Dashboard', 'page_description' => 'Ringkasan data master, user, posisi, dan kesiapan kriteria DSS'])] class extends Component
{
    //
};
?>

<div class="flex flex-1 flex-col gap-6">
    <livewire:admin.dashboard.header />

    <livewire:admin.dashboard.stats-overview />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <livewire:admin.dashboard.role-distribution />
        <livewire:admin.dashboard.master-data-health />
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <livewire:admin.dashboard.latest-users />
        <livewire:admin.dashboard.positions-criteria />
    </div>
</div>
