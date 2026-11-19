@extends('layouts.app')

@section('title', 'Kartu Stok')

@section('content')
    <h1>Kartu Stok</h1>

    <!-- <p class="muted">
        Seluruh baris di bawah ini dibuat oleh trigger <strong>trg_kartu_stok</strong>
        (AFTER INSERT pada <code>detail_penerimaan</code>) dan satu baris
        <code>kartu_stok</code> manual tidak pernah ditulis dari PHP.
    </> -->

    <div class="actions">
        <a href="{{ route('penerimaan.create') }}"><button type="button">Tambah Penerimaan</button></a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Barang</th>
                <th>Jenis Transaksi</th>
                <th>ID Transaksi</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kartuStoks as $kartu)
                <tr>
                    <td>{{ $kartu->idkartu_stok }}</td>
                    <td>{{ $kartu->created_at }}</td>
                    <td>{{ $kartu->barang_nama ?? '-' }}</td>
                    <td>{{ $kartu->jenis_transaksi }}</td>
                    <td>#{{ $kartu->idtransaksi }}</td>
                    <td>{{ $kartu->masuk }}</td>
                    <td>{{ $kartu->keluar }}</td>
                    <td>{{ $kartu->stock }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Belum ada mutasi stok. </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
