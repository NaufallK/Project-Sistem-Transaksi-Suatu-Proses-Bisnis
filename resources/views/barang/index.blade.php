@extends('layouts.app')

@section('title', 'Data Barang')

@section('content')
    <h1>Data Barang</h1>

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
                    <td>{{ $barang->nama_satuan ?? '-' }}</td>
                    <td>Rp {{ number_format($barang->harga, 0, ',', '.') }}</td>
                    <td>{{ $barang->status ? 'Aktif' : 'Nonaktif' }}</td>
                    <td style="display: flex; gap: 6px;">
                        <a href="{{ route('barang.edit', $barang->idbarang) }}"><button type="button" style="background: #506173;">Edit</button></a>
                        @if ($barang->status)
                            <form action="{{ route('barang.destroy', $barang->idbarang) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: #a83232;">Nonaktifkan</button>
                            </form>
                        @else
                            <form action="{{ route('barang.activate', $barang->idbarang) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="background: #2b7a4b;">Aktifkan</button>
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
