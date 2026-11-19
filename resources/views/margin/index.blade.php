@extends('layouts.app')

@section('title', 'Data Margin Penjualan')

@section('content')
    <h1>Data Margin Penjualan</h1>

    <div class="actions">
        <a href="{{ route('margin.create') }}"><button type="button">Tambah Margin</button></a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Persentase Margin</th>
                <th>User Pembuat</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($margins as $margin)
                <tr>
                    <td>{{ $margin->idmargin_penjualan }}</td>
                    <td><strong>{{ $margin->persen }}%</strong></td>
                    <td>{{ $margin->username ?? "ID: {$margin->iduser}" }}</td>
                    <td>{{ $margin->status ? 'Aktif' : 'Nonaktif' }}</td>
                    <td style="display: flex; gap: 6px;">
                        <a href="{{ route('margin.edit', $margin->idmargin_penjualan) }}"><button type="button" style="background: #506173;">Edit</button></a>
                        @if ($margin->status)
                            <form action="{{ route('margin.destroy', $margin->idmargin_penjualan) }}" method="POST" onsubmit="return confirm('Hapus / nonaktifkan margin ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: #a83232;">Hapus</button>
                            </form>
                        @else
                            <form action="{{ route('margin.activate', $margin->idmargin_penjualan) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="background: #2b7a4b;">Aktifkan</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data margin penjualan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
