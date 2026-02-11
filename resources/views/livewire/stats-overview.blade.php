<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach(($stats ?? []) as $stat)
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-300">{{ $stat['label'] }}</div>
                    <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stat['value'] }}</div>
                </div>
            </div>
            @if(!empty($stat['desc']))
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $stat['desc'] }}</div>
            @endif
        </div>
    @endforeach
</div>
