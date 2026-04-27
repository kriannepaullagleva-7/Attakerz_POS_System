<x-app-layout>
    <div class="content">
        <div class="flex">
            <h2>Stock In #{{ $stockIn->id }}</h2>
            <a href="{{ route('stock-in.index') }}" class="btn btn-primary">Back</a>
        </div>

        <div class="card">
            <div class="card-header">Stock In Details</div>
            <table>
                <tr>
                    <th>Stock In ID</th>
                    <td>#{{ $stockIn->id }}</td>
                </tr>
                <tr>
                    <th>Supplier</th>
                    <td>{{ $stockIn->supplier->supplier_name }}</td>
                </tr>
                <tr>
                    <th>Employee</th>
                    <td>{{ $stockIn->employee->first_name }} {{ $stockIn->employee->last_name }}</td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ $stockIn->date->format('M d, Y') }}</td>
                </tr>
            </table>
        </div>

        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">Items</div>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Cost per Unit</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockIn->details as $detail)
                        <tr>
                            <td>{{ $detail->product->product_name }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>₱{{ number_format($detail->cost_per_unit, 2) }}</td>
                            <td>₱{{ number_format($detail->quantity * $detail->cost_per_unit, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="text-align: right; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #27ae60;">
                <strong style="font-size: 1.2rem;">Total Cost: ₱{{ number_format($stockIn->total_cost, 2) }}</strong>
            </div>
        </div>
    </div>
</x-app-layout>
