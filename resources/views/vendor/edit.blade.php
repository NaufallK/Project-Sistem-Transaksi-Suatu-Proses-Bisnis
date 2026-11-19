@extends('layouts.app')

@section('title', 'Edit Vendor')

@section('content')
    <h1>Edit Vendor</h1>

    <form action="{{ route('vendor.update', $vendor->idvendor) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="row">
            <div>
                <label for="nama_vendor">Nama Vendor</label>
                <input type="text" name="nama_vendor" id="nama_vendor" value="{{ old('nama_vendor', $vendor->nama_vendor) }}" required>
            </div>

            <div>
                <label for="badan_hukum">Badan Hukum</label>
                <input type="text" name="badan_hukum" id="badan_hukum" value="{{ old('badan_hukum', $vendor->badan_hukum) }}" required>
            </div>

            <div>
                <button type="submit">Perbarui</button>
            </div>
        </div>
    </form>

    <a href="{{ route('vendor.index') }}">&larr; Kembali ke daftar vendor</a>
@endsection
