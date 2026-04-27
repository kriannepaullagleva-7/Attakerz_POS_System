<x-app-layout>
    <div class="content">
        <h2>Edit Supplier</h2>

        <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="supplier_name">Supplier Name</label>
                <input type="text" id="supplier_name" name="supplier_name" required value="{{ $supplier->supplier_name }}">
                @error('supplier_name')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number" value="{{ $supplier->contact_number }}">
                @error('contact_number')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3">{{ $supplier->address }}</textarea>
                @error('address')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Update Supplier</button>
                <a href="{{ route('suppliers.index') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
