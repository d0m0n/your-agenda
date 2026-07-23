@props(['label' => 'スクリーンショット', 'url' => 'your-agenda.example/dashboard'])

{{-- 実画面のスクリーンショットに差し替える想定のプレースホルダー。
     ブラウザ風のフレームに入れ、実在する製品であることを印象づける。 --}}
<div {{ $attributes->merge(['class' => 'rounded-xl overflow-hidden shadow-2xl border border-paper-200 dark:border-ink-700 bg-paper-50 dark:bg-ink-800']) }}>
    <div class="flex items-center gap-2 px-4 py-3 bg-paper-200/60 dark:bg-ink-900/60 border-b border-paper-200 dark:border-ink-700">
        <span class="h-2.5 w-2.5 rounded-full bg-leather-300/70"></span>
        <span class="h-2.5 w-2.5 rounded-full bg-brass-300/70"></span>
        <span class="h-2.5 w-2.5 rounded-full bg-ink-400/40 dark:bg-paper-100/30"></span>
        <span class="ml-3 truncate text-[11px] text-ink-400 dark:text-ink-200 tracking-wide">{{ $url }}</span>
    </div>
    <div class="aspect-[16/10] flex items-center justify-center bg-[repeating-linear-gradient(135deg,transparent,transparent_10px,rgba(0,0,0,0.03)_10px,rgba(0,0,0,0.03)_11px)] dark:bg-[repeating-linear-gradient(135deg,transparent,transparent_10px,rgba(255,255,255,0.03)_10px,rgba(255,255,255,0.03)_11px)]">
        <div class="flex flex-col items-center gap-2 px-6 text-center">
            <svg viewBox="0 0 24 24" class="h-8 w-8 text-ink-400 dark:text-paper-100/40" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="14" rx="1.5" />
                <path d="M3 8h18" />
                <path d="M8 21h8M12 18v3" />
            </svg>
            <p class="text-xs text-ink-400 dark:text-paper-100/40 tracking-wide">{{ $label }}</p>
        </div>
    </div>
</div>
