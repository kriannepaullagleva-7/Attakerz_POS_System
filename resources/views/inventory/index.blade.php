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
                    <th>Stock Used</th>
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
                    <td style="color:var(--gray-600);font-size:13px;">{{ $inv->total_used ?? 0 }} {{ $inv->product->unit }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $status }}</span></td>
                    <td style="font-size:13px;color:var(--gray-600);">{{ $inv->border_point ?? 10 }} {{ $inv->product->unit }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="toggleAddStock({{ $inv->id }})">
                            <i class="fas fa-plus"></i> Add Stock
                        </button>
                    </td>
                </tr>
                <tr>
                    <td colspan="7" style="padding:0;">
                        <div class="quick-add-form" id="addForm-{{ $inv->id }}">
                            <form action="{{ route('inventory.quick-add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $inv->product_id }}">
                                <div class="quick-add-row">
                                    <div class="form-group" style="margin:0;flex:0 0 140px;">
                                        <label class="form-label">Quantity to Add</label>
                                        <input type="number" name="quantity" class="form-control" min="1" placeholder="0" required>
                                    </div>
                                    <div class="form-group" style="margin:0;flex:0 0 140px;">
                                        <label class="form-label">Cost per Unit</label>
                                        <input type="number" name="cost_per_unit" class="form-control" step="0.01" placeholder="0.00">
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
</script>
@endpush