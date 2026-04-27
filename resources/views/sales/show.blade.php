<x-app-layout>
    <div class="content">
        <div class="flex">
            <h2>Sale Receipt #{{ $sale->id }}</h2>
            <a href="{{ route('sales.index') }}" class="btn btn-primary">Back to Sales</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header">Sale Details</div>
            <table>
                <tr>
                    <th>Sale ID</th>
                    <td>#{{ $sale->id }}</td>
                </tr>
                <tr>
                    <th>Employee</th>
                    <td>{{ $sale->employee->first_name }} {{ $sale->employee->last_name }}</td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ $sale->created_at->format('M d, Y H:i A') }}</td>
                </tr>
            </table>
        </div>

        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">Items Sold</div>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->details as $detail)
                        <tr>
                            <td>{{ $detail->product->product_name }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>₱{{ number_format($detail->unit_price, 2) }}</td>
                            <td>₱{{ number_format($detail->quantity * $detail->unit_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="text-align: right; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #27ae60;">
                <strong style="font-size: 1.2rem;">Total Amount: ₱{{ number_format($sale->total_amount, 2) }}</strong>
            </div>
        </div>
    </div>
</x-app-layout>
