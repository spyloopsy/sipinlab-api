<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    protected $table = 'peminjaman';

    protected $fillable = [
        'kode', 'alat_id', 'user_id', 'nama_peminjam', 'nim', 'jumlah',
        'tanggal_pinjam', 'tanggal_kembali_rencana', 'tanggal_kembali',
        'status', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'tanggal_pinjam' => 'date',
            'tanggal_kembali_rencana' => 'date',
            'tanggal_kembali' => 'date',
        ];
    }

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Peminjaman dianggap terlambat bila belum dikembalikan
     * dan tanggal rencana kembali sudah lewat.
     */
    public function getTerlambatAttribute(): bool
    {
        return $this->status === 'dipinjam'
            && $this->tanggal_kembali_rencana->lt(today());
    }
}
