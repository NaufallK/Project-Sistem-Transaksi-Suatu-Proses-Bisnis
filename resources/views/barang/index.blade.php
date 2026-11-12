@extends('layouts.app')

@section('title', 'Data Barang')

@section('content')
    <h1>Data Barang</h1>

    <!-- <p class="muted">
        Penambahan barang memanggil <strong>stored procedure</strong>
        <code>sp_tambah_barang(nama, jenis, idsatuan, harga)</code>.
    </p> -->

    <div class="actions">
        <a href="{{ route('barang.create') }}"><button type="button">Tambah Barang</button></a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Jenis</th>
                <th>Nama</th>
                <th>Satuan</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($barangs as $barang)
                <tr>
                    <td>{{ $barang->idbarang }}</td>
                    <td>{{ $barang->jenis }}</td>
                    <td>{{ $barang->nama }}</td>
                    <td>{{ $barang->satuan->nama_satuan ?? '-' }}</td>
                    <td>Rp {{ number_format($barang->harga, 0, ',', '.') }}</td>
                    <td>{{ $barang->status ? 'Aktif' : 'Nonaktif' }}</td>
                    <td>
                        @if ($barang->status)
                            <form action="{{ route('barang.destroy', $barang) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Nonaktifkan</button>
                            </form>
                        @else
                            <form action="{{ route('barang.activate', $barang) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit">Aktifkan</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada data barang.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
