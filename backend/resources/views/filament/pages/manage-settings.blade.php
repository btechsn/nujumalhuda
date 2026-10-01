<x-filament-panels::page>
    <form wire:submit="save" class="nh-settings" style="width: min(100%, {{ $this->settingsWidth() }})">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
        />
    </form>

    <style>
        .nh-settings {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }
    </style>
</x-filament-panels::page>
