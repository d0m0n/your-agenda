<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ランディングページ掲載用スクリーンショットを撮るためだけのサンプル組織
 * 「おまつり実行委員会」を作成する。DatabaseSeeder::run()からは呼ばず、
 * 必要な時だけ単独実行する想定:
 *   php artisan db:seed --class=OmatsuriDemoSeeder
 * 再実行すると同名の組織を一度削除してから作り直す(関連レコードは
 * organization_id の cascadeOnDelete で一緒に消える)。
 */
class OmatsuriDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Organization::where('name', 'おまつり実行委員会')->delete();

            $organization = Organization::create([
                'name' => 'おまつり実行委員会',
            ]);
            // トライアル切れ・未契約でのペイウォール表示を気にせずスクリーンショットを
            // 撮れるよう、無償提供モードを有効にしておく。
            $organization->forceFill(['free_access_enabled' => true])->save();

            User::factory()->create([
                'organization_id' => $organization->id,
                'role' => UserRole::General,
                'name' => '鈴木一郎',
                'email' => 'omatsuri@example.com',
            ]);

            $positions = $this->createPositions($organization);
            $members = $this->createMembers($organization, $positions);
            $this->createMeetings($organization, $members);
        });
    }

    /**
     * @return array<string, Position>
     */
    private function createPositions(Organization $organization): array
    {
        $positions = [];

        foreach ([
            [1, '実行委員長'],
            [2, '副委員長'],
            [3, '事務局長'],
            [4, '会計'],
            [5, '委員'],
        ] as [$serialNumber, $name]) {
            $positions[$name] = Position::create([
                'organization_id' => $organization->id,
                'serial_number' => $serialNumber,
                'name' => $name,
            ]);
        }

        return $positions;
    }

    /**
     * @param  array<string, Position>  $positions
     * @return array<string, Member>
     */
    private function createMembers(Organization $organization, array $positions): array
    {
        // [通し番号, 氏名, フリガナ, ローマ字, 生年月日, 性別, 役職, 勤務先, 電話, メール, LINE ID, 趣味, 座右の銘]
        $memberDefinitions = [
            [1, '鈴木一郎', 'すずきいちろう', 'Ichiro Suzuki', '1986-03-12', 'male', '実行委員長', '鈴木酒店', '090-1234-5601', 'suzuki.ichiro@example.com', 'suzuki_ichiro', '釣り', '一期一会'],
            [2, '佐藤久美子', 'さとうくみこ', 'Kumiko Sato', '1988-07-08', 'female', '副委員長', 'カフェ ひだまり', '090-1234-5602', 'sato.kumiko@example.com', 'sato_kumiko', 'ガーデニング', '笑う門には福来る'],
            [3, '高橋直樹', 'たかはしなおき', 'Naoki Takahashi', '1991-02-18', 'male', '事務局長', '高橋工務店', '090-1234-5603', 'takahashi.naoki@example.com', 'takahashi_naoki', '将棋', '継続は力なり'],
            [4, '田中恵美', 'たなかえみ', 'Emi Tanaka', '1993-05-20', 'female', '会計', '田中税理士事務所', '090-1234-5604', 'tanaka.emi@example.com', 'tanaka_emi', '手芸', '塵も積もれば山となる'],
            [5, '伊藤大輔', 'いとうだいすけ', 'Daisuke Ito', '1995-06-08', 'male', '委員', '伊藤青果店', '090-1234-5605', 'ito.daisuke@example.com', 'ito_daisuke', 'サッカー観戦', '七転び八起き'],
            [6, '中村さくら', 'なかむらさくら', 'Sakura Nakamura', '1997-02-14', 'female', '委員', 'フラワーショップ中村', '090-1234-5606', 'nakamura.sakura@example.com', 'nakamura_sakura', '写真撮影', '一日一善'],
            [7, '小林翔太', 'こばやししょうた', 'Shota Kobayashi', '1999-06-30', 'male', '委員', '小林精肉店', '090-1234-5607', 'kobayashi.shota@example.com', 'kobayashi_shota', '筋トレ', '有言実行'],
            [8, '山本真由美', 'やまもとまゆみ', 'Mayumi Yamamoto', '2001-04-03', 'female', '委員', '山本呉服店', '090-1234-5608', 'yamamoto.mayumi@example.com', 'yamamoto_mayumi', '茶道', '温故知新'],
            [9, '加藤翼', 'かとうつばさ', 'Tsubasa Kato', '2003-07-19', 'male', '委員', '加藤自動車整備', '090-1234-5609', 'kato.tsubasa@example.com', 'kato_tsubasa', 'バイク', '初心忘るべからず'],
            [10, '吉田愛', 'よしだあい', 'Ai Yoshida', '2004-01-25', 'female', '委員', '吉田パン工房', '090-1234-5610', 'yoshida.ai@example.com', 'yoshida_ai', 'お菓子作り', '笑顔第一'],
        ];

        $members = [];

        foreach ($memberDefinitions as [$serialNumber, $name, $kana, $romaji, $birthDate, $gender, $positionName, $company, $phone, $email, $lineId, $hobby, $motto]) {
            $members[$name] = Member::create([
                'organization_id' => $organization->id,
                'position_id' => $positions[$positionName]->id,
                'serial_number' => $serialNumber,
                'name' => $name,
                'name_kana' => $kana,
                'name_romaji' => $romaji,
                'birth_date' => $birthDate,
                'gender' => $gender,
                'company' => $company,
                'phone' => $phone,
                'email' => $email,
                'line_id' => $lineId,
                'hobby' => $hobby,
                'motto' => $motto,
            ]);
        }

        return $members;
    }

    /**
     * @param  array<string, Member>  $members
     */
    private function createMeetings(Organization $organization, array $members): void
    {
        $venue = '湊町コミュニティセンター 2階会議室';
        $venueAddress = '湊町1丁目2番3号';
        $venueMapUrl = 'https://www.google.com/maps/search/?api=1&query='.urlencode($venue);

        // 秋まつりの開催に向けて、毎月1回のペースで実行委員会を開く想定。
        // held_atはタイトル通り第1回〜第10回の順で、過去分・今後の予定分が
        // 両方できるよう当日をまたぐ月次スケジュールにしてある。
        $meetingDefinitions = [
            ['2026-01-15 19:00', [
                ['実行委員長あいさつ', '鈴木一郎'],
                ['今年度まつりの開催概要について', '鈴木一郎'],
                ['実行委員の役割分担について', '高橋直樹'],
            ]],
            ['2026-02-12 19:00', [
                ['前回議事録の確認', '高橋直樹'],
                ['予算案について', '田中恵美'],
                ['出店募集の進め方について', '伊藤大輔'],
            ]],
            ['2026-03-12 19:00', [
                ['出店募集要項の決定', '伊藤大輔', ['飲食ブースの出店条件', '物販ブースの出店条件']],
                ['会場レイアウトの検討', '中村さくら'],
                ['警備・交通整理計画', '加藤翼'],
            ]],
            ['2026-04-09 19:00', [
                ['出店応募状況の報告', '伊藤大輔'],
                ['広報・チラシデザインについて', '山本真由美'],
                ['協賛金集めの進捗', '田中恵美'],
            ]],
            ['2026-05-14 19:00', [
                ['ステージイベント企画について', '小林翔太', ['地元太鼓演奏', '盆踊り', '抽選会']],
                ['会場設営スケジュールの確認', '高橋直樹'],
                ['ボランティアスタッフの募集', '中村さくら'],
            ]],
            ['2026-06-11 19:00', [
                ['出店者説明会の実施報告', '伊藤大輔'],
                ['熱中症・防犯対策について', '加藤翼'],
                ['予算執行状況の報告', '田中恵美'],
            ]],
            ['2026-07-09 19:00', [
                ['前回議事録の確認', '高橋直樹'],
                ['チラシ・ポスター配布状況', '山本真由美'],
                ['当日タイムスケジュール(案)', '鈴木一郎'],
            ]],
            ['2026-08-13 19:00', [
                ['当日運営マニュアルの確認', '高橋直樹'],
                ['荒天時の対応について', '鈴木一郎'],
                ['各班の最終確認', '吉田愛'],
            ]],
            ['2026-09-10 19:00', [
                ['最終予算・収支見込みの報告', '田中恵美'],
                ['当日役割分担の最終確認', '高橋直樹'],
                ['前日準備の段取り', '伊藤大輔'],
            ]],
            ['2026-10-08 19:00', [
                ['最終確認事項', '鈴木一郎'],
                ['緊急連絡体制の共有', '高橋直樹'],
                ['まつり成功に向けて', '鈴木一郎'],
            ]],
        ];

        $memos = [
            6 => '会場は例年通り第2駐車場を臨時駐車場として開放予定。',
            9 => '荒天の場合は前日18時までにLINEで連絡します。',
        ];

        foreach ($meetingDefinitions as $index => [$heldAt, $items]) {
            $round = $index + 1;
            $heldAtDate = Carbon::parse($heldAt);

            $meeting = Meeting::create([
                'organization_id' => $organization->id,
                'name' => "第{$round}回実行委員会",
                'held_at' => $heldAtDate,
                'ends_at' => $heldAtDate->copy()->addMinutes(90),
                'location' => $venue,
                'venue_address' => $venueAddress,
                'venue_map_url' => $venueMapUrl,
                'wifi_ssid' => 'minato-guest',
                'wifi_password' => 'omatsuri2026',
                'memo' => $memos[$round] ?? null,
            ]);

            $order = 0;
            foreach ($items as $item) {
                $order++;
                [$title, $assigneeName] = $item;
                $parent = $meeting->agendaItems()->create([
                    'order' => $order,
                    'title' => $title,
                    'member_id' => $members[$assigneeName]->id,
                ]);

                $childOrder = 0;
                foreach ($item[2] ?? [] as $childTitle) {
                    $childOrder++;
                    $meeting->agendaItems()->create([
                        'parent_id' => $parent->id,
                        'order' => $childOrder,
                        'title' => $childTitle,
                    ]);
                }
            }
        }
    }
}
