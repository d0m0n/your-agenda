@php
    $sections = [
        [
            'id' => 'overview',
            'title' => 'はじめに',
            'body' => '<p>一般ユーザー(組織の管理権限を持つアカウント)向けに、各画面の使い方をまとめています。画面左上のロゴから、いつでもダッシュボードに戻れます。</p>',
        ],
        [
            'id' => 'dashboard',
            'title' => 'ダッシュボード',
            'body' => '<p>ログイン後の最初の画面です。次の4つのペインが表示されます(表示・非表示は基本設定から切り替えられます)。</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>今後の会議予定(開催日時が近い順に表示されます)</li>
                    <li>カレンダー(基本設定で登録したGoogleカレンダーが埋め込み表示されます)</li>
                    <li>今月の誕生日のメンバー(本日が誕生日のメンバーは強調表示されます)</li>
                    <li>その他の資料置き場(直近にアップロードされた資料)</li>
                </ul>',
        ],
        [
            'id' => 'meetings',
            'title' => '会議管理',
            'body' => '<p>ナビゲーションの「会議管理」から、会議の一覧・登録・編集ができます。</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>会議の詳細画面(次第)には、「会議情報編集」と「次第編集」という2つの編集画面へのリンクがあります</li>
                    <li><span class="font-medium text-ink-700 dark:text-paper-100">会議情報編集</span>: 会議名、開始・終了日時、開催場所(住所・地図URL)、懇親会・宿泊情報、Wi-Fi情報、メモ、会議ごとのヘッダー画像を設定します</li>
                    <li><span class="font-medium text-ink-700 dark:text-paper-100">次第編集</span>: 議案ファイル(Zip・PDF・画像)のアップロード、次第項目の追加・並び替え、担当者の割り当てができます。過去の会議の次第をコピーすることもできます</li>
                    <li>次第の画面は印刷ボタンで、白黒・A4基準の印刷用レイアウトに切り替わります</li>
                    <li>次第ごとに、ログイン不要で閲覧できる外部共有リンクを発行・再発行・無効化できます</li>
                    <li>会議一覧から、会議1件分の次第と議案ファイルをまとめてダウンロードできます</li>
                </ul>',
        ],
        [
            'id' => 'invitation',
            'title' => '案内文作成',
            'body' => '<p>会議ごとの「案内文作成」画面で、会議の情報からPDF・メール・LINE用の案内文を自動生成できます。</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>生成された文章は自由に手直しでき、保存すると次に開いたときも編集内容が残ります</li>
                    <li>「テンプレートに戻す」で、いつでも組み込みの文面に戻せます</li>
                    <li>PDF案内文では、行の先頭に「&gt;&gt;」を付けるとその行だけ右揃えになります(差出人情報などに便利です)</li>
                    <li>行に「[改ページ]」とだけ書くと、印刷時にそこで強制的にページが分かれます</li>
                    <li>基本設定画面では、組織共通のデフォルト文面を編集できます(会議ごとに手直しする前の下地になります)</li>
                </ul>',
        ],
        [
            'id' => 'positions-departments',
            'title' => '役職管理・部署管理',
            'body' => '<p>ナビゲーションの「役職管理」「部署管理」から、それぞれのマスタを登録・編集・削除できます。登録した役職・部署は、メンバー登録時に選んで紐付けられます。</p>',
        ],
        [
            'id' => 'members',
            'title' => 'メンバー管理',
            'body' => '<p>ナビゲーションの「メンバー管理」から、メンバーの一覧・登録・編集ができます。</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>必須項目は氏名のみで、その他の項目は任意です</li>
                    <li>CSVで複数名をまとめて登録・更新でき、テンプレートのダウンロードもできます</li>
                    <li>登録済みメンバーの一覧はCSVでエクスポートできます(写真・役職・部署はCSVの対象外です)</li>
                    <li>一覧は表形式・カード形式を切り替えられます</li>
                    <li>メンバーの行をクリックすると、名刺のようなプロフィール画面が開きます</li>
                </ul>',
        ],
        [
            'id' => 'materials',
            'title' => '資料管理',
            'body' => '<p>ナビゲーションの「資料管理」から、組織内で共有したい資料をアップロード・管理できます。アップロードした資料はダッシュボードや次第の議題からリンクでき、URLひとつでメンバーに共有できます。アップロード済みのファイルは、タイトルやリンク先を変えずに中身だけ差し替えることもできます。</p>',
        ],
        [
            'id' => 'settings',
            'title' => '基本設定',
            'body' => '<p>ナビゲーションの「基本設定」から、以下の設定ができます。</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>組織名、ヘッダー画像、アイコン画像</li>
                    <li>GoogleカレンダーID(ダッシュボードに埋め込み表示されます)</li>
                    <li>ダッシュボードの各ペインの表示・非表示</li>
                    <li>案内文のデフォルトテンプレート</li>
                    <li>オブザーブユーザーの追加・削除</li>
                    <li>次第の一括ダウンロード</li>
                    <li>データ使用量の確認</li>
                </ul>',
        ],
        [
            'id' => 'observers',
            'title' => 'オブザーブユーザーの管理',
            'body' => '<p>基本設定画面から、閲覧専用のオブザーブユーザーを追加できます。ログインID・パスワードを設定して発行し、後から変更・削除もできます。オブザーブユーザーは会議・次第・メンバー・資料の閲覧のみ行え、登録・編集・削除はできません。</p>',
        ],
        [
            'id' => 'billing',
            'title' => 'ご契約・お支払い',
            'body' => '<p>組織登録から14日間は、クレジットカードの登録なしですべての機能を無料でお試しいただけます。</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>お支払い情報の登録・変更はStripeのお支払いページで行います(カード情報は当サービスのサーバーを経由しません)</li>
                    <li>お試し期間の終了後にお支払い情報が未登録の場合、新規作成・編集などの操作はできなくなりますが、次第の閲覧と一括・個別ダウンロードは引き続き行えます</li>
                    <li>お支払いが必要な状態になると、画面上部に再度お支払いを行うための案内が表示されます</li>
                </ul>',
        ],
        [
            'id' => 'storage',
            'title' => 'データ容量について',
            'body' => '<p>アップロードできるデータ量には、登録する一般ユーザーごとに上限があります。上限に近づくと基本設定画面やナビゲーションに使用量が表示され、上限を超えるとそれ以上のアップロードができなくなります(登録済みのデータは引き続き利用できます)。</p>',
        ],
        [
            'id' => 'contact',
            'title' => 'お問い合わせ',
            'body' => '<p>ナビゲーション右側の吹き出しアイコンから、お問い合わせ・不具合報告・機能追加のご要望を送信できます。</p>',
        ],
    ];
@endphp

<div class="bg-paper-50 dark:bg-ink-800 border border-paper-200 dark:border-ink-700 rounded-lg overflow-hidden divide-y divide-paper-200 dark:divide-ink-700">
    <div class="px-6 py-6">
        <p class="text-sm text-ink-600 dark:text-paper-100/70 leading-relaxed">
            {{ __('一般ユーザー向けに、各画面の使い方をまとめています。目次から知りたい項目に移動できます。') }}
        </p>
        <nav class="mt-4 flex flex-wrap gap-x-4 gap-y-1.5 text-sm">
            @foreach ($sections as $section)
                <a href="#{{ $section['id'] }}" class="text-leather-500 dark:text-leather-300 hover:underline">{{ $section['title'] }}</a>
            @endforeach
        </nav>
    </div>

    @foreach ($sections as $section)
        <section id="{{ $section['id'] }}" class="scroll-mt-20 px-6 py-6">
            <h3 class="font-serif text-lg font-semibold text-ink-800 dark:text-paper-100">{{ $section['title'] }}</h3>
            <div class="mt-3 text-sm text-ink-600 dark:text-paper-100/70 leading-relaxed space-y-2">
                {!! $section['body'] !!}
            </div>
        </section>
    @endforeach
</div>
