<x-filament-panels::page>
    <div class="nh-settings">
        <div>
            {{ $this->composeForm }}

            <div class="mt-4">
                <x-filament-panels::form.actions :actions="[$this->sendAction()]" />
            </div>
        </div>

        <div>
            {{ $this->table }}
        </div>

        <div>
            {{ $this->form }}

            <div class="mt-4">
                <x-filament-panels::form.actions :actions="[$this->saveSettingsAction()]" />
            </div>
        </div>
    </div>

    <style>
        .nh-settings {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: min(100%, 68rem);
        }
    </style>
</x-filament-panels::page>
