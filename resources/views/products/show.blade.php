<x-app-layout>
    <div class="container-fluid">
        <div class="mb-4">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Product Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 40%;">Product Name</th>
                                <td><strong>{{ $product->product_name }}</strong></td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td>
                                    <span class="badge {{ $product->category == 'raw' ? 'bg-info' : 'bg-success' }}">
                                        {{ ucfirst($product->category) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Unit</th>
                                <td>{{ $product->unit }}</td>
                            </tr>
                            <tr>
                                <th>Price</th>
                                <td><strong class="text-success">₱{{ number_format($product->price, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Created</th>
                                <td>{{ $product->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>

                        <div class="d-flex gap-2 mt-4">
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('products.destroy', $product) }}" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
