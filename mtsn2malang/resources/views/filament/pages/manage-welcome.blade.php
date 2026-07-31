<x-filament-panels::page>
    <x-filament-panels::form id="form" wire:submit="save">
        {{ $this->form }}

        <div class="fi-form-actions flex flex-wrap items-center gap-3 mt-6">
            <x-filament::button type="submit" color="primary" size="lg">
                Simpan Data
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
