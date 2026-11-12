<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Apotek')</title>
    <style>
        body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; margin: 0; background: #f4f6f9; color: #1f2430; }
        header { background: #1e3a5f; padding: 14px 24px; }
        header a { color: #fff; text-decoration: none; margin-right: 18px; font-weight: 600; font-size: 14px; }
        header a:hover { text-decoration: underline; }
        main { max-width: 1100px; margin: 0 auto; padding: 24px; }
        h1 { font-size: 22px; margin: 0 0 16px; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border: 1px solid #d7dbe0; padding: 8px 10px; font-size: 14px; text-align: left; vertical-align: top; }
        th { background: #eef1f5; }
        input, select { padding: 6px 8px; border: 1px solid #b9c0c8; border-radius: 4px; font-size: 14px; }
        button { padding: 7px 16px; border: 0; border-radius: 4px; background: #1e3a5f; color: #fff; cursor: pointer; font-size: 14px; }
        button:hover { background: #2b5588; }
        .alert-success { background: #e6f6ea; border: 1px solid #9ed4ab; padding: 10px 12px; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: #fdecec; border: 1px solid #e6a4a4; padding: 10px 12px; margin-bottom: 16px; font-size: 14px; }
        .card { background: #fff; border: 1px solid #d7dbe0; border-radius: 6px; padding: 18px; margin-bottom: 16px; }
        .muted { color: #66707c; font-size: 13px; }
        .info { background: #eef4ff; border: 1px solid #b9cdf0; padding: 10px 12px; margin: 12px 0; font-size: 14px; }
        .row { display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end; }
        .row > div { display: flex; flex-direction: column; gap: 4px; }
        label { font-size: 13px; font-weight: 600; color: #3b4350; }
        .actions { margin: 16px 0; }
    </style>
</head>
<body>
    <header>
        <a href="{{ route('barang.index') }}">Barang</a>
        <a href="{{ route('pengadaan.index') }}">Pengadaan</a>
        <a href="{{ route('penerimaan.index') }}">Penerimaan</a>
        <a href="{{ route('kartu-stok.index') }}">Kartu Stok</a>
        <a href="{{ route('penjualan.index') }}">Penjualan</a>
    </header>

    <main>
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
