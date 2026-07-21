# あなた次第(Your agenda)

## 概要
青年会議所(JC)の委員会運営を楽にするWebサービス。
委員会組織ごとに契約し、会議・次第・メンバー・資料を一元管理できる。
アップロードしたZipファイル内のHTML議案(gian.htm)をURLひとつで共有できる。
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
- 次は 7(LP+組織登録フロー)、8(課金)を実装する

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
  public の内容を公開ディレクトリに配置(index.php のパス書き換え)
- SSH接続可、composer は SSH 経由で実行
- 本番でSailは使わない(Dockerは開発専用)。デプロイは git pull +
  composer install --no-dev + migrate を想定
- cron はコントロールパネルから設定(Laravelスケジューラ用に
  php artisan schedule:run を毎分または最小間隔で登録)

## ユーザー権限(2種類)
1. 一般ユーザー
   - 委員会組織ごとに契約するアカウント
   - 組織情報・メンバー情報・会議・次第・資料の管理(全CRUD)ができる
2. オブザーブユーザー
   - 一般ユーザー(の組織)に紐付く閲覧専用アカウント
   - ダッシュボード・会議画面・資料の閲覧のみ可能
   - ナビゲーションメニュー(管理系)は一切表示しない
- 認証は Laravel Breeze(Bladeスタック)
- 認可は role カラム + Policy/Gate で制御(すべての管理操作をガード)
- マルチテナント: 全データは organization_id でスコープする。
  他組織のデータには絶対にアクセスできないこと(グローバルスコープ推奨)

## データ構造(想定)
- organizations: name, header_image_path, google_calendar_id,
  contracted_at, plan_status, timestamps
- users: organization_id, role (general / observer), name, email, password
  - オブザーブユーザーは一般ユーザーが招待・作成する
- members: organization_id, name, name_kana, name_romaji, birth_date,
  company, phone, email, line_id, x_account, instagram_account,
  facebook_account, tiktok_account, hobby, motto, photo_path, timestamps
  - ログインユーザーとは別概念。誕生日表示・議題担当者に使う
  - 必須項目は name のみ、他は任意
  - CSVの一括登録・テンプレートDL・一括エクスポートに対応
    (photo_pathはCSVの対象外。写真はメンバーごとに個別アップロード)
  - 顔写真はjpg/png/webpのみ許可し、リサイズ・再エンコードして保存する
- meetings: organization_id, name, held_at, location,
  wifi_ssid, wifi_password, header_image_path, timestamps
- agenda_items(次第): meeting_id, order, title, member_id(担当者),
  site_id(nullable, Zip議案へのリンク), timestamps
- sites(Zip議案): organization_id, uuid, title, original_filename,
  index_path, user_id, timestamps
- materials(資料置き場): organization_id, title, file_path,
  original_filename, user_id, timestamps

## 画面構成

### ダッシュボード(トップページ・4ペイン構造)
- 上部に組織のヘッダー画像を表示(基本設定でアップロード)
- ペイン1: 会議一覧(直近の会議、会議画面へのリンク)
- ペイン2: カレンダー(組織で設定したGoogleカレンダーをiframe埋め込み表示)
- ペイン3: 今月の誕生日メンバー(membersのbirth_dateから当月分を抽出)
- ペイン4: その他の資料置き場(materialsの一覧・ダウンロード)
- オブザーブユーザーも閲覧可能

### ナビゲーションメニュー(一般ユーザーのみ表示)
1. 会議管理
2. カレンダー管理(GoogleカレンダーIDの設定)
3. メンバー管理(members の CRUD、誕生日登録)
4. 資料管理(資料置き場にアップするデータの管理画面)
5. 基本設定
   - ダッシュボードの組織ヘッダー画像の設定
   - 組織情報の編集
   - 次第の一括ダウンロード機能(下記参照)
- オブザーブユーザーにはこれらのメニューを一切表示しない(Blade側の
  @can だけでなく、ルート側でも必ずミドルウェア/Policyでガードする)

### 会議画面
- 会議ごとのヘッダー画像をトップに表示(設定可能)
- アジェンダ(次第)を表示。各議題にアップ済みZip議案(gian.htm)をリンクし、
  クリックで議案を確認できる
- 次第管理メニュー(一般ユーザーのみ):
  会議名 / 開催日時 / 開催場所 / Wi-Fi情報 / 次第 / 議題の担当者 /
  会議ヘッダー画像 を追加・入力・編集・削除できる

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

## セキュリティ要件(必ず守ること)
- Zip Slip対策: エントリ名に「..」や先頭「/」を含む場合は拒否
- Zip爆弾対策: 展開後合計100MB以下、ファイル数1000以下
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