<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts::hr', ['page_title' => 'New Vacancies'])] class extends Component
{
    //
};
?>

<div class="flex flex-1 flex-col gap-4 rounded-xl">
    <livewire:bread-crumbs/>
</div>
