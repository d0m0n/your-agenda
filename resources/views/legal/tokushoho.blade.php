<x-legal-layout title="特定商取引法に基づく表記">
    @php
        $isPlaceholder = fn (?string $value) => $value === null || str_contains($value, '［');
        $monthlyPrice = number_format(config('billing.monthly_price_yen'));
        $trialDays = config('billing.trial_days');
    @endphp

    <p>
        本ページは、特定商取引法(通信販売)第11条に基づく表記です。
        「あなた(の)次第」(以下「本サービス」といいます)は月額課金制のサブスクリプションサービスです。
    </p>

    <dl>
        <div>
            <dt>販売業者</dt>
            <dd class="{{ $isPlaceholder(config('legal.operator_name')) ? 'legal-placeholder' : '' }}">{{ config('legal.operator_name') }}</dd>
        </div>

        <div>
            <dt>運営者</dt>
            <dd class="{{ $isPlaceholder(config('legal.operator_representative')) ? 'legal-placeholder' : '' }}">{{ config('legal.operator_representative') ?: '［個人事業主の場合、屋号に加えて運営者の本名を入力してください(特定商取引法上の必須事項)］' }}</dd>
        </div>

        @if (config('legal.disclose_address_on_request'))
            <div>
                <dt>所在地・電話番号</dt>
                <dd>ご請求いただいた場合には、遅滞なく開示いたします。開示をご希望の場合は下記メールアドレスまでご連絡ください。</dd>
            </div>
        @else
            <div>
                <dt>所在地</dt>
                <dd class="{{ $isPlaceholder(config('legal.address')) ? 'legal-placeholder' : '' }}">{{ config('legal.address') }}</dd>
            </div>

            <div>
                <dt>電話番号</dt>
                <dd class="{{ $isPlaceholder(config('legal.phone')) ? 'legal-placeholder' : '' }}">{{ config('legal.phone') }}</dd>
            </div>
        @endif

        <div>
            <dt>メールアドレス</dt>
            <dd class="{{ $isPlaceholder(config('legal.contact_email')) ? 'legal-placeholder' : '' }}">{{ config('legal.contact_email') }}</dd>
        </div>

        <div>
            <dt>提供するサービス</dt>
            <dd>会議・次第(進行表)・メンバー情報・資料を組織単位で一元管理できるクラウドサービス「あなた(の)次第」の利用</dd>
        </div>

        <div>
            <dt>販売価格</dt>
            <dd>
                月額{{ $monthlyPrice }}円(税込)。
                登録から{{ $trialDays }}日間は無料でお試しいただけます(クレジットカード登録不要)。
                トライアル終了後、継続利用にはお支払い情報のご登録が必要です。
            </dd>
        </div>

        <div>
            <dt>商品代金以外の必要料金</dt>
            <dd>なし(インターネット接続に関する通信料等はお客様のご負担となります)。</dd>
        </div>

        <div>
            <dt>お支払い方法</dt>
            <dd>クレジットカード決済(Stripe, Inc.が提供する決済システムを利用します。カード情報は本サービスのサーバーには保存されません)。</dd>
        </div>

        <div>
            <dt>お支払い時期</dt>
            <dd>お支払い情報のご登録時に決済され、以降は毎月同日に自動的に課金されます。</dd>
        </div>

        <div>
            <dt>サービス提供時期</dt>
            <dd>お支払い手続き完了後、直ちにご利用いただけます。</dd>
        </div>

        <div>
            <dt>解約・返金について</dt>
            <dd>
                サブスクリプションの性質上、日割りでの返金は行っておりません。
                解約はお客様のマイページ(お支払い管理画面)からいつでも手続きいただけ、
                解約手続き後は次回の請求が停止します(既にお支払いいただいた期間の途中解約による返金はございません)。
            </dd>
        </div>

        <div>
            <dt>動作環境</dt>
            <dd>インターネット接続環境、および主要ブラウザ(Google Chrome、Safari、Microsoft Edge等)の最新版。</dd>
        </div>
    </dl>

    <p class="mt-8 text-xs">
        制定日: {{ now()->jst()->format('Y年n月j日') }}
    </p>
</x-legal-layout>
