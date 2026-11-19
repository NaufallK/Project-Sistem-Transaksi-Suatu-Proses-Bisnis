@extends('layouts.app')

@section('title', 'Data Pengadaan')

@section('content')
    <h1>Data Pengadaan</h1>

    <div class="actions">
        <a href="{{ route('pengadaan.create') }}"><button type="button">Tambah Pengadaan</button></a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>User</th>
                <th>Vendor</th>
                <th>Status</th>
                <th>Subtotal</th>
                <th>PPN (11%)</th>
                <th>Total</th>
                <th>Detail Barang</th>
                <th>Aksi</th>
        </thead>
        <tbody>
            @forelse ($pengadaans as $pengadaan)
                <tr>
                    <td>{{ $pengadaan->idpengadaan }}</td>
                    <td>{{ $pengadaan->created_at }}</td>
                    <td>{{ $pengadaan->user->username ?? '-' }}</td>
                    <td>{{ $pengadaan->vendor->nama_vendor ?? '-' }}</td>
                    <td>{{ $pengadaan->status }}</td>
                    <td>Rp {{ number_format($pengadaan->subtotal, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($pengadaan->ppn, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($pengadaan->total_nilai, 0, ',', '.') }}</td>
                    <td>
                        @foreach ($pengadaan->details as $detail)
                            {{ $detail->barang->nama ?? '-' }} &times; {{ $detail->jumlah }}<br>
                        @endforeach
                    </td>
                    <td>
                        <form action="{{ route('pengadaan.destroy', ['pengadaan' => $pengadaan->idpengadaan]) }}" method="POST" onsubmit="return confirm('Hapus pengadaan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #a83232;">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">Belum ada data pengadaan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
