<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Models\Material;
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
