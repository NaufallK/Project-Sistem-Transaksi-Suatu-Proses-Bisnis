@extends('layouts.app')

@section('title', 'Edit Margin Penjualan')

@section('content')
    <h1>Edit Margin Penjualan</h1>

    <form action="{{ route('margin.update', $margin->idmargin_penjualan) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="row">
            <div>
                <label for="persen">Persentase Margin (%)</label>
                <input type="number" name="persen" id="persen" value="{{ old('persen', $margin->persen) }}" min="0" max="100" step="0.01" required>
            </div>

            <div>
                <label for="iduser">User Pembuat</label>
                <select name="iduser" id="iduser" required>
                    <option value="">-- Pilih User --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->iduser }}" @selected(old('iduser', $margin->iduser) == $user->iduser)>
                            {{ $user->username }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit">Perbarui</button>
            </div>
        </div>
    </form>

    <a href="{{ route('margin.index') }}">&larr; Kembali ke daftar margin</a>
@endsection
