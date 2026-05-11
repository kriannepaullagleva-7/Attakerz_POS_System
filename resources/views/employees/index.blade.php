@extends('components.app-layout')

@section('title', 'Employees')
@section('subtitle', 'Manage your team members and staff')

@push('styles')
<style>
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 999; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: #fff; border-radius: 14px; width: 540px; max-height: 90vh; overflow-y: auto; box-shadow: 0 24px 64px rgba(0,0,0,0.15); }
    .modal-header { padding: 18px 22px; border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; background: #fff; z-index: 1; }
    .modal-title { font-size: 16px; font-weight: 700; }
    .modal-close { background: none; border: none; font-size: 18px; cursor: pointer; color: var(--gray-600); }
    .modal-body { padding: 22px; }
    .modal-footer { padding: 16px 22px; border-top: 1px solid var(--gray-200); display: flex; gap: 10px; justify-content: flex-end; position: sticky; bottom: 0; background: #fff; }

    .emp-avatar {
        width: 36px; height: 36px;
        background: var(--red-primary);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 800; color: #fff;
        flex-shrink: 0;
    }
</style>
@endpush

@section('topbar-actions')
<button class="btn btn-primary" onclick="openAddModal()">
    <i class="fas fa-user-plus"></i> Add Employee
</button>
@endsection

@section('content')

<!-- Stats -->
<div style="margin-bottom:24px;">
    <div class="stat-card" style="max-width:220px;">
        <div class="icon" style="background:#FEE2E2;"><i class="fas fa-users" style="color:var(--red-primary)"></i></div>
        <div class="value">{{ $totalEmployees }}</div>
        <div class="label">Total Employees</div>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Employee Directory</div>
        <div style="position:relative;">
            <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--gray-600);font-size:12px;"></i>
            <input type="text" id="empSearch" class="form-control" placeholder="Search employees..." style="padding-left:30px;width:220px;">
        </div>
    </div>
    <div class="table-wrap">
        <table id="empTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Activity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $i => $emp)
                <tr data-name="{{ strtolower($emp->full_name) }}">
                    <td style="color:var(--gray-600);font-size:12px;">{{ $i + 1 }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="emp-avatar">{{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:700;font-size:13px;">{{ $emp->last_name }}, {{ $emp->first_name }}{{ $emp->middle_name ? ' ' . substr($emp->middle_name, 0, 1) . '.' : '' }}</div>
                                @if($emp->contact_number)
                                <div style="font-size:11px;color:var(--gray-600);"><i class="fas fa-phone" style="font-size:10px;margin-right:3px;"></i>{{ $emp->contact_number }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="font-size:13px;color:var(--gray-600);">
                        {{ $emp->contact_number ?? '—' }}
                    </td>
                    <td style="font-size:13px;color:var(--gray-600);max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $emp->address ?? '—' }}
                    </td>
                    <td>
                        <div style="display:flex;gap:8px;font-size:12px;color:var(--gray-600);">
                            <span title="Sales"><i class="fas fa-receipt" style="color:var(--red-primary);"></i> {{ $emp->sales_count }}</span>
                            <span title="Stock-ins"><i class="fas fa-truck-ramp-box" style="color:#2563EB;"></i> {{ $emp->stock_ins_count }}</span>
                            <span title="Productions"><i class="fas fa-fire-burner" style="color:var(--success);"></i> {{ $emp->productions_count }}</span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-sm btn-secondary"
                                onclick="openEditModal(
                                    {{ $emp->id }},
                                    '{{ addslashes($emp->first_name) }}',
                                    '{{ addslashes($emp->middle_name ?? '') }}',
                                    '{{ addslashes($emp->last_name) }}',
                                    '{{ $emp->contact_number ?? '' }}',
                                    '{{ addslashes($emp->address ?? '') }}',
                                    '{{ $emp->role }}'
                                )">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form action="{{ route('employees.destroy', $emp->id) }}" method="POST"
                                onsubmit="return confirm('Remove {{ addslashes($emp->full_name) }} from the team? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:50px;color:var(--gray-600);">
                        <i class="fas fa-users" style="font-size:36px;opacity:0.2;display:block;margin-bottom:14px;"></i>
                        <p style="font-size:15px;font-weight:600;">No employees yet</p>
                        <p style="font-size:13px;margin-top:4px;">Add your first team member to get started.</p>
                        <button class="btn btn-primary" style="margin-top:16px;" onclick="openAddModal()">
                            <i class="fas fa-user-plus"></i> Add First Employee
                        </button>
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
            <div class="modal-title"><i class="fas fa-user-plus" style="color:var(--red-primary)"></i> Add Employee</div>
            <button class="modal-close" onclick="closeModal('addModal')"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('employees.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">First Name <span style="color:var(--red-primary)">*</span></label>
                        <input type="text" name="first_name" class="form-control" placeholder="e.g. Juan" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name <span style="color:var(--red-primary)">*</span></label>
                        <input type="text" name="last_name" class="form-control" placeholder="e.g. Dela Cruz" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Middle Name <span style="font-size:11px;color:var(--gray-600);font-weight:400;">(optional)</span></label>
                    <input type="text" name="middle_name" class="form-control" placeholder="e.g. Santos">
                </div>
                <div class="form-group">
                    <label class="form-label">Role <span style="color:var(--red-primary)">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="">Select role...</option>
                        <option value="cashier">Cashier</option>
                        <option value="staff">Staff</option>
                    </select>
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
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Employee</button>
            </div>
        </form>
    </div>
</div>

<!-- ─── EDIT MODAL ─── -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-user-pen" style="color:var(--red-primary)"></i> Edit Employee</div>
            <button class="modal-close" onclick="closeModal('editModal')"><i class="fas fa-times"></i></button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">First Name <span style="color:var(--red-primary)">*</span></label>
                        <input type="text" name="first_name" id="editFirstName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name <span style="color:var(--red-primary)">*</span></label>
                        <input type="text" name="last_name" id="editLastName" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" id="editMiddleName" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Role <span style="color:var(--red-primary)">*</span></label>
                    <select name="role" id="editRole" class="form-control" required>
                        <option value="cashier">Cashier</option>
                        <option value="staff">Staff</option>
                    </select>
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
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Update Employee</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openAddModal() { document.getElementById('addModal').classList.add('show'); }
function closeModal(id)  { document.getElementById(id).classList.remove('show'); }

function openEditModal(id, firstName, middleName, lastName, contact, address, role) {
    document.getElementById('editForm').action = `/employees/${id}`;
    document.getElementById('editFirstName').value  = firstName;
    document.getElementById('editMiddleName').value = middleName;
    document.getElementById('editLastName').value   = lastName;
    document.getElementById('editRole').value       = role;
    document.getElementById('editContact').value    = contact;
    document.getElementById('editAddress').value    = address;
    document.getElementById('editModal').classList.add('show');
}

document.getElementById('empSearch').addEventListener('input', function() {
    const search = this.value.toLowerCase();
    document.querySelectorAll('#empTable tbody tr[data-name]').forEach(row => {
        row.style.display = row.dataset.name.includes(search) ? '' : 'none';
    });
});

['addModal','editModal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) closeModal(id);
    });
});
</script>
@endpush
