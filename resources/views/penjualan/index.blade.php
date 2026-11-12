@extends('layouts.app')

@section('title', 'Data Penjualan')

@section('content')
    <h1>Data Penjualan</h1>

    <!-- <p class="muted">
        Harga jual pada transaksi dihitung memakai function database
        <code>fn_hitung_harga_jual(idbarang, idmargin_penjualan)</code>.
    </p> -->

    <div class="actions">
        <a href="{{ route('penjualan.create') }}"><button type="button">Tambah Penjualan</button></a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>User</th>
                <th>Margin</th>
                <th>Barang</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Aksi</th>
        </thead>
        <tbody>
            @forelse ($penjualans as $penjualan)
                <tr>
                    <td>{{ $penjualan->idpenjualan }}</td>
                    <td>{{ $penjualan->created_at }}</td>
                    <td>{{ $penjualan->user->username ?? '-' }}</td>
                    <td>{{ $penjualan->margin->persen ?? 0 }}%</td>
                    <td>
                        @foreach ($penjualan->details as $detail)
                            {{ $detail->barang->nama ?? '-' }}<br>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($penjualan->details as $detail)
                            {{ $detail->jumlah }}<br>
                        @endforeach
                    </td>
                    <td>Rp {{ number_format($penjualan->total_nilai, 0, ',', '.') }}</td>
                    <td>
                        <form action="{{ route('penjualan.destroy', $penjualan) }}" method="POST" onsubmit="return confirm('Hapus penjualan ini? Kartu stok keluar juga dihapus.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #a83232;">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Belum ada data penjualan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
