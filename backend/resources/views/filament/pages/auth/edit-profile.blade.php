<x-filament-panels::page>
    <div class="nh-profile">
        <x-filament-panels::header
            heading="Profil"
            subheading="Photo, nom et mot de passe de votre compte."
        />

        <x-filament-panels::form id="form" wire:submit="save">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    </div>

    <style>
        .nh-profile {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: min(100%, 46rem);
        }

        .nh-profile .fi-header-heading {
            font-size: 1.65rem;
        }

        .nh-profile .fi-header-subheading {
            font-size: 0.95rem;
            line-height: 1.45;
        }

        .nh-profile .fi-fo-file-upload .filepond--drop-label {
            font-size: 0.75rem;
            line-height: 1.2;
        }

        .nh-profile .fi-fo-field-wrp-helper-text {
            max-width: 9rem;
        }
    </style>
</x-filament-panels::page>
