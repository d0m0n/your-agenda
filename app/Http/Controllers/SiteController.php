<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidSiteZipException;
use App\Http\Requests\StoreSiteRequest;
use App\Models\Meeting;
use App\Models\Site;
use App\Services\SiteZipInstaller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View
    {
        $sites = Site::with('user')->latest()->paginate(20);

        return view('sites.index', ['sites' => $sites]);
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

        return redirect()->route('meetings.edit', $meeting)->with('status', '議案ファイルをアップロードしました。');
    }

    public function destroy(Site $site): RedirectResponse
    {
        File::deleteDirectory(storage_path('app/public/sites/'.$site->uuid));

        $site->delete();

        return redirect()->route('sites.index')->with('status', 'サイトを削除しました。');
    }
}
