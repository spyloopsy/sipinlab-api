<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeminjamanResource;
use App\Models\Alat;
use App\Models\Peminjaman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanController extends Controller
{
    /**
     * GET /api/peminjaman?status=dipinjam|dikembalikan|terlambat&search=&page=1
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);
        $status = $request->input('status');

        $peminjaman = Peminjaman::with('alat')
            ->when($status === 'terlambat', fn ($q) => $q->where('status', 'dipinjam')
                ->whereDate('tanggal_kembali_rencana', '<', today()))
            ->when(in_array($status, ['dipinjam', 'dikembalikan'], true), fn ($q) => $q->where('status', $status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $kata = '%'.$request->input('search').'%';
                $query->where(fn ($q) => $q->where('kode', 'like', $kata)
                    ->orWhere('nama_peminjam', 'like', $kata)
                    ->orWhere('nim', 'like', $kata)
                    ->orWhereHas('alat', fn ($a) => $a->where('nama', 'like', $kata)));
            })
            ->latest()
            ->paginate($perPage > 0 ? $perPage : 10)
            ->withQueryString();

        return PeminjamanResource::collection($peminjaman);
    }

    /** POST /api/peminjaman */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'alat_id' => ['required', 'integer', 'exists:alat,id'],
            'nama_peminjam' => ['required', 'string', 'max:100'],
            'nim' => ['required', 'string', 'max:20'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_kembali_rencana' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $peminjaman = DB::transaction(function () use ($data, $request) {
            // Kunci baris alat agar stok tidak dipakai dua transaksi sekaligus.
            $alat = Alat::lockForUpdate()->findOrFail($data['alat_id']);

            if ($data['jumlah'] > $alat->jumlah_tersedia) {
                throw ValidationException::withMessages([
                    'jumlah' => ["Stok tidak cukup. Unit tersedia: {$alat->jumlah_tersedia}."],
                ]);
            }

            $urutan = Peminjaman::whereDate('created_at', today())->count() + 1;
            $data['kode'] = 'PJM-'.now()->format('Ymd').'-'.str_pad($urutan, 3, '0', STR_PAD_LEFT);
            $data['user_id'] = $request->user()->id;
            $data['status'] = 'dipinjam';

            $alat->decrement('jumlah_tersedia', $data['jumlah']);

            return Peminjaman::create($data);
        });

        return (new PeminjamanResource($peminjaman->load('alat', 'petugas')))
            ->additional(['message' => 'Peminjaman berhasil dicatat.'])
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/peminjaman/{peminjaman} */
    public function show(Peminjaman $peminjaman): PeminjamanResource
    {
        return new PeminjamanResource($peminjaman->load('alat', 'petugas'));
    }

    /** PATCH /api/peminjaman/{peminjaman}/kembali */
    public function kembali(Peminjaman $peminjaman): PeminjamanResource|JsonResponse
    {
        if ($peminjaman->status === 'dikembalikan') {
            return response()->json(['message' => 'Alat pada peminjaman ini sudah dikembalikan.'], 422);
        }

        DB::transaction(function () use ($peminjaman) {
            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => today(),
            ]);
            $peminjaman->alat()->increment('jumlah_tersedia', $peminjaman->jumlah);
        });

        return (new PeminjamanResource($peminjaman->fresh()->load('alat', 'petugas')))
            ->additional(['message' => 'Pengembalian berhasil dicatat.']);
    }
}
