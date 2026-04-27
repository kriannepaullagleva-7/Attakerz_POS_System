<x-app-layout>
    <div class="container-fluid">
        <h2 class="mb-4"><i class="bi bi-cash-coin"></i> Create Sale</h2>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            {{-- Products Section --}}
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-grid-3x3-gap"></i> Available Products</h5>
                    </div>
                    <div class="card-body">
                        @if($products->isEmpty())
                            <div class="alert alert-info text-center" role="alert">
                                <i class="bi bi-info-circle"></i> No products available. <a href="{{ route('products.create') }}">Create products first</a>
                            </div>
                        @else
                            <div class="row g-3">
                                @foreach($products as $product)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 product-card">
                                            <div class="card-body text-center">
                                                <h5 class="card-title">{{ $product->product_name }}</h5>
                                                <p class="text-success fs-5 fw-bold">₱{{ number_format($product->price, 2) }}</p>
                                                <button type="button" class="btn btn-sm btn-success w-100" data-product-id="{{ $product->id }}" data-product-name="{{ $product->product_name }}" data-product-price="{{ $product->price }}" onclick="addToCartFromButton(this)">
                                                    <i class="bi bi-plus-circle"></i> Add
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Cart Section --}}
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-cart3"></i> Shopping Cart</h5>
                    </div>

                    <form action="{{ route('sales.store') }}" method="POST">
                        @csrf

                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-borderless" id="cartTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody">
                                </tbody>
                            </table>
                        </div>

                        <div class="card-body border-top">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee *</label>
                                <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                                    <option value="">-- Select Employee --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 p-3 bg-light rounded">
                                <div class="fs-5 fw-bold">
                                    Total: <span id="totalAmount" class="text-success">₱0.00</span>
                                </div>
                            </div>

                            <input type="hidden" id="cartItems" name="items" value="[]">

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Complete Sale
                                </button>
                                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .product-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid #e0e0e0;
        }
        .product-card:hover {
            border-color: #28a745;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
            transform: translateY(-4px);
        }
    </style>
                        <a href="{{ route('sales.index') }}" class="btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <script>
            let cart = {};

            function addToCartFromButton(button) {
                const id = parseInt(button.getAttribute('data-product-id'));
                const name = button.getAttribute('data-product-name');
                const price = parseFloat(button.getAttribute('data-product-price'));
                addToCart(id, name, price);
            }

            function addToCart(id, name, price) {
                if (cart[id]) {
                    cart[id].quantity++;
                } else {
                    cart[id] = {
                        product_id: id,
                        product_name: name,
                        price: price,
                        quantity: 1
                    };
                }
                updateCart();
            }

            function removeFromCart(id) {
                delete cart[id];
                updateCart();
            }

            function updateQuantity(id, qty) {
                qty = parseInt(qty);
                if (qty <= 0) {
                    removeFromCart(id);
                } else {
                    cart[id].quantity = qty;
                }
                updateCart();
            }

            function updateCart() {
                let html = '';
                let total = 0;

                for (let id in cart) {
                    let item = cart[id];
                    let subtotal = item.price * item.quantity;
                    total += subtotal;

                    html += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td><input type="number" class="qty-input" value="${item.quantity}" min="1" onchange="updateQuantity(${id}, this.value)"></td>
                            <td>₱${item.price.toFixed(2)}</td>
                            <td><button type="button" class="btn btn-danger btn-small" onclick="removeFromCart(${id})">Remove</button></td>
                        </tr>
                    `;
                }

                document.getElementById('cartBody').innerHTML = html || '<tr><td colspan="4" style="text-align:center;color:#999;">Cart is empty</td></tr>';
                document.getElementById('totalAmount').textContent = '₱' + total.toFixed(2);

                // Convert cart object to array
                let cartArray = Object.values(cart).map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity
                }));

                document.getElementById('cartItems').value = JSON.stringify(cartArray);
            }

            // Initialize empty cart display
            updateCart();
        </script>
    </div>
</x-app-layout>