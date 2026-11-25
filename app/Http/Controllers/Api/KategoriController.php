<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriResource;
use App\Models\Kategori;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    /** GET /api/kategori */
    public function index()
    {
        return KategoriResource::collection(
            Kategori::withCount('alat')->orderBy('nama')->get()
        );
    }

    /** POST /api/kategori */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:kategori,nama'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ]);

        $kategori = Kategori::create($data);

        return (new KategoriResource($kategori))
            ->additional(['message' => 'Kategori berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/kategori/{kategori} */
    public function show(Kategori $kategori): KategoriResource
    {
        return new KategoriResource($kategori->loadCount('alat'));
    }

    /** PUT /api/kategori/{kategori} */
    public function update(Request $request, Kategori $kategori): KategoriResource
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('kategori', 'nama')->ignore($kategori->id)],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ]);

        $kategori->update($data);

        return (new KategoriResource($kategori))
            ->additional(['message' => 'Kategori berhasil diperbarui.']);
    }

    /** DELETE /api/kategori/{kategori} (khusus admin) */
    public function destroy(Request $request, Kategori $kategori): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403, 'Hanya admin yang boleh menghapus data.');

        if ($kategori->alat()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki data alat.',
            ], 422);
        }

        $kategori->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
