<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlatResource;
use App\Models\Alat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AlatController extends Controller
{
    /**
     * GET /api/alat?search=&kategori_id=&kondisi=&tersedia=1&page=1&per_page=10
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);

        $alat = Alat::with('kategori')
            ->when($request->filled('search'), function ($query) use ($request) {
                $kata = '%'.$request->input('search').'%';
                $query->where(fn ($q) => $q->where('nama', 'like', $kata)
                    ->orWhere('kode', 'like', $kata)
                    ->orWhere('merek', 'like', $kata));
            })
            ->when($request->filled('kategori_id'), fn ($q) => $q->where('kategori_id', $request->input('kategori_id')))
            ->when($request->filled('kondisi'), fn ($q) => $q->where('kondisi', $request->input('kondisi')))
            ->when($request->boolean('tersedia'), fn ($q) => $q->where('jumlah_tersedia', '>', 0))
            ->orderBy('nama')
            ->paginate($perPage > 0 ? $perPage : 10)
            ->withQueryString();

        return AlatResource::collection($alat);
    }

    /** POST /api/alat */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->aturan());
        $data['jumlah_tersedia'] = $data['jumlah_total'];

        $alat = Alat::create($data)->load('kategori');

        return (new AlatResource($alat))
            ->additional(['message' => 'Alat berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/alat/{alat} */
    public function show(Alat $alat): AlatResource
    {
        return new AlatResource($alat->load('kategori'));
    }

    /** PUT /api/alat/{alat} */
    public function update(Request $request, Alat $alat): AlatResource
    {
        $data = $request->validate($this->aturan($alat));

        // Unit yang sedang dipinjam tidak boleh hilang ketika jumlah total diubah.
        $sedangDipinjam = $alat->jumlah_total - $alat->jumlah_tersedia;
        if ($data['jumlah_total'] < $sedangDipinjam) {
            throw ValidationException::withMessages([
                'jumlah_total' => ["Jumlah total minimal {$sedangDipinjam} karena unit tersebut sedang dipinjam."],
            ]);
        }
        $data['jumlah_tersedia'] = $data['jumlah_total'] - $sedangDipinjam;

        $alat->update($data);

        return (new AlatResource($alat->load('kategori')))
            ->additional(['message' => 'Alat berhasil diperbarui.']);
    }

    /** DELETE /api/alat/{alat} (khusus admin) */
    public function destroy(Request $request, Alat $alat): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403, 'Hanya admin yang boleh menghapus data.');

        if ($alat->peminjaman()->exists()) {
            return response()->json([
                'message' => 'Alat tidak dapat dihapus karena memiliki riwayat peminjaman.',
            ], 422);
        }

        $alat->delete();

        return response()->json(['message' => 'Alat berhasil dihapus.']);
    }

    private function aturan(?Alat $alat = null): array
    {
        return [
            'kategori_id' => ['required', 'integer', 'exists:kategori,id'],
            'kode' => ['required', 'string', 'max:20', Rule::unique('alat', 'kode')->ignore($alat?->id)],
            'nama' => ['required', 'string', 'max:150'],
            'merek' => ['nullable', 'string', 'max:100'],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'jumlah_total' => ['required', 'integer', 'min:1', 'max:10000'],
            'kondisi' => ['required', Rule::in(['baik', 'rusak_ringan', 'rusak_berat'])],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
