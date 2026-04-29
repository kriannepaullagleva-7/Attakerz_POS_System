@extends('components.app-layout')

@section('title', 'Products')
@section('subtitle', 'Manage your raw materials and finished goods catalog')

@push('styles')
<style>
    .prod-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }

    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 999; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: #fff; border-radius: 14px; width: 480px; box-shadow: 0 24px 64px rgba(0,0,0,0.15); }
    .modal-header { padding: 18px 22px; border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; justify-content: space-between; }
    .modal-title { font-size: 16px; font-weight: 700; }
    .modal-close { background: none; border: none; font-size: 18px; cursor: pointer; color: var(--gray-600); }
    .modal-body { padding: 22px; }
    .modal-footer { padding: 16px 22px; border-top: 1px solid var(--gray-200); display: flex; gap: 10px; justify-content: flex-end; }

    .category-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;
    }
    .pill-raw { background: #DBEAFE; color: #1E40AF; }
    .pill-finished { background: #FEE2E2; color: #991B1B; }

    .price-cell { font-family: var(--font-mono); font-weight: 700; font-size: 14px; color: var(--red-primary); }

    .radio-group { display: flex; gap: 12px; }
    .radio-card {
        flex: 1;
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        padding: 12px 14px;
        cursor: pointer;
        transition: all 0.15s;
        display: flex; align-items: center; gap: 10px;
    }
    .radio-card input[type="radio"] { display: none; }
    .radio-card.selected { border-color: var(--red-primary); background: #FEF2F2; }
    .radio-card .rc-icon { font-size: 20px; }
    .radio-card .rc-label { font-size: 13px; font-weight: 700; color: var(--gray-800); }
    .radio-card .rc-desc { font-size: 11px; color: var(--gray-600); }
</style>
@endpush

@section('topbar-actions')
<button class="btn btn-primary" onclick="openAddModal()">
    <i class="fas fa-plus"></i> Add Product
</button>
@endsection

@section('content')

<div class="prod-stats">
    <div class="stat-card">
        <div class="icon" style="background:#FEE2E2;"><i class="fas fa-drumstick-bite" style="color:var(--red-primary)"></i></div>
        <div class="value">{{ $totalProducts }}</div>
        <div class="label">Total Products</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:#DBEAFE;"><i class="fas fa-wheat-awn" style="color:#2563EB"></i></div>
        <div class="value">{{ $rawCount }}</div>
        <div class="label">Raw Materials</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:#D1FAE5;"><i class="fas fa-fire-burner" style="color:var(--success)"></i></div>
        <div class="value">{{ $finishedCount }}</div>
        <div class="label">Finished Products</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Product Catalog</div>
        <div style="display:flex;gap:10px;align-items:center;">
            <div style="position:relative;">
                <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--gray-600);font-size:12px;"></i>
                <input type="text" id="prodSearch" class="form-control" placeholder="Search products..." style="padding-left:30px;width:220px;">
            </div>
            <div style="display:flex;gap:4px;background:var(--gray-100);padding:4px;border-radius:8px;">
                <button class="tab-btn active" data-cat="all" onclick="filterProds(this)">All</button>
                <button class="tab-btn" data-cat="raw" onclick="filterProds(this)">Raw</button>
                <button class="tab-btn" data-cat="finished" onclick="filterProds(this)">Finished</button>
            </div>
        </div>
    </div>
    <div class="table-wrap">
        <table id="prodTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $i => $product)
                <tr data-cat="{{ $product->category }}">
                    <td style="color:var(--gray-600);font-size:12px;">{{ $i + 1 }}</td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $product->product_name }}</div>
                    </td>
                    <td>
                        <span class="category-pill {{ $product->category === 'finished' ? 'pill-finished' : 'pill-raw' }}">
                            {{ $product->category === 'finished' ? '🍗 Finished' : '🌾 Raw' }}
                        </span>
                    </td>
                    <td style="font-size:13px;color:var(--gray-600);">{{ $product->unit }}</td>
                    <td class="price-cell">₱{{ number_format($product->price, 2) }}</td>
                    <td>
                        @if($product->inventory)
                            @php $qty = $product->inventory->quantity_on_hand; @endphp
                            <span class="badge {{ $qty <= 0 ? 'badge-red' : ($qty <= 10 ? 'badge-yellow' : 'badge-green') }}">
                                {{ $qty }} {{ $product->unit }}
                            </span>
                        @else
                            <span class="badge badge-gray">No inventory</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-sm btn-secondary"
                                onclick="openEditModal(
                                    {{ $product->id }},
                                    '{{ addslashes($product->product_name) }}',
                                    '{{ $product->category }}',
                                    '{{ $product->unit }}',
                                    {{ $product->price }}
                                )">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                onsubmit="return confirm('Delete {{ $product->product_name }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px;color:var(--gray-600);">
                        <i class="fas fa-drumstick-bite" style="font-size:32px;opacity:0.2;display:block;margin-bottom:12px;"></i>
                        No products yet. Add your first product!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ─── ADD MODAL ─── -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-plus-circle" style="color:var(--red-primary)"></i> Add Product</div>
            <button class="modal-close" onclick="closeModal('addModal')"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Product Name <span style="color:var(--red-primary)">*</span></label>
                    <input type="text" name="product_name" class="form-control" placeholder="e.g. Whole Lechon Manok" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Category <span style="color:var(--red-primary)">*</span></label>
                    <div class="radio-group" id="addCatGroup">
                        <label class="radio-card" id="addRawCard" onclick="selectCat('add','raw',this)">
                            <input type="radio" name="category" value="raw">
                            <div class="rc-icon">🌾</div>
                            <div>
                                <div class="rc-label">Raw Material</div>
                                <div class="rc-desc">Ingredients &amp; supplies</div>
                            </div>
                        </label>
                        <label class="radio-card" id="addFinCard" onclick="selectCat('add','finished',this)">
                            <input type="radio" name="category" value="finished">
                            <div class="rc-icon">🍗</div>
                            <div>
                                <div class="rc-label">Finished Product</div>
                                <div class="rc-desc">Ready to sell items</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Unit <span style="color:var(--red-primary)">*</span></label>
                        <select name="unit" class="form-control" required>
                            <option value="">Select unit...</option>
                            <option value="pcs">pcs (pieces)</option>
                            <option value="kg">kg (kilograms)</option>
                            <option value="g">g (grams)</option>
                            <option value="L">L (liters)</option>
                            <option value="sack">sack</option>
                            <option value="bottle">bottle</option>
                            <option value="pack">pack</option>
                            <option value="serving">serving</option>
                            <option value="tray">tray</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (₱) <span style="color:var(--red-primary)">*</span></label>
                        <input type="number" name="price" class="form-control" min="0" step="0.01"
                            placeholder="0.00" required style="font-family:var(--font-mono);">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- ─── EDIT MODAL ─── -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-pen" style="color:var(--red-primary)"></i> Edit Product</div>
            <button class="modal-close" onclick="closeModal('editModal')"><i class="fas fa-times"></i></button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Product Name <span style="color:var(--red-primary)">*</span></label>
                    <input type="text" name="product_name" id="editName" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <div class="radio-group" id="editCatGroup">
                        <label class="radio-card" id="editRawCard" onclick="selectCat('edit','raw',this)">
                            <input type="radio" name="category" value="raw">
                            <div class="rc-icon">🌾</div>
                            <div><div class="rc-label">Raw Material</div></div>
                        </label>
                        <label class="radio-card" id="editFinCard" onclick="selectCat('edit','finished',this)">
                            <input type="radio" name="category" value="finished">
                            <div class="rc-icon">🍗</div>
                            <div><div class="rc-label">Finished Product</div></div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Unit <span style="color:var(--red-primary)">*</span></label>
                        <select name="unit" id="editUnit" class="form-control" required>
                            <option value="pcs">pcs</option>
                            <option value="kg">kg</option>
                            <option value="g">g</option>
                            <option value="L">L</option>
                            <option value="sack">sack</option>
                            <option value="bottle">bottle</option>
                            <option value="pack">pack</option>
                            <option value="serving">serving</option>
                            <option value="tray">tray</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (₱)</label>
                        <input type="number" name="price" id="editPrice" class="form-control"
                            min="0" step="0.01" style="font-family:var(--font-mono);">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Update Product</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<style>
.tab-btn { padding:6px 14px;border-radius:6px;border:none;background:none;font-family:var(--font);font-size:12px;font-weight:600;color:var(--gray-600);cursor:pointer;transition:all 0.15s; }
.tab-btn.active { background:#fff;color:var(--gray-800);box-shadow:0 1px 3px rgba(0,0,0,0.1); }
</style>
<script>
function openAddModal() { document.getElementById('addModal').classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function selectCat(prefix, val, el) {
    const group = document.getElementById(prefix + 'CatGroup');
    group.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;
}

function openEditModal(id, name, category, unit, price) {
    document.getElementById('editForm').action = `/products/${id}`;
    document.getElementById('editName').value = name;
    document.getElementById('editUnit').value = unit;
    document.getElementById('editPrice').value = price;

    // Set category
    const rawCard = document.getElementById('editRawCard');
    const finCard = document.getElementById('editFinCard');
    rawCard.classList.remove('selected');
    finCard.classList.remove('selected');
    if (category === 'finished') {
        finCard.classList.add('selected');
        finCard.querySelector('input').checked = true;
    } else {
        rawCard.classList.add('selected');
        rawCard.querySelector('input').checked = true;
    }
    document.getElementById('editModal').classList.add('show');
}

// Search
document.getElementById('prodSearch').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    filterTable(val);
});

function filterProds(btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filterTable(document.getElementById('prodSearch').value.toLowerCase());
}

function filterTable(search) {
    const cat = document.querySelector('.tab-btn.active').dataset.cat;
    document.querySelectorAll('#prodTable tbody tr').forEach(row => {
        const name = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() ?? '';
        const rowCat = row.dataset.cat ?? '';
        const matchSearch = name.includes(search);
        const matchCat = cat === 'all' || rowCat === cat;
        row.style.display = matchSearch && matchCat ? '' : 'none';
    });
}

['addModal','editModal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) closeModal(id);
    });
});
</script>
@endpush