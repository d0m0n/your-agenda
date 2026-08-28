<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\NotFoundException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Exceptions\MinutesGenerationException;
use Throwable;

/**
 * 次第・添付ファイルの内容(MeetingMinutesContextBuilderが組み立てたコンテンツ
 * ブロック)と文字起こしテキストをClaude APIに渡し、議事録ドラフトを生成する。
 * テストでモックしやすいようfinalにしない。
 */
class ClaudeMinutesGenerator
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
あなたは委員会・団体の会議において議事録作成を担当する書記です。
以下の情報をもとに、日本語で正式な議事録のドラフトを作成してください。

- 次第(会議の議題構成・担当者)
- 次第に添付された議案ファイル・資料の内容
- 会議の文字起こしテキスト

議事録は次第の項目ごとに区切り、各項目について「議論の概要」「決定事項」
「今後の対応(action item)」を、文字起こしや添付資料から実際に読み取れる
範囲でまとめてください。出席者・決定事項・対応事項について、根拠となる
記載が無い内容を推測で書き加えないでください。分からない場合は
「(文字起こしからは確認できませんでした)」のように明記してください。

出力形式は必ずプレーンテキストにしてください。Markdown記法(#による見出し、
**による太字、-による箇条書き等)は一切使わないでください。見出しは
「【開会宣言】」のように【】で囲み、箇条書きは行頭に「・」を使ってください。
PROMPT;

    public function __construct(private readonly Client $client) {}

    /**
     * @param  array<int, array<string, mixed>>  $contentBlocks
     */
    public function generate(string $transcript, array $contentBlocks): string
    {
        $userContent = [
            ...$contentBlocks,
            ['type' => 'text', 'text' => "――― 会議の文字起こし ―――\n{$transcript}"],
        ];

        try {
            $message = $this->client->messages->create(
                model: (string) config('claude.model'),
                maxTokens: (int) config('claude.max_tokens'),
                system: self::SYSTEM_PROMPT,
                messages: [['role' => 'user', 'content' => $userContent]],
            );
        } catch (NotFoundException $e) {
            throw new MinutesGenerationException('議事録の生成サービスに接続できませんでした。運営者にお問い合わせください。', previous: $e);
        } catch (RateLimitException $e) {
            throw new MinutesGenerationException('現在アクセスが集中しています。しばらく待ってから再度お試しください。', previous: $e);
        } catch (APIStatusException $e) {
            throw new MinutesGenerationException(match ($e->type?->value) {
                'overloaded_error' => '現在サービスが混み合っています。しばらく待ってから再度お試しください。',
                'invalid_request_error' => '添付ファイルの合計ページ数または容量が上限を超えている可能性があります。次第から一部の議案ファイル・資料のリンクを外すか、内容を減らして再度お試しください。',
                default => '議事録の生成に失敗しました。しばらく待ってから再度お試しください。',
            }, previous: $e);
        } catch (APIConnectionException $e) {
            throw new MinutesGenerationException('議事録生成サービスへの接続に失敗しました。通信環境をご確認のうえ再度お試しください。', previous: $e);
        } catch (Throwable $e) {
            throw new MinutesGenerationException('議事録の生成に失敗しました。しばらく待ってから再度お試しください。', previous: $e);
        }

        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                return $block->text;
            }
        }

        throw new MinutesGenerationException('議事録の生成に失敗しました。しばらく待ってから再度お試しください。');
    }
}
