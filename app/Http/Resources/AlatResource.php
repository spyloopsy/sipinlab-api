<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'nama' => $this->nama,
            'merek' => $this->merek,
            'lokasi' => $this->lokasi,
            'jumlah_total' => $this->jumlah_total,
            'jumlah_tersedia' => $this->jumlah_tersedia,
            'kondisi' => $this->kondisi,
            'keterangan' => $this->keterangan,
            'kategori_id' => $this->kategori_id,
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
