<div x-show="uploading" x-cloak class="mt-2">
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-150" :style="`width: ${percent}%`"></div>
    </div>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="`アップロード中… ${percent}%`"></p>
</div>
