@props(['label' => 'メインビジュアル画像'])

{{-- 製品スクリーンショットではなく、写真やイラストのメインビジュアルを
     差し込む想定のプレースホルダー。ブラウザ枠は付けない。アイコンを
     領域いっぱいに表示し、実画像に差し替える際のサイズ感が伝わるように
     している(差し替え時はsvg/pをimg(class="h-full w-full object-cover")
     に置き換える)。 --}}
<div {{ $attributes->merge(['class' => 'relative rounded-2xl overflow-hidden shadow-2xl border border-paper-200 dark:border-ink-700 bg-paper-50 dark:bg-ink-800 aspect-[4/3] bg-[repeating-linear-gradient(135deg,transparent,transparent_10px,rgba(0,0,0,0.03)_10px,rgba(0,0,0,0.03)_11px)] dark:bg-[repeating-linear-gradient(135deg,transparent,transparent_10px,rgba(255,255,255,0.03)_10px,rgba(255,255,255,0.03)_11px)]']) }}>
    <svg viewBox="0 0 24 24" class="absolute inset-0 h-full w-full p-10 sm:p-14 lg:p-16 text-ink-200 dark:text-paper-100/15" fill="none" stroke="currentColor" stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="3" y="4" width="18" height="16" rx="1.5" />
        <circle cx="9" cy="10" r="1.8" />
        <path d="M21 16l-5.5-5.5a1.5 1.5 0 00-2.1 0L4 19" />
    </svg>
    <p class="absolute bottom-5 left-1/2 -translate-x-1/2 rounded-full bg-paper-50/80 dark:bg-ink-900/60 px-4 py-1.5 text-xs text-ink-400 dark:text-paper-100/50 tracking-wide backdrop-blur-sm">{{ $label }}</p>
</div>
