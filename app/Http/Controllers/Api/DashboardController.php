<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeminjamanResource;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     * Ringkasan angka untuk halaman dasbor.
     */
    public function __invoke(): JsonResponse
    {
        $terbaru = Peminjaman::with('alat')->latest()->take(5)->get();

        return response()->json([
            'data' => [
                'total_alat' => Alat::count(),
                'total_unit' => (int) Alat::sum('jumlah_total'),
                'unit_tersedia' => (int) Alat::sum('jumlah_tersedia'),
                'peminjaman_aktif' => Peminjaman::where('status', 'dipinjam')->count(),
                'terlambat' => Peminjaman::where('status', 'dipinjam')
                    ->whereDate('tanggal_kembali_rencana', '<', today())
                    ->count(),
                'alat_per_kategori' => Kategori::withCount('alat')
                    ->orderBy('nama')
                    ->get()
                    ->map(fn ($k) => ['nama' => $k->nama, 'jumlah' => $k->alat_count]),
                'peminjaman_terbaru' => PeminjamanResource::collection($terbaru),
            ],
        ]);
    }
}
