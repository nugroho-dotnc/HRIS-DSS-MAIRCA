<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::guest')] class extends Component
{
    //
};
?>

<div>
    <div>
        <x-candidate.hero/>
        <livewire:candidate.vacancies-section/>
    </div>
</div>
