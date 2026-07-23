<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\Organization;

/**
 * 案内文(PDF・メール・LINE)の組み立てを担当する。
 *
 * 3層構造になっている:
 *   1. 組み込みの既定テンプレート(プレースホルダー入り、下記の定数)
 *   2. 組織が基本設定画面で上書きしたテンプレート(同じくプレースホルダー入り)
 *   3. 会議ごとに手直しして保存された最終テキスト(プレースホルダーなし、
 *      呼び出し側で $meeting->invitation_{type}_body ?? を使って優先させる)
 *
 * このサービスが担当するのは1と2、およびそれをテンプレートとして
 * 会議データで置換する処理まで。
 */
class MeetingInvitationTemplateService
{
    private const TYPES = ['pdf', 'email', 'line'];

    /**
     * テンプレート内で使える置換用プレースホルダーの一覧(表示用)。
     */
    public const PLACEHOLDERS = [
        '{{組織名}}', '{{会議名}}', '{{開催日時}}', '{{開催場所}}',
        '{{WiFi_SSID}}', '{{WiFiパスワード}}', '{{議題}}', '{{公開URL}}',
        '{{住所}}', '{{地図URL}}', '{{懇親会情報}}', '{{宿泊情報}}', '{{時候の挨拶}}',
    ];

    /**
     * 月ごとの時候の挨拶。案内文を開いた(作成した)月にあわせて自動で選ばれる。
     */
    private const SEASONAL_GREETINGS = [
        1 => '新春の候',
        2 => '立春の候',
        3 => '早春の候',
        4 => '陽春の候',
        5 => '新緑の候',
        6 => '梅雨の候',
        7 => '盛夏の候',
        8 => '残暑の候',
        9 => '初秋の候',
        10 => '仲秋の候',
        11 => '晩秋の候',
        12 => '師走の候',
    ];

    private const PDF_DEFAULT = <<<'TEXT'
{{組織名}}
メンバー各位

>>{{組織名}}

{{会議名}} 開催のご案内

拝啓　{{時候の挨拶}}、平素は格別のご高配を賜り、厚く御礼申し上げます。
さて、下記のとおり{{会議名}}を開催いたしますので、
ご多用のところ誠に恐れ入りますが、ご出席くださいますようご案内申し上げます。

敬具

記

一、日　時　{{開催日時}}
一、場　所　{{開催場所}}

【議題】
{{議題}}

以上
TEXT;

    private const EMAIL_DEFAULT = <<<'TEXT'
件名: 【ご案内】{{会議名}}

{{組織名}} 会員各位

お世話になっております。
下記のとおり{{会議名}}を開催いたします。
ご多用のところ恐れ入りますが、ご出席のほどよろしくお願いいたします。

■日時
{{開催日時}}

■場所
{{開催場所}}

■議題
{{議題}}

お手数ですが、出欠のご連絡をお願いいたします。
よろしくお願いいたします。
TEXT;

    private const LINE_DEFAULT = <<<'TEXT'
【{{会議名}}のご案内】

日時: {{開催日時}}
場所: {{開催場所}}

議題:
{{議題}}

出欠のご連絡をお願いします。
TEXT;

    public function isValidType(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }

    /**
     * 組み込みの既定テンプレート(プレースホルダーのまま、未置換)。
     */
    public function builtInDefault(string $type): string
    {
        return match ($type) {
            'pdf' => self::PDF_DEFAULT,
            'email' => self::EMAIL_DEFAULT,
            'line' => self::LINE_DEFAULT,
            default => '',
        };
    }

    /**
     * 組織のテンプレート(基本設定で上書きされていればそれ、なければ
     * 組み込みの既定)。プレースホルダーはまだ置換していない状態。
     */
    public function organizationTemplate(Organization $organization, string $type): string
    {
        return $organization->{'invitation_'.$type.'_template'} ?? $this->builtInDefault($type);
    }

    /**
     * 会議の案内文本文。組織のテンプレート(上書き済みならそれ、なければ
     * 組み込みの既定)を、この会議のデータで置換して返す。
     */
    public function template(Meeting $meeting, string $type): string
    {
        return $this->render($this->organizationTemplate($meeting->organization, $type), $meeting);
    }

    private function render(string $template, Meeting $meeting): string
    {
        $replacements = [
            '{{組織名}}' => $meeting->organization->name,
            '{{会議名}}' => $meeting->name,
            '{{開催日時}}' => $meeting->scheduleLabel() ?? '未定',
            '{{開催場所}}' => $meeting->location ?? '未定',
            '{{WiFi_SSID}}' => $meeting->wifi_ssid ?? '',
            '{{WiFiパスワード}}' => $meeting->wifi_password ?? '',
            '{{議題}}' => $this->agendaList($meeting),
            '{{公開URL}}' => $meeting->publicUrl() ?? '',
            '{{住所}}' => $meeting->venue_address ?? '',
            '{{地図URL}}' => $meeting->venue_map_url ?? '',
            '{{懇親会情報}}' => $meeting->social_event_info ?? '',
            '{{宿泊情報}}' => $meeting->recommended_hotel_info ?? '',
            '{{時候の挨拶}}' => $this->seasonalGreeting(),
        ];

        return strtr($template, $replacements);
    }

    /**
     * 案内文を作成(表示)した時点の月に応じた時候の挨拶。
     */
    private function seasonalGreeting(): string
    {
        return self::SEASONAL_GREETINGS[now()->month];
    }

    private function agendaList(Meeting $meeting): string
    {
        $titles = $meeting->topLevelAgendaItems->pluck('title');

        if ($titles->isEmpty()) {
            return '(次第は準備中です)';
        }

        return $titles->map(fn ($title, $index) => ($index + 1).'. '.$title)->implode("\n");
    }
}
