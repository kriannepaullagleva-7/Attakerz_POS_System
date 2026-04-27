<x-app-layout>
    <div class="content">
        <div class="flex">
            <h2>{{ $supplier->supplier_name }}</h2>
            <div>
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('suppliers.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Supplier Details</div>
            <table>
                <tr>
                    <th>Supplier Name</th>
                    <td>{{ $supplier->supplier_name }}</td>
                </tr>
                <tr>
                    <th>Contact Number</th>
                    <td>{{ $supplier->contact_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>{{ $supplier->address ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Created</th>
                    <td>{{ $supplier->created_at->format('M d, Y H:i') }}</td>
                </tr>
            </table>
        </div>
    </div>
</x-app-layout>
