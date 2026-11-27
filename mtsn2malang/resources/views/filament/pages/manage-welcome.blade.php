<x-filament-panels::page>
    <form id="form" wire:submit="save" class="fi-form grid gap-y-6">
        {{ $this->form }}

        <div class="fi-form-actions flex flex-wrap items-center gap-3 mt-6">
            <x-filament::button type="submit" color="primary" size="lg">
                Simpan Data
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
