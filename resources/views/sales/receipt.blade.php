<h2>OFFICIAL RECEIPT</h2>

<p>Date: {{ $sale->date }}</p>

<table border="1">
<tr>
    <th>Product</th>
    <th>Qty</th>
    <th>Price</th>
</tr>

@foreach($sale->details as $d)
<tr>
    <td>{{ $d->product->product_name }}</td>
    <td>{{ $d->quantity }}</td>
    <td>{{ $d->unit_price }}</td>
</tr>
@endforeach
</table>

<h3>Total: {{ $sale->total_amount }}</h3>

<script>
window.print();
</script>