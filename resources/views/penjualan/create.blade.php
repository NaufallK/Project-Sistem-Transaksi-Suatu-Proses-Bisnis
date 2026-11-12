@extends('layouts.app')

@section('title', 'Tambah Penjualan')

@section('content')
    <h1>Tambah Penjualan</h1>

    <!-- <div class="info">
        Harga jual tidak dihitung dengan rumus di PHP. Form ini memanggil
        <code>SELECT fn_hitung_harga_jual(?, ?) AS harga_jual</code> ke MySQL.
    </div> -->

    <form action="{{ route('penjualan.store') }}" method="POST" class="card">
        @csrf

        <div class="row">
            <div>
                <label for="iduser">User</label>
                <select name="iduser" id="iduser" required>
                    @foreach ($users as $user)
                        <option value="{{ $user->iduser }}">{{ $user->username }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="idbarang">Barang</label>
                <select name="idbarang" id="idbarang" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach ($barangs as $barang)
                        <option value="{{ $barang->idbarang }}" data-harga="{{ $barang->harga }}">
                            {{ $barang->nama }} - Stok: {{ (int) ($stok[$barang->idbarang] ?? 0) }}
                            - Rp {{ number_format($barang->harga, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="idmargin_penjualan">Margin</label>
                <select name="idmargin_penjualan" id="idmargin_penjualan" required>
                    <option value="">-- Pilih Margin --</option>
                    @foreach ($margins as $margin)
                        <option value="{{ $margin->idmargin_penjualan }}">
                            {{ $margin->persen }}%
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="jumlah">Jumlah</label>
                <input type="number" name="jumlah" id="jumlah" min="1" value="1" required>
            </div>
        </div>

        <p id="preview" class="muted" style="margin-top: 18px;">Pilih barang dan margin untuk melihat harga jual.</p>

        <button type="submit">Simpan Penjualan</button>
    </form>

    <script>
        const barang = document.getElementById('idbarang');
        const margin = document.getElementById('idmargin_penjualan');
        const preview = document.getElementById('preview');

        async function previewHargaJual() {
            if (!barang.value || !margin.value) {
                preview.textContent = 'Pilih barang dan margin untuk melihat harga jual.';
                return;
            }

            const params = new URLSearchParams({
                idbarang: barang.value,
                idmargin: margin.value,
            });

            const response = await fetch(`{{ route('harga-jual') }}?${params}`);
            const data = await response.json();

            preview.textContent = `Harga jual setelah margin: Rp ${
                new Intl.NumberFormat('id-ID').format(data.harga_jual)
            }`;
        }

        barang.addEventListener('change', previewHargaJual);
        margin.addEventListener('change', previewHargaJual);
    </script>
@endsection
