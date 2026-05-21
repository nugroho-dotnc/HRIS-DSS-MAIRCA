<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::employee', ['page_title' => 'Dashboard'])] class extends Component
{
    //
};
?>

<div>
    {{-- Employee Dashboard --}}
    <flux:text>Selamat datang, {{ auth()->user()->name }}!</flux:text>
</div>
