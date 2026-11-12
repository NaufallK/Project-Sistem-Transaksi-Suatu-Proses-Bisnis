@extends('layouts.app')

@section('title', 'Tambah Pengadaan')

@section('content')
    <h1>Tambah Pengadaan</h1>
    <form action="{{ route('pengadaan.store') }}" method="POST">
        @csrf

        <div class="card">
            <div class="row">
                <div>
                    <label for="iduser">User</label>
                    <select name="iduser" id="iduser" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->iduser }}">{{ $user->username }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="idvendor">Vendor</label>
                    <select name="idvendor" id="idvendor" required>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->idvendor }}">{{ $vendor->nama_vendor }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 style="font-size: 16px; margin-top: 0;">Daftar Barang</h2>
            <p class="muted">Biarkan jumlah 0 untuk barang yang tidak dipesan.</p>

            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Satuan</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($barangs as $index => $barang)
                        <tr>
                            <td>
                                {{ $barang->nama }}
                                <input type="hidden" name="barang[{{ $index }}][idbarang]" value="{{ $barang->idbarang }}">
                            </td>
                            <td>{{ $barang->satuan->nama_satuan ?? '-' }}</td>
                            <td>
                                <input type="number" name="barang[{{ $index }}][harga_satuan]"
                                       value="{{ $barang->harga }}" min="0" step="0.01">
                            </td>
                            <td>
                                <input type="number" name="barang[{{ $index }}][jumlah]"
                                       value="{{ old('barang.' . $index . '.jumlah', 0) }}" min="0">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit">Simpan Pengadaan</button>
        <a href="{{ route('pengadaan.index') }}">&larr; Kembali</a>
    </form>
@endsection
