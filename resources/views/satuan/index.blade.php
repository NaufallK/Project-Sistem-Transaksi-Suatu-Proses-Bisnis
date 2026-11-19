@extends('layouts.app')

@section('title', 'Data Satuan')

@section('content')
    <h1>Data Satuan</h1>

    <div class="actions">
        <a href="{{ route('satuan.create') }}"><button type="button">Tambah Satuan</button></a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Satuan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($satuans as $satuan)
                <tr>
                    <td>{{ $satuan->idsatuan }}</td>
                    <td>{{ $satuan->nama_satuan }}</td>
                    <td>{{ $satuan->status ? 'Aktif' : 'Nonaktif' }}</td>
                    <td style="display: flex; gap: 6px;">
                        <a href="{{ route('satuan.edit', $satuan->idsatuan) }}"><button type="button" style="background: #506173;">Edit</button></a>
                        @if ($satuan->status)
                            <form action="{{ route('satuan.destroy', $satuan->idsatuan) }}" method="POST" onsubmit="return confirm('Hapus / nonaktifkan satuan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: #a83232;">Hapus</button>
                            </form>
                        @else
                            <form action="{{ route('satuan.activate', $satuan->idsatuan) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="background: #2b7a4b;">Aktifkan</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Belum ada data satuan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
