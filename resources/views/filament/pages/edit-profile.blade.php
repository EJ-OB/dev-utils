<x-filament-panels::page>
    <x-filament-panels::form id="form" wire:submit="save">
        {{ $this->form }}
    </x-filament-panels::form>

    <x-filament-panels::form id="twoFactorAuthForm" wire:submit="confirm">
        {{ $this->twoFactorAuthForm }}
    </x-filament-panels::form>

    <x-filament-panels::form id="browserSessionsForm">
        {{ $this->browserSessionsForm }}
    </x-filament-panels::form>
</x-filament-panels::page>
