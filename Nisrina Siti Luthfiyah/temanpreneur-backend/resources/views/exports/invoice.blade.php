<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; }
        .header { width: 100%; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 3px solid #e53e3e; }
        .header-inner { width: 100%; }
        .header-left { float: left; width: 60%; }
        .header-right { float: right; width: 38%; text-align: right; }
        .logo { display: inline-block; width: 48px; height: 48px; background: #e53e3e; color: #fff; font-weight: bold; font-size: 16px; text-align: center; line-height: 48px; border-radius: 10px; margin-right: 12px; }
        .brand h1 { font-size: 20px; color: #111827; font-weight: bold; }
        .brand p { font-size: 10px; color: #6b7280; }
        .order-num { font-size: 14px; font-weight: bold; color: #e53e3e; }
        .clear { clear: both; }
        .meta-grid { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-grid td { padding: 8px 10px; background: #f9fafb; border: 1px solid #e5e7eb; }
        .meta-label { font-size: 9px; text-transform: uppercase; color: #9ca3af; font-weight: bold; display: block; }
        .meta-value { font-size: 11px; font-weight: bold; color: #111827; }
        .section { margin-bottom: 18px; }
        .section h3 { font-size: 12px; color: #e53e3e; border-bottom: 2px solid #fecaca; padding-bottom: 6px; margin-bottom: 10px; }
        .info-row { margin-bottom: 4px; }
        .info-row strong { color: #6b7280; font-weight: normal; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th { background: #fef2f2; color: #991b1b; padding: 8px; text-align: left; font-size: 9px; text-transform: uppercase; border: 1px solid #fecaca; }
        table.items td { padding: 8px; border: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary { margin-top: 16px; padding: 14px; background: #f9fafb; border: 1px solid #e5e7eb; }
        .summary-row { width: 100%; margin-bottom: 4px; }
        .summary-row td { padding: 2px 0; }
        .summary-total td { font-size: 14px; color: #e53e3e; font-weight: bold; border-top: 2px solid #e5e7eb; padding-top: 8px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        .status-diproses { background: #fef3c7; color: #92400e; }
        .status-dikemas { background: #dbeafe; color: #1e40af; }
        .status-diantarkan { background: #ede9fe; color: #5b21b6; }
        .status-selesai { background: #d1fae5; color: #065f46; }
        .status-dibatalkan { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 30px; text-align: center; padding-top: 16px; border-top: 1px dashed #d1d5db; color: #6b7280; font-size: 10px; }
        .footer strong { color: #e53e3e; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-inner">
            <div class="header-left">
                <span class="logo">TP</span>
                <span class="brand">
                    <h1>TemanPreneur</h1>
                    <p>Marketplace Sekolah — Struk Pesanan</p>
                </span>
            </div>
            <div class="header-right">
                <p class="order-num">{{ $order_number }}</p>
                <p style="font-size:10px;color:#6b7280;">{{ $generated_at }}</p>
            </div>
            <div class="clear"></div>
        </div>
    </div>

    <table class="meta-grid">
        <tr>
            <td width="25%">
                <span class="meta-label">Status</span>
                <span class="meta-value">
                    <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                </span>
            </td>
            <td width="25%">
                <span class="meta-label">Pembayaran</span>
                <span class="meta-value">{{ strtoupper($order->payment_method ?? '-') }}</span>
            </td>
            <td width="25%">
                <span class="meta-label">Toko</span>
                <span class="meta-value">{{ $business->name ?? '-' }}</span>
            </td>
            <td width="25%">
                <span class="meta-label">Tanggal Order</span>
                <span class="meta-value">{{ $order->created_at->format('d M Y H:i') }}</span>
            </td>
        </tr>
    </table>

    <div class="section">
        <h3>Informasi Pembeli</h3>
        <div class="info-row"><strong>Nama: </strong>{{ $buyer->name ?? '-' }}</div>
        <div class="info-row"><strong>Email: </strong>{{ $buyer->email ?? '-' }}</div>
        <div class="info-row"><strong>Telepon: </strong>{{ $buyer->phone ?? $order->shipping_phone ?? '-' }}</div>
        <div class="info-row"><strong>Alamat: </strong>{{ $order->shipping_address ?? '-' }}</div>
        @if($order->buyer_notes)
        <div class="info-row"><strong>Catatan: </strong>{{ $order->buyer_notes }}</div>
        @endif
    </div>

    <div class="section">
        <h3>Detail Pesanan</h3>
        <table class="items">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="text-center" width="60">Qty</th>
                    <th class="text-right" width="100">Harga</th>
                    <th class="text-right" width="110">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Produk' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="summary" width="100%">
        <tr class="summary-row"><td>Subtotal</td><td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td></tr>
        <tr class="summary-row"><td>Ongkir</td><td class="text-right">Rp {{ number_format($shipping_cost, 0, ',', '.') }}</td></tr>
        <tr class="summary-total"><td><strong>TOTAL</strong></td><td class="text-right"><strong>Rp {{ number_format($total_amount, 0, ',', '.') }}</strong></td></tr>
    </table>

    <div class="footer">
        <p>Terima kasih telah berbelanja di <strong>TemanPreneur</strong></p>
        <p>Struk ini dicetak pada {{ $generated_at }} — Simpan sebagai bukti transaksi</p>
    </div>
</body>
</html>
