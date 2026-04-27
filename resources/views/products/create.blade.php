<x-app-layout>
    <div class="container-fluid">
        <div class="mb-4">
            <h2><i class="bi bi-plus-circle"></i> Add New Product</h2>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('products.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name *</label>
                                <input type="text" class="form-control @error('product_name') is-invalid @enderror" 
                                    id="product_name" name="product_name" value="{{ old('product_name') }}" required>
                                @error('product_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select @error('category') is-invalid @enderror" 
                                    id="category" name="category" required>
                                    <option value="">-- Select Category --</option>
                                    <option value="raw" {{ old('category') == 'raw' ? 'selected' : '' }}>Raw Material</option>
                                    <option value="finished" {{ old('category') == 'finished' ? 'selected' : '' }}>Finished Product</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="unit" class="form-label">Unit *</label>
                                <input type="text" class="form-control @error('unit') is-invalid @enderror" 
                                    id="unit" name="unit" value="{{ old('unit') }}" placeholder="e.g., pc, kg, bottle" required>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Price (₱) *</label>
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                    id="price" name="price" value="{{ old('price') }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Save Product
                                </button>
                                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
