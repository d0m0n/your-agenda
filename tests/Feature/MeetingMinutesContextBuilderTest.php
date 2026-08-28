<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Organization;
use App\Models\Site;
use App\Models\User;
use App\Services\MeetingMinutesContextBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MeetingMinutesContextBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function makeMeetingWithAgendaItem(): array
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        return [$organization, $user, $meeting];
    }

    public function test_txt_material_content_is_included_as_a_text_block(): void
    {
        Storage::fake('local');
        [$organization, $user, $meeting] = $this->makeMeetingWithAgendaItem();

        Storage::disk('local')->put('materials/memo.txt', '議題についての補足メモです。');
        $material = Material::factory()->for($organization, 'organization')->for($user, 'user')->create([
            'title' => '補足メモ',
            'file_path' => 'materials/memo.txt',
            'original_filename' => 'memo.txt',
        ]);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議案審議', 'material_id' => $material->id]);

        $result = app(MeetingMinutesContextBuilder::class)->build($meeting);

        $textBlocks = array_filter($result['content_blocks'], fn ($b) => $b['type'] === 'text');
        $combined = implode("\n", array_column($textBlocks, 'text'));

        $this->assertStringContainsString('議題についての補足メモです。', $combined);
        $this->assertEmpty($result['skipped']);
    }

    public function test_unsupported_material_format_is_skipped_with_a_reason(): void
    {
        Storage::fake('local');
        [$organization, $user, $meeting] = $this->makeMeetingWithAgendaItem();

        Storage::disk('local')->put('materials/report.docx', 'fake docx bytes');
        $material = Material::factory()->for($organization, 'organization')->for($user, 'user')->create([
            'title' => '報告書',
            'file_path' => 'materials/report.docx',
            'original_filename' => 'report.docx',
        ]);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '報告事項', 'material_id' => $material->id]);

        $result = app(MeetingMinutesContextBuilder::class)->build($meeting);

        $this->assertCount(1, $result['skipped']);
        $this->assertSame('報告書', $result['skipped'][0]['title']);
        $this->assertStringContainsString('docx', $result['skipped'][0]['reason']);
    }

    public function test_single_file_pdf_site_becomes_a_document_block(): void
    {
        Storage::fake('public');
        [$organization, $user, $meeting] = $this->makeMeetingWithAgendaItem();

        Storage::disk('public')->put('sites/pdf-uuid/document.pdf', '%PDF-1.4 fake pdf content');
        $site = Site::factory()->for($organization, 'organization')->for($meeting, 'meeting')->for($user, 'user')->create([
            'uuid' => 'pdf-uuid',
            'title' => 'PDF議案',
            'original_filename' => 'gian.pdf',
            'index_path' => 'document.pdf',
        ]);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議案審議', 'site_id' => $site->id]);

        $result = app(MeetingMinutesContextBuilder::class)->build($meeting);

        $documentBlocks = array_values(array_filter($result['content_blocks'], fn ($b) => $b['type'] === 'document'));

        $this->assertCount(1, $documentBlocks);
        $this->assertSame('application/pdf', $documentBlocks[0]['source']['media_type']);
        $this->assertNotEmpty($documentBlocks[0]['source']['data']);
        $this->assertSame(base64_encode('%PDF-1.4 fake pdf content'), $documentBlocks[0]['source']['data']);
    }

    public function test_single_file_image_site_becomes_an_image_block(): void
    {
        Storage::fake('public');
        [$organization, $user, $meeting] = $this->makeMeetingWithAgendaItem();

        Storage::disk('public')->put('sites/img-uuid/document.png', 'fake png bytes');
        $site = Site::factory()->for($organization, 'organization')->for($meeting, 'meeting')->for($user, 'user')->create([
            'uuid' => 'img-uuid',
            'title' => '画像議案',
            'original_filename' => 'gian.png',
            'index_path' => 'document.png',
        ]);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議案審議', 'site_id' => $site->id]);

        $result = app(MeetingMinutesContextBuilder::class)->build($meeting);

        $imageBlocks = array_values(array_filter($result['content_blocks'], fn ($b) => $b['type'] === 'image'));

        $this->assertCount(1, $imageBlocks);
        $this->assertSame('image/png', $imageBlocks[0]['source']['media_type']);
    }

    public function test_zip_site_gian_htm_content_is_read_as_text(): void
    {
        Storage::fake('public');
        [$organization, $user, $meeting] = $this->makeMeetingWithAgendaItem();

        Storage::disk('public')->put(
            'sites/zip-uuid/gian.htm',
            '<html><head><meta charset="utf-8"></head><body><h1>第1号議案</h1><p>会費改定について審議する。</p></body></html>'
        );
        $site = Site::factory()->for($organization, 'organization')->for($meeting, 'meeting')->for($user, 'user')->create([
            'uuid' => 'zip-uuid',
            'title' => 'Zip議案',
            'original_filename' => 'gian.zip',
            'index_path' => 'gian.htm',
        ]);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議案審議', 'site_id' => $site->id]);

        $result = app(MeetingMinutesContextBuilder::class)->build($meeting);

        $textBlocks = array_filter($result['content_blocks'], fn ($b) => $b['type'] === 'text');
        $combined = implode("\n", array_column($textBlocks, 'text'));

        $this->assertStringContainsString('会費改定について審議する。', $combined);
        $this->assertStringNotContainsString('<h1>', $combined);
    }

    public function test_shift_jis_gian_htm_is_decoded_to_utf8(): void
    {
        Storage::fake('public');
        [$organization, $user, $meeting] = $this->makeMeetingWithAgendaItem();

        $sjisHtml = mb_convert_encoding(
            '<html><head><meta charset="Shift_JIS"></head><body>予算案の審議</body></html>',
            'SJIS-win',
            'UTF-8'
        );
        Storage::disk('public')->put('sites/sjis-uuid/gian.htm', $sjisHtml);
        $site = Site::factory()->for($organization, 'organization')->for($meeting, 'meeting')->for($user, 'user')->create([
            'uuid' => 'sjis-uuid',
            'title' => 'SJIS議案',
            'original_filename' => 'gian.zip',
            'index_path' => 'gian.htm',
        ]);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議案審議', 'site_id' => $site->id]);

        $result = app(MeetingMinutesContextBuilder::class)->build($meeting);

        $textBlocks = array_filter($result['content_blocks'], fn ($b) => $b['type'] === 'text');
        $combined = implode("\n", array_column($textBlocks, 'text'));

        $this->assertStringContainsString('予算案の審議', $combined);
    }

    public function test_attachments_beyond_the_byte_budget_are_skipped(): void
    {
        Storage::fake('local');
        config(['claude.max_attachment_bytes' => 10]);
        [$organization, $user, $meeting] = $this->makeMeetingWithAgendaItem();

        Storage::disk('local')->put('materials/one.txt', str_repeat('a', 20));
        Storage::disk('local')->put('materials/two.txt', str_repeat('b', 20));
        $materialOne = Material::factory()->for($organization, 'organization')->for($user, 'user')->create([
            'title' => '資料1', 'file_path' => 'materials/one.txt', 'original_filename' => 'one.txt',
        ]);
        $materialTwo = Material::factory()->for($organization, 'organization')->for($user, 'user')->create([
            'title' => '資料2', 'file_path' => 'materials/two.txt', 'original_filename' => 'two.txt',
        ]);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議案1', 'material_id' => $materialOne->id]);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 2, 'title' => '議案2', 'material_id' => $materialTwo->id]);

        $result = app(MeetingMinutesContextBuilder::class)->build($meeting);

        $this->assertNotEmpty($result['skipped']);
        $this->assertSame('容量上限のため読み込めませんでした', $result['skipped'][0]['reason']);
    }

    public function test_agenda_outline_is_included_even_without_attachments(): void
    {
        [, , $meeting] = $this->makeMeetingWithAgendaItem();
        $meeting->update(['name' => '定例理事会']);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '開会宣言', 'assignee_name' => '理事長']);

        $result = app(MeetingMinutesContextBuilder::class)->build($meeting);

        $this->assertStringContainsString('定例理事会', $result['content_blocks'][0]['text']);
        $this->assertStringContainsString('開会宣言', $result['content_blocks'][0]['text']);
        $this->assertStringContainsString('理事長', $result['content_blocks'][0]['text']);
    }
}
