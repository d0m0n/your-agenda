<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Material;
use App\Services\StorageUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialController extends Controller
{
    public function index(): View
    {
        $materials = Material::with('user')->latest()->paginate(20);

        return view('materials.index', ['materials' => $materials]);
    }

    public function store(MaterialRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('materials', $filename, 'local');

        Material::create([
            'title' => $request->string('title'),
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('materials.index')->with('status', '資料をアップロードしました。');
    }

    /**
     * Replaces an already-uploaded material's file: the new file is stored
     * under a new name first, so a storage failure never leaves the old
     * file deleted with nothing in its place. The material's id (and
     * therefore its download URL and any agenda_items.material_id links)
     * stays the same — only file_path/original_filename change.
     */
    public function update(UpdateMaterialRequest $request, Material $material, StorageUsageService $storageUsage): RedirectResponse
    {
        $file = $request->file('file');

        $oldPath = $material->file_path;
        $oldBytes = Storage::disk('local')->exists($oldPath) ? (Storage::disk('local')->size($oldPath) ?: 0) : 0;

        $projectedUsage = $storageUsage->usedBytes($material->organization) - $oldBytes + $file->getSize();

        if ($projectedUsage > $storageUsage->quotaBytes($request->user())) {
            return back()->withInput()->withErrors(['file' => 'データ容量の上限に達しているため、置き換えできません。基本設定の使用量をご確認ください。']);
        }

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $newPath = $file->storeAs('materials', $filename, 'local');

        Storage::disk('local')->delete($oldPath);

        $material->update([
            'file_path' => $newPath,
            'original_filename' => $file->getClientOriginalName(),
        ]);

        return redirect()->route('materials.index')->with('status', '資料を差し替えました。');
    }

    public function download(Material $material): StreamedResponse
    {
        return Storage::disk('local')->download($material->file_path, $material->original_filename);
    }

    public function destroy(Material $material): RedirectResponse
    {
        Storage::disk('local')->delete($material->file_path);
        $material->delete();

        return redirect()->route('materials.index')->with('status', '資料を削除しました。');
    }
}
