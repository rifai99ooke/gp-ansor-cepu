<x-filament-panels::page>
    <style>
        .gp-settings-form {
            display: grid;
            gap: 1.5rem;
        }

        .gp-settings-form .fi-section {
            background: transparent !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            overflow: visible;
        }

        .gp-settings-form .fi-section-header {
            background: transparent !important;
            border-bottom: 0 !important;
            padding-bottom: 0.75rem;
        }

        .gp-settings-logo-upload,
        .gp-settings-favicon-upload {
            max-width: 20rem;
        }

        .gp-settings-save-bar {
            position: sticky;
            bottom: 1rem;
            z-index: 20;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem;
            border: 1px solid var(--gp-emerald-border);
            border-radius: 0.75rem;
            background: #fff;
            box-shadow: 0 2px 12px rgb(0 0 0 / 8%);
        }

        .gp-settings-save-status {
            color: #4b5563;
            font-size: 0.875rem;
        }

        .gp-settings-save-status[data-changed='true'] {
            color: #92400e;
            font-weight: 600;
        }

        @media (max-width: 639px) {
            .gp-settings-save-bar > button {
                width: 100%;
            }
        }
    </style>

    <form
        wire:submit="save"
        class="gp-settings-form"
        x-data="{
            get hasChanges() {
                return JSON.stringify($wire.data) !== JSON.stringify($wire.savedData);
            }
        }"
    >
        {{ $this->form }}

        <div class="gp-settings-save-bar">
            <p
                class="gp-settings-save-status"
                role="status"
                aria-live="polite"
                :data-changed="hasChanges"
                x-text="hasChanges ? 'Ada perubahan belum disimpan.' : 'Semua perubahan tersimpan.'"
            ></p>

            <x-filament::button type="submit" form="save" wire:target="save">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
