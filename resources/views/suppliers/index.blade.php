@extends('components.app-layout')

@section('title', 'Suppliers')
@section('subtitle', 'Manage your ingredient and supply vendors')

@push('styles')
<style>
    .supplier-card {
        background: #fff;
        border: 1.5px solid var(--gray-200);
        border-radius: 12px;
        padding: 18px;
        transition: all 0.15s;
        position: relative;
    }
    .supplier-card:hover {
        border-color: var(--red-primary);
        box-shadow: 0 4px 16px rgba(192,57,43,0.1);
    }

    .sup-avatar {
        width: 44px; height: 44px;
        background: var(--red-primary);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; font-weight: 800; color: #fff;
        margin-bottom: 12px;
        flex-shrink: 0;
    }

    .sup-name { font-size: 15px; font-weight: 700; color: var(--gray-800); margin-bottom: 4px; }
    .sup-meta { font-size: 12px; color: var(--gray-600); display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
    .sup-meta i { color: var(--red-primary); width: 13px; }

    .sup-actions { display: flex; gap: 6px; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--gray-100); }

    /* Modal */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 999; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: #fff; border-radius: 14px; width: 500px; box-shadow: 0 24px 64px rgba(0,0,0,0.15); }
    .modal-header { padding: 18px 22px; border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; justify-content: space-between; }
    .modal-title { font-size: 16px; font-weight: 700; }
    .modal-close { background: none; border: none; font-size: 18px; cursor: pointer; color: var(--gray-600); }
    .modal-body { padding: 22px; }
    .modal-footer { padding: 16px 22px; border-top: 1px solid var(--gray-200); display: flex; gap: 10px; justify-content: flex-end; }
</style>
@endpush

@section('topbar-actions')
<button class="btn btn-primary" onclick="openModal()">
    <i class="fas fa-plus"></i> Add Supplier
</button>
@endsection

@section('content')

<!-- Search bar -->
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <div style="position:relative;flex:1;max-width:300px;">
        <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--gray-600);font-size:13px;"></i>
        <input type="text" id="supSearch" class="form-control" placeholder="Search suppliers..." style="padding-left:36px;">
    </div>
    <div style="color:var(--gray-600);font-size:13px;">{{ $suppliers->count() }} supplier(s) total</div>
</div>

<!-- Supplier Grid -->
<div class="grid grid-3" id="supplierGrid">
    @forelse($suppliers as $sup)
    <div class="supplier-card" data-name="{{ strtolower($sup->supplier_name) }}">
        <div style="display:flex;align-items:flex-start;gap:12px;">
            <div class="sup-avatar">{{ strtoupper(substr($sup->supplier_name, 0, 1)) }}</div>
            <div style="flex:1;">
                <div class="sup-name">{{ $sup->supplier_name }}</div>
                @if($sup->contact_number)
                <div class="sup-meta"><i class="fas fa-phone"></i> {{ $sup->contact_number }}</div>
                @endif
                @if($sup->address)
                <div class="sup-meta"><i class="fas fa-location-dot"></i> {{ $sup->address }}</div>
                @endif
                <div class="sup-meta">
                    <i class="fas fa-truck"></i>
                    {{ $sup->stock_ins_count ?? 0 }} deliveries
                </div>
            </div>
        </div>
        <div class="sup-actions">
            <button class="btn btn-sm btn-secondary" style="flex:1;justify-content:center;"
                onclick="openEditModal({{ $sup->id }}, '{{ addslashes($sup->supplier_name) }}', '{{ $sup->contact_number }}', '{{ addslashes($sup->address) }}')">
                <i class="fas fa-pen"></i> Edit
            </button>
            <form action="{{ route('suppliers.destroy', $sup->id) }}" method="POST" onsubmit="return confirm('Delete {{ $sup->supplier_name }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--gray-600);">
        <i class="fas fa-people-carry-box" style="font-size:40px;opacity:0.2;display:block;margin-bottom:14px;"></i>
        <p style="font-size:15px;font-weight:600;">No suppliers yet</p>
        <p style="font-size:13px;margin-top:4px;">Add your first supplier to get started.</p>
        <button class="btn btn-primary" style="margin-top:16px;" onclick="openModal()">
            <i class="fas fa-plus"></i> Add First Supplier
        </button>
    </div>
    @endforelse
</div>

<!-- ─── ADD MODAL ─── -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-people-carry-box" style="color:var(--red-primary)"></i> Add Supplier</div>
            <button class="modal-close" onclick="closeModal('addModal')"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Supplier Name <span style="color:var(--red-primary)">*</span></label>
                    <input type="text" name="supplier_name" class="form-control" placeholder="e.g. Farm Fresh Poultry" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" placeholder="e.g. 09123456789">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Full address..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Supplier</button>
            </div>
        </form>
    </div>
</div>

<!-- ─── EDIT MODAL ─── -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-pen" style="color:var(--red-primary)"></i> Edit Supplier</div>
            <button class="modal-close" onclick="closeModal('editModal')"><i class="fas fa-times"></i></button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Supplier Name <span style="color:var(--red-primary)">*</span></label>
                    <input type="text" name="supplier_name" id="editName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" id="editContact" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" id="editAddress" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Update Supplier</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openModal() { document.getElementById('addModal').classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function openEditModal(id, name, contact, address) {
    document.getElementById('editForm').action = `/suppliers/${id}`;
    document.getElementById('editName').value = name;
    document.getElementById('editContact').value = contact;
    document.getElementById('editAddress').value = address;
    document.getElementById('editModal').classList.add('show');
}

document.getElementById('supSearch').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.supplier-card').forEach(card => {
        card.style.display = card.dataset.name.includes(val) ? '' : 'none';
    });
});

// Close modals on overlay click
['addModal','editModal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) closeModal(id);
    });
});
</script>
@endpush