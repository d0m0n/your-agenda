<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class MemberCsvService
{
    /**
     * Japanese CSV header => members column, in display order.
     * photo_path is intentionally excluded: CSV isn't a practical way to
     * carry image data, photos are uploaded per-member instead.
     *
     * @var array<string, string>
     */
    private const COLUMNS = [
        '氏名' => 'name',
        'よみがな' => 'name_kana',
        'ローマ字表記' => 'name_romaji',
        '生年月日' => 'birth_date',
        '所属企業' => 'company',
        '電話番号' => 'phone',
        'メールアドレス' => 'email',
        'LINE ID' => 'line_id',
        'Xアカウント' => 'x_account',
        'Instagramアカウント' => 'instagram_account',
        'Facebookアカウント' => 'facebook_account',
        'TikTokアカウント' => 'tiktok_account',
        '趣味' => 'hobby',
        '座右の銘' => 'motto',
    ];

    public function template(): string
    {
        return $this->buildCsv([array_fill_keys(array_keys(self::COLUMNS), '')]);
    }

    public function export(Collection $members): string
    {
        $rows = $members->map(function (Member $member) {
            $row = [];
            foreach (self::COLUMNS as $header => $column) {
                $value = $member->{$column};
                $row[$header] = $column === 'birth_date' && $value ? $value->format('Y-m-d') : (string) $value;
            }

            return $row;
        })->all();

        return $this->buildCsv($rows);
    }

    /**
     * @return array{created: int, skipped: list<array{row: int, reason: string}>}
     */
    public function import(UploadedFile $file, int $organizationId): array
    {
        $rows = $this->readCsv($file);

        if (empty($rows)) {
            return ['created' => 0, 'skipped' => []];
        }

        $header = array_map('trim', array_shift($rows));
        $created = 0;
        $skipped = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +1 for header, +1 for 1-based display

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // silently skip blank lines
            }

            $data = [];
            foreach ($header as $i => $columnHeader) {
                $data[$columnHeader] = trim($row[$i] ?? '');
            }

            $name = $data['氏名'] ?? '';
            if ($name === '') {
                $skipped[] = ['row' => $rowNumber, 'reason' => '氏名が空です'];

                continue;
            }

            $attributes = ['organization_id' => $organizationId, 'name' => $name];
            foreach (self::COLUMNS as $csvHeader => $column) {
                if ($column === 'name') {
                    continue;
                }
                $value = $data[$csvHeader] ?? '';
                $attributes[$column] = $value === '' ? null : $value;
            }

            if ($attributes['birth_date'] && ! $this->isValidDate($attributes['birth_date'])) {
                $skipped[] = ['row' => $rowNumber, 'reason' => '生年月日の形式が不正です(YYYY-MM-DD)'];

                continue;
            }

            Member::create($attributes);
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * @param  list<array<string, string>>  $rows
     */
    private function buildCsv(array $rows): string
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel compatibility
        fputcsv($stream, array_keys(self::COLUMNS));

        foreach ($rows as $row) {
            fputcsv($stream, array_values($row));
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }

    /**
     * @return list<list<string>>
     */
    private function readCsv(UploadedFile $file): array
    {
        $contents = file_get_contents($file->getRealPath());
        $contents = $this->toUtf8($contents);

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $contents);
        rewind($stream);

        $rows = [];
        while (($row = fgetcsv($stream)) !== false) {
            $rows[] = $row;
        }
        fclose($stream);

        return $rows;
    }

    private function toUtf8(string $contents): string
    {
        // Strip a UTF-8 BOM if present.
        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            return substr($contents, 3);
        }

        $encoding = mb_detect_encoding($contents, ['UTF-8', 'SJIS-win', 'SJIS', 'EUC-JP'], true) ?: 'SJIS-win';

        return $encoding === 'UTF-8' ? $contents : mb_convert_encoding($contents, 'UTF-8', $encoding);
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value;
    }
}
