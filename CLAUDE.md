# あなた次第(Your agenda)

## 概要
青年会議所(JC)の委員会運営を楽にするWebサービス。
委員会組織ごとに契約し、会議・次第・メンバー・資料を一元管理できる。
アップロードしたZipファイル内のHTML議案(gian.htm)や、PDF・画像単体の
議案ファイルをURLひとつで共有できる。
サービス名の由来: 「議題(agenda)はあなた次第」

## ブランディング
- APP_NAME: あなた次第
- 英語表記: Your agenda
- ログイン画面・ヘッダー・LPにサービス名を表示する

## 現在の進捗
- 実装の推奨順序 1〜4 まで完了:
  1. organizations / role / マルチテナント基盤
  2. メンバー管理 + 誕生日抽出(CSV一括登録・エクスポート込み)
  3. 会議管理 + 次第(agenda_items) + Zip議案リンク
  4. ダッシュボード4ペイン(カレンダー埋め込み含む)
     - 前倒しでmaterials(資料置き場)のCRUD一式と、基本設定(組織名・
       ヘッダー画像・GoogleカレンダーID)の最小実装も完了させた
       (次第の一括ダウンロード機能はStep6で別途実装する)
- 5(資料置き場)・6(基本設定)も完了:
  - 5は4の前倒し実装で完了済み
  - 6: 次第の一括ダウンロード(会議ごとにZip議案の中身も含めて圧縮)、
    オブザーブユーザー管理(作成・ログインID/パスワード変更・削除)、
    ダッシュボードのペイン表示オンオフ、UIのダーク/ライトモード
    手動切り替え(tailwind darkMode:'class' + localStorage永続)を実装
- 上記に加え、進捗ログ未記載のまま以下も実装済み:
  - 役職管理(positions テーブル・CRUD、メンバーへの紐付け、ナビ「役職管理」)
  - 次第(agenda_items)の親子構造(1階層のみ、parent_id)と、
    Member未登録でも担当者名を自由入力できる assignee_name
  - 議案アップロードはZipだけでなく、PDF・画像(jpg/png/gif/webp)単体の
    直接アップロードにも対応(ファイル名がgian.htmでなくても開ける)
  - 会議に終了日時(ends_at)を追加(開始のみだったheld_atに加えて設定可能)
  - ダッシュボードの会議一覧ペインに開催場所を表示
  - 議案ファイル(sites)・資料(materials)のアップロードにプログレスバーを表示
    (XHRで送信し進捗を表示、レスポンスはそのまま画面に差し替えるため
    サーバー側のリダイレクト/バリデーションエラー表示はそのまま機能する)
  - 次第作成画面で、他の会議の次第(トップレベル項目+子項目)を選んで
    コピーする機能(会議専用の議案ファイル(sites)のリンクはコピーしないが、
    組織内共有の資料(materials)のリンクはコピーする)
  - 次第の議案データのリンク先に、この会議専用の議案ファイル(sites)だけ
    でなく、組織内で共有されている資料置き場(materials)のデータも選べる
    ように変更(下記「データ構造」のagenda_itemsを参照)
  - 議案ファイル(sites)・資料(materials)ともに、アップロード済みファイルの
    中身だけを差し替える機能(id・URL・次第からのリンクは維持したまま)
  - オブザーブユーザーのナビに「会議一覧」「メンバー一覧」を追加
    (meetings.index / members.index を一般ユーザーと共有し、
    作成・編集・削除・CSV入出力のUIのみ@can('manage')で隠す)
  - ストレージ容量制限(下記「ストレージ容量」参照)と、基本設定画面での
    使用量表示
  - 管理者アカウント(super_admin)による管理者パネル
    (下記「管理者パネル」参照)
- 7(LP+組織登録フロー)・8(課金)は未着手:
  - resources/views/welcome.blade.php はBreeze標準のまま残っているが、
    どのルートからも参照されない死んだファイル(LPの実装ではない)
  - 次は 7、8 の順で実装する

## 開発環境
- Docker + Laravel Sail
- PHP 8.3 に固定(本番のさくらのレンタルサーバーに合わせる)
  - sail導入時: php artisan sail:install 後、docker-compose.yml の
    build context を ./vendor/laravel/sail/runtimes/8.3 にする
- DB は MySQL(さくらの提供DBがMySQLのため。SQLiteは使わない)
- composer.json に "require": { "php": "^8.3" } を明記

## 本番環境(さくらのレンタルサーバー)の制約
- Webサーバーは Apache。nginx設定は不可、.htaccess で制御する
- PHPはコントロールパネルでバージョン選択(8.3を使用)
- root権限なし。サーバー全体の設定変更は不可
- ドキュメントルートは ~/www 配下。Laravel本体は www の外に置き、
  public の内容を公開ディレクトリに配置する。index.php のパス書き換えでも
  良いが、`ln -s ~/your-agenda/public ~/www/your-agenda` のように
  シンボリックリンクで公開ディレクトリに見せる方法でも動作確認済み
  (index.phpの相対パスがそのまま使えるため書き換え不要)
- SSH接続可、composer は SSH 経由で実行
- 本番でSailは使わない(Dockerは開発専用)。デプロイは git pull +
  composer install --no-dev + migrate を想定
- フロントエンド資材(public/build/)は.gitignore対象のためgit pullでは
  配置されず、サーバーにNode.jsも無い想定。デプロイ前にローカルで
  npm run build を実行し、生成された public/build/ を rsync/scp/FTP
  (FTPSかSFTP推奨)でサーバーへ手動アップロードする(この手順を忘れると
  Vite manifest not foundで500エラーになる)。再デプロイ時はファイル名に
  ハッシュが付くため古いassetsが残っても動作に支障はないが、容量整理の
  ため定期的に public/build/ ごと差し替えるとよい
- 初回デプロイ時（サーバー移行時など）は php artisan storage:link を
  1回実行しておくこと（public/storage → storage/app/public のシンボリック
  リンク）。忘れると議案Zipのgian.htmリンクや会議/組織のヘッダー画像、
  メンバー写真などstorage/app/public配下のアップロードファイルが軒並み
  404になる。git pullでは作られないため、通常のデプロイフローとは別に
  一度だけ実行が必要
- cron はコントロールパネルから設定(Laravelスケジューラ用に
  php artisan schedule:run を毎分または最小間隔で登録)

## ユーザー権限(3種類)
1. 一般ユーザー
   - 委員会組織ごとに契約するアカウント
   - 組織情報・メンバー情報・会議・次第・資料の管理(全CRUD)ができる
2. オブザーブユーザー
   - 一般ユーザー(の組織)に紐付く閲覧専用アカウント
   - ダッシュボード・会議画面・資料・会議一覧・メンバー一覧の閲覧のみ可能
   - ナビゲーションメニューの管理系項目は一切表示しない
3. 管理者アカウント(super_admin)
   - 運営側がプラットフォーム全体を管理するための、組織に属さないアカウント
   - 詳細は下記「管理者パネル」を参照
- 認証は Laravel Breeze(Bladeスタック)
- 認可は role カラム + Gate で制御(すべての管理操作をガード)。
  Policyクラスは使わず、AppServiceProvider の
  Gate::define('manage', fn (User $user) => $user->isGeneral()) と
  Gate::define('super-admin', fn (User $user) => $user->isSuperAdmin())
  に一本化し、ルート側は Route::middleware(['auth', 'can:manage']) /
  Route::middleware(['auth', 'can:super-admin']) でグループ化する
- マルチテナント: 全データは organization_id でスコープする。
  他組織のデータには絶対にアクセスできないこと
  (BelongsToOrganization トレイトのグローバルスコープで実装済み)。
  super_adminは組織に属さない(organization_id が null)ため、
  管理者パネルのコントローラでは意図的に
  ->withoutGlobalScope(OrganizationScope::class) を使って組織を横断する

## データ構造(実装済み)
- organizations: name, header_image_path, icon_image_path,
  google_calendar_id, contracted_at, plan_status,
  show_meetings_pane, show_calendar_pane, show_birthday_pane,
  show_materials_pane(ダッシュボード各ペインの表示オンオフ), timestamps
- users: organization_id(nullable, super_adminのみnull), role
  (general / observer / super_admin), name, email, password,
  storage_quota_bytes(nullable, nullならconfig('storage_quota.default_bytes')
  =2GBを使う。管理者パネルから一般ユーザーごとに上書きできる)
  - オブザーブユーザーは一般ユーザーが招待・作成する
  - super_adminはUIからの登録経路がなく、
    php artisan admin:create-super-admin {name} {email} {password}
    でのみ作成する(一般ユーザーの登録がseeder/tinker限定なのと同じ方針)
- members: organization_id, name, name_kana, name_romaji, birth_date,
  gender, position_id(nullable, positionsへのFK), serial_number(組織内一意),
  company, phone, email, line_id, x_account, instagram_account,
  facebook_account, tiktok_account, hobby, motto, photo_path, timestamps
  - ログインユーザーとは別概念。誕生日表示・議題担当者に使う
  - 必須項目は name のみ、他は任意
  - CSVの一括登録・テンプレートDL・一括エクスポートに対応
    (photo_pathはCSVの対象外。写真はメンバーごとに個別アップロード)
  - 顔写真はjpg/png/webpのみ許可し、リサイズ・再エンコードして保存する
- positions(役職): organization_id, serial_number(組織内一意, 表示順),
  name, timestamps
  - メンバー管理から独立した役職マスタ。ナビ「役職管理」から管理する
- meetings: organization_id, name, held_at, ends_at(nullable, 終了日時),
  location, wifi_ssid, wifi_password, header_image_path, memo, timestamps
- agenda_items(次第): meeting_id, parent_id(nullable, 自己参照FKで1階層の
  子項目に対応), order, title, member_id(担当者, nullable),
  assignee_name(nullable, member未登録時の自由入力担当者名),
  site_id(nullable, この会議専用の議案ファイルへのリンク),
  material_id(nullable, 組織内共有の資料置き場データへのリンク),
  timestamps
  - site_id/material_idは排他(同時に両方は入らない)。画面上は
    「議案データのリンク」という単一のセレクトで選ばせ、
    site:{id} / material:{id} という値(フォーム上のフィールド名は
    agenda_link)をAgendaItemRequest::prepareForValidation()で
    site_id/material_idに振り分けてから通常のバリデーションにかける
  - 次第コピー機能(過去の次第からコピー)では、site_idは会議ごとに
    紐付くためコピーされないが、material_idは組織内で共有される
    データのため次第と一緒にコピーされる
- sites(議案ファイル): organization_id, meeting_id(nullable), uuid, title,
  original_filename, index_path, user_id, timestamps
  - meeting_id が null の場合は組織共通の公開サイト、値がある場合はその
    会議専用の議案ファイルとして扱う
  - Zip(展開してgian.htmを探す)だけでなく、PDF・画像(jpg/jpeg/png/gif/
    webp)単体の直接アップロードにも対応。単体アップロードの場合は
    ファイル名を問わずそのまま公開する(gian.htmという名前は不要)
- materials(資料置き場): organization_id, title, file_path,
  original_filename, user_id, timestamps
  - 差し替え(MaterialController::update)に対応。id・title・ダウンロードURL
    (materials.downloadはidキー)は変わらず、file_path/original_filenameの
    みが更新される。site同様、新ファイルを先に保存してから旧ファイルを
    削除する順序で処理し、失敗時に既存ファイルが消えないようにしている

## 画面構成

### ダッシュボード(トップページ・4ペイン構造)
- 上部に組織のヘッダー画像を表示(基本設定でアップロード)
- ペイン1: 今後の会議予定(本日以降に開催予定の会議を開催日時の昇順で表示。
  開催日時・開催場所・会議画面へのリンク。該当なしの場合は「予定なし」と表示)
- ペイン2: カレンダー(組織で設定したGoogleカレンダーをiframe埋め込み表示)
- ペイン3: 今月の誕生日メンバー(membersのbirth_dateから当月分を抽出)
- ペイン4: その他の資料置き場(materialsの一覧・ダウンロード)
- オブザーブユーザーも閲覧可能

### ナビゲーションメニュー(一般ユーザーのみ表示)
1. 会議管理
2. 役職管理(positions の CRUD、メンバーへの紐付けに使う役職マスタ)
3. メンバー管理(members の CRUD、誕生日登録)
4. 資料管理(資料置き場にアップするデータの管理画面)
5. 基本設定
   - ダッシュボードの組織ヘッダー画像の設定
   - 組織情報の編集
   - GoogleカレンダーIDの設定(独立したメニュー項目ではなく基本設定に統合)
   - 次第の一括ダウンロード機能(下記参照)
   - データ使用量の表示(下記「ストレージ容量」参照)
- オブザーブユーザーには上記の管理系メニューを一切表示しない(Blade側の
  @can だけでなく、ルート側でも必ずミドルウェア/Gateでガードする)。
  ただし「会議一覧」「メンバー一覧」は閲覧専用として例外的に表示する
  (meetings.index / members.index への遷移のみ。作成・編集・削除・
  CSV入出力へのリンクはビュー側で @can('manage') により非表示にする)

### 会議画面
- 会議ごとのヘッダー画像をトップに表示(設定可能)
- アジェンダ(次第)を表示。各議題にアップ済みZip議案(gian.htm)をリンクし、
  クリックで議案を確認できる
- 次第管理メニュー(一般ユーザーのみ):
  会議名 / 開始・終了日時(ends_at) / 開催場所 / Wi-Fi情報 / 次第 /
  議題の担当者 / 会議ヘッダー画像 を追加・入力・編集・削除できる
  - 各次第項目の「議案データのリンク」は、この会議専用の議案ファイル
    (sites)と、組織内で共有されている資料置き場(materials)のどちらか
    一方を選べる(単一のセレクトに議案ファイル/資料置き場をoptgroupで
    まとめて表示)
  - 次第は他の会議からのコピーにも対応(トップレベル項目を選ぶと
    その子項目もまとめて末尾に追加される。会議専用の議案ファイル
    (sites)のリンクは会議ごとの紐付けのためコピーされないが、
    組織内共有の資料(materials)のリンクはコピーされる)
  - 議案ファイル・資料のアップロードは進捗バー付き
    (XHR送信+アップロード中%表示、完了後は通常のリダイレクト/
    バリデーションエラー表示にそのまま切り替わる)
  - アップロード済みの議案ファイルは「差し替え」で中身だけを置き換えられる
    (SiteController::updateForMeeting)。新ファイルは一時ディレクトリに
    展開・検証してから既存ファイルと入れ替えるため、Zipが壊れている等の
    失敗時は既存ファイルを一切壊さない。site の id・uuid(公開URL)は
    変わらないため、その議案ファイルを参照しているagenda_items.site_idの
    リンクは何もしなくても新しい中身を指したままになる。容量判定は
    「既存ファイル分を差し引いてから新ファイル分を足す」形で行うため、
    容量上限ぎりぎりでも同程度以下のサイズへの差し替えは拒否されない

### ランディングページ(LP・未ログイン公開ページ)
- ターゲット: JCメンバー(委員長・幹事クラス)
- コンセプト: 「毎月タバコ1箱分我慢すれば、委員会運営が楽になる」
- 料金: 月額タバコ1箱分の価格帯(具体額は確定後に反映)
- LPからユーザー登録(組織契約)へ誘導する
- ※一般公開の登録が入るため、Breezeのregister無効化方針は変更:
  LP経由の組織登録フローを新設する(組織作成+一般ユーザー作成をセットで)

## 契約・課金
- 1組織 = 1契約。月額課金、基本的に1年間単位の運用を想定
- 決済手段は未確定(Stripe + Laravel Cashier を第一候補として設計する。
  実装前にユーザーに確認すること)
- 解約時: その年度に作成した次第・議案を一括ダウンロードできる機能を提供
  (基本設定の一括ダウンロードと共通の仕組みでよい)

## 次第の一括ダウンロード
- 組織の meetings + agenda_items + 紐付くsites(Zip議案)をまとめて
  1つのZipに固めてダウンロードさせる
- 会議ごとにフォルダ分けし、次第はHTMLまたはPDFで書き出す
- 解約時のデータ持ち出し手段でもあるため、確実に全件含めること

## ストレージ容量
- 一般ユーザー1人あたり、デフォルトで最大2GBのデータ容量を割り当てる
  (config('storage_quota.default_bytes')、users.storage_quota_bytesで
  ユーザーごとに上書き可能。上書きは管理者パネルからのみ行う)
- 集計対象(組織単位で合算し、アップロードを行った一般ユーザー自身の
  割り当て容量と比較する): 資料置き場(materials)、議案ファイル
  (sites。Zipは展開後の全ファイル、PDF/画像は本体)、組織のヘッダー/
  アイコン画像、会議のヘッダー画像、メンバーの顔写真
  - app/Services/StorageUsageService が集計を担当。ファイルサイズは
    その都度ディスクから実測する(size列をDBに持たせていない)
- 上限を超える場合は新規アップロードをバリデーションエラーとして拒否する
  (app/Http/Requests/Concerns/EnforcesStorageQuota トレイトを各
  FormRequestのwithValidator()から呼び出す)。既存データの保持には
  影響しない
- 基本設定画面に現在の使用量/割り当て容量をプログレスバーで表示する

## 管理者パネル(super_admin)
- 一般ユーザー・オブザーブユーザーとは別のロール。ログイン画面・URLは
  共通(Breezeのlogin)だが、ログイン後は layouts/admin.blade.php という
  専用レイアウト(通常ナビとは別、組織アイコン等に依存しない)を使う
- ルートは routes/admin.php、prefix('admin')・name('admin.')・
  middleware(['auth', 'can:super-admin']) でグループ化
- 提供機能:
  - 組織一覧(契約ステータス・契約日・ユーザー数・データ使用量)
  - 組織詳細: 契約状況の確認、ユーザー一覧(一般/オブザーブ)、
    一般ユーザーごとの割り当て容量(GB)の変更、アカウント削除
  - 「アップロード済みデータを削除」: その組織のmaterials/sites/
    各種画像を全て削除して使用量をゼロに戻す(app/Services/
    OrganizationDataPurgeServiceが担当)。会議・メンバー等のレコード
    自体は削除しない点が組織削除とは異なる
- 契約ステータス(plan_status)自体の変更・組織削除・課金操作は
  未実装(Step8の決済手段確定後にあわせて設計する)

## セキュリティ要件(必ず守ること)
- Zip Slip対策: エントリ名に「..」や先頭「/」を含む場合は拒否
- Zip爆弾対策: 展開後合計200MB以下、ファイル数1000以下
- 拡張子ホワイトリスト: html, htm, css, js, png, jpg, jpeg, gif, svg,
  webp, ico, woff, woff2, ttf, json, txt, pdf, mp4 のみ展開
- .php等は絶対に展開しない
- PHP実行禁止は .htaccess で行う(Apacheのため。nginx設定は書かない):
  展開ルートディレクトリ(sites/)にアプリ側から .htaccess を自動生成する
  内容: RemoveHandler .php .phtml .phar / RemoveType .php /
        <FilesMatch "\.(php|phtml|phar|cgi|pl)$"> Require all denied </FilesMatch>
  ※ Zip展開先の各ディレクトリではなく sites/ 直下に1つ置けば配下全体に効く
- X-Robots-Tag(noindex)も .htaccess の Header ディレクティブで付与
- AddDefaultCharset Off も同じ .htaccess に付与する:
  Apacheのデフォルト設定はcharset=UTF-8をレスポンスヘッダーで強制するため、
  アップロードされるサイトにUTF-8とShift_JIS(Word書き出しHTML等)が混在する
  現状では、ファイル自身の<meta charset>宣言より優先されて文字化けする。
  Offにしてブラウザに各ファイルの宣言を判断させる。
- gian.htmはルート直下または1階層下フォルダを探索(__MACOSXは除外)
- 見つからなければ展開ディレクトリを削除してエラー
- Zipではなく単体ファイル(PDF・jpg・jpeg・png・gif・webp)をアップロード
  した場合はこの探索は行わず、拡張子ホワイトリストで許可した種類だけを
  そのまま保存してそのまま開く(ファイル名にgian.htmを要求しない)
- ヘッダー画像等のアップロードは画像形式(jpg/png/webp)のみ許可し、
  リサイズ・再エンコードして保存する
- Wi-Fiパスワードは閲覧権限のある組織メンバーだけが見られること
- マルチテナント境界: organization_id によるスコープ漏れを疑うテストを書く
  (他組織のsites/meetings/materialsのURL直叩きで403/404になること)

## デザイン方針
- 管理画面・LPのUI作成・改修時は frontend-design スキルを適用する
- トーン: 青年会議所の運営ツールとして、誠実さと前向きなエネルギーが
  両立するデザイン。堅すぎず、しかし議案書を扱うのにふさわしい品位を保つ
- サービス名「あなた次第(Your agenda)」をLP・ログイン画面で印象的に見せる
- LPは「毎月タバコ1箱分で委員会運営が楽になる」を軸にしたコピーで構成する
- Breeze標準のレイアウト(layouts/app.blade.php)をベースに管理画面を統一
- デザイン変更時は機能・ルーティング・バリデーションに手を加えないこと
- 進め方: まず機能を動く状態まで実装し、その後デザインを磨く2段階で行う

## 実装の推奨順序
1. organizations / role / マルチテナント基盤(既存sitesにorganization_id追加)
2. メンバー管理 + 誕生日抽出
3. 会議管理 + 次第(agenda_items) + Zip議案リンク
4. ダッシュボード4ペイン(カレンダー埋め込み含む)
5. 資料置き場
6. 基本設定(ヘッダー画像・組織情報・一括ダウンロード)
7. LP + 組織登録フロー
8. 課金(決済手段確定後)