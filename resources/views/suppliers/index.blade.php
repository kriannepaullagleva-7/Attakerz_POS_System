<x-app-layout>
    <div class="content">
        <div class="flex">
            <h2>Suppliers</h2>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">+ Add Supplier</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($suppliers->isEmpty())
            <div class="empty-state">
                <p>No suppliers found. <a href="{{ route('suppliers.create') }}">Create one</a></p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Contact Number</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->supplier_name }}</td>
                            <td>{{ $supplier->contact_number ?? 'N/A' }}</td>
                            <td>{{ $supplier->address ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-primary btn-small">View</a>
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning btn-small">Edit</a>
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-small" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
