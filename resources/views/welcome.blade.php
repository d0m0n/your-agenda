<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('layouts.theme-init')

        <title>{{ config('app.name') }} | 議題はあなた次第。委員会運営がもっと楽になる</title>
        <meta name="description" content="会議・次第・メンバー・資料をひとつの場所で。委員会運営を、毎月タバコ1箱分の負担で楽にするクラウドサービス「あなた次第」。">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-paper-100 dark:bg-night text-ink-800 dark:text-paper-100">

        {{-- ヘッダー --}}
        <header class="sticky top-0 z-40 bg-paper-100/90 dark:bg-night/90 backdrop-blur border-b border-paper-200 dark:border-ink-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ url('/lp') }}" class="flex items-center gap-2">
                    <x-brand-mark class="h-7 w-7 text-leather-400 shrink-0" />
                    <span class="font-serif font-semibold text-lg text-ink-800 dark:text-paper-100">あなた次第</span>
                </a>

                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-ink-600 dark:text-paper-100/70">
                    <a href="#features" class="hover:text-leather-500 dark:hover:text-leather-300 transition-colors">機能</a>
                    <a href="#pricing" class="hover:text-leather-500 dark:hover:text-leather-300 transition-colors">料金</a>
                    <a href="#faq" class="hover:text-leather-500 dark:hover:text-leather-300 transition-colors">よくある質問</a>
                </nav>

                <div class="flex items-center gap-3">
                    <x-theme-toggle />
                    <a href="{{ route('login') }}" class="hidden sm:inline-block text-sm font-medium text-ink-600 dark:text-paper-100/70 hover:text-leather-500 dark:hover:text-leather-300 transition-colors">
                        ログイン
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-leather-500 hover:bg-leather-600 text-white text-sm font-semibold shadow-sm transition-colors">
                        無料ではじめる
                    </a>
                </div>
            </div>
        </header>

        {{-- ヒーロー --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-brass-400/10 blur-3xl"></div>
            <div class="pointer-events-none absolute top-40 -left-24 h-72 w-72 rounded-full bg-leather-400/10 blur-3xl"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 lg:pt-24 lg:pb-28">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    <div>
                        <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase">
                            委員会運営を、もっと軽やかに
                        </p>
                        <h1 class="mt-4 font-serif text-4xl sm:text-5xl font-bold leading-tight text-ink-800 dark:text-paper-100">
                            議題を決めるのも、<br>進めるのも――<br>
                            <span class="text-leather-500 dark:text-leather-300">あなた次第</span>です。
                        </h1>
                        <p class="mt-6 text-base leading-relaxed text-ink-600 dark:text-paper-100/70 max-w-lg">
                            会議・次第・メンバー・資料が、Excelやメール、LINEに散らばっていませんか。
                            「あなた次第」なら、ぜんぶひとつの場所にまとまります。
                            ご負担は、毎月タバコ1箱分程度から。
                        </p>
                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 rounded-md bg-leather-500 hover:bg-leather-600 text-white font-semibold shadow-md transition-colors">
                                無料ではじめる
                            </a>
                            <a href="#features" class="inline-flex items-center px-6 py-3 rounded-md border border-paper-200 dark:border-ink-600 text-ink-700 dark:text-paper-100 font-medium hover:bg-paper-50 dark:hover:bg-ink-800 transition-colors">
                                機能を見る
                            </a>
                        </div>
                        <p class="mt-4 text-xs text-ink-400 dark:text-paper-100/40">
                            クレジットカード登録は不要です。まずは組織名・お名前・メールアドレスだけでお試しいただけます。
                        </p>
                    </div>

                    <div class="lg:pl-6">
                        <x-image-placeholder label="メインビジュアル画像" />
                    </div>
                </div>
            </div>
        </section>

        {{-- 課題提起 --}}
        <section class="bg-paper-50 dark:bg-ink-900/40 border-y border-paper-200 dark:border-ink-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
                <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase text-center">
                    こんなお悩みありませんか
                </p>
                <h2 class="mt-3 font-serif text-2xl sm:text-3xl font-bold text-center text-ink-800 dark:text-paper-100">
                    委員会運営の「面倒」、放置していませんか
                </h2>

                <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ([
                        ['title' => '次第がバラバラ', 'body' => 'Excel・Word・紙のプリントが混在し、最新版がどれか分からなくなる。', 'icon' => 'scatter'],
                        ['title' => '議案ファイルの共有が面倒', 'body' => 'Zipファイルをメールで送っても開けない、容量が大きくて送れない。', 'icon' => 'file'],
                        ['title' => '引き継ぎで情報が消える', 'body' => '役員が交代するたびに、過去の次第やメンバー名簿が引き継がれない。', 'icon' => 'handoff'],
                        ['title' => '誕生日や連絡先が分からない', 'body' => 'メンバーの情報が個人のスマホや紙の名簿にしかなく、共有されていない。', 'icon' => 'members'],
                    ] as $pain)
                        <div class="bg-paper-50 dark:bg-ink-800 border border-paper-200 dark:border-ink-700 rounded-lg p-6">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-leather-50 dark:bg-leather-700/20 text-leather-500 dark:text-leather-300">
                                @if ($pain['icon'] === 'scatter')
                                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="6" cy="7" r="1.6" /><circle cx="17" cy="6" r="1.6" /><circle cx="12" cy="13" r="1.6" /><circle cx="18" cy="17" r="1.6" /><circle cx="6" cy="17" r="1.6" />
                                    </svg>
                                @elseif ($pain['icon'] === 'file')
                                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M7 3h7l4 4v14H7z" /><path d="M14 3v4h4" /><path d="M9.5 13.5l2 2 3-3.5" />
                                    </svg>
                                @elseif ($pain['icon'] === 'handoff')
                                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M4 12h11M11 8l4 4-4 4" /><path d="M20 6v12" stroke-dasharray="2 2" />
                                    </svg>
                                @else
                                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="9" cy="8" r="3" /><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6" /><circle cx="18" cy="9" r="2.3" /><path d="M15.5 20c.3-2.6 2.2-4.6 4.5-4.9" />
                                    </svg>
                                @endif
                            </span>
                            <h3 class="mt-4 font-semibold text-sm text-ink-800 dark:text-paper-100">{{ $pain['title'] }}</h3>
                            <p class="mt-2 text-sm text-ink-600 dark:text-paper-100/60 leading-relaxed">{{ $pain['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- 機能紹介 --}}
        <section id="features" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 space-y-24 lg:space-y-32">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase">Features</p>
                <h2 class="mt-3 font-serif text-2xl sm:text-3xl font-bold text-ink-800 dark:text-paper-100">
                    実際の画面で見る、「あなた次第」でできること
                </h2>
            </div>

            {{-- 機能1: 次第管理 --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1">
                    <x-screenshot-placeholder label="次第(会議)画面のスクリーンショット" url="your-agenda.example/meetings/1" />
                </div>
                <div class="order-1 lg:order-2">
                    <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase">次第管理</p>
                    <h3 class="mt-2 font-serif text-2xl font-bold text-ink-800 dark:text-paper-100">紙の次第そのままの、迷わない画面</h3>
                    <p class="mt-4 text-sm leading-relaxed text-ink-600 dark:text-paper-100/70">
                        会議名・開催日時・開催場所・Wi-Fi情報・議題・担当者を1枚にまとめて表示。
                        議案データはZip・PDF・画像をアップロードするだけで、URLひとつで開けるようになります。
                        印刷してそのまま配布することもできます。
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600 dark:text-paper-100/70">
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>過去の次第を選んでそのままコピーできる</li>
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>子項目(サブ議題)にも対応</li>
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>印刷用レイアウトをボタンひとつで表示</li>
                    </ul>
                </div>
            </div>

            {{-- 機能2: ダッシュボード --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase">ダッシュボード</p>
                    <h3 class="mt-2 font-serif text-2xl font-bold text-ink-800 dark:text-paper-100">開いた瞬間に、今日やることが分かる</h3>
                    <p class="mt-4 text-sm leading-relaxed text-ink-600 dark:text-paper-100/70">
                        今後の会議予定・Googleカレンダー・今月の誕生日・資料置き場を1画面に。
                        本日が誕生日のメンバーはひと目で分かるようにハイライト表示されます。
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600 dark:text-paper-100/70">
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>ペインの表示・非表示は組織ごとに設定可能</li>
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>お使いのGoogleカレンダーをそのまま埋め込み表示</li>
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>今後も便利な新機能を追加予定</li>
                    </ul>
                </div>
                <div>
                    <x-screenshot-placeholder label="ダッシュボード画面のスクリーンショット" url="your-agenda.example/dashboard" />
                </div>
            </div>

            {{-- 機能3: メンバー管理 --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1">
                    <x-screenshot-placeholder label="メンバープロフィール画面のスクリーンショット" url="your-agenda.example/members/1" />
                </div>
                <div class="order-1 lg:order-2">
                    <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase">メンバー管理</p>
                    <h3 class="mt-2 font-serif text-2xl font-bold text-ink-800 dark:text-paper-100">名刺のように、ひと目で分かるプロフィール</h3>
                    <p class="mt-4 text-sm leading-relaxed text-ink-600 dark:text-paper-100/70">
                        役職・所属企業・連絡先・SNSまで、メンバーごとに1枚のカードにまとまります。
                        CSVでの一括登録・エクスポートにも対応しているので、既存の名簿からすぐに移行できます。
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600 dark:text-paper-100/70">
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>一覧は表形式・カード形式を切り替え可能</li>
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>役職マスタで組織図を管理</li>
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>誕生日を自動抽出してダッシュボードに表示</li>
                    </ul>
                </div>
            </div>

            {{-- 機能4: 資料・議案ファイル共有 --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase">資料・議案ファイル共有</p>
                    <h3 class="mt-2 font-serif text-2xl font-bold text-ink-800 dark:text-paper-100">URLひとつで、議案も資料も共有完了</h3>
                    <p class="mt-4 text-sm leading-relaxed text-ink-600 dark:text-paper-100/70">
                        Zip・PDF・画像をアップロードするだけで、公開用URLが発行されます。
                        メールやLINEにURLを貼るだけで、メンバー全員が同じ議案・資料を開けます。
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600 dark:text-paper-100/70">
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>組織内で共有できる「資料置き場」も完備</li>
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>アップロード済みファイルは中身だけ差し替え可能</li>
                        <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">・</span>解約時は一括ダウンロードでデータを持ち出せる</li>
                    </ul>
                </div>
                <div>
                    <x-screenshot-placeholder label="資料置き場画面のスクリーンショット" url="your-agenda.example/materials" />
                </div>
            </div>
        </section>

        {{-- 選ばれる理由 --}}
        <section class="bg-paper-50 dark:bg-ink-900/40 border-y border-paper-200 dark:border-ink-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
                <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase text-center">Why Your agenda</p>
                <h2 class="mt-3 font-serif text-2xl sm:text-3xl font-bold text-center text-ink-800 dark:text-paper-100">
                    選ばれる理由
                </h2>

                <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach ([
                        ['title' => '組織ごとにデータを完全分離', 'body' => '他の委員会・組織のデータには一切アクセスできない設計です。'],
                        ['title' => '役員が交代しても引き継げる', 'body' => '次第も名簿も資料も、組織のアカウントに残り続けます。'],
                        ['title' => '印刷しても読みやすい', 'body' => '紙の次第そのままのレイアウトで、印刷用ボタンもワンクリック。'],
                        ['title' => 'ダーク/ライト表示に対応', 'body' => '会議室でも屋外でも、見やすい表示に切り替えられます。'],
                    ] as $reason)
                        <div>
                            <div class="h-1 w-8 bg-brass-400 rounded-full"></div>
                            <h3 class="mt-4 font-semibold text-sm text-ink-800 dark:text-paper-100">{{ $reason['title'] }}</h3>
                            <p class="mt-2 text-sm text-ink-600 dark:text-paper-100/60 leading-relaxed">{{ $reason['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- 料金 --}}
        <section id="pricing" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 text-center">
            <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase">Pricing</p>
            <h2 class="mt-3 font-serif text-2xl sm:text-3xl font-bold text-ink-800 dark:text-paper-100">
                毎月、タバコ1箱分。
            </h2>
            <p class="mt-3 text-sm text-ink-600 dark:text-paper-100/70">
                それだけで、委員会全体の運営が楽になります。
            </p>

            <div class="mt-10 bg-paper-50 dark:bg-ink-800 border border-paper-200 dark:border-ink-700 rounded-xl shadow-md p-8 sm:p-10">
                <p class="text-xs text-ink-400 dark:text-paper-100/40">1組織あたり</p>
                <p class="mt-2 font-serif text-4xl font-bold text-ink-800 dark:text-paper-100">
                    &yen; <span class="text-leather-500 dark:text-leather-300">--,---</span> <span class="text-base font-normal text-ink-500 dark:text-paper-100/60">/ 月(税別・価格は現在調整中です)</span>
                </p>
                <ul class="mt-6 text-sm text-ink-600 dark:text-paper-100/70 space-y-2 text-left max-w-sm mx-auto">
                    <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">✓</span>メンバー・会議・次第の管理を無制限に利用</li>
                    <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">✓</span>一般ユーザー1名あたり2GBのデータ容量</li>
                    <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">✓</span>閲覧専用のオブザーブユーザーを追加可能</li>
                    <li class="flex items-start gap-2"><span class="text-leather-500 dark:text-leather-300 mt-0.5">✓</span>解約時のデータ一括ダウンロードに対応</li>
                </ul>
                <a href="{{ route('register') }}" class="mt-8 inline-flex items-center px-6 py-3 rounded-md bg-leather-500 hover:bg-leather-600 text-white font-semibold shadow-md transition-colors">
                    無料ではじめる
                </a>
            </div>
        </section>

        {{-- 導入の流れ --}}
        <section class="bg-paper-50 dark:bg-ink-900/40 border-y border-paper-200 dark:border-ink-700">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
                <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase text-center">Getting Started</p>
                <h2 class="mt-3 font-serif text-2xl sm:text-3xl font-bold text-center text-ink-800 dark:text-paper-100">
                    導入の流れ
                </h2>

                <ol class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach ([
                        ['title' => '無料登録', 'body' => '組織名・お名前・メールアドレスを入力するだけ。'],
                        ['title' => '組織情報を設定', 'body' => 'ヘッダー画像やGoogleカレンダーIDを設定。'],
                        ['title' => 'メンバーを登録', 'body' => 'CSV一括登録にも対応しています。'],
                        ['title' => '会議・次第を作成', 'body' => 'URLひとつで議案をメンバーに共有。'],
                    ] as $index => $step)
                        <li>
                            <span class="font-serif text-2xl text-leather-500 dark:text-leather-300">{{ sprintf('%02d', $index + 1) }}</span>
                            <h3 class="mt-2 font-semibold text-sm text-ink-800 dark:text-paper-100">{{ $step['title'] }}</h3>
                            <p class="mt-1 text-sm text-ink-600 dark:text-paper-100/60 leading-relaxed">{{ $step['body'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- FAQ --}}
        <section id="faq" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
            <p class="text-xs font-medium tracking-[0.2em] text-leather-500 dark:text-leather-300 uppercase text-center">FAQ</p>
            <h2 class="mt-3 font-serif text-2xl sm:text-3xl font-bold text-center text-ink-800 dark:text-paper-100">
                よくある質問
            </h2>

            <div class="mt-10 space-y-3">
                @foreach ([
                    ['q' => '複数の委員会や部会でも使えますか?', 'a' => '組織単位でのご契約となります。委員会・部会ごとにご利用いただく場合は、それぞれ組織登録をお願いしております。'],
                    ['q' => 'セキュリティは大丈夫ですか?', 'a' => '組織ごとにデータを完全に分離しており、他組織のデータには一切アクセスできない設計になっています。'],
                    ['q' => '解約したらデータはどうなりますか?', 'a' => '解約前に、その年度に作成した次第・議案を一括でダウンロードいただけます。データを手元に残したまま解約できます。'],
                    ['q' => '導入にどれくらい時間がかかりますか?', 'a' => '組織登録は数分で完了します。メンバー登録もCSV一括登録に対応しているので、既存の名簿からすぐに移行できます。'],
                    ['q' => 'パソコンが苦手でも使えますか?', 'a' => '紙の次第や名刺に近い見た目にしているので、普段の資料と同じ感覚でご利用いただけます。'],
                ] as $item)
                    <details class="group bg-paper-50 dark:bg-ink-800 border border-paper-200 dark:border-ink-700 rounded-lg px-5 py-4">
                        <summary class="flex items-center justify-between cursor-pointer list-none font-medium text-sm text-ink-800 dark:text-paper-100">
                            {{ $item['q'] }}
                            <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0 text-leather-500 dark:text-leather-300 transition-transform group-open:rotate-45" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                        </summary>
                        <p class="mt-3 text-sm text-ink-600 dark:text-paper-100/70 leading-relaxed">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </section>

        {{-- クロージングCTA --}}
        <section class="relative overflow-hidden bg-ink-900">
            <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-brass-400/15 blur-3xl"></div>
            <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                <p class="font-serif text-3xl sm:text-4xl font-bold text-paper-100">
                    すべては、<br class="sm:hidden">あなた次第。
                </p>
                <p class="mt-4 text-sm text-paper-100/60">
                    まずは無料で、組織の次第をひとつにまとめてみませんか。
                </p>
                <a href="{{ route('register') }}" class="mt-8 inline-flex items-center px-8 py-3 rounded-md bg-brass-400 hover:bg-brass-300 text-ink-900 font-semibold shadow-md transition-colors">
                    無料ではじめる
                </a>
            </div>
        </section>

        {{-- フッター --}}
        <footer class="bg-paper-100 dark:bg-night">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ url('/lp') }}" class="flex items-center gap-2">
                    <x-brand-mark class="h-6 w-6 text-leather-400" />
                    <span class="font-serif font-semibold text-ink-800 dark:text-paper-100">あなた次第</span>
                </a>
                <div class="flex items-center gap-6 text-sm text-ink-500 dark:text-paper-100/50">
                    <a href="{{ route('login') }}" class="hover:text-leather-500 dark:hover:text-leather-300 transition-colors">ログイン</a>
                    <a href="mailto:info@example.com" class="hover:text-leather-500 dark:hover:text-leather-300 transition-colors">お問い合わせ</a>
                </div>
                <p class="text-xs text-ink-400 dark:text-paper-100/40">&copy; {{ date('Y') }} あなた次第</p>
            </div>
        </footer>
    </body>
</html>
