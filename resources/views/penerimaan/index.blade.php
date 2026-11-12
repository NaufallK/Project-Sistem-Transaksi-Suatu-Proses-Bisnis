@extends('layouts.app')

@section('title', 'Data Penerimaan')

@section('content')
    <h1>Data Penerimaan Barang</h1>

    <!-- <p class="muted">
        Setiap detail penerimaan yang disimpan otomatis dicatat ke tabel
        <code>kartu_stok</code> oleh trigger <strong>trg_kartu_stok</strong>.
        Tidak ada kode <code>KartuStok::create()</code> di aplikasi.
    </p>
 -->

    <div class="actions">
        <a href="{{ route('penerimaan.create') }}"><button type="button">Tambah Penerimaan</button></a>
        <a href="{{ route('kartu-stok.index') }}"><button type="button" style="background: #35694a;">Lihat Kartu Stok</button></a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Pengadaan</th>
                <th>User</th>
                <th>Vendor</th>
                <th>Status</th>
                <th>Total</th>
                <th>Detail</th>
                <th>Aksi</th>
        </thead>
        <tbody>
            @forelse ($penerimaans as $penerimaan)
                <tr>
                    <td>{{ $penerimaan->idpenerimaan }}</td>
                    <td>{{ $penerimaan->created_at }}</td>
                    <td>#{{ $penerimaan->idpengadaan }}</td>
                    <td>{{ $penerimaan->user->username ?? '-' }}</td>
                    <td>{{ $penerimaan->vendor->nama_vendor ?? '-' }}</td>
                    <td>{{ $penerimaan->status }}</td>
                    <td>Rp {{ number_format($penerimaan->total_nilai, 0, ',', '.') }}</td>
                    <td>
                        @foreach ($penerimaan->details as $detail)
                            {{ $detail->barang->nama ?? '-' }} &times; {{ $detail->jumlah_terima }}<br>
                        @endforeach
                    </td>
                    <td>
                        <form action="{{ route('penerimaan.destroy', $penerimaan) }}" method="POST" onsubmit="return confirm('Hapus penerimaan ini? Kartu stoknya juga dihapus.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #a83232;">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Belum ada data penerimaan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
