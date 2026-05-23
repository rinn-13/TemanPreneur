<h2>Laporan Performa</h2>

<p>Total User: {{ $data['total_users'] }}</p>
<p>Total Order: {{ $data['total_orders'] }}</p>
<p>Total Revenue: Rp {{ number_format($data['total_revenue']) }}</p>
<p>Total Produk: {{ $data['total_products'] }}</p>

<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Jumlah Order</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['orders_per_day'] as $d)
        <tr>
            <td>{{ $d['date'] }}</td>
            <td>{{ $d['total'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>