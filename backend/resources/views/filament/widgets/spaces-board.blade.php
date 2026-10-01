<x-filament-widgets::widget>
    <x-filament::section
        heading="Les espaces"
        description="Chaque partie de l’institut, avec ses chiffres du moment."
    >
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($areas as $area)
                <a
                    href="{{ $area['url'] ?? '#' }}"
                    class="block rounded-xl bg-gray-50 p-4 ring-1 ring-gray-950/5 transition hover:bg-white dark:bg-white/5 dark:ring-white/10 dark:hover:bg-white/10"
                >
                    <div class="flex items-center gap-2">
                        <x-filament::icon
                            :icon="$area['icon']"
                            class="h-5 w-5 text-primary-600 dark:text-primary-400"
                        />
                        <span class="font-semibold text-gray-950 dark:text-white">
                            {{ $area['title'] }}
                        </span>
                    </div>

                    <dl class="mt-3 space-y-1.5">
                        @foreach ($area['items'] as $item)
                            <div class="flex items-baseline justify-between gap-3 text-sm">
                                <dt class="text-gray-500 dark:text-gray-400">{{ $item['label'] }}</dt>
                                <dd class="font-medium text-gray-950 dark:text-white">{{ $item['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
