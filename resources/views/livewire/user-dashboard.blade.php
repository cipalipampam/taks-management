<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    <div>
        <flux:heading size="xl">Dashboard</flux:heading>
        <flux:text class="text-zinc-500 dark:text-zinc-300">Selamat datang di dashboard tugas Anda.</flux:text>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <livewire:stats-overview />
    </div>
</div>
