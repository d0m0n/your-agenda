<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidSiteZipException;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Meeting;
use App\Models\Site;
use App\Services\SiteZipInstaller;
use App\Services\StorageUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SiteController extends Controller
{
    public function index(): View
    {
        $sites = Site::with('user')->latest()->paginate(20);

        return view('sites.index', ['sites' => $sites]);
    }

    /**
     * 議案ファイルの実体はWebサーバーが直接配信する静的ファイル(public
     * ディスク)のため、Laravelのルート/ミドルウェアを経由しない。この
     * 「開く入口」だけをルート(subscribedミドルウェア付き)でラップし、
     * 未契約組織はここでペイウォールへ弾かれるようにする。Siteは
     * BelongsToOrganizationのグローバルスコープを持つため、暗黙の
     * ルートモデルバインディングだけで他組織のsiteは自動的に404になる。
     */
    public function open(Site $site): RedirectResponse
    {
        return redirect()->to($site->publicUrl());
    }

    public function create(): View
    {
        return view('sites.create');
    }

    public function store(StoreSiteRequest $request, SiteZipInstaller $installer): RedirectResponse
    {
        $uuid = (string) Str::uuid();
        $zipFile = $request->file('zip_file');

        try {
            $indexPath = $installer->install($zipFile, $uuid);
        } catch (InvalidSiteZipException $e) {
            return back()->withInput()->withErrors(['zip_file' => $e->getMessage()]);
        }

        Site::create([
            'uuid' => $uuid,
            'title' => $request->string('title'),
            'original_filename' => $zipFile->getClientOriginalName(),
            'index_path' => $indexPath,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('sites.index')->with('status', 'サイトを公開しました。');
    }

    public function storeForMeeting(StoreSiteRequest $request, Meeting $meeting, SiteZipInstaller $installer): RedirectResponse
    {
        $uuid = (string) Str::uuid();
        $zipFile = $request->file('zip_file');

        try {
            $indexPath = $installer->install($zipFile, $uuid);
        } catch (InvalidSiteZipException $e) {
            return back()->withInput()->withErrors(['zip_file' => $e->getMessage()]);
        }

        Site::create([
            'meeting_id' => $meeting->id,
            'uuid' => $uuid,
            'title' => $request->string('title'),
            'original_filename' => $zipFile->getClientOriginalName(),
            'index_path' => $indexPath,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('meetings.agenda', $meeting)->with('status', '議案ファイルをアップロードしました。');
    }

    /**
     * Replaces an already-uploaded site's file in place: the new file is
     * validated/extracted into a staging directory first, so a broken
     * upload (invalid Zip, no gian.htm, over quota) never touches the
     * existing working file. Only once that succeeds do we swap it in
     * under the same uuid, keeping the public URL and any agenda_items
     * links (which reference the site's id, not its uuid) intact.
     */
    public function updateForMeeting(UpdateSiteRequest $request, Meeting $meeting, Site $site, SiteZipInstaller $installer, StorageUsageService $storageUsage): RedirectResponse
    {
        $this->ensureBelongsToMeeting($meeting, $site);

        $uploadedFile = $request->file('zip_file');
        $stagingUuid = $site->uuid.'-staging-'.Str::random(8);

        try {
            $indexPath = $installer->install($uploadedFile, $stagingUuid);
        } catch (InvalidSiteZipException $e) {
            return back()->withInput()->withErrors(['zip_file' => $e->getMessage()]);
        }

        $projectedUsage = $storageUsage->usedBytes($meeting->organization)
            - $storageUsage->bytesForSiteUuid($site->uuid)
            + $storageUsage->bytesForSiteUuid($stagingUuid);

        if ($projectedUsage > $storageUsage->quotaBytes($request->user())) {
            File::deleteDirectory(storage_path("app/public/sites/{$stagingUuid}"));

            return back()->withInput()->withErrors(['zip_file' => 'データ容量の上限に達しているため、置き換えできません。基本設定の使用量をご確認ください。']);
        }

        File::deleteDirectory(storage_path("app/public/sites/{$site->uuid}"));
        File::moveDirectory(
            storage_path("app/public/sites/{$stagingUuid}"),
            storage_path("app/public/sites/{$site->uuid}")
        );

        $site->update([
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'index_path' => $indexPath,
        ]);

        return redirect()->route('meetings.agenda', $meeting)->with('status', '議案ファイルを差し替えました。');
    }

    public function destroy(Site $site): RedirectResponse
    {
        File::deleteDirectory(storage_path('app/public/sites/'.$site->uuid));

        $site->delete();

        return redirect()->route('sites.index')->with('status', 'サイトを削除しました。');
    }

    private function ensureBelongsToMeeting(Meeting $meeting, Site $site): void
    {
        if ($site->meeting_id !== $meeting->id) {
            throw new NotFoundHttpException;
        }
    }
}
