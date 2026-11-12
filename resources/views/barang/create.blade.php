@extends('layouts.app')

@section('title', 'Tambah Barang')

@section('content')
    <h1>Tambah Barang</h1>

    <!-- <div class="info">
        Simpan data ini akan menjalankan di MySQL:
        <code>CALL sp_tambah_barang(nama, jenis, idsatuan, harga)</code>
    </div> -->

    <form action="{{ route('barang.store') }}" method="POST" class="card">
        @csrf

        <div class="row">
            <div>
                <label for="nama">Nama Barang</label>
                <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required>
            </div>

            <div>
                <label for="jenis">Jenis</label>
                <input type="text" name="jenis" id="jenis" value="{{ old('jenis') }}" required>
            </div>

            <div>
                <label for="idsatuan">Satuan</label>
                <select name="idsatuan" id="idsatuan" required>
                    <option value="">-- Pilih Satuan --</option>
                    @foreach ($satuans as $satuan)
                        <option value="{{ $satuan->idsatuan }}" @selected(old('idsatuan') == $satuan->idsatuan)>
                            {{ $satuan->nama_satuan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="harga">Harga Dasar</label>
                <input type="number" name="harga" id="harga" value="{{ old('harga') }}" min="0" step="0.01" required>
            </div>

            <div>
                <button type="submit">Simpan</button>
            </div>
        </div>
    </form>

    <a href="{{ route('barang.index') }}">&larr; Kembali ke daftar barang</a>
@endsection
