@extends('layouts.app')

@section('title', 'Data Vendor')

@section('content')
    <h1>Data Vendor</h1>

    <div class="actions">
        <a href="{{ route('vendor.create') }}"><button type="button">Tambah Vendor</button></a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Vendor</th>
                <th>Badan Hukum</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($vendors as $vendor)
                <tr>
                    <td>{{ $vendor->idvendor }}</td>
                    <td>{{ $vendor->nama_vendor }}</td>
                    <td>{{ $vendor->badan_hukum }}</td>
                    <td>{{ $vendor->status ? 'Aktif' : 'Nonaktif' }}</td>
                    <td style="display: flex; gap: 6px;">
                        <a href="{{ route('vendor.edit', $vendor->idvendor) }}"><button type="button" style="background: #506173;">Edit</button></a>
                        @if ($vendor->status)
                            <form action="{{ route('vendor.destroy', $vendor->idvendor) }}" method="POST" onsubmit="return confirm('Hapus / nonaktifkan vendor ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: #a83232;">Hapus</button>
                            </form>
                        @else
                            <form action="{{ route('vendor.activate', $vendor->idvendor) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="background: #2b7a4b;">Aktifkan</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data vendor.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
