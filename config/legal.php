<?php

return [
    // 特定商取引法に基づく表記・利用規約・プライバシーポリシーで共通して使う
    // 運営者情報。3ページに同じ内容を書き散らさず、ここ(または.env)を
    // 直せば全ページに反映されるようにしている。公開前に必ず実際の内容へ
    // 差し替えること(現状はダミーのプレースホルダーが入っている)。
    'operator_name' => env('LEGAL_OPERATOR_NAME', '［事業者名または運営者氏名を入力してください］'),

    // 個人事業主が屋号(operator_name)で表記する場合、特定商取引法上は
    // 運営者の本名(個人名)の併記が原則必要(屋号だけでは不可)。
    // 法人の場合は運営統括責任者名として使う。
    'operator_representative' => env('LEGAL_OPERATOR_REPRESENTATIVE'),

    // 所在地・電話番号を開示請求対応の文言(「請求があれば遅滞なく開示する」)
    // に代えるかどうか。個人事業主向けの特例で、要件の詳細は消費者庁の
    // ガイドライン確認、または行政書士等への確認を推奨(CLAUDE.md参照)。
    // falseにすると、下記address/phoneの実際の値を表記する形に切り替わる。
    'disclose_address_on_request' => env('LEGAL_DISCLOSE_ADDRESS_ON_REQUEST', true),

    // disclose_address_on_requestがfalseの場合にのみ表示される所在地。
    'address' => env('LEGAL_ADDRESS', '［所在地を入力してください］'),

    // disclose_address_on_requestがfalseの場合にのみ表示される電話番号。
    'phone' => env('LEGAL_PHONE', '［電話番号を入力してください］'),

    // 特商法表記・お問い合わせ窓口として掲載する連絡先メールアドレス。
    'contact_email' => env('LEGAL_CONTACT_EMAIL', '［連絡先メールアドレスを入力してください］'),
];
