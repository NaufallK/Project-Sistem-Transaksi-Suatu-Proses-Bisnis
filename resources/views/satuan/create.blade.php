@extends('layouts.app')

@section('title', 'Tambah Satuan')

@section('content')
    <h1>Tambah Satuan</h1>

    <form action="{{ route('satuan.store') }}" method="POST" class="card">
        @csrf

        <div class="row">
            <div>
                <label for="nama_satuan">Nama Satuan</label>
                <input type="text" name="nama_satuan" id="nama_satuan" value="{{ old('nama_satuan') }}" placeholder="Contoh: Strip, Box, Botol" required>
            </div>

            <div>
                <button type="submit">Simpan</button>
            </div>
        </div>
    </form>

    <a href="{{ route('satuan.index') }}">&larr; Kembali ke daftar satuan</a>
@endsection
