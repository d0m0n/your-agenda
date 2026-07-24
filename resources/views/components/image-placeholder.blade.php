@props(['label' => 'メインビジュアル画像', 'fill' => false])

{{-- 製品スクリーンショットではなく、写真やイラストのメインビジュアルを
     差し込む想定のプレースホルダー。ブラウザ枠は付けない。アイコンを
     領域いっぱいに表示し、実画像に差し替える際のサイズ感が伝わるように
     している(差し替え時はsvg/pをimg(class="h-full w-full object-cover")
     に置き換える)。fillを指定すると、カード枠(角丸・枠線・影・
     アスペクト比)を外し親要素いっぱいに敷き詰めるスタイルになる
     (ヒーローの全面背景として使う想定)。 --}}
<div {{ $attributes->merge(['class' => ($fill ? 'relative h-full w-full' : 'relative rounded-2xl shadow-2xl border border-paper-200 dark:border-ink-700 aspect-[4/3]').' overflow-hidden bg-paper-50 dark:bg-ink-800 bg-[repeating-linear-gradient(135deg,transparent,transparent_10px,rgba(0,0,0,0.03)_10px,rgba(0,0,0,0.03)_11px)] dark:bg-[repeating-linear-gradient(135deg,transparent,transparent_10px,rgba(255,255,255,0.03)_10px,rgba(255,255,255,0.03)_11px)]']) }}>
    <svg viewBox="0 0 24 24" class="absolute inset-0 h-full w-full p-10 sm:p-14 lg:p-16 text-ink-200 dark:text-paper-100/15" fill="none" stroke="currentColor" stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="3" y="4" width="18" height="16" rx="1.5" />
        <circle cx="9" cy="10" r="1.8" />
        <path d="M21 16l-5.5-5.5a1.5 1.5 0 00-2.1 0L4 19" />
    </svg>
    <p class="absolute {{ $fill ? 'top-5 right-5 bg-ink-900/60 text-paper-50' : 'bottom-5 left-1/2 -translate-x-1/2 bg-paper-50/80 dark:bg-ink-900/60 text-ink-400 dark:text-paper-100/50' }} rounded-full px-4 py-1.5 text-xs tracking-wide backdrop-blur-sm">{{ $label }}</p>
</div>
