@extends('layouts.app')

@section('title', 'Edit Satuan')

@section('content')
    <h1>Edit Satuan</h1>

    <form action="{{ route('satuan.update', $satuan->idsatuan) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="row">
            <div>
                <label for="nama_satuan">Nama Satuan</label>
                <input type="text" name="nama_satuan" id="nama_satuan" value="{{ old('nama_satuan', $satuan->nama_satuan) }}" required>
            </div>

            <div>
                <button type="submit">Perbarui</button>
            </div>
        </div>
    </form>

    <a href="{{ route('satuan.index') }}">&larr; Kembali ke daftar satuan</a>
@endsection
