@extends('layouts.app')

@section('title', 'Tambah Vendor')

@section('content')
    <h1>Tambah Vendor</h1>

    <form action="{{ route('vendor.store') }}" method="POST" class="card">
        @csrf

        <div class="row">
            <div>
                <label for="nama_vendor">Nama Vendor</label>
                <input type="text" name="nama_vendor" id="nama_vendor" value="{{ old('nama_vendor') }}" placeholder="PT Kalbe Farma Tbk" required>
            </div>

            <div>
                <label for="badan_hukum">Badan Hukum</label>
                <input type="text" name="badan_hukum" id="badan_hukum" value="{{ old('badan_hukum', 'PT') }}" placeholder="PT / CV" required>
            </div>

            <div>
                <button type="submit">Simpan</button>
            </div>
        </div>
    </form>

    <a href="{{ route('vendor.index') }}">&larr; Kembali ke daftar vendor</a>
@endsection
