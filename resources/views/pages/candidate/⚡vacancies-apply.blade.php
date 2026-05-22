<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts::guest')] class extends Component
{
    public $id;
};
?>

<div class="flex flex-1 flex-col gap-6 rounded-xl">
    <livewire:bread-crumbs/>

    <flux:fieldset class="space-y-6">
        <div class="grid grid-cols-2 gap-x-4 gap-y-6">
            <flux:input label="Nama" placeholder="ex: Jane Doe" />
            <flux:input label="Email" type="email" placeholder="janedoe@example.com" />
        </div>
        <flux:input label="Phone number" type="phone" placeholder="+62xxxxxxxxxxx" />
        <flux:radio.group wire:model="payment" label="Jenis kelamin anda?">
            <flux:radio value="L" label="Laki - laki" />
            <flux:radio value="P" label="Perempuan" />
        </flux:radio.group>
    </flux:fieldset>

    <flux:separator text="address"/>

    <flux:fieldset class="grid grid-cols-2 gap-x-4 gap-y-6">
        <flux:input label="City" placeholder="San Francisco" />
        <flux:input label="Postal / Zip code" placeholder="12345" />
        <div class="flex flex-col gap-2 col-span-2">
            <flux:textarea wire:model="description" label="Alamat Lengkap" rows="4"
                placeholder="Jelaskan gambaran umum posisi ini, tanggung jawab utama, dan lingkungan kerja..."/>
        </div>
    </flux:fieldset>

    <flux:separator text="experiences"/>

    {{-- experiences --}}

    <flux:fieldset class="grid grid-cols-2 gap-x-4 gap-y-6">
        <div class="col-span-2">
            <flux:textarea wire:model="requirements" label="experiences" rows="4" placeholder="Tuliskan pengalaman anda yang berhubungan dengan posisi ini..." />
        </div>
        <flux:input label="CV" type="file" placeholder="12345" accept=".pdf,.doc,.docx" />
        <flux:input label="Portofolio" type="file" placeholder="12345" accept=".pdf,.doc,.docx" />
        <div class="col-span-2">
            <flux:input label="Web Portofolio Url" placeholder="www.nugroho.porto.com" />
        </div>
    </flux:fieldset>

    {{-- <div class="flex items-center gap-2">
        <flux:badge color="{{ $status ? 'green' : 'zinc' }}" size="sm" inset="top bottom">
            {{ $status ? 'Open' : 'Closed' }}
        </flux:badge>
        <flux:switch wire:model.live="status" align="left"/>
    </div> --}}
    <flux:button variant="primary" class="cursor-pointer" wire:click="save">Simpan</flux:button>

</div>
