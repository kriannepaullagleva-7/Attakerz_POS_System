<x-app-layout>
    <div class="content">
        <h2>Create Stock In</h2>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <style>
            .stockin-container {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 2rem;
            }

            .product-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }

            .product-item {
                border: 1px solid #ddd;
                padding: 1rem;
                border-radius: 8px;
                background: #f9f9f9;
            }

            .product-item h4 {
                margin-bottom: 0.5rem;
            }

            .product-item .fields {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .product-item input {
                padding: 0.25rem;
                font-size: 0.9rem;
            }

            .product-item button {
                margin-top: 0.5rem;
            }

            .stockin-summary {
                position: sticky;
                top: 100px;
            }

            #stockTable {
                width: 100%;
                border-collapse: collapse;
                margin-top: 1rem;
                font-size: 0.9rem;
            }

            #stockTable th, #stockTable td {
                padding: 0.5rem;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }

            #stockTable th {
                background: #ecf0f1;
            }

            .stock-total {
                font-size: 1.2rem;
                font-weight: bold;
                color: #27ae60;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 2px solid #27ae60;
            }
        </style>

        <div class="stockin-container">
            {{-- Products Section --}}
            <div>
                <h3>Add Stock Items</h3>
                @if($products->isEmpty())
                    <div class="empty-state">
                        <p>No products available. <a href="{{ route('products.create') }}">Create products first</a></p>
                    </div>
                @else
                    <div class="product-grid">
                        @foreach($products as $product)
                            <div class="product-item">
                                <h4>{{ $product->product_name }}</h4>
                                <p style="font-size: 0.9rem; color: #666;">{{ $product->unit }}</p>
                                <div class="fields">
                                    <input type="number" placeholder="Quantity" min="1" value="1" data-product-id="{{ $product->id }}" data-product-name="{{ $product->product_name }}" data-product-unit="{{ $product->unit }}" class="qty-input">
                                    <input type="number" placeholder="Cost per unit" min="0" step="0.01" value="0" data-product-id="{{ $product->id }}" class="cost-input">
                                    <button type="button" class="btn btn-success" onclick="addStockItem(this)">Add</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Summary Section --}}
            <div class="card stockin-summary">
                <div class="card-header">Stock In Summary</div>

                <form action="{{ route('stock-in.store') }}" method="POST">
                    @csrf

                    <table id="stockTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Cost</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="stockBody">
                        </tbody>
                    </table>

                    <div class="stock-total">
                        Total Cost: <span id="totalCost">₱0.00</span>
                    </div>

                    <input type="hidden" id="stockItems" name="items" value="[]">

                    <div class="form-group">
                        <label for="supplier_id">Supplier</label>
                        <select id="supplier_id" name="supplier_id" required>
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->supplier_name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="employee_id">Employee</label>
                        <select id="employee_id" name="employee_id" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                            @endforeach
                        </select>
                        @error('employee_id')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">Record Stock In</button>
                        <a href="{{ route('stock-in.index') }}" class="btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <script>
            let stockItems = {};

            function addStockItem(button) {
                const fields = button.parentElement;
                const qtyInput = fields.querySelector('.qty-input');
                const costInput = fields.querySelector('.cost-input');
                const productId = qtyInput.getAttribute('data-product-id');
                const productName = qtyInput.getAttribute('data-product-name');
                const productUnit = qtyInput.getAttribute('data-product-unit');

                const qty = parseInt(qtyInput.value);
                const cost = parseFloat(costInput.value);

                if (qty <= 0 || cost < 0) {
                    alert('Please enter valid quantity and cost');
                    return;
                }

                stockItems[productId] = {
                    product_id: parseInt(productId),
                    product_name: productName,
                    unit: productUnit,
                    quantity: qty,
                    cost: cost
                };

                updateStockTable();
            }

            function removeStockItem(productId) {
                delete stockItems[productId];
                updateStockTable();
            }

            function updateStockTable() {
                let html = '';
                let total = 0;

                for (let id in stockItems) {
                    let item = stockItems[id];
                    let subtotal = item.quantity * item.cost;
                    total += subtotal;

                    html += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.quantity}</td>
                            <td>₱${item.cost.toFixed(2)}</td>
                            <td><button type="button" class="btn btn-danger btn-small" onclick="removeStockItem(${id})">Remove</button></td>
                        </tr>
                    `;
                }

                document.getElementById('stockBody').innerHTML = html || '<tr><td colspan="4" style="text-align:center;color:#999;">No items added</td></tr>';
                document.getElementById('totalCost').textContent = '₱' + total.toFixed(2);

                let stockArray = Object.values(stockItems);
                document.getElementById('stockItems').value = JSON.stringify(stockArray);
            }

            // Initialize
            updateStockTable();
        </script>
    </div>
</x-app-layout>
