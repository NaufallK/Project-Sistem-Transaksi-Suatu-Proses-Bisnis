@extends('layouts.app')

@section('title', 'Edit Barang')

@section('content')
    <h1>Edit Barang</h1>

    <form action="{{ route('barang.update', $barang->idbarang) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="row">
            <div>
                <label for="nama">Nama Barang</label>
                <input type="text" name="nama" id="nama" value="{{ old('nama', $barang->nama) }}" required>
            </div>

            <div>
                <label for="jenis">Jenis</label>
                <input type="text" name="jenis" id="jenis" value="{{ old('jenis', $barang->jenis) }}" required>
            </div>

            <div>
                <label for="idsatuan">Satuan</label>
                <select name="idsatuan" id="idsatuan" required>
                    <option value="">-- Pilih Satuan --</option>
                    @foreach ($satuans as $satuan)
                        <option value="{{ $satuan->idsatuan }}" @selected(old('idsatuan', $barang->idsatuan) == $satuan->idsatuan)>
                            {{ $satuan->nama_satuan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="harga">Harga Dasar</label>
                <input type="number" name="harga" id="harga" value="{{ old('harga', $barang->harga) }}" min="0" step="0.01" required>
            </div>

            <div>
                <button type="submit">Perbarui</button>
            </div>
        </div>
    </form>

    <a href="{{ route('barang.index') }}">&larr; Kembali ke daftar barang</a>
@endsection
