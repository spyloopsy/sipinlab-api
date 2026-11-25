<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi data awal SIPINLAB.
     * Aman dijalankan berulang kali (updateOrCreate/firstOrCreate).
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@sipinlab.test'],
            ['name' => 'Admin Laboratorium', 'password' => 'password', 'role' => 'admin']
        );

        User::updateOrCreate(
            ['email' => 'petugas@sipinlab.test'],
            ['name' => 'Petugas Laboratorium', 'password' => 'password', 'role' => 'petugas']
        );

        $kategori = collect([
            ['nama' => 'Optik', 'deskripsi' => 'Mikroskop, lup, dan alat optik lainnya'],
            ['nama' => 'Pengukuran', 'deskripsi' => 'Timbangan, jangka sorong, dan alat ukur'],
            ['nama' => 'Elektronika', 'deskripsi' => 'Multimeter, osiloskop, catu daya'],
            ['nama' => 'Gelas Kimia', 'deskripsi' => 'Gelas beker, labu ukur, pipet'],
            ['nama' => 'Pemanas', 'deskripsi' => 'Hot plate, oven, penangas air'],
        ])->mapWithKeys(fn ($k) => [$k['nama'] => Kategori::updateOrCreate(['nama' => $k['nama']], $k)]);

        $daftarAlat = [
            ['Optik', 'OPT-001', 'Mikroskop Binokuler', 'Olympus', 'Lemari A1', 10, 'baik'],
            ['Optik', 'OPT-002', 'Mikroskop Stereo', 'Nikon', 'Lemari A1', 4, 'baik'],
            ['Optik', 'OPT-003', 'Lup Tangan 10x', 'Kenko', 'Laci A2', 15, 'baik'],
            ['Pengukuran', 'UKR-001', 'Timbangan Analitik', 'Ohaus', 'Meja B1', 3, 'baik'],
            ['Pengukuran', 'UKR-002', 'Jangka Sorong Digital', 'Mitutoyo', 'Laci B2', 12, 'baik'],
            ['Pengukuran', 'UKR-003', 'Termometer Digital', 'Krisbow', 'Laci B2', 8, 'rusak_ringan'],
            ['Pengukuran', 'UKR-004', 'pH Meter', 'Hanna', 'Meja B1', 5, 'baik'],
            ['Elektronika', 'ELK-001', 'Multimeter Digital', 'Sanwa', 'Lemari C1', 20, 'baik'],
            ['Elektronika', 'ELK-002', 'Osiloskop 2 Kanal', 'Rigol', 'Meja C2', 4, 'baik'],
            ['Elektronika', 'ELK-003', 'Catu Daya DC', 'Zhaoxin', 'Meja C2', 6, 'rusak_berat'],
            ['Gelas Kimia', 'GLS-001', 'Gelas Beker 250 ml', 'Pyrex', 'Rak D1', 30, 'baik'],
            ['Gelas Kimia', 'GLS-002', 'Labu Ukur 100 ml', 'Iwaki', 'Rak D1', 20, 'baik'],
            ['Gelas Kimia', 'GLS-003', 'Pipet Volume 10 ml', 'Iwaki', 'Rak D2', 25, 'baik'],
            ['Pemanas', 'PMN-001', 'Hot Plate Stirrer', 'IKA', 'Meja E1', 5, 'baik'],
            ['Pemanas', 'PMN-002', 'Oven Laboratorium', 'Memmert', 'Ruang E', 2, 'baik'],
        ];

        $alat = [];
        foreach ($daftarAlat as [$kat, $kode, $nama, $merek, $lokasi, $jumlah, $kondisi]) {
            // firstOrCreate: stok tidak di-reset bila seeder dijalankan ulang.
            $alat[$kode] = Alat::firstOrCreate(['kode' => $kode], [
                'kategori_id' => $kategori[$kat]->id,
                'nama' => $nama,
                'merek' => $merek,
                'lokasi' => $lokasi,
                'jumlah_total' => $jumlah,
                'jumlah_tersedia' => $jumlah,
                'kondisi' => $kondisi,
            ]);
        }

        // Contoh transaksi: dua masih dipinjam (satu terlambat), satu sudah kembali.
        if (Peminjaman::count() === 0) {
            $contoh = [
                ['PJM-CONTOH-001', 'OPT-001', 'Rina Wulandari', '2210511001', 2, -3, 4, 'dipinjam'],
                ['PJM-CONTOH-002', 'ELK-001', 'Bagas Pratama', '2210511002', 3, -10, -3, 'dipinjam'],
                ['PJM-CONTOH-003', 'UKR-001', 'Siti Aminah', '2210511003', 1, -7, -5, 'dikembalikan'],
            ];

            foreach ($contoh as [$kode, $kodeAlat, $nama, $nim, $jumlah, $pinjam, $kembali, $status]) {
                Peminjaman::create([
                    'kode' => $kode,
                    'alat_id' => $alat[$kodeAlat]->id,
                    'user_id' => $admin->id,
                    'nama_peminjam' => $nama,
                    'nim' => $nim,
                    'jumlah' => $jumlah,
                    'tanggal_pinjam' => today()->addDays($pinjam),
                    'tanggal_kembali_rencana' => today()->addDays($kembali),
                    'tanggal_kembali' => $status === 'dikembalikan' ? today()->addDays($kembali) : null,
                    'status' => $status,
                ]);

                if ($status === 'dipinjam') {
                    $alat[$kodeAlat]->decrement('jumlah_tersedia', $jumlah);
                }
            }
        }
    }
}
