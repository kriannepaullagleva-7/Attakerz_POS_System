@extends('components.app-layout')

@section('title', 'New Stock In')
@section('subtitle', 'Record incoming inventory from a supplier')

@push('styles')
<style>
    .si-form-grid { display: grid; grid-template-columns: 340px 1fr; gap: 20px; align-items: start; }

    .items-table { width: 100%; border-collapse: collapse; }
    .items-table thead th {
        background: var(--gray-50); padding: 10px 12px;
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.6px; color: var(--gray-600);
        border-bottom: 1px solid var(--gray-200); text-align: left;
    }
    .items-table tbody td { padding: 8px 6px; border-bottom: 1px solid var(--gray-100); vertical-align: middle; }
    .items-table tbody tr:last-child td { border-bottom: none; }

    .item-subtotal {
        font-family: var(--font-mono); font-weight: 700;
        color: var(--red-primary); font-size: 13px; text-align: right; padding-right: 8px;
    }

    .remove-item-btn {
        width: 28px; height: 28px; border-radius: 6px;
        background: #FEE2E2; border: none; color: #DC2626;
        cursor: pointer; font-size: 13px;
        display: flex; align-items: center; justify-content: center;
    }

    .add-row-btn {
        display: flex; align-items: center; gap: 8px;
        width: 100%; padding: 11px 16px;
        border: 1.5px dashed var(--gray-300);
        background: none; border-radius: 8px;
        font-family: var(--font); font-size: 13px; font-weight: 600;
        color: var(--gray-600); cursor: pointer;
        transition: all 0.15s; margin-top: 10px;
    }
    .add-row-btn:hover { border-color: var(--red-primary); color: var(--red-primary); background: #FEF2F2; }

    .total-bar {
        display: flex; justify-content: space-between; align-items: center;
        background: var(--red-primary); color: #fff;
        border-radius: 10px; padding: 14px 20px;
        margin-top: 16px;
    }
    .total-bar .label { font-size: 13px; font-weight: 600; opacity: 0.85; }
    .total-bar .amount { font-size: 22px; font-weight: 800; font-family: var(--font-mono); }

    @media (max-width: 900px) { .si-form-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('topbar-actions')
<a href="{{ route('stock-in.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back
</a>
@endsection

@section('content')

<form action="{{ route('stock-in.store') }}" method="POST" id="siForm">
@csrf
<input type="hidden" name="total_cost" id="totalCostInput">

<div class="si-form-grid">

    <!-- ─── LEFT: Transaction Info ─── -->
    <div class="card" style="position:sticky;top:80px;">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-clipboard-list" style="color:var(--red-primary)"></i> Transaction Details</div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Supplier <span style="color:var(--red-primary)">*</span></label>
                <select name="supplier_id" class="form-control" required>
                    <option value="">Select supplier...</option>
                    @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                        {{ $sup->supplier_name }}
                    </option>
                    @endforeach
                </select>
                <div style="margin-top:6px;">
                    <a href="{{ route('suppliers.index') }}" style="font-size:11px;color:var(--red-primary);text-decoration:none;">
                        <i class="fas fa-arrow-right"></i> Manage suppliers
                    </a>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Received By <span style="color:var(--red-primary)">*</span></label>
                <select name="employee_id" class="form-control" required>
                    <option value="">Select employee...</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->first_name }} {{ $emp->last_name }} — {{ $emp->role }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Delivery Date <span style="color:var(--red-primary)">*</span></label>
                <input type="datetime-local" name="date" class="form-control"
                    value="{{ old('date', now()->format('Y-m-d\TH:i')) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Notes / Remarks</label>
                <textarea name="notes" class="form-control" rows="3"
                    placeholder="Optional delivery notes...">{{ old('notes') }}</textarea>
            </div>

            <div style="margin-top:8px;">
                <div class="total-bar">
                    <div class="label"><i class="fas fa-peso-sign"></i> Total Cost</div>
                    <div class="amount" id="totalDisplay">₱0.00</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-top:14px;" id="submitBtn" disabled>
                <i class="fas fa-truck-ramp-box"></i> Record Stock In
            </button>
        </div>
    </div>

    <!-- ─── RIGHT: Items ─── -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="fas fa-boxes-stacked" style="color:var(--red-primary)"></i> Items Received</div>
                <div class="card-subtitle">Add all products received in this delivery</div>
            </div>
            <span class="badge badge-blue" id="itemCountBadge">0 items</span>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width:35%">Product</th>
                            <th style="width:18%">Qty</th>
                            <th style="width:22%">Cost / Unit</th>
                            <th style="width:18%">Subtotal</th>
                            <th style="width:7%"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <!-- rows added by JS -->
                    </tbody>
                </table>
            </div>

            <button type="button" class="add-row-btn" onclick="addRow()">
                <i class="fas fa-plus-circle"></i> Add Product Line
            </button>

            @error('items')
            <div class="alert alert-error" style="margin-top:12px;">{{ $message }}</div>
            @enderror
        </div>
    </div>

</div>
</form>

@endsection

@push('scripts')
<script>
const products = @json($products);

const productOptions = products.map(p =>
    `<option value="${p.id}" data-unit="${p.unit}">${p.product_name} (${p.unit})</option>`
).join('');

let rowCount = 0;

function addRow() {
    rowCount++;
    const id = 'row_' + rowCount;

    const tr = document.createElement('tr');
    tr.id = id;
    tr.innerHTML = `
        <td>
            <select name="product_id[]" class="form-control" onchange="updateRowMeta('${id}')" required>
                <option value="">Select product...</option>
                ${productOptions}
            </select>
        </td>
        <td>
            <input type="number" name="quantity[]" class="form-control" min="1" step="0.01"
                placeholder="0" oninput="calcRow('${id}')" required style="font-family:var(--font-mono);">
        </td>
        <td>
            <div style="position:relative;">
                <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--gray-600);font-size:12px;">₱</span>
                <input type="number" name="cost_per_unit[]" class="form-control" min="0" step="0.01"
                    placeholder="0.00" oninput="calcRow('${id}')" required style="padding-left:22px;font-family:var(--font-mono);">
            </div>
        </td>
        <td class="item-subtotal" id="sub_${id}">₱0.00</td>
        <td>
            <button type="button" class="remove-item-btn" onclick="removeRow('${id}')">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    document.getElementById('itemsBody').appendChild(tr);
    updateSummary();
}

function removeRow(id) {
    document.getElementById(id)?.remove();
    updateSummary();
}

function calcRow(id) {
    const row = document.getElementById(id);
    if (!row) return;
    const qty  = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
    const cost = parseFloat(row.querySelector('input[name="cost_per_unit[]"]').value) || 0;
    const sub  = qty * cost;
    document.getElementById('sub_' + id).textContent = '₱' + sub.toLocaleString('en-PH', {minimumFractionDigits:2});
    updateSummary();
}

function updateSummary() {
    const rows = document.querySelectorAll('#itemsBody tr');
    let total = 0;

    rows.forEach(row => {
        const qty  = parseFloat(row.querySelector('input[name="quantity[]"]')?.value) || 0;
        const cost = parseFloat(row.querySelector('input[name="cost_per_unit[]"]')?.value) || 0;
        total += qty * cost;
    });

    document.getElementById('totalDisplay').textContent = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits:2});
    document.getElementById('totalCostInput').value = total.toFixed(2);
    document.getElementById('itemCountBadge').textContent = rows.length + ' item' + (rows.length !== 1 ? 's' : '');
    document.getElementById('submitBtn').disabled = rows.length === 0;
}

// Start with one row
addRow();
</script>
@endpush