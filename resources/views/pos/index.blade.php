@extends('layouts.app')

@section('title', 'Point of Sale')
@section('subtitle', 'Select items to add to order')

@push('styles')
<style>
    /* ─── POS LAYOUT ─────────────────────────────────────────── */
    .content { padding: 0 !important; }

    .pos-wrapper {
        display: grid;
        grid-template-columns: 1fr 320px;
        height: calc(100vh - 65px);
        overflow: hidden;
    }

    /* ─── LEFT: PRODUCT GRID ──────────────────────────────────── */
    .pos-left {
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .pos-search-bar {
        padding: 16px 20px;
        background: #fff;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .pos-search-wrap {
        flex: 1;
        position: relative;
    }

    .pos-search-wrap i {
        position: absolute;
        left: 12px; top: 50%;
        transform: translateY(-50%);
        color: var(--gray-600);
        font-size: 14px;
    }

    .pos-search {
        width: 100%;
        padding: 9px 14px 9px 36px;
        border: 1.5px solid var(--gray-200);
        border-radius: 8px;
        font-family: var(--font);
        font-size: 14px;
        outline: none;
        transition: border-color 0.15s;
    }

    .pos-search:focus { border-color: var(--red-primary); }

    .category-tabs {
        display: flex;
        gap: 6px;
        flex-wrap: nowrap;
        overflow-x: auto;
    }

    .cat-tab {
        padding: 7px 14px;
        border-radius: 20px;
        font-size: 12px; font-weight: 600;
        cursor: pointer;
        border: 1.5px solid var(--gray-200);
        background: #fff;
        color: var(--gray-600);
        white-space: nowrap;
        transition: all 0.15s;
        font-family: var(--font);
    }

    .cat-tab.active, .cat-tab:hover {
        background: var(--red-primary);
        border-color: var(--red-primary);
        color: #fff;
    }

    .product-grid {
        flex: 1;
        overflow-y: auto;
        padding: 16px 20px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        align-content: start;
    }

    @media (max-width: 1100px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }

    .product-card {
        background: #fff;
        border-radius: 10px;
        border: 1.5px solid var(--gray-200);
        overflow: hidden;
        cursor: pointer;
        transition: all 0.15s ease;
        position: relative;
    }

    .product-card:hover {
        border-color: var(--red-primary);
        box-shadow: 0 4px 16px rgba(192,57,43,0.12);
        transform: translateY(-2px);
    }

    .product-card.out-of-stock {
        opacity: 0.55;
        cursor: not-allowed;
        pointer-events: none;
    }

    .product-img {
        height: 90px;
        background: var(--gray-100);
        display: flex; align-items: center; justify-content: center;
        font-size: 36px;
        position: relative;
    }

    .product-category-badge {
        position: absolute; top: 6px; right: 6px;
        font-size: 9px; font-weight: 700;
        padding: 2px 7px; border-radius: 10px;
        text-transform: uppercase; letter-spacing: 0.5px;
    }

    .product-info { padding: 10px 12px; }
    .product-name { font-size: 13px; font-weight: 700; color: var(--gray-800); margin-bottom: 4px; }
    .product-stock { font-size: 11px; color: var(--gray-600); margin-bottom: 8px; }

    .product-footer {
        display: flex; align-items: center; justify-content: space-between;
    }

    .product-price {
        font-size: 15px; font-weight: 800;
        color: var(--red-primary);
        font-family: var(--font-mono);
    }

    .add-btn {
        width: 28px; height: 28px;
        background: var(--red-primary);
        border: none; border-radius: 6px;
        color: #fff; cursor: pointer;
        font-size: 14px;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.15s;
    }
    .add-btn:hover { background: var(--red-dark); }

    .oos-badge {
        position: absolute; inset: 0;
        background: rgba(0,0,0,0.35);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; color: #fff;
        border-radius: 10px;
    }

    /* ─── RIGHT: ORDER CART ───────────────────────────────────── */
    .pos-right {
        background: #fff;
        border-left: 1px solid var(--gray-200);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .cart-header {
        padding: 16px 18px;
        border-bottom: 1px solid var(--gray-200);
        display: flex; align-items: center; justify-content: space-between;
    }

    .cart-title { font-size: 14px; font-weight: 700; color: var(--gray-800); }
    .cart-count { 
        background: var(--red-primary); color: #fff;
        font-size: 11px; font-weight: 700;
        padding: 2px 8px; border-radius: 10px;
    }

    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 10px 0;
    }

    .cart-item {
        padding: 10px 18px;
        border-bottom: 1px solid var(--gray-100);
        display: flex; gap: 10px;
    }

    .ci-emoji { font-size: 24px; flex-shrink: 0; }

    .ci-info { flex: 1; }
    .ci-name { font-size: 13px; font-weight: 600; color: var(--gray-800); }
    .ci-price { font-size: 11px; color: var(--gray-600); }

    .ci-controls {
        display: flex; align-items: center; gap: 6px;
    }

    .qty-btn {
        width: 24px; height: 24px;
        border: 1.5px solid var(--gray-200);
        background: #fff; border-radius: 5px;
        cursor: pointer; font-size: 13px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        color: var(--gray-800);
        transition: all 0.1s;
    }

    .qty-btn:hover { background: var(--red-primary); border-color: var(--red-primary); color: #fff; }

    .qty-num {
        width: 28px; text-align: center;
        font-size: 13px; font-weight: 700;
        font-family: var(--font-mono);
    }

    .ci-total {
        text-align: right;
        min-width: 56px;
    }
    .ci-total-amt { font-size: 13px; font-weight: 700; color: var(--gray-800); font-family: var(--font-mono); }
    .ci-remove { 
        font-size: 10px; color: #EF4444; cursor: pointer; 
        background: none; border: none; font-family: var(--font);
        font-weight: 600;
    }

    .cart-empty {
        flex: 1; display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 40px 20px; color: var(--gray-600); text-align: center;
    }
    .cart-empty i { font-size: 36px; margin-bottom: 12px; opacity: 0.3; }
    .cart-empty p { font-size: 13px; }

    .cart-footer {
        padding: 16px 18px;
        border-top: 1px solid var(--gray-200);
        background: var(--gray-50);
    }

    .subtotal-row {
        display: flex; justify-content: space-between;
        font-size: 13px; color: var(--gray-600);
        margin-bottom: 6px;
    }

    .total-row {
        display: flex; justify-content: space-between; align-items: center;
        font-size: 18px; font-weight: 800; color: var(--gray-800);
        margin: 10px 0 14px;
        font-family: var(--font-mono);
    }

    .cash-input-wrap {
        margin-bottom: 10px;
    }

    .cash-label { font-size: 12px; font-weight: 600; color: var(--gray-600); margin-bottom: 5px; }

    .cash-input {
        width: 100%;
        padding: 9px 14px;
        border: 1.5px solid var(--gray-200);
        border-radius: 8px;
        font-family: var(--font-mono);
        font-size: 15px; font-weight: 700;
        text-align: right;
        outline: none;
        transition: border-color 0.15s;
    }
    .cash-input:focus { border-color: var(--red-primary); }

    .change-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 14px;
        background: #D1FAE5; border-radius: 8px;
        margin-bottom: 12px;
        font-family: var(--font-mono);
    }
    .change-label { font-size: 12px; font-weight: 600; color: #065F46; }
    .change-amount { font-size: 16px; font-weight: 800; color: #065F46; }

    .complete-btn {
        width: 100%;
        padding: 13px;
        background: var(--red-primary);
        color: #fff; border: none; border-radius: 10px;
        font-family: var(--font);
        font-size: 15px; font-weight: 700;
        cursor: pointer;
        transition: all 0.15s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .complete-btn:hover { background: var(--red-dark); }
    .complete-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .clear-btn {
        width: 100%;
        padding: 8px;
        background: none;
        color: var(--gray-600); border: none;
        font-family: var(--font);
        font-size: 12px; font-weight: 600;
        cursor: pointer; margin-top: 8px;
    }
    .clear-btn:hover { color: #EF4444; }

    /* ─── RECEIPT MODAL ───────────────────────────────────────── */
    .modal-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.5);
        display: none; align-items: center; justify-content: center;
        z-index: 999;
    }
    .modal-overlay.show { display: flex; }

    .receipt-modal {
        background: #fff;
        border-radius: 16px;
        width: 380px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 24px 64px rgba(0,0,0,0.2);
    }

    .receipt-header {
        background: var(--red-primary);
        color: #fff;
        padding: 20px;
        text-align: center;
        border-radius: 16px 16px 0 0;
    }

    .receipt-success-icon {
        width: 48px; height: 48px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        margin: 0 auto 10px;
    }

    .receipt-body { padding: 20px; }

    .receipt-store {
        text-align: center;
        padding-bottom: 14px;
        border-bottom: 2px dashed var(--gray-200);
        margin-bottom: 14px;
    }

    .receipt-store h3 { font-size: 16px; font-weight: 800; }
    .receipt-store p { font-size: 11px; color: var(--gray-600); }

    .receipt-meta { 
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 8px; margin-bottom: 14px;
    }
    .rm-item .rm-label { font-size: 10px; color: var(--gray-600); text-transform: uppercase; letter-spacing: 0.5px; }
    .rm-item .rm-value { font-size: 12px; font-weight: 600; color: var(--gray-800); }

    .receipt-items-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--gray-600); margin-bottom: 8px; }

    .receipt-item {
        display: flex; justify-content: space-between;
        font-size: 13px; padding: 5px 0;
        border-bottom: 1px solid var(--gray-100);
    }
    .receipt-item:last-child { border: none; }
    .ri-name { color: var(--gray-800); }
    .ri-qty { color: var(--gray-600); font-size: 12px; }
    .ri-price { font-weight: 600; font-family: var(--font-mono); }

    .receipt-total {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 0; border-top: 2px solid var(--gray-200); margin-top: 8px;
        font-size: 16px; font-weight: 800;
    }

    .receipt-actions { display: flex; gap: 8px; padding: 16px 20px 20px; }
    .receipt-actions .btn { flex: 1; justify-content: center; }
</style>
@endpush

@section('content')

<div class="pos-wrapper">
    <!-- ─── LEFT: PRODUCTS ─── -->
    <div class="pos-left">
        <div class="pos-search-bar">
            <div class="pos-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" class="pos-search" id="productSearch" placeholder="Search products...">
            </div>
            <div class="category-tabs">
                <button class="cat-tab active" data-cat="all">All Items</button>
            </div>
        </div>

        <div class="product-grid" id="productGrid">
            @foreach($products as $product)
            @php
                $stock = $product->inventory ? $product->inventory->quantity_on_hand : 0;
                $emojis = ['Whole Lechon Manok'=>'🍗','Half Chicken'=>'🍗','Quarter Chicken'=>'🍗','Plain Rice'=>'🍚','Java Rice'=>'🍚','Gravy'=>'🫙','Soda'=>'🥤','Juice'=>'🧃','Water'=>'💧'];
                $emoji = $emojis[$product->product_name] ?? ($product->category === 'finished' ? '🍽️' : '📦');
            @endphp
            <div class="product-card {{ $stock <= 0 ? 'out-of-stock' : '' }}"
                 data-id="{{ $product->id }}"
                 data-name="{{ $product->product_name }}"
                 data-price="{{ $product->price }}"
                 data-unit="{{ $product->unit }}"
                 data-stock="{{ $stock }}"
                 data-cat="{{ $product->category }}"
                 data-emoji="{{ $emoji }}"
                 onclick="addToCart(this)">
                <div class="product-img">
                    {{ $emoji }}
                    <span class="product-category-badge badge {{ $product->category === 'finished' ? 'badge-red' : 'badge-blue' }}">
                        {{ $product->category === 'finished' ? 'Finished' : 'Raw' }}
                    </span>
                    @if($stock <= 0)
                    <div class="oos-badge">Out of Stock</div>
                    @endif
                </div>
                <div class="product-info">
                    <div class="product-name">{{ $product->product_name }}</div>
                    <div class="product-stock">
                        {{ $stock }} {{ $product->unit }} in stock
                    </div>
                    <div class="product-footer">
                        <div class="product-price">₱{{ number_format($product->price, 2) }}</div>
                        <button class="add-btn" onclick="addToCart(this.closest('.product-card'))">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- ─── RIGHT: CART ─── -->
    <div class="pos-right">
        <div class="cart-header">
            <div class="cart-title">
                <i class="fas fa-bag-shopping" style="color:var(--red-primary)"></i>
                Current Order
            </div>
            <span class="cart-count" id="cartCount">0 items</span>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="cart-empty" id="cartEmpty">
                <i class="fas fa-shopping-basket"></i>
                <p>No items yet.<br>Tap a product to add it.</p>
            </div>
        </div>

        <div class="cart-footer">
            <div class="subtotal-row">
                <span>Subtotal</span>
                <span id="subtotalAmt">₱0.00</span>
            </div>
            <div class="subtotal-row">
                <span>Items</span>
                <span id="totalItems">0</span>
            </div>
            <div class="total-row">
                <span style="font-family:var(--font);font-size:14px;font-weight:700">TOTAL</span>
                <span id="totalAmt">₱0.00</span>
            </div>

            <div class="cash-input-wrap">
                <div class="cash-label">Cash Payment</div>
                <input type="number" class="cash-input" id="cashInput" placeholder="0.00" oninput="calcChange()">
            </div>

            <div class="change-row" id="changeRow" style="display:none;">
                <span class="change-label"><i class="fas fa-coins"></i> Change</span>
                <span class="change-amount" id="changeAmt">₱0.00</span>
            </div>

            <button class="complete-btn" id="completeBtn" onclick="completeSale()" disabled>
                <i class="fas fa-circle-check"></i> Complete Order
            </button>
            <button class="clear-btn" onclick="clearCart()">
                <i class="fas fa-trash"></i> Clear Cart
            </button>
        </div>
    </div>
</div>

<!-- ─── RECEIPT MODAL ─── -->
<div class="modal-overlay" id="receiptModal">
    <div class="receipt-modal">
        <div class="receipt-header">
            <div class="receipt-success-icon"><i class="fas fa-check"></i></div>
            <h3 style="font-size:16px;margin-bottom:4px;">Order Completed!</h3>
            <p style="font-size:12px;opacity:0.8;">Transaction processed successfully</p>
        </div>
        <div class="receipt-body">
            <div class="receipt-store">
                <h3>🍗 ATTACKERS</h3>
                <p style="font-weight:700;font-size:13px;">LECHON MANOK</p>
                <p>171 Main Street, Bunawan, Davao City</p>
                <p>Mobile: +63 917 123 4567</p>
            </div>
            <div style="font-weight:700;font-size:13px;text-align:center;margin-bottom:12px;color:var(--red-primary);letter-spacing:2px;">OFFICIAL RECEIPT</div>
            <div class="receipt-meta" id="receiptMeta"></div>
            <div class="receipt-items-title">Order Items</div>
            <div id="receiptItems"></div>
            <div class="receipt-total">
                <span>Total</span>
                <span id="receiptTotal" style="font-family:var(--font-mono);color:var(--red-primary)"></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--gray-600);">
                <span>Cash</span><span id="receiptCash" style="font-family:var(--font-mono)"></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;color:var(--success);margin-top:4px;">
                <span>Change</span><span id="receiptChange" style="font-family:var(--font-mono)"></span>
            </div>
        </div>
        <div class="receipt-actions">
            <button class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            <button class="btn btn-primary" onclick="newOrder()"><i class="fas fa-plus"></i> New Order</button>
        </div>
    </div>
</div>

<form id="saleForm" action="{{ route('sales.store') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="cart_data" id="cartData">
    <input type="hidden" name="cash_paid" id="cashPaidInput">
    <input type="hidden" name="employee_id" value="{{ $employees->first()?->id }}">
</form>

@endsection

@push('scripts')
<script>
let cart = {};

function addToCart(el) {
    const id = el.dataset.id;
    const name = el.dataset.name;
    const price = parseFloat(el.dataset.price);
    const stock = parseInt(el.dataset.stock);
    const emoji = el.dataset.emoji;

    if (cart[id] && cart[id].qty >= stock) {
        alert('Not enough stock!');
        return;
    }

    if (!cart[id]) {
        cart[id] = { id, name, price, qty: 0, emoji, stock };
    }
    cart[id].qty++;
    renderCart();
}

function updateQty(id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) delete cart[id];
    renderCart();
}

function removeItem(id) {
    delete cart[id];
    renderCart();
}

function renderCart() {
    const items = Object.values(cart);
    const cartEl = document.getElementById('cartItems');
    const empty = document.getElementById('cartEmpty');
    const count = items.reduce((s, i) => s + i.qty, 0);
    const total = items.reduce((s, i) => s + (i.price * i.qty), 0);

    document.getElementById('cartCount').textContent = count + ' item' + (count !== 1 ? 's' : '');
    document.getElementById('subtotalAmt').textContent = '₱' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('totalAmt').textContent = '₱' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('totalItems').textContent = count;

    if (items.length === 0) {
        empty.style.display = 'flex';
        document.getElementById('completeBtn').disabled = true;
        return;
    }

    empty.style.display = 'none';

    let html = '';
    items.forEach(item => {
        html += `
        <div class="cart-item">
            <div class="ci-emoji">${item.emoji}</div>
            <div class="ci-info">
                <div class="ci-name">${item.name}</div>
                <div class="ci-price">₱${item.price.toFixed(2)} each</div>
            </div>
            <div>
                <div class="ci-controls">
                    <button class="qty-btn" onclick="updateQty('${item.id}', -1)">−</button>
                    <span class="qty-num">${item.qty}</span>
                    <button class="qty-btn" onclick="updateQty('${item.id}', 1)">+</button>
                </div>
                <div class="ci-total" style="margin-top:4px;">
                    <div class="ci-total-amt">₱${(item.price * item.qty).toFixed(2)}</div>
                    <button class="ci-remove" onclick="removeItem('${item.id}')">Remove</button>
                </div>
            </div>
        </div>`;
    });

    // Keep empty div but hidden, add items before it
    cartEl.innerHTML = html + '<div class="cart-empty" id="cartEmpty" style="display:none;"></div>';
    calcChange();

    const cash = parseFloat(document.getElementById('cashInput').value) || 0;
    const hasEnoughCash = cash >= total;
    document.getElementById('completeBtn').disabled = !hasEnoughCash || items.length === 0;
}

function calcChange() {
    const items = Object.values(cart);
    const total = items.reduce((s, i) => s + (i.price * i.qty), 0);
    const cash = parseFloat(document.getElementById('cashInput').value) || 0;
    const change = cash - total;
    const changeRow = document.getElementById('changeRow');

    if (cash > 0) {
        changeRow.style.display = 'flex';
        document.getElementById('changeAmt').textContent = '₱' + Math.max(0, change).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        changeRow.style.background = change < 0 ? '#FEE2E2' : '#D1FAE5';
        document.getElementById('changeAmt').style.color = change < 0 ? '#991B1B' : '#065F46';
    } else {
        changeRow.style.display = 'none';
    }

    document.getElementById('completeBtn').disabled = items.length === 0 || change < 0;
}

function completeSale() {
    const items = Object.values(cart);
    if (items.length === 0) return;

    const total = items.reduce((s, i) => s + (i.price * i.qty), 0);
    const cash = parseFloat(document.getElementById('cashInput').value) || 0;
    const change = cash - total;
    const orderNum = 'P' + String(Math.floor(Math.random() * 90000) + 10000);

    document.getElementById('cartData').value = JSON.stringify(items);
    document.getElementById('cashPaidInput').value = cash;

    // Show receipt
    const now = new Date();
    document.getElementById('receiptMeta').innerHTML = `
        <div class="rm-item"><div class="rm-label">Order No.</div><div class="rm-value">${orderNum}</div></div>
        <div class="rm-item"><div class="rm-label">Date</div><div class="rm-value">${now.toLocaleDateString('en-PH',{month:'short',day:'2-digit',year:'numeric'})}</div></div>
        <div class="rm-item"><div class="rm-label">Time</div><div class="rm-value">${now.toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</div></div>
        <div class="rm-item"><div class="rm-label">Cashier</div><div class="rm-value">Cashier 01</div></div>
    `;

    document.getElementById('receiptItems').innerHTML = items.map(i => `
        <div class="receipt-item">
            <div><div class="ri-name">${i.name}</div><div class="ri-qty">x${i.qty}</div></div>
            <div class="ri-price">₱${(i.price * i.qty).toFixed(2)}</div>
        </div>
    `).join('');

    document.getElementById('receiptTotal').textContent = '₱' + total.toFixed(2);
    document.getElementById('receiptCash').textContent = '₱' + cash.toFixed(2);
    document.getElementById('receiptChange').textContent = '₱' + change.toFixed(2);

    document.getElementById('receiptModal').classList.add('show');
    // Form is submitted when user clicks "New Order" (see newOrder())
}

function newOrder() {
    document.getElementById('saleForm').submit();
}

function clearCart() {
    cart = {};
    document.getElementById('cashInput').value = '';
    document.getElementById('changeRow').style.display = 'none';
    renderCart();
    const cartEl = document.getElementById('cartItems');
    cartEl.innerHTML = '<div class="cart-empty" id="cartEmpty" style="display:flex;"><i class="fas fa-shopping-basket"></i><p>No items yet.<br>Tap a product to add it.</p></div>';
    document.getElementById('cartCount').textContent = '0 items';
    document.getElementById('subtotalAmt').textContent = '₱0.00';
    document.getElementById('totalAmt').textContent = '₱0.00';
    document.getElementById('totalItems').textContent = '0';
}

// ─── SEARCH & FILTER ───
document.getElementById('productSearch').addEventListener('input', function() {
    filterProducts();
});

document.querySelectorAll('.cat-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        filterProducts();
    });
});

function filterProducts() {
    const search = document.getElementById('productSearch').value.toLowerCase();
    const cat = document.querySelector('.cat-tab.active').dataset.cat;

    document.querySelectorAll('.product-card').forEach(card => {
        const name = card.dataset.name.toLowerCase();
        const cardCat = card.dataset.cat;
        const matchSearch = name.includes(search);
        const matchCat = cat === 'all' || cardCat === cat;
        card.style.display = matchSearch && matchCat ? '' : 'none';
    });
}
</script>
@endpush