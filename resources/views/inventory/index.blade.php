@extends('components.app-layout')

@section('title', 'Inventory Management')
@section('subtitle', 'Track and manage your stock levels')

@push('styles')
<style>
    .inv-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }

    .filter-bar {
        background: #fff; border: 1px solid var(--gray-200); border-radius: 12px;
        padding: 14px 18px; margin-bottom: 16px;
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    }

    .filter-bar input { max-width: 220px; }

    .tab-group { display: flex; gap: 4px; background: var(--gray-100); padding: 4px; border-radius: 8px; }
    .tab-btn {
        padding: 6px 14px; border-radius: 6px; border: none; background: none;
        font-family: var(--font); font-size: 12px; font-weight: 600; color: var(--gray-600);
        cursor: pointer; transition: all 0.15s;
    }
    .tab-btn.active { background: #fff; color: var(--gray-800); box-shadow: 0 1px 3px rgba(0,0,0,0.1); }

    .stock-bar { width: 100px; height: 8px; background: var(--gray-200); border-radius: 4px; overflow: hidden; }
    .stock-fill { height: 100%; border-radius: 4px; }

    .quick-add-form {
        display: none; padding: 16px 20px; background: var(--gray-50);
        border-top: 1px solid var(--gray-200);
    }
    .quick-add-form.open { display: block; }
    .quick-add-row { display: flex; gap: 10px; align-items: flex-end; }

    /* Manage Stock Modal */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 999; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: #fff; border-radius: 14px; width: 460px; box-shadow: 0 24px 64px rgba(0,0,0,0.15); }
    .modal-header { padding: 18px 22px; border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; justify-content: space-between; }
    .modal-title { font-size: 16px; font-weight: 700; }
    .modal-close { background: none; border: none; font-size: 18px; cursor: pointer; color: var(--gray-600); }
    .modal-body { padding: 22px; }

    .manage-section {
        border: 1.5px solid var(--gray-200); border-radius: 10px;
        padding: 16px; margin-bottom: 14px;
    }
    .manage-section-title {
        font-size: 13px; font-weight: 700; color: var(--gray-800);
        margin-bottom: 10px; display: flex; align-items: center; gap: 7px;
    }
    .danger-zone {
        border-color: #FCA5A5; background: #FFF5F5;
    }
    .danger-zone .manage-section-title { color: #991B1B; }
    .danger-note { font-size: 12px; color: #991B1B; margin-bottom: 12px; line-height: 1.5; }
</style>
@endpush

@section('topbar-actions')
<a href="{{ route('stock-in.create') }}" class="btn btn-primary">
    <i class="fas fa-truck-ramp-box"></i> Add Stock
</a>
@endsection

@section('content')

<!-- Stats -->
<div class="inv-stats">
    <div class="stat-card">
        <div class="icon" style="background:#FEE2E2;"><i class="fas fa-layer-group" style="color:var(--red-primary)"></i></div>
        <div class="value">{{ $totalItems }}</div>
        <div class="label">Total Items</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:#DBEAFE;"><i class="fas fa-wheat-awn" style="color:#2563EB"></i></div>
        <div class="value">{{ $rawMaterialCount }}</div>
        <div class="label">Raw Materials</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:#D1FAE5;"><i class="fas fa-drumstick-bite" style="color:var(--success)"></i></div>
        <div class="value">{{ $finishedCount }}</div>
        <div class="label">Finished Products</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:#FEF3C7;"><i class="fas fa-triangle-exclamation" style="color:#D97706"></i></div>
        <div class="value">{{ $lowStockCount }}</div>
        <div class="label">Low Stock Items</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <div style="position:relative;flex:1;max-width:260px;">
        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--gray-600);font-size:13px;"></i>
        <input type="text" class="form-control" id="invSearch" placeholder="Search inventory..." style="padding-left:32px;">
    </div>
    <div class="tab-group">
        <button class="tab-btn active" data-filter="all">All Items</button>
        <button class="tab-btn" data-filter="raw">Raw Materials</button>
        <button class="tab-btn" data-filter="finished">Finished Products</button>
    </div>
    <div style="margin-left:auto;font-size:13px;color:var(--gray-600);">
        Last updated: {{ now()->format('M d, Y h:i A') }}
    </div>
</div>

<!-- Inventory Table -->
<div class="card">
    <div class="table-wrap">
        <table id="invTable">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Type</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Border Point</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventories as $inv)
                @php
                    $qty = $inv->quantity_on_hand;
                    $border = $inv->border_point ?? 10;
                    $pct = min(100, ($qty / max(1, $border * 3)) * 100);
                    $status = $qty <= 0 ? 'Out of Stock' : ($qty <= $border ? 'Low' : 'Medium');
                    $badgeClass = $qty <= 0 ? 'badge-red' : ($qty <= $border ? 'badge-yellow' : 'badge-green');
                    $fillClass = $qty <= 0 ? 'critical' : ($qty <= $border ? 'low' : 'ok');
                    $colors = ['critical'=>'#EF4444','low'=>'#F59E0B','ok'=>'#059669'];
                @endphp
                <tr class="inv-row" data-type="{{ $inv->product->category }}">
                    <td>
                        <div style="font-weight:600;color:var(--gray-800);">{{ $inv->product->product_name }}</div>
                        <div style="font-size:11px;color:var(--gray-600);">per {{ $inv->product->unit }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $inv->product->category === 'finished' ? 'badge-red' : 'badge-blue' }}">
                            {{ $inv->product->category === 'finished' ? 'Finished' : 'Raw' }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="stock-bar">
                                <div class="stock-fill" style="width:{{ $pct }}%;background:{{ $colors[$fillClass] }};"></div>
                            </div>
                            <span style="font-weight:600;font-family:var(--font-mono);font-size:13px;">
                                {{ $qty }} <span style="font-weight:400;color:var(--gray-600);font-family:var(--font);font-size:11px;">{{ $inv->product->unit }}</span>
                            </span>
                        </div>
                    </td>
                    <td><span class="badge {{ $badgeClass }}">{{ $status }}</span></td>
                    <td style="font-size:13px;color:var(--gray-600);">{{ $inv->border_point ?? 10 }} {{ $inv->product->unit }}</td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <button class="btn btn-sm btn-primary" onclick="toggleAddStock({{ $inv->id }})">
                                <i class="fas fa-plus"></i> Add Stock
                            </button>
                            <button class="btn btn-sm btn-danger" title="Manage / Remove stock"
                                onclick="openManageModal(
                                    {{ $inv->id }},
                                    '{{ addslashes($inv->product->product_name) }}',
                                    {{ $inv->quantity_on_hand }},
                                    '{{ $inv->product->unit }}'
                                )">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="6" style="padding:0;">
                        <div class="quick-add-form" id="addForm-{{ $inv->id }}">
                            <form action="{{ route('inventory.quick-add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $inv->product_id }}">
                                <div class="quick-add-row">
                                    <div class="form-group" style="margin:0;flex:0 0 160px;">
                                        <label class="form-label">Quantity to Add</label>
                                        <input type="number" name="quantity" class="form-control" min="1" step="1" placeholder="0" required>
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> Confirm Add
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="toggleAddStock({{ $inv->id }})">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- ─── MANAGE STOCK MODAL ─── -->
<div class="modal-overlay" id="manageModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fas fa-sliders" style="color:var(--red-primary)"></i>
                Manage Stock — <span id="manageProductName"></span>
            </div>
            <button class="modal-close" onclick="closeManageModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">

            <div style="background:var(--gray-50);border-radius:8px;padding:10px 14px;margin-bottom:18px;font-size:13px;color:var(--gray-600);display:flex;align-items:center;gap:8px;">
                <i class="fas fa-boxes-stacked" style="color:var(--red-primary);"></i>
                Current stock: <strong id="manageCurrentQty" style="color:var(--gray-800);margin-left:4px;font-family:var(--font-mono);"></strong>
            </div>

            <!-- Reduce quantity -->
            <div class="manage-section">
                <div class="manage-section-title">
                    <i class="fas fa-minus-circle" style="color:#D97706;"></i> Reduce Stock
                </div>
                <form id="reduceForm" method="POST">
                    @csrf @method('PATCH')
                    <div style="display:flex;gap:10px;align-items:flex-end;">
                        <div class="form-group" style="margin:0;flex:1;">
                            <label class="form-label">Quantity to Remove</label>
                            <input type="number" name="quantity" id="reduceQty" class="form-control"
                                min="1" step="1" placeholder="0" required>
                        </div>
                        <div style="padding-bottom:1px;">
                            <span id="reduceUnit" style="font-size:13px;color:var(--gray-600);white-space:nowrap;"></span>
                        </div>
                        <button type="submit" class="btn btn-secondary" style="flex-shrink:0;margin-bottom:1px;">
                            <i class="fas fa-check"></i> Confirm
                        </button>
                    </div>
                </form>
            </div>

            <!-- Remove entirely -->
            <div class="manage-section danger-zone">
                <div class="manage-section-title">
                    <i class="fas fa-trash"></i> Remove from Inventory
                </div>
                <p class="danger-note">This permanently removes the inventory record for this product. The product itself is not deleted. Use this only if you want to stop tracking this item.</p>
                <form id="deleteForm" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"
                        onclick="return confirm('Are you sure? This will remove the inventory record entirely.')">
                        <i class="fas fa-trash"></i> Remove Entirely
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleAddStock(id) {
    const form = document.getElementById('addForm-' + id);
    form.classList.toggle('open');
}

// Filter
document.getElementById('invSearch').addEventListener('input', filterInv);
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        filterInv();
    });
});

function filterInv() {
    const search = document.getElementById('invSearch').value.toLowerCase();
    const filter = document.querySelector('.tab-btn.active').dataset.filter;

    document.querySelectorAll('.inv-row').forEach(row => {
        const name = row.querySelector('td:first-child').textContent.toLowerCase();
        const type = row.dataset.type;
        const matchSearch = name.includes(search);
        const matchFilter = filter === 'all' || type === filter;
        // Show/hide both the data row and its following quick-add row
        row.style.display = matchSearch && matchFilter ? '' : 'none';
        const next = row.nextElementSibling;
        if (next) next.style.display = matchSearch && matchFilter ? '' : 'none';
    });
}

function openManageModal(id, name, qty, unit) {
    document.getElementById('manageProductName').textContent = name;
    document.getElementById('manageCurrentQty').textContent  = qty + ' ' + unit;
    document.getElementById('reduceUnit').textContent        = unit;
    document.getElementById('reduceQty').max                 = qty;
    document.getElementById('reduceQty').value               = '';
    document.getElementById('reduceForm').action  = `/inventory/${id}/reduce`;
    document.getElementById('deleteForm').action  = `/inventory/${id}`;
    document.getElementById('manageModal').classList.add('show');
}

function closeManageModal() {
    document.getElementById('manageModal').classList.remove('show');
}

document.getElementById('manageModal').addEventListener('click', function(e) {
    if (e.target === this) closeManageModal();
});
</script>
@endpush