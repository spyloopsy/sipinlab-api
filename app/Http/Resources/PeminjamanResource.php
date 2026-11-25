<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'nama_peminjam' => $this->nama_peminjam,
            'nim' => $this->nim,
            'jumlah' => $this->jumlah,
            'tanggal_pinjam' => $this->tanggal_pinjam?->toDateString(),
            'tanggal_kembali_rencana' => $this->tanggal_kembali_rencana?->toDateString(),
            'tanggal_kembali' => $this->tanggal_kembali?->toDateString(),
            'status' => $this->status,
            'terlambat' => $this->terlambat,
            'catatan' => $this->catatan,
            'alat' => new AlatResource($this->whenLoaded('alat')),
            'petugas' => new UserResource($this->whenLoaded('petugas')),
        ];
    }
}
