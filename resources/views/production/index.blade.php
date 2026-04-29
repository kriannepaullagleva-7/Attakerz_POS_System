@extends('components.app-layout')

@section('title', 'Production Monitoring')
@section('subtitle', 'Track raw materials used and finished products made')

@push('styles')
<style>
    .prod-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }

    .prod-log {
        border: 1.5px solid var(--gray-200);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 12px;
        transition: border-color 0.15s;
    }

    .prod-log:hover { border-color: var(--red-primary); }

    .prod-log-header {
        padding: 14px 18px;
        display: flex; align-items: center; justify-content: space-between;
        background: var(--gray-50);
        cursor: pointer;
    }

    .prod-log-title { font-size: 14px; font-weight: 700; color: var(--gray-800); }
    .prod-log-meta { font-size: 12px; color: var(--gray-600); }

    .prod-log-body {
        padding: 16px 18px;
        display: none;
        border-top: 1px solid var(--gray-200);
    }
    .prod-log-body.open { display: block; }

    .raw-finished-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
    }

    .mat-section-title {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.8px; color: var(--gray-600); margin-bottom: 8px;
    }

    .mat-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 8px 12px; border-radius: 6px;
        background: var(--gray-50);
        margin-bottom: 6px;
        font-size: 13px;
    }

    .mat-name { font-weight: 600; color: var(--gray-800); }
    .mat-qty { font-family: var(--font-mono); color: var(--gray-600); }

    .output-qty { 
        font-family: var(--font-mono); font-weight: 700;
        color: var(--success);
        background: #D1FAE5; padding: 2px 8px; border-radius: 4px;
        font-size: 12px;
    }

    /* ─── NEW PRODUCTION MODAL ─── */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 999; }
    .modal-overlay.show { display: flex; }

    .modal-box {
        background: #fff; border-radius: 14px;
        width: 680px; max-height: 90vh; overflow-y: auto;
        box-shadow: 0 24px 64px rgba(0,0,0,0.15);
    }

    .modal-header {
        padding: 18px 22px; border-bottom: 1px solid var(--gray-200);
        display: flex; align-items: center; justify-content: space-between;
        position: sticky; top: 0; background: #fff; z-index: 1;
    }

    .modal-title { font-size: 16px; font-weight: 700; }
    .modal-close { background: none; border: none; font-size: 18px; cursor: pointer; color: var(--gray-600); }

    .modal-body { padding: 22px; }

    .section-header {
        font-size: 13px; font-weight: 700; color: var(--gray-800);
        margin-bottom: 12px; padding-bottom: 8px;
        border-bottom: 1px solid var(--gray-200);
        display: flex; align-items: center; gap: 8px;
    }

    .items-list { margin-bottom: 16px; }

    .item-row {
        display: grid; grid-template-columns: 1fr 100px 60px auto; gap: 8px;
        align-items: center; margin-bottom: 8px;
    }

    .add-item-btn {
        display: flex; align-items: center; gap: 6px;
        padding: 8px 14px; border-radius: 8px;
        border: 1.5px dashed var(--gray-300);
        background: none; color: var(--gray-600);
        font-family: var(--font); font-size: 13px; font-weight: 600;
        cursor: pointer; width: 100%;
        transition: all 0.15s;
    }
    .add-item-btn:hover { border-color: var(--red-primary); color: var(--red-primary); }

    .remove-row-btn {
        width: 28px; height: 28px; border-radius: 6px;
        background: #FEE2E2; border: none; color: #DC2626;
        cursor: pointer; font-size: 14px;
        display: flex; align-items: center; justify-content: center;
    }
</style>
@endpush

@section('topbar-actions')
<button class="btn btn-primary" onclick="document.getElementById('newProdModal').classList.add('show')">
    <i class="fas fa-fire-burner"></i> Add Production Log
</button>
@endsection

@section('content')

<!-- Stats -->
<div class="prod-stats">
    <div class="stat-card">
        <div class="icon" style="background:#FEE2E2;"><i class="fas fa-fire" style="color:var(--red-primary)"></i></div>
        <div class="value">{{ $todayProductions }}</div>
        <div class="label">Today's Production Uses</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:#D1FAE5;"><i class="fas fa-drumstick-bite" style="color:var(--success)"></i></div>
        <div class="value">{{ $todayOutput }}</div>
        <div class="label">Total Output Today</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:#DBEAFE;"><i class="fas fa-clipboard-list" style="color:#2563EB"></i></div>
        <div class="value">{{ $totalLogs }}</div>
        <div class="label">Total Logs</div>
    </div>
</div>

<!-- Production History -->
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Production History</div>
            <div class="card-subtitle">Recent production activity — click to expand</div>
        </div>
    </div>
    <div class="card-body">
        @forelse($productions as $prod)
        <div class="prod-log">
            <div class="prod-log-header" onclick="toggleLog({{ $prod->production_id }})">
                <div>
                    <div class="prod-log-title">
                        <i class="fas fa-fire-burner" style="color:var(--red-primary);margin-right:6px;"></i>
                        Production Batch #{{ $prod->production_id }} — {{ $prod->employee->first_name ?? 'Staff' }} {{ $prod->employee->last_name ?? '' }}
                    </div>
                    <div class="prod-log-meta">
                        {{ \Carbon\Carbon::parse($prod->date)->format('F d, Y · h:i A') }}
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <span class="badge badge-green">
                        {{ $prod->outputs->sum('quantity_produced') }} units produced
                    </span>
                    @if(\Carbon\Carbon::parse($prod->date)->isToday())
                    <form action="{{ route('production.destroy', $prod->production_id) }}" method="POST"
                          onclick="event.stopPropagation()"
                          onsubmit="return confirm('Reverse and delete this production batch? Inventory will be restored.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" style="padding:5px 10px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                    <i class="fas fa-chevron-down" style="color:var(--gray-600);font-size:12px;"></i>
                </div>
            </div>
            <div class="prod-log-body" id="prodLog-{{ $prod->production_id }}">
                <div class="raw-finished-grid">
                    <div>
                        <div class="mat-section-title">
                            <i class="fas fa-wheat-awn" style="color:#2563EB"></i> Raw Materials Used
                        </div>
                        @foreach($prod->rawMaterials as $rm)
                        <div class="mat-item">
                            <span class="mat-name">{{ $rm->product->product_name ?? 'Unknown' }}</span>
                            <span class="mat-qty">{{ $rm->quantity_used }} {{ $rm->product->unit ?? 'pcs' }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div>
                        <div class="mat-section-title">
                            <i class="fas fa-drumstick-bite" style="color:var(--success)"></i> Output Produced
                        </div>
                        @foreach($prod->outputs as $out)
                        <div class="mat-item">
                            <span class="mat-name">{{ $out->product->product_name ?? 'Unknown' }}</span>
                            <span class="output-qty">{{ $out->quantity_produced }} {{ $out->product->unit ?? 'pcs' }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:40px;color:var(--gray-600);">
            <i class="fas fa-fire-burner" style="font-size:36px;opacity:0.2;display:block;margin-bottom:12px;"></i>
            <p>No production logs yet. Start by adding a new production batch.</p>
        </div>
        @endforelse
    </div>
    @if($productions->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--gray-200);">
        {{ $productions->links() }}
    </div>
    @endif
</div>

<!-- ─── NEW PRODUCTION MODAL ─── -->
<div class="modal-overlay" id="newProdModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-fire-burner" style="color:var(--red-primary)"></i> Record Production Batch</div>
            <button class="modal-close" onclick="document.getElementById('newProdModal').classList.remove('show')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form action="{{ route('production.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">Select employee...</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Production Date</label>
                    <input type="datetime-local" name="date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>

                <!-- Raw Materials -->
                <div class="section-header">
                    <i class="fas fa-wheat-awn" style="color:#2563EB"></i> Raw Materials Used
                </div>
                <div class="items-list" id="rawList">
                    <div class="item-row">
                        <select name="raw_product_id[]" class="form-control" required>
                            <option value="">Select material...</option>
                            @foreach($rawProducts as $p)
                            <option value="{{ $p->id }}">{{ $p->product_name }} ({{ $p->unit }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="raw_quantity[]" class="form-control" placeholder="Qty" min="0.01" step="0.01" required>
                        <span style="font-size:13px;color:var(--gray-600);">units</span>
                        <button type="button" class="remove-row-btn" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <button type="button" class="add-item-btn" onclick="addRawRow()">
                    <i class="fas fa-plus"></i> Add Raw Material
                </button>

                <!-- Outputs -->
                <div class="section-header" style="margin-top:20px;">
                    <i class="fas fa-drumstick-bite" style="color:var(--success)"></i> Finished Products Produced
                </div>
                <div class="items-list" id="outputList">
                    <div class="item-row">
                        <select name="output_product_id[]" class="form-control" required>
                            <option value="">Select product...</option>
                            @foreach($finishedProducts as $p)
                            <option value="{{ $p->id }}">{{ $p->product_name }} ({{ $p->unit }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="output_quantity[]" class="form-control" placeholder="Qty" min="1" required>
                        <span style="font-size:13px;color:var(--gray-600);">units</span>
                        <button type="button" class="remove-row-btn" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <button type="button" class="add-item-btn" onclick="addOutputRow()">
                    <i class="fas fa-plus"></i> Add Output Product
                </button>

                <div style="margin-top:24px;display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary btn-lg" style="flex:1;">
                        <i class="fas fa-fire-burner"></i> Record Production
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('newProdModal').classList.remove('show')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleLog(id) {
    const body = document.getElementById('prodLog-' + id);
    body.classList.toggle('open');
}

const rawOpts = `@foreach($rawProducts as $p)<option value="{{ $p->id }}">{{ $p->product_name }} ({{ $p->unit }})</option>@endforeach`;
const outOpts = `@foreach($finishedProducts as $p)<option value="{{ $p->id }}">{{ $p->product_name }} ({{ $p->unit }})</option>@endforeach`;

function addRawRow() {
    const row = document.createElement('div');
    row.className = 'item-row';
    row.innerHTML = `
        <select name="raw_product_id[]" class="form-control" required>
            <option value="">Select material...</option>${rawOpts}
        </select>
        <input type="number" name="raw_quantity[]" class="form-control" placeholder="Qty" min="0.01" step="0.01" required>
        <span style="font-size:13px;color:var(--gray-600);">units</span>
        <button type="button" class="remove-row-btn" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
    `;
    document.getElementById('rawList').appendChild(row);
}

function addOutputRow() {
    const row = document.createElement('div');
    row.className = 'item-row';
    row.innerHTML = `
        <select name="output_product_id[]" class="form-control" required>
            <option value="">Select product...</option>${outOpts}
        </select>
        <input type="number" name="output_quantity[]" class="form-control" placeholder="Qty" min="1" required>
        <span style="font-size:13px;color:var(--gray-600);">units</span>
        <button type="button" class="remove-row-btn" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
    `;
    document.getElementById('outputList').appendChild(row);
}

function removeRow(btn) {
    const list = btn.closest('.items-list');
    if (list.querySelectorAll('.item-row').length > 1) {
        btn.closest('.item-row').remove();
    }
}
</script>
@endpush