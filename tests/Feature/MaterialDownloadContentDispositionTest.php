<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Material;
use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

/**
 * PDFの資料は、次第画面のリンクを新しいタブで開いたときにダウンロードでは
 * なくその場で閲覧できるよう、Content-Dispositionをinlineで返す
 * (MaterialController::download())。PDF以外(ブラウザがネイティブ表示
 * できない形式)は従来通りattachmentのままダウンロードさせる。
 */
class MaterialDownloadContentDispositionTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_pdf_materials_are_served_inline(): void
    {
        [$organization, $general] = $this->createTenant();
        Storage::disk('local')->put('materials/test.pdf', '%PDF-1.4 fake');
        $material = Material::factory()->for($organization, 'organization')->for($general, 'user')->create([
            'file_path' => 'materials/test.pdf',
            'original_filename' => 'sample.pdf',
        ]);

        $response = $this->actingAs($general)->get(route('materials.download', $material));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'inline; filename=sample.pdf');
    }

    public function test_non_pdf_materials_are_still_downloaded_as_attachments(): void
    {
        [$organization, $general] = $this->createTenant();
        Storage::disk('local')->put('materials/test.docx', 'fake docx');
        $material = Material::factory()->for($organization, 'organization')->for($general, 'user')->create([
            'file_path' => 'materials/test.docx',
            'original_filename' => 'sample.docx',
        ]);

        $response = $this->actingAs($general)->get(route('materials.download', $material));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename=sample.docx');
    }

    public function test_pdf_materials_are_served_inline_via_the_public_sharing_link(): void
    {
        [$organization, $general] = $this->createTenant();
        Storage::disk('local')->put('materials/public-test.pdf', '%PDF-1.4 fake');
        $material = Material::factory()->for($organization, 'organization')->for($general, 'user')->create([
            'file_path' => 'materials/public-test.pdf',
            'original_filename' => 'public-sample.pdf',
        ]);
        $meeting = Meeting::factory()->for($organization, 'organization')->create([
            'public_token' => (string) Str::uuid(),
        ]);
        AgendaItem::create([
            'meeting_id' => $meeting->id,
            'order' => 1,
            'title' => '資料確認',
            'material_id' => $material->id,
        ]);

        $response = $this->get(route('public.meetings.materials.download', [
            'meeting' => $meeting->public_token,
            'material' => $material->id,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'inline; filename=public-sample.pdf');
    }
}
