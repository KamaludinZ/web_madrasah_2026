<x-filament-panels::page>
    <form id="form" wire:submit="save" class="fi-form grid gap-y-6">
        {{ $this->form }}

        <div class="fi-form-actions flex flex-wrap items-center gap-3 mt-6">
            <x-filament::button type="submit" color="primary" size="lg">
                Simpan Pengaturan
            </x-filament::button>

            <x-filament::button tag="a" href="{{ route('portal') }}" target="_blank" color="gray" size="lg" icon="heroicon-o-arrow-top-right-on-square">
                Lihat Portal
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
