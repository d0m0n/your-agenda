@props(['label' => 'メインビジュアル画像'])

{{-- 製品スクリーンショットではなく、写真やイラストのメインビジュアルを
     差し込む想定のプレースホルダー。ブラウザ枠は付けない。 --}}
<div {{ $attributes->merge(['class' => 'relative rounded-2xl overflow-hidden shadow-2xl border border-paper-200 dark:border-ink-700 bg-paper-50 dark:bg-ink-800 aspect-[4/3] flex items-center justify-center bg-[repeating-linear-gradient(135deg,transparent,transparent_10px,rgba(0,0,0,0.03)_10px,rgba(0,0,0,0.03)_11px)] dark:bg-[repeating-linear-gradient(135deg,transparent,transparent_10px,rgba(255,255,255,0.03)_10px,rgba(255,255,255,0.03)_11px)]']) }}>
    <div class="flex flex-col items-center gap-3 px-6 text-center">
        <svg viewBox="0 0 24 24" class="h-10 w-10 text-ink-400 dark:text-paper-100/40" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="4" width="18" height="16" rx="1.5" />
            <circle cx="9" cy="10" r="1.8" />
            <path d="M21 16l-5.5-5.5a1.5 1.5 0 00-2.1 0L4 19" />
        </svg>
        <p class="text-xs text-ink-400 dark:text-paper-100/40 tracking-wide">{{ $label }}</p>
    </div>
</div>
