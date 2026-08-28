<?php

namespace App\Services;

use App\Models\AgendaItem;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Site;
use Illuminate\Support\Facades\Storage;

/**
 * 議事録AI生成のために、会議の次第構造と添付ファイル(議案ファイル・資料)の
 * 中身をClaude APIへ渡せる形のコンテンツブロックへ組み立てる。
 *
 * MeetingArchiveExportService(次第の一括ダウンロード)はmaterialをeager load
 * しておらず資料が漏れるが、ここでは同じ欠陥を再現しないよう両方読み込む。
 *
 * 対応する添付形式はPDF・画像(jpg/jpeg/png/gif/webp)・Zip議案内のgian.htm
 * (プレーンHTMLとして読む)・txt/csv資料のみ。docx/xlsx/pptx等は変換用の
 * ライブラリを導入しない方針(さくらのレンタルサーバーはroot権限が無い)のため、
 * 自動読み込み対象外とし、理由付きでskippedに記録する(黙って無視しない)。
 */
class MeetingMinutesContextBuilder
{
    private const IMAGE_MEDIA_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];

    private const HTML_EXTENSIONS = ['htm', 'html'];

    private const TEXT_MATERIAL_EXTENSIONS = ['txt', 'csv'];

    private int $bytesUsed = 0;

    private int $attachmentsUsed = 0;

    /**
     * @return array{content_blocks: array<int, array<string, mixed>>, skipped: array<int, array{title: string, reason: string}>}
     */
    public function build(Meeting $meeting): array
    {
        $meeting->loadMissing([
            'topLevelAgendaItems.member.position',
            'topLevelAgendaItems.site',
            'topLevelAgendaItems.material',
            'topLevelAgendaItems.children.member.position',
            'topLevelAgendaItems.children.site',
            'topLevelAgendaItems.children.material',
        ]);

        $this->bytesUsed = 0;
        $this->attachmentsUsed = 0;
        $skipped = [];

        $blocks = [
            ['type' => 'text', 'text' => $this->buildAgendaOutline($meeting)],
        ];

        foreach ($meeting->topLevelAgendaItems as $item) {
            $this->appendItemContent($item, $blocks, $skipped);

            foreach ($item->children as $child) {
                $this->appendItemContent($child, $blocks, $skipped);
            }
        }

        return [
            'content_blocks' => $blocks,
            'skipped' => $skipped,
        ];
    }

    private function buildAgendaOutline(Meeting $meeting): string
    {
        $lines = ["――― 次第: {$meeting->name} ―――"];

        foreach ($meeting->topLevelAgendaItems as $index => $item) {
            $lines[] = sprintf('%d. %s%s', $index + 1, $item->title, $this->assigneeSuffix($item));

            foreach ($item->children as $childIndex => $child) {
                $lines[] = sprintf('    %02d. %s%s', $childIndex + 1, $child->title, $this->assigneeSuffix($child));
            }
        }

        return implode("\n", $lines);
    }

    private function assigneeSuffix(AgendaItem $item): string
    {
        $label = $item->assigneeLabel();

        return $label ? "(担当: {$label})" : '';
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<int, array{title: string, reason: string}>  $skipped
     */
    private function appendItemContent(AgendaItem $item, array &$blocks, array &$skipped): void
    {
        if ($item->site) {
            $this->appendSiteContent($item, $item->site, $blocks, $skipped);

            return;
        }

        if ($item->material) {
            $this->appendMaterialContent($item, $item->material, $blocks, $skipped);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<int, array{title: string, reason: string}>  $skipped
     */
    private function appendSiteContent(AgendaItem $item, Site $site, array &$blocks, array &$skipped): void
    {
        $extension = strtolower((string) pathinfo($site->index_path, PATHINFO_EXTENSION));
        $path = "sites/{$site->uuid}/{$site->index_path}";
        $label = "議案ファイル: {$item->title} / {$site->title}";

        if (in_array($extension, self::HTML_EXTENSIONS, true)) {
            $bytes = Storage::disk('public')->get($path);

            if ($bytes === null) {
                $skipped[] = ['title' => $site->title, 'reason' => 'ファイルが見つかりませんでした'];

                return;
            }

            if (! $this->reserveBudget(strlen($bytes), $site->title, $skipped)) {
                return;
            }

            $blocks[] = ['type' => 'text', 'text' => "――― {$label} ―――\n".$this->htmlToPlainText($bytes)];

            return;
        }

        $this->appendFileAsBlock($path, 'public', $extension, $label, $site->title, $blocks, $skipped);
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<int, array{title: string, reason: string}>  $skipped
     */
    private function appendMaterialContent(AgendaItem $item, Material $material, array &$blocks, array &$skipped): void
    {
        $extension = strtolower((string) pathinfo($material->original_filename, PATHINFO_EXTENSION));
        $label = "資料: {$item->title} / {$material->title}";

        if (in_array($extension, self::TEXT_MATERIAL_EXTENSIONS, true)) {
            $bytes = Storage::disk('local')->get($material->file_path);

            if ($bytes === null) {
                $skipped[] = ['title' => $material->title, 'reason' => 'ファイルが見つかりませんでした'];

                return;
            }

            if (! $this->reserveBudget(strlen($bytes), $material->title, $skipped)) {
                return;
            }

            $blocks[] = ['type' => 'text', 'text' => "――― {$label} ―――\n".$this->toUtf8($bytes)];

            return;
        }

        if (array_key_exists($extension, self::IMAGE_MEDIA_TYPES) || $extension === 'pdf') {
            $this->appendFileAsBlock($material->file_path, 'local', $extension, $label, $material->title, $blocks, $skipped);

            return;
        }

        $skipped[] = ['title' => $material->title, 'reason' => "この形式は自動読み込み対象外です({$extension})"];
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<int, array{title: string, reason: string}>  $skipped
     */
    private function appendFileAsBlock(string $path, string $disk, string $extension, string $label, string $title, array &$blocks, array &$skipped): void
    {
        $bytes = Storage::disk($disk)->get($path);

        if ($bytes === null) {
            $skipped[] = ['title' => $title, 'reason' => 'ファイルが見つかりませんでした'];

            return;
        }

        if (! $this->reserveBudget(strlen($bytes), $title, $skipped)) {
            return;
        }

        $data = base64_encode($bytes);
        $blocks[] = ['type' => 'text', 'text' => "――― {$label} ―――"];

        if ($extension === 'pdf') {
            $blocks[] = [
                'type' => 'document',
                'source' => ['type' => 'base64', 'media_type' => 'application/pdf', 'data' => $data],
            ];

            return;
        }

        $blocks[] = [
            'type' => 'image',
            'source' => ['type' => 'base64', 'media_type' => self::IMAGE_MEDIA_TYPES[$extension], 'data' => $data],
        ];
    }

    /**
     * @param  array<int, array{title: string, reason: string}>  $skipped
     */
    private function reserveBudget(int $bytes, string $title, array &$skipped): bool
    {
        if ($this->attachmentsUsed >= (int) config('claude.max_attachments')) {
            $skipped[] = ['title' => $title, 'reason' => '添付件数の上限のため読み込めませんでした'];

            return false;
        }

        if ($this->bytesUsed + $bytes > (int) config('claude.max_attachment_bytes')) {
            $skipped[] = ['title' => $title, 'reason' => '容量上限のため読み込めませんでした'];

            return false;
        }

        $this->attachmentsUsed++;
        $this->bytesUsed += $bytes;

        return true;
    }

    /**
     * gian.htmはUTF-8とレガシーなShift_JIS(Word書き出し等)が混在するため、
     * <meta charset>宣言を優先して判定する(SiteZipInstallerが.htaccessで
     * AddDefaultCharset Offにしているのと同じ理由 — ブラウザは各ファイルの
     * 宣言を見て判断するが、Claudeに渡す前にこちら側でUTF-8へ揃える必要がある)。
     */
    private function htmlToPlainText(string $bytes): string
    {
        $text = strip_tags($this->toUtf8($bytes, $this->detectHtmlCharset($bytes)));

        return trim(preg_replace('/[ \t]+/', ' ', preg_replace('/\n{3,}/', "\n\n", $text)) ?? $text);
    }

    private function detectHtmlCharset(string $bytes): ?string
    {
        if (preg_match('/<meta[^>]+charset=["\']?\s*([\w-]+)/i', substr($bytes, 0, 4096), $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function toUtf8(string $bytes, ?string $knownCharset = null): string
    {
        $charset = $knownCharset ?: (mb_detect_encoding($bytes, ['UTF-8', 'SJIS-win', 'SJIS', 'EUC-JP'], true) ?: null);

        if (! $charset || strtoupper($charset) === 'UTF-8') {
            return $bytes;
        }

        return mb_convert_encoding($bytes, 'UTF-8', $charset);
    }
}
