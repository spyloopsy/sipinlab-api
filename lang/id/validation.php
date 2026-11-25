<?php

/*
| Pesan validasi berbahasa Indonesia untuk aturan yang dipakai SIPINLAB.
| Aturan lain yang tidak tercantum akan memakai pesan bawaan (bahasa Inggris).
*/

return [
    'after_or_equal' => ':Attribute harus berupa tanggal yang sama atau setelah :date.',
    'boolean' => ':Attribute harus bernilai benar atau salah.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'email' => ':Attribute harus berupa alamat surel yang valid.',
    'exists' => ':Attribute yang dipilih tidak ditemukan.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'max' => [
        'numeric' => ':Attribute tidak boleh lebih dari :max.',
        'string' => ':Attribute tidak boleh lebih dari :max karakter.',
    ],
    'min' => [
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'numeric' => ':Attribute harus berupa angka.',
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah digunakan.',

    'attributes' => [
        'email' => 'surel',
        'password' => 'kata sandi',
        'nama' => 'nama',
        'deskripsi' => 'deskripsi',
        'kategori_id' => 'kategori',
        'kode' => 'kode',
        'merek' => 'merek',
        'lokasi' => 'lokasi',
        'jumlah_total' => 'jumlah total',
        'kondisi' => 'kondisi',
        'keterangan' => 'keterangan',
        'alat_id' => 'alat',
        'nama_peminjam' => 'nama peminjam',
        'nim' => 'NIM',
        'jumlah' => 'jumlah',
        'tanggal_pinjam' => 'tanggal pinjam',
        'tanggal_kembali_rencana' => 'tanggal rencana kembali',
        'catatan' => 'catatan',
    ],
];
