<x-app-layout>
    <div class="content">
        <h2>Create Supplier</h2>

        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="supplier_name">Supplier Name</label>
                <input type="text" id="supplier_name" name="supplier_name" required value="{{ old('supplier_name') }}">
                @error('supplier_name')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number') }}">
                @error('contact_number')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3">{{ old('address') }}</textarea>
                @error('address')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Create Supplier</button>
                <a href="{{ route('suppliers.index') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
