<x-filament-widgets::widget>
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-orange-600" />
                <div>
                    <h3 class="text-sm font-medium text-orange-800">
                        Режим имперсонации
                    </h3>
                    <p class="text-sm text-orange-700">
                        Вы вошли под пользователем: <strong>{{ $currentUser->name }}</strong> ({{ $currentUser->email }})
                    </p>
                </div>
            </div>
            <div class="flex space-x-2">
                <a
                    href="{{ route('admin.stop-impersonating') }}"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                >
                    <x-heroicon-o-arrow-left-on-rectangle class="h-4 w-4 mr-2" />
                    Выйти из аккаунта
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
