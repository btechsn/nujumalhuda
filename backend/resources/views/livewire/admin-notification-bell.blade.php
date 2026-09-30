<div wire:poll.30s class="flex items-center">
    <x-filament::dropdown placement="bottom-end" teleport width="sm" max-height="24rem">
        <x-slot name="trigger">
            <x-filament::icon-button
                :badge="$unreadCount > 0 ? ($unreadCount > 9 ? '9+' : $unreadCount) : null"
                color="gray"
                icon="heroicon-o-bell"
                icon-size="lg"
                label="Notifications"
                class="fi-topbar-database-notifications-btn"
            />
        </x-slot>

        <x-filament::dropdown.header>
            <span class="flex w-full items-center gap-3">
                <span class="font-semibold">Notifications</span>
                @if ($unreadCount > 0)
                    <button
                        type="button"
                        wire:click="markAllAsRead"
                        class="ms-auto shrink-0 text-xs font-medium text-primary-600 hover:underline dark:text-primary-400"
                    >
                        Tout lire
                    </button>
                @endif
            </span>
        </x-filament::dropdown.header>

        @forelse ($notifications as $notification)
            <button
                type="button"
                wire:click="open('{{ $notification->id }}')"
                @class([
                    'flex w-full flex-col gap-0.5 px-3 py-2.5 text-start transition hover:bg-gray-50 dark:hover:bg-white/5',
                    'bg-primary-50/60 dark:bg-primary-400/5' => $notification->read_at === null,
                ])
            >
                <span class="flex items-center gap-2">
                    @if ($notification->read_at === null)
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-primary-500"></span>
                    @endif
                    <span class="truncate text-sm font-medium text-gray-950 dark:text-white">
                        {{ $notification->title }}
                    </span>
                </span>
                <span class="line-clamp-2 text-xs text-gray-600 dark:text-gray-400">
                    {{ $notification->message }}
                </span>
                <span class="text-[11px] text-gray-400 dark:text-gray-500">
                    {{ $notification->created_at?->diffForHumans() }}
                </span>
            </button>
        @empty
            <p class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                Aucune notification
            </p>
        @endforelse
    </x-filament::dropdown>
</div>
