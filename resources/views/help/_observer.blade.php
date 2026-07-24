@php
    $sections = [
        [
            'id' => 'overview',
            'title' => 'はじめに',
            'body' => '<p>オブザーブユーザーは、一般ユーザーが所属組織の会議・メンバー・資料を閲覧するために発行する専用アカウントです。登録・編集・削除などの操作は一般ユーザーのみが行えます。</p>',
        ],
        [
            'id' => 'dashboard',
            'title' => 'ダッシュボード',
            'body' => '<p>ログイン後の最初の画面です。今後の会議予定・カレンダー・今月の誕生日のメンバー・資料置き場を確認できます(表示内容は組織の一般ユーザーが設定しています)。</p>',
        ],
        [
            'id' => 'meetings',
            'title' => '会議一覧・次第の閲覧',
            'body' => '<p>ナビゲーションの「会議一覧」から、組織の会議とその次第を確認できます。次第の議題にリンクされた議案ファイルや資料も、クリックして中身を確認できます。会議の新規登録・編集・削除は行えません。</p>',
        ],
        [
            'id' => 'members',
            'title' => 'メンバー一覧の閲覧',
            'body' => '<p>ナビゲーションの「メンバー一覧」から、組織のメンバー情報(役職・所属・連絡先など)を確認できます。メンバーの行をクリックすると、名刺のようなプロフィール画面が開きます。登録・編集・削除、CSVの入出力は行えません。</p>',
        ],
        [
            'id' => 'materials',
            'title' => '資料の閲覧',
            'body' => '<p>ダッシュボードや会議の次第からリンクされている資料をダウンロードして確認できます。資料のアップロード・削除は行えません。</p>',
        ],
        [
            'id' => 'not-available',
            'title' => 'オブザーブユーザーができないこと',
            'body' => '<p>以下の操作は一般ユーザーのみ行えます。オブザーブユーザーの画面には、これらのメニューは表示されません。</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>会議・次第・議案ファイルの登録・編集・削除</li>
                    <li>メンバー・役職・部署の登録・編集・削除、CSVの入出力</li>
                    <li>資料のアップロード・削除</li>
                    <li>基本設定の変更、オブザーブユーザーの追加・削除</li>
                    <li>案内文の作成・編集</li>
                    <li>お支払い情報の登録・変更</li>
                </ul>
                <p>ご不明な点や、閲覧したい情報が見つからない場合は、組織の一般ユーザーにご確認いただくか、下記のお問い合わせからご連絡ください。</p>',
        ],
        [
            'id' => 'contact',
            'title' => 'お問い合わせ',
            'body' => '<p>ナビゲーション右側の吹き出しアイコンから、お問い合わせ・不具合報告・機能追加のご要望を送信できます。</p>',
        ],
    ];
@endphp

<div class="bg-paper-50 dark:bg-ink-800 border border-paper-200 dark:border-ink-700 rounded-lg p-6">
    <p class="text-sm text-ink-600 dark:text-paper-100/70 leading-relaxed">
        {{ __('オブザーブユーザー(閲覧専用アカウント)向けに、各画面の使い方をまとめています。目次から知りたい項目に移動できます。') }}
    </p>
    <nav class="mt-4 flex flex-wrap gap-x-4 gap-y-1.5 text-sm">
        @foreach ($sections as $section)
            <a href="#{{ $section['id'] }}" class="text-leather-500 dark:text-leather-300 hover:underline">{{ $section['title'] }}</a>
        @endforeach
    </nav>
</div>

@foreach ($sections as $section)
    <section id="{{ $section['id'] }}" class="scroll-mt-20 bg-paper-50 dark:bg-ink-800 border border-paper-200 dark:border-ink-700 rounded-lg p-6">
        <h3 class="font-serif text-lg font-semibold text-ink-800 dark:text-paper-100">{{ $section['title'] }}</h3>
        <div class="mt-3 text-sm text-ink-600 dark:text-paper-100/70 leading-relaxed space-y-2">
            {!! $section['body'] !!}
        </div>
    </section>
@endforeach
