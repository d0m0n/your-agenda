<x-app-layout>
    <x-slot name="title">{{ $meeting->organization->name }}の次第 | {{ $meeting->name }}の案内文</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-xl font-semibold text-ink-800 dark:text-paper-100 leading-tight">
                {{ __('PDF案内文') }}: {{ $meeting->name }}
            </h2>
            <div class="print-hidden flex items-center gap-4 text-sm">
                <button type="button" onclick="window.print()" class="text-leather-500 dark:text-leather-300 hover:underline">
                    {{ __('印刷 / PDF保存') }}
                </button>
                <a href="{{ route('meetings.invitation.edit', $meeting) }}" class="text-gray-500 dark:text-gray-400 hover:underline">
                    {{ __('編集に戻る') }}
                </a>
            </div>
        </div>
    </x-slot>

    @php
        // 公式文書らしい体裁に整えるための軽い整形。本文の文字自体は
        // 一切変更せず、行ごとに「記」「敬具」「以上」など定型句や、
        // 行頭の ">>" マーカー(差出人情報など任意の行を右揃えにしたい
        // ときに使う)を検出して配置だけを調整する(該当しない行はそのまま)。
        // 行全体が「[改ページ]」の場合は、その行自体は表示せず、そこで
        // 強制的にページを分ける(複数日程・複数プログラムの長い案内文向け)。
        $lines = explode("\n", $body);
        $centeredLines = ['記'];
        $rightAlignedLines = ['敬具', '以上'];
        $pageBreakMarker = '[改ページ]';
        $isTitleLine = fn (string $line) => str_ends_with($line, 'ご案内');
        $isHeadingLine = fn (string $line) => str_starts_with($line, '【') && str_ends_with($line, '】');
    @endphp

    <div class="py-12 print:py-0">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 print:max-w-none print:mx-0 print:px-0">
            <div class="print-sheet bg-paper-50 dark:bg-ink-800 shadow-md sm:rounded-lg overflow-hidden border border-paper-200 dark:border-ink-700 print:rounded-none px-8 py-10 sm:px-12">
                <p class="text-right text-sm text-ink-600 dark:text-paper-100/70 font-serif">{{ $issueDate }}</p>

                <div class="mt-4 text-sm leading-loose text-ink-800 dark:text-paper-100 font-serif">
                    @foreach ($lines as $line)
                        @php
                            $trimmed = trim($line);
                        @endphp
                        @if ($trimmed === $pageBreakMarker)
                            <div style="break-before: page; page-break-before: always;"></div>
                            @continue
                        @endif
                        @php
                            $isRightAlignMarked = str_starts_with($trimmed, '>>');
                            $displayLine = $isRightAlignMarked ? substr($trimmed, 2) : $line;
                            // 空行はテキストが一切ないdivになり、CSS上は高さ0に潰れて
                            // しまうため、幅を持たない改行スペースを入れて高さを確保する。
                            if ($displayLine === '') {
                                $displayLine = "\u{00A0}";
                            }
                            $isHeading = $trimmed === '記' || $isHeadingLine($trimmed) || ($trimmed !== '' && $isTitleLine($trimmed));
                            $alignClass = match (true) {
                                $isRightAlignMarked => 'text-right',
                                in_array($trimmed, $centeredLines, true) => 'text-center tracking-[0.4em]',
                                in_array($trimmed, $rightAlignedLines, true) => 'text-right',
                                $trimmed !== '' && $isTitleLine($trimmed) => 'text-center font-semibold text-base underline underline-offset-4',
                                default => '',
                            };
                            $breakClass = 'print-avoid-break'.($isHeading ? ' print-avoid-break-after' : '');
                        @endphp
                        <div class="whitespace-pre-wrap {{ $alignClass }} {{ $breakClass }}">{{ $displayLine }}</div>
                    @endforeach
                </div>

                @if ($mapQrCodeDataUri || $meeting->venue_address)
                    <div class="print-avoid-break mt-8 pt-6 border-t border-dashed border-paper-200 dark:border-ink-600 flex items-center gap-6">
                        @if ($mapQrCodeDataUri)
                            <img src="{{ $mapQrCodeDataUri }}" alt="{{ __('地図QRコード') }}" class="h-28 w-28 shrink-0">
                        @endif
                        <div class="text-sm text-ink-800 dark:text-paper-100">
                            @if ($meeting->venue_address)
                                <p class="font-medium">{{ $meeting->location }}</p>
                                <p class="text-ink-600 dark:text-paper-100/70">{{ $meeting->venue_address }}</p>
                            @endif
                            @if ($mapQrCodeDataUri)
                                <p class="mt-1 text-xs text-ink-400 dark:text-paper-100/40">{{ __('QRコードを読み取ると地図が開きます') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
