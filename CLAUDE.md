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
- ブランドマーク(`x-brand-mark`、次第の書類としおりをかたどったアイコン)を
  favicon(`public/favicon.svg`、`favicon.ico`、`apple-touch-icon.png`)にも
  使用する。SVGラスタライズツールがサーバー環境に無いため、GDのプリミティブ
  (矩形・楕円・多角形)で同モチーフを直接描画して生成した
  (`resources/views/layouts/_favicon.blade.php`を各レイアウトの
  `<head>`でtheme-initと一緒にincludeする)

## 現在の進捗
- 実装の推奨順序 1〜8 まですべて完了(詳細は下記「実装の推奨順序」参照)
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
- 7(LP+組織登録フロー)も完了:
  - resources/views/welcome.blade.php を全面刷新し、`/lp`ルートで公開
    (未ログイン時の`/`は引き続き`/login`へ自動リダイレクトするため、
    LPへは`/lp`で個別にアクセスする形。トップイメージはブラウザ枠付き
    スクリーンショットではなく写真/イラスト向けのプレースホルダー画像)
  - `/register`を再有効化し、組織作成+一般ユーザー作成をDBトランザクション
    でセットで行うフローを新設(RegisteredUserController、組織名入力欄を
    追加)。登録後は自動ログインしてダッシュボードへ
- 上記に加え、進捗ログ未記載のまま以下も実装済み(このセッションで追加):
  - メンバープロフィール画面(`members.show`、名刺風デザイン)。メンバー
    一覧から行クリックで遷移。一覧は表形式/カード形式をAlpineの
    storeで切り替え可能(localStorageに保持)。プロフィール画面には
    前後のメンバーへ移動するリンクがあり、対応ブラウザではView
    Transitions APIでカードがめくれるアニメーションが入る
  - 会議一覧・メンバー一覧は行全体をクリックすると詳細画面に遷移する
    (編集・削除ボタン領域はx-on:click.stopで行クリックと分離)
  - 次第(会議詳細)画面に印刷ボタンを追加。印刷時はナビ・ヘッダー画像・
    操作ボタンを隠し、次第シートを白黒・A4基準の配色に切り替える
    (resources/css/app.cssの`.print-hidden`/`.print-sheet`)
  - 次第の外部公開共有リンク(下記「次第の公開共有リンク」参照)
  - 案内文作成機能(下記「案内文作成」参照): PDF/メール/LINEの3種類を
    会議情報から自動生成し、手直しして保存できる
  - 会議の管理画面を「会議情報編集」(`meetings.edit`)と「次第編集」
    (`meetings.agenda`、議案ファイル・過去の次第からコピー・次第項目の
    管理)の2画面に分割
  - 会議に開催場所の住所・地図URL・懇親会/2次会情報・推奨ホテル情報を
    追加(案内文のプレースホルダーとして利用)
  - オブザーブユーザーは自分自身のプロフィール編集画面(Breeze標準の
    `/profile`)にアクセスできないようにルート・ナビ両方でガード
    (`can:manage`ミドルウェア配下に移動)
- 上記に加え、進捗ログ未記載のまま以下も実装済み(さらに別セッションで追加):
  - 管理者パネル(`/admin`)へのBasic認証によるアクセス制限
    (`ADMIN_BASIC_AUTH_USERNAME`/`PASSWORD`、両方設定時のみ有効。
    `admin.basic_auth`ミドルウェア、`auth`より先に実行されるよう
    優先度リストに追加)
  - super_adminアカウントの二段階認証(TOTP、pragmarx/google2fa)と
    ログイン失敗10回でのアカウントロック(30分、自動解除)。初回ログイン時に
    QRコードでのセットアップを強制し、リカバリーコードを1回だけ表示する。
    `php artisan admin:reset-two-factor {email}`で紛失時にリセット可能
  - 一般/オブザーブユーザー向けの「お問い合わせ」機能(問い合わせ・不具合
    報告・機能要望の3種、ナビ右側のアイコンからモーダルで送信)と、
    管理者パネルでの一覧・フィルタ(状態/種類/組織/キーワード)・
    対応済みトグル(`inquiries`テーブル、`Inquiry`モデル)
  - ブランドマーク(`x-brand-mark`)を、次第の書類としおりをかたどった
    アイコンに変更(`currentColor`で利用箇所ごとのブランドカラーに追従)
  - LP・登録画面から「青年会議所」表記を削除し、「会議運営」という
    汎用的な表現に統一(特定団体名を出さない方針)
- 8(課金)完了: Stripe + Laravel Cashierによる月額課金と、14日間の
  無料トライアル(カード登録不要)を実装。詳細は下記「契約・課金」参照
- 上記に加え、進捗ログ未記載のまま以下も実装済み:
  - 部署管理(departments テーブル・CRUD、メンバーへの紐付け、ナビ
    「部署管理」。positionsと同じマスタ管理方式)
  - 解約・トライアル終了後を「次第の閲覧専用モード」にする変更、無償提供
    モード、常設の再課金導線バナー(詳細は「契約・課金」参照)
  - 次第の個別ダウンロード(会議一覧から会議1件分だけダウンロード、
    `meetings.export`)と、一括/個別ダウンロード共通のフォルダ命名を
    開催日時ベース(`YmdHi`)に変更(詳細は「次第の一括ダウンロード・
    個別ダウンロード」参照)
  - 案内文作成画面(会議ごと)に、基本設定のデフォルト編集画面と同じ
    `>>`(右揃え)・`[改ページ]`の説明文を追記
  - 基本設定画面のカード表示順を変更(オブザーブユーザー管理を案内文の
    デフォルトより上に配置)
  - ダークモードの配色統一(下記「デザイン方針」のガイドライン参照。
    テーブルヘッダー・ドロップダウンメニュー・お問い合わせフォーム等で
    Tailwind標準のgray-*系が混入していたのをブランドトークンに統一)

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
  google_calendar_id, contracted_at,
  stripe_id, pm_type, pm_last_four(Laravel Cashierの`Billable`トレイトが
  管理する顧客ID・支払い方法情報。Cashierの標準マイグレーションを
  usersではなくorganizationsに向けて発行している),
  trial_ends_at(nullable datetime, 14日間無料トライアルの終了日時。
  組織登録時にセットする。カード登録不要のトライアルのため、Stripe側には
  サブスクリプションを作らずCashierの`onGenericTrial()`で判定する。
  詳細は下記「契約・課金」参照。旧`plan_status`カラムは廃止し、
  `Organization::subscriptionStatusLabel()`で都度導出する形に統一した),
  show_meetings_pane, show_calendar_pane, show_birthday_pane,
  show_materials_pane(ダッシュボード各ペインの表示オンオフ),
  invitation_pdf_template, invitation_email_template,
  invitation_line_template(nullable text, 案内文のデフォルトテンプレート。
  未設定なら組み込みの既定テンプレートを使う。詳細は下記「案内文作成」参照),
  timestamps
- users: organization_id(nullable, super_adminのみnull), role
  (general / observer / super_admin), name, email, password,
  storage_quota_bytes(nullable, nullならconfig('storage_quota.default_bytes')
  =2GBを使う。管理者パネルから一般ユーザーごとに上書きできる)
  - オブザーブユーザーは一般ユーザーが招待・作成する
  - super_adminはUIからの登録経路がなく、
    php artisan admin:create-super-admin {name} {email} {password}
    でのみ作成する(一般ユーザーの登録がseeder/tinker限定なのと同じ方針)
- members: organization_id, name, name_kana, name_romaji, birth_date,
  gender, position_id(nullable, positionsへのFK), department_id
  (nullable, departmentsへのFK。小委員会・小会議など組織内の所属部署),
  serial_number(組織内一意),
  company, phone, email, line_id, x_account, instagram_account,
  facebook_account, tiktok_account, hobby, motto, photo_path, timestamps
  - ログインユーザーとは別概念。誕生日表示・議題担当者に使う
  - 必須項目は name のみ、他は任意
  - CSVの一括登録・テンプレートDL・一括エクスポートに対応
    (photo_path/position_id/department_idはCSVの対象外。写真は
    メンバーごとに個別アップロード、役職・部署はマスタ選択のため)
  - 顔写真はjpg/png/webpのみ許可し、リサイズ・再エンコードして保存する
- positions(役職): organization_id, serial_number(組織内一意, 表示順),
  name, timestamps
  - メンバー管理から独立した役職マスタ。ナビ「役職管理」から管理する
- departments(部署): organization_id, serial_number(組織内一意, 表示順),
  name, timestamps
  - positionsと同じマスタ管理方式の部署(小委員会・小会議等)マスタ。
    ナビ「部署管理」から管理し、メンバー登録時に紐付ける
- meetings: organization_id, name, held_at, ends_at(nullable, 終了日時),
  location, venue_address(nullable, 開催場所の住所), venue_map_url
  (nullable, 地図URL。設定するとPDF案内文にQRコードが自動掲載される),
  social_event_info(nullable text, 懇親会・2次会情報), recommended_hotel_info
  (nullable text, 推奨ホテル情報), wifi_ssid, wifi_password, header_image_path,
  memo, public_token(nullable uuid, unique, 外部公開共有リンク用。詳細は
  下記「次第の公開共有リンク」参照), invitation_pdf_body,
  invitation_email_body, invitation_line_body(いずれもnullable text,
  会議ごとに手直しして保存した案内文の最終テキスト。詳細は下記
  「案内文作成」参照), timestamps
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
- ペイン2: カレンダー(組織で設定したGoogleカレンダーをiframe埋め込み表示。
  Google側の埋め込みUIは背景色をこちらで指定できないため、show*系パラメータで
  タブ・ナビ等の不要なUIだけ非表示にしている)
- ペイン3: 今月の誕生日メンバー(membersのbirth_dateから当月分を抽出。
  本日が誕生日のメンバーは強調表示される。絵文字は使わない方針)
- ペイン4: その他の資料置き場(materialsの一覧・ダウンロード)
- オブザーブユーザーも閲覧可能
- 組織にメンバー・会議が1件も無い状態(初回セットアップ時)は、一般
  ユーザー向けに「メンバー登録→最初の会議作成→組織情報設定」の
  3ステップ案内カードを表示する

### ナビゲーションメニュー(一般ユーザーのみ表示)
1. 会議管理
2. 役職管理(positions の CRUD、メンバーへの紐付けに使う役職マスタ)
3. 部署管理(departments の CRUD、メンバーへの紐付けに使う部署マスタ。
   positions と同じ管理方式)
4. メンバー管理(members の CRUD、誕生日登録)
5. 資料管理(資料置き場にアップするデータの管理画面)
6. 基本設定
   - ダッシュボードの組織ヘッダー画像の設定
   - 組織情報の編集
   - GoogleカレンダーIDの設定(独立したメニュー項目ではなく基本設定に統合)
   - オブザーブユーザー管理への導線
   - 案内文のデフォルトテンプレート編集(下記「案内文作成」参照)
   - 次第の一括ダウンロード機能(下記参照)
   - データ使用量の表示(下記「ストレージ容量」参照)
   - (画面上の表示順は上記の並びのとおり。オブザーブユーザー管理の
     カードを案内文のデフォルトより上に配置している)
- オブザーブユーザーには上記の管理系メニューを一切表示しない(Blade側の
  @can だけでなく、ルート側でも必ずミドルウェア/Gateでガードする)。
  ただし「会議一覧」「メンバー一覧」は閲覧専用として例外的に表示する
  (meetings.index / members.index への遷移のみ。作成・編集・削除・
  CSV入出力へのリンクはビュー側で @can('manage') により非表示にする)。
  Breeze標準の個人プロフィール編集画面(/profile)も同様にオブザーブ
  ユーザーからは隠し、ルートも can:manage 配下でガードする
- ナビ右側に、割り当て容量に対する使用量が80%を超えた一般ユーザーへ
  さりげなく警告バッジを表示する(基本設定へのリンク付き)

### 会議画面(次第・会議情報編集・次第編集)
- `meetings.index`(会議一覧): 一般ユーザーには新規登録・編集・次第編集・
  案内文作成・削除に加え、会議単体の次第を個別ダウンロードするリンク
  (`meetings.export`)を表示する。オブザーブユーザーには詳細への
  遷移のみ表示する
- `meetings.show`(次第画面): 会議ごとのヘッダー画像をトップに表示
  (設定可能)。アジェンダ(次第)を表示し、各議題にアップ済み議案
  ファイル(sites)や資料(materials)をリンク、クリックで確認できる。
  一般ユーザーには印刷ボタン(下記参照)と、以下2画面へのリンクを表示
- 会議の管理は目的別に2画面に分かれている(いずれも一般ユーザーのみ):
  - **会議情報編集**(`meetings.edit`): 会議名 / 開始・終了日時(ends_at) /
    開催場所 / 開催場所の住所・地図URL / 懇親会・2次会情報 /
    推奨ホテル情報 / Wi-Fi情報 / メモ / 会議ヘッダー画像
  - **次第編集**(`meetings.agenda`): 議案ファイルのアップロード・差し替え・
    削除、過去の次第からのコピー、次第項目(agenda_items)の追加・編集・
    削除・並び替え
- 次第編集画面の詳細:
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
- **印刷**: 次第画面・PDF案内文画面それぞれに印刷ボタンがあり、印刷時は
  ナビ・ヘッダー画像・操作ボタンを隠して白黒・A4基準の配色に切り替える
  (`.print-hidden`/`.print-sheet`、`@page { size: A4; }`)

### 次第の公開共有リンク
- 会議ごとに、ログイン不要でアクセスできる公開共有リンクを発行できる
  (meetings.public_token、UUID)。一般ユーザーが次第画面から「発行する」
  「再発行」「無効化」を操作する
- 公開ページ(`/s/meetings/{token}`、PublicMeetingController)には
  Wi-Fi情報・メモは一切表示しない(組織メンバー限定の情報のため)
- 公開ページから開ける資料(materials)は、そのトークンの会議に実際に
  紐づいている資料だけに限定する(`/s/meetings/{token}/materials/{material}`
  で毎回検証。トークンを知っていても組織の他の資料にはアクセスできない)
- 議案ファイル(sites)は元々公開ディスク上のURLのためそのまま利用できる

### 案内文作成
- 会議ごとに「案内文作成」画面(`meetings.invitation.edit`)があり、
  PDF・メール・LINE本文の3種類を会議情報から自動生成する。手直しして
  保存すると次回開いたときも編集内容が残り、「テンプレートに戻す」で
  組み込み既定に戻せる(app/Services/MeetingInvitationTemplateService)
- テンプレートは3層構造: ①組み込みの既定テンプレート → ②組織が基本設定
  画面で上書きしたデフォルト(organizations.invitation_{type}_template)
  → ③会議ごとに手直しして保存した最終テキスト(meetings.invitation_{type}_body)。
  優先順位は③>②>①
- プレースホルダー(`{{会議名}}`等)で会議データを差し込める。一覧は
  `MeetingInvitationTemplateService::PLACEHOLDERS`を参照。Wi-Fi・住所・
  懇親会・宿泊・公開URLはopt-in(既定テンプレートには含まれず、使いたい
  組織だけ追記する)
- PDF案内文は「行の先頭に`>>`」で右揃え(差出人情報など)、「行に
  `[改ページ]`とだけ書く」で強制改ページに対応。時候の挨拶
  (`{{時候の挨拶}}`)は表示月に応じて自動選択される
- PDFファイルの生成は専用ライブラリを使わず、印刷用ページをブラウザの
  「PDFとして保存」で書き出す方式(次第画面の印刷と同じ仕組み)
- 地図URL(venue_map_url)が設定されている会議は、PDF案内文にQRコードを
  自動掲載する(endroid/qr-code、外部APIなしでサーバー内生成)

### メンバープロフィール画面
- `members.show`: メンバーごとに名刺風のプロフィールカードを表示
  (顔写真・氏名・役職・所属企業・連絡先・SNS・生年月日・趣味・座右の銘)。
  メンバー一覧から行クリックで遷移する
- 前後のメンバー(氏名順)へ移動するリンクがあり、対応ブラウザでは
  View Transitions APIでカードがめくれるアニメーションが入る
  (非対応ブラウザは通常の画面遷移になるだけで機能に支障はない)
- メンバー一覧は表形式・カード形式をAlpine.jsのstoreで切り替えられる
  (localStorageに保持)

### ランディングページ(LP・未ログイン公開ページ、`/lp`)
- ターゲット: 会議・委員会運営に悩む団体の担当者(青年会議所に限定しない
  一般的な表現にしている)
- コンセプト: 「毎月タバコ1箱分我慢すれば、会議の準備が楽になる」
- コピーの方針: 「青年会議所」「委員会」「部会」「役員」等の特定団体を
  想起させる表現や、「マスタ」「オブザーブユーザー」「CSV」「エクスポート」
  等の専門用語・内部の機能名はLP上では使わない。困りごと(課題提起
  セクション)→解決策の順で、ユーザー目線の平易な言葉で訴求する
  (「役職マスタで組織図を管理」ではなく「役職を登録しておけば、みんなの
  肩書きもすぐ分かる」のように言い換える)
- 料金: 月額タバコ1箱分の価格帯を訴求軸に、具体額(月額600円・税込)を
  表示(`config('billing.monthly_price_yen')`)。14日間の無料トライアル
  (カード登録不要)も明記している
- ヒーロー画像・各機能紹介の画面キャプチャは、実画面に差し替えやすい
  プレースホルダー(`x-image-placeholder`/`x-screenshot-placeholder`)
- CTAは`/register`(組織登録フロー)へ誘導する
- 未ログイン時に`/`へアクセスすると引き続き`/login`へ自動リダイレクトする
  (LP自体は`/`を差し替えず、`/lp`で個別に公開する方針)

## 契約・課金
- 1組織 = 1契約。月額600円(税込)、Stripe + Laravel Cashierで実装済み
- Cashierの`Billable`トレイトは`User`ではなく`Organization`モデルに付与
  (1組織=1契約のため、Stripeの顧客は組織単位)。
  `Cashier::useCustomerModel(Organization::class)`を`AppServiceProvider`
  で指定している
- **14日間の無料トライアル**: 組織登録時にカード情報の入力は求めない。
  `RegisteredUserController`が`organizations.trial_ends_at`に
  `now()->addDays(config('billing.trial_days'))`をセットするのみで、
  Stripe側にはサブスクリプションを作らない(Cashierの
  `newSubscription()->trialDays()`は使わず、`onGenericTrial()`
  ——trial_ends_atがサブスクリプション無しでも未来日時かどうかだけを見る
  ——をそのまま使う)
- **トライアル終了後のアクセス制御**: `App\Http\Middleware\
  EnsureOrganizationHasAccess`(エイリアス`subscribed`)が、一般/
  オブザーブユーザー向けの保護ルート(`routes/web.php`)に適用されている。
  `Organization::hasActiveAccess()`(トライアル中 or 有効な
  サブスクリプションあり)がfalseならペイウォール(`/billing`、
  `billing.paywall`)へリダイレクトする。super_adminは対象外
  (`routes/admin.php`には適用しない)
- **支払い方法**: Stripe Checkout(ホスト型決済ページ)にリダイレクトする
  方式。カード情報は自社サーバーを経由しない。支払い操作(`billing.checkout`、
  POST)は一般ユーザーのみ(`can:manage`)。支払い完了後はStripeの
  Webhook(`/stripe/webhook`、Cashier標準の`WebhookController`、
  CSRF検証は`bootstrap/app.php`で除外)がサブスクリプションを同期する
- **解約後は次第の閲覧専用モードになる**: トライアル終了・未払い状態
  (`hasActiveAccess()`がfalse)でも、`meetings.index`/`meetings.show`
  (次第の一覧・閲覧)だけは`subscribed`ミドルウェアの対象外にしており
  引き続き閲覧できる。新規作成・編集・削除(`meetings.create`等)は
  引き続き`can:manage`+`subscribed`でブロックされる
- **次第にリンクされた議案データは開けなくなる**: 議案ファイル(sites)は
  Zip展開後、Webサーバーが`public/storage`経由で直接配信する静的ファイル
  でありLaravelのルート/ミドルウェアを経由しないため、「開く入口」だけを
  `SiteController::open`(ルート名`sites.open`、`subscribed`ミドルウェア
  付き)でラップし、`AgendaItem::linkUrl()`はここを経由するURLを返す
  ようにしている。未契約時はここで`billing.paywall`へリダイレクトされる
  (フォルダ内のCSS/画像等の付随アセット自体は技術的には静的配信のまま
  だが、入口である`gian.htm`等を開けなければ実用上は同じ効果になる、
  というトレードオフを採用している)。`materials.download`は元々
  `subscribed`ミドルウェア配下のため変更不要。`resources/views/meetings/
  show.blade.php`では、リンクをクリックしてからペイウォールに弾かれる
  体験を避けるため、未契約時はそもそもリンクにせず鍵アイコン付きの
  案内テキストとして表示する
- **公開共有リンクも未契約なら閲覧不可**: 発行元組織が未契約の場合、
  `PublicMeetingController::show()`は次第本体の代わりに専用の利用不可
  ビュー(`meetings/public-unavailable.blade.php`)を返す。資料・議案
  ファイルの公開ダウンロード(`public.meetings.materials.download`、
  新設`public.meetings.sites.open`)も同様に404にする
- **解約後もデータ持ち出し可能**: `/settings/export`(次第の一括
  ダウンロード)は`subscribed`ミドルウェアの対象外にしている。トライアル
  終了・未払い状態でもこれまでのデータをダウンロードできる
- **無償提供モード**: super_adminが管理者パネル(組織詳細画面)から、
  特定組織に対して課金なしで全機能を使わせる「無償提供モード」を
  オン/オフできる(`organizations.free_access_enabled`、
  `AdminOrganizationController::toggleFreeAccess`)。
  `Organization::hasActiveAccess()`はこのフラグが立っていれば
  トライアル・サブスクリプション状態に関わらずtrueを返す
- **常設の再課金導線**: `layouts.app`のView composerが
  `organizationHasActiveAccess`を共有し、アクセス制限中は
  `layouts/_access-blocked-banner.blade.php`がどのページにも(ペイウォール
  画面自体を除き)表示され、`/billing`への導線を常に提供する
- 旧`organizations.plan_status`カラムは廃止(実質未使用だった上、
  Cashierのサブスクリプション状態と二重管理になり食い違いの元になるため)。
  契約状態の表示は`Organization::subscriptionStatusLabel()`が
  無償提供/トライアル/サブスクリプション状態から都度導出する
- 未実装: Stripe側での複数プラン対応、管理者パネルからの契約ステータス
  手動変更(無償提供モードのオン/オフは実装済み)、組織の完全削除
  (退会処理)。詳細はREADME.mdの「課金・トライアル」セクション参照
  (テストモードでのセットアップ手順を含む)

## 次第の一括ダウンロード・個別ダウンロード
- 組織の meetings + agenda_items + 紐付くsites(Zip議案)をまとめて
  1つのZipに固めてダウンロードさせる(基本設定、`settings.export`)。
  会議一覧からは会議1件分だけをダウンロードすることもできる
  (`meetings.export`、`MeetingArchiveExportService::exportMeeting()`)。
  どちらも中身の構成(HTML書き出し+sites同梱)を共有する
  private `addMeetingContents()` を経由する
- 会議ごとのフォルダ名は `held_at` を `YmdHi`(例: `202608221900`)形式に
  したもの(以前は会議IDとスラッグ化した会議名だったが、わかりにくいため
  変更した)。`held_at` が未設定(開催日時未定)の会議は会議IDを使う。
  同一分に複数の会議がある場合は2件目以降に `-{会議ID}` を付けて衝突を
  回避する(衝突したままだとZip内で同名の agenda.html が欠落するため)
- 次第はHTMLで書き出す(app/Services/MeetingArchiveExportService)
- 一括ダウンロードは解約時のデータ持ち出し手段でもあるため、確実に
  全件含めること。一括・個別どちらのダウンロードルートも
  `subscribed`ミドルウェアの対象外(トライアル終了・未契約でも
  ダウンロードできる。詳細は下記「契約・課金」参照)

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
  middleware(['admin.basic_auth', 'auth', 'can:super-admin']) でグループ化
- 提供機能:
  - 組織一覧(契約ステータス・契約日・ユーザー数・データ使用量・
    お問い合わせ件数)
  - 組織詳細: 契約状況(トライアル/契約中/無償提供中の別、トライアル
    終了日、Stripe Customer ID)の確認、ユーザー一覧(一般/オブザーブ)、
    一般ユーザーごとの割り当て容量(GB)の変更、アカウント削除、
    「無償提供モード」のオン/オフ切り替え
  - 「アップロード済みデータを削除」: その組織のmaterials/sites/
    各種画像を全て削除して使用量をゼロに戻す(app/Services/
    OrganizationDataPurgeServiceが担当)。会議・メンバー等のレコード
    自体は削除しない点が組織削除とは異なる
- 未実装: Stripe側での複数プラン対応、組織の完全削除(退会処理)

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
- **ダークモードの配色は、Tailwind標準のgray-*系ではなくブランドトークン
  (ink/paper/brass/leather、tailwind.config.jsのcolors参照)を使うこと**。
  Breezeの標準コンポーネント(x-dropdown、x-text-input以外の生の
  select/textarea等)をコピーして使うと`dark:bg-gray-900`のような汎用色が
  混入しやすく、実際に複数箇所で「他のUIパーツと色が浮く」不具合が
  発生した。特に`dark:bg-night`(ページ背景)と`dark:bg-ink-900`は
  たまたま同一色(#16110B)のため、テーブルヘッダー等の背景に
  `dark:bg-ink-900`を使うと背景と一体化して見えなくなる点に注意
  (カード本体は`dark:bg-ink-800`、それより目立たせたい見出し帯は
  `dark:bg-ink-700`を使う)

## 実装の推奨順序
1. organizations / role / マルチテナント基盤(既存sitesにorganization_id追加) ✅
2. メンバー管理 + 誕生日抽出 ✅
3. 会議管理 + 次第(agenda_items) + Zip議案リンク ✅
4. ダッシュボード4ペイン(カレンダー埋め込み含む) ✅
5. 資料置き場 ✅
6. 基本設定(ヘッダー画像・組織情報・一括ダウンロード) ✅
7. LP + 組織登録フロー ✅
8. 課金(Stripe + Laravel Cashier、14日間無料トライアル) ✅