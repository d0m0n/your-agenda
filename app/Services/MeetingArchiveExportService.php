<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\Organization;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use ZipArchive;

class MeetingArchiveExportService
{
    /**
     * Build a zip containing every meeting's agenda (as HTML) plus a copy
     * of each linked Zip議案's files, so the export is fully self-contained.
     * Used both for the "基本設定" bulk download and as the data takeaway
     * on contract cancellation.
     *
     * @return string Absolute path to the generated zip (caller must clean it up).
     */
    public function export(Organization $organization): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'meetings-export-').'.zip';

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $meetings = $organization->meetings()->with([
            'topLevelAgendaItems.member.position',
            'topLevelAgendaItems.site',
            'topLevelAgendaItems.children.member.position',
            'topLevelAgendaItems.children.site',
        ])->get();

        foreach ($meetings as $meeting) {
            $folder = $meeting->id.'_'.Str::slug($meeting->name, '-', 'ja');
            $folder = $folder === $meeting->id.'_' ? (string) $meeting->id : $folder;

            $this->addMeetingContents($zip, $meeting, $folder.'/');
        }

        if ($zip->numFiles === 0) {
            // libzip drops the archive entirely on close() if it has zero
            // entries, so an org with no meetings would produce no file at all.
            $zip->addFromString('README.txt', "会議データはまだ登録されていません。\n");
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * 会議単体の次第(HTML)+紐づくZip議案一式をダウンロードする。
     * export()と中身の構成は同じだが、会議1件分だけをZip直下に展開する
     * (組織一括ダウンロードのような会議IDフォルダは作らない)。
     *
     * @return string Absolute path to the generated zip (caller must clean it up).
     */
    public function exportMeeting(Meeting $meeting): string
    {
        $meeting->loadMissing([
            'topLevelAgendaItems.member.position',
            'topLevelAgendaItems.site',
            'topLevelAgendaItems.children.member.position',
            'topLevelAgendaItems.children.site',
        ]);

        $zipPath = tempnam(sys_get_temp_dir(), 'meeting-export-').'.zip';

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $this->addMeetingContents($zip, $meeting, '');

        $zip->close();

        return $zipPath;
    }

    private function addMeetingContents(ZipArchive $zip, Meeting $meeting, string $basePath): void
    {
        $html = View::make('exports.agenda', ['meeting' => $meeting])->render();
        $zip->addFromString($basePath.'agenda.html', $html);

        foreach ($meeting->topLevelAgendaItems as $item) {
            if ($item->site) {
                $this->addSiteDirectory($zip, $item->site->uuid, $basePath.'sites/'.$item->site->uuid);
            }

            foreach ($item->children as $child) {
                if ($child->site) {
                    $this->addSiteDirectory($zip, $child->site->uuid, $basePath.'sites/'.$child->site->uuid);
                }
            }
        }
    }

    private function addSiteDirectory(ZipArchive $zip, string $uuid, string $zipBasePath): void
    {
        $sourceDir = storage_path('app/public/sites/'.$uuid);

        if (! is_dir($sourceDir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getFilename() === '.htaccess') {
                continue;
            }

            $relativePath = substr($file->getPathname(), strlen($sourceDir) + 1);
            $zip->addFile($file->getPathname(), $zipBasePath.'/'.$relativePath);
        }
    }
}
