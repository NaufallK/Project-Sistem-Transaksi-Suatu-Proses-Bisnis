@extends('layouts.app')

@section('title', 'Tambah Penerimaan')

@section('content')
    <h1>Tambah Penerimaan Barang</h1>

    <form action="{{ route('penerimaan.store') }}" method="POST">
        @csrf

        <div class="card">
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
                    <label for="idpengadaan">Pilih Pengadaan</label>
                    <select name="idpengadaan" id="idpengadaan" required>
                        @forelse ($pengadaans as $pengadaan)
                            <option value="{{ $pengadaan->idpengadaan }}" data-details="{{ $pengadaan->items_data->toJson() }}">
                                #{{ $pengadaan->idpengadaan }} - {{ $pengadaan->vendor->nama_vendor ?? '-' }} ({{ $pengadaan->status }})
                            </option>
                        @empty
                            <option value="">Tidak ada pengadaan yang perlu diterima</option>
                        @endforelse
                    </select>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 style="font-size: 16px; margin-top: 0;">Barang Pada Pengadaan Terpilih</h2>
            <div id="items">
                <p class="muted">Pilih pengadaan terlebih dahulu.</p>
            </div>
        </div>
        <button type="submit">Simpan Penerimaan</button>
    </form>

    <script>
        const pengadaanSelect = document.getElementById('idpengadaan');
        const itemsContainer = document.getElementById('items');

        function renderItems() {
            const selected = pengadaanSelect.selectedOptions[0];
            if (!selected || !selected.dataset.details) {
                itemsContainer.innerHTML = '<p class="muted">Pilih pengadaan terlebih dahulu.</p>';
                return;
            }

            const details = JSON.parse(selected.dataset.details);
            if (details.length === 0) {
                itemsContainer.innerHTML = '<p class="muted">Semua barang pada pengadaan ini sudah selesai diterima.</p>';
                return;
            }

            itemsContainer.innerHTML = `<table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Harga Satuan</th>
                        <th>Dipesan</th>
                        <th>Sudah Diterima</th>
                        <th>Sisa</th>
                        <th>Jumlah Terima</th>
                    </tr>
                </thead>
                <tbody>${
                details.map((detail, index) => `
                    <tr>
                        <td>${detail.nama}<input type="hidden" name="barang[${index}][idbarang]" value="${detail.idbarang}"></td>
                        <td>Rp ${Number(detail.harga).toLocaleString('id-ID')}</td>
                        <td>${detail.pesan}</td>
                        <td>${detail.sudah}</td>
                        <td><strong>${detail.sisa}</strong></td>
                        <td><input type="number" name="barang[${index}][jumlah_terima]" min="0" max="${detail.sisa}" value="${detail.sisa}"></td>
                    </tr>`).join('')
            }</tbody></table>`;
        }

        pengadaanSelect.addEventListener('change', renderItems);
        renderItems();
    </script>
@endsection
