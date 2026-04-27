<x-app-layout>
    <div class="content">
        <div class="flex">
            <h2>Stock In</h2>
            <a href="{{ route('stock-in.create') }}" class="btn btn-primary">+ New Stock In</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($stocks->isEmpty())
            <div class="empty-state">
                <p>No stock-in records yet. <a href="{{ route('stock-in.create') }}">Create one</a></p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Stock In ID</th>
                        <th>Supplier</th>
                        <th>Employee</th>
                        <th>Total Cost</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stocks as $stock)
                        <tr>
                            <td>#{{ $stock->id }}</td>
                            <td>{{ $stock->supplier->supplier_name ?? 'Unknown' }}</td>
                            <td>{{ $stock->employee->first_name ?? 'Unknown' }}</td>
                            <td>₱{{ number_format($stock->total_cost, 2) }}</td>
                            <td>{{ $stock->date->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('stock-in.show', $stock) }}" class="btn btn-primary btn-small">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
