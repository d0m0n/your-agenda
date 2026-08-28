<?php

return [
    // Anthropic APIキー。取得は https://console.anthropic.com 。
    // .envに ANTHROPIC_API_KEY として設定する(Bladeや設定画面には一切出力しない)。
    'api_key' => env('ANTHROPIC_API_KEY'),

    // 議事録生成に使うモデル。ユーザー指定によりclaude-haiku-4-5固定
    // (Opus/Sonnet等への切り替えはこの値をenvで上書きするだけで良く、
    // コード変更は不要)。200Kコンテキストモデルのため、PDF添付は
    // 100ページ上限(1Mコンテキストモデルの600ページ上限とは異なる)。
    'model' => env('CLAUDE_MODEL', 'claude-haiku-4-5'),

    // 生成される議事録本文の出力トークン上限。
    'max_tokens' => (int) env('CLAUDE_MAX_TOKENS', 8000),

    // Anthropic SDKのHTTPタイムアウト(秒)。添付資料が多い会議は数十秒
    // かかることがあるため、SDK既定(10分)より短いが余裕を持たせた値にする。
    // 本番(さくらのレンタルサーバー)にはキューワーカーが常駐していないため、
    // 議事録生成は1つのHTTPリクエスト内で同期的に完結させる設計になっている
    // (CLAUDE.mdの「AI議事録生成」参照)。
    'timeout_seconds' => (int) env('CLAUDE_TIMEOUT_SECONDS', 90),

    // PDFページ数のソフト上限。ライブラリ無しでは正確なページ数を数えられない
    // ため、あくまで画面上の事前警告用の目安として使う(実際の可否はAPI呼び出し
    // 自体のエラーハンドリングに委ねる)。
    'soft_max_pdf_pages' => (int) env('CLAUDE_SOFT_MAX_PDF_PAGES', 80),

    // 添付コンテンツ(議案ファイル・資料)の合計バイト数の上限。Anthropic APIの
    // 32MBリクエスト上限に対し、base64化による約1.33倍の膨張や次第テキスト・
    // 文字起こし分の余裕を見込んで、生バイト数ベースで控えめに設定する。
    'max_attachment_bytes' => (int) env('CLAUDE_MAX_ATTACHMENT_BYTES', 20 * 1024 * 1024),

    // 1回の生成で読み込む添付(議案ファイル+資料)の最大件数。
    'max_attachments' => (int) env('CLAUDE_MAX_ATTACHMENTS', 20),
];
