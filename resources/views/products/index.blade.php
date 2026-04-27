<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-box-seam"></i> Products</h2>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($products->isEmpty())
            <div class="alert alert-info text-center" role="alert">
                <i class="bi bi-info-circle"></i> No products available. <a href="{{ route('products.create') }}">Create one</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Price</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td><strong>{{ $product->product_name }}</strong></td>
                                <td>
                                    <span class="badge {{ $product->category == 'raw' ? 'bg-info' : 'bg-success' }}">
                                        {{ ucfirst($product->category) }}
                                    </span>
                                </td>
                                <td>{{ $product->unit }}</td>
                                <td>₱{{ number_format($product->price, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
