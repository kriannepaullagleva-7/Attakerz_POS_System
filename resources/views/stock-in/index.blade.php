@extends('components.app-layout')

@section('title', 'Stock In')
@section('subtitle', 'Record and view incoming inventory from suppliers')

@push('styles')
<style>
    .si-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }

    .txn-row { cursor: pointer; }
    .txn-row:hover td { background: #FEF2F2 !important; }

    .detail-row td { padding: 0 !important; background: var(--gray-50) !important; }
    .detail-panel {
        display: none;
        padding: 14px 20px 14px 52px;
        border-top: 1px solid var(--gray-200);
    }
    .detail-panel.open { display: block; }

    .detail-items { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; }
    .detail-item {
        background: #fff;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 10px 14px;
    }
    .di-name { font-size: 13px; font-weight: 600; color: var(--gray-800); }
    .di-meta { font-size: 11px; color: var(--gray-600); margin-top: 2px; }
    .di-cost { font-size: 13px; font-weight: 700; color: var(--red-primary); font-family: var(--font-mono); margin-top: 4px; }
</style>
@endpush

@section('topbar-actions')
<a href="{{ route('stock-in.create') }}" class="btn btn-primary">
    <i class="fas fa-truck-ramp-box"></i> New Stock In
</a>
@endsection

@section('content')

<div class="si-stats">
    <div class="stat-card">
        <div class="icon" style="background:#FEE2E2;"><i class="fas fa-peso-sign" style="color:var(--red-primary)"></i></div>
        <div class="value">₱{{ number_format($totalCostThisMonth, 2) }}</div>
        <div class="label">Total Cost This Month</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:#DBEAFE;"><i class="fas fa-truck" style="color:#2563EB"></i></div>
        <div class="value">{{ $totalTransactions }}</div>
        <div class="label">Total Transactions</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:#D1FAE5;"><i class="fas fa-calendar-day" style="color:var(--success)"></i></div>
        <div class="value">{{ $todayTransactions }}</div>
        <div class="label">Today's Deliveries</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Stock In History</div>
            <div class="card-subtitle">Click a row to see item details</div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <div style="position:relative;">
                <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--gray-600);font-size:12px;"></i>
                <input type="text" id="siSearch" class="form-control" placeholder="Search..." style="padding-left:30px;width:200px;">
            </div>
            <input type="month" class="form-control" id="monthFilter" style="width:160px;" value="{{ now()->format('Y-m') }}">
        </div>
    </div>
    <div class="table-wrap">
        <table id="siTable">
            <thead>
                <tr>
                    <th width="32"></th>
                    <th>Txn #</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Received By</th>
                    <th>Items</th>
                    <th>Total Cost</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockIns as $si)
                <tr class="txn-row" onclick="toggleDetail({{ $si->id }})">
                    <td style="text-align:center;">
                        <i class="fas fa-chevron-right" id="chevron-{{ $si->id }}" style="color:var(--gray-600);font-size:11px;transition:transform 0.2s;"></i>
                    </td>
                    <td style="font-family:var(--font-mono);font-weight:700;color:var(--red-primary);">
                        ST{{ str_pad($si->id, 5, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>
                        <div style="font-size:13px;font-weight:600;">{{ \Carbon\Carbon::parse($si->date)->format('M d, Y') }}</div>
                        <div style="font-size:11px;color:var(--gray-600);">{{ \Carbon\Carbon::parse($si->date)->format('h:i A') }}</div>
                    </td>
                    <td>
                        <div style="font-size:13px;font-weight:600;">{{ $si->supplier->supplier_name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--gray-600);">{{ $si->supplier->contact_number ?? '' }}</div>
                    </td>
                    <td style="font-size:13px;">
                        {{ $si->employee->first_name ?? '—' }} {{ $si->employee->last_name ?? '' }}
                    </td>
                    <td>
                        <span class="badge badge-blue">{{ $si->details->count() }} item(s)</span>
                    </td>
                    <td style="font-family:var(--font-mono);font-weight:700;font-size:14px;">
                        ₱{{ number_format($si->total_cost, 2) }}
                    </td>
                    <td onclick="event.stopPropagation()">
                        <a href="{{ route('stock-in.show', $si->id) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <tr class="detail-row">
                    <td colspan="8">
                        <div class="detail-panel" id="detail-{{ $si->id }}">
                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--gray-600);margin-bottom:10px;">
                                <i class="fas fa-boxes-stacked" style="color:var(--red-primary)"></i> Items Received
                            </div>
                            <div class="detail-items">
                                @foreach($si->details as $detail)
                                <div class="detail-item">
                                    <div class="di-name">{{ $detail->product->product_name ?? 'Unknown' }}</div>
                                    <div class="di-meta">{{ $detail->quantity }} {{ $detail->product->unit ?? 'pcs' }} received</div>
                                    <div class="di-cost">₱{{ number_format($detail->cost_per_unit, 2) }}/{{ $detail->product->unit ?? 'pc' }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:var(--gray-600);">
                        <i class="fas fa-truck" style="font-size:32px;opacity:0.2;display:block;margin-bottom:12px;"></i>
                        No stock-in transactions yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($stockIns->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--gray-200);">
        {{ $stockIns->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function toggleDetail(id) {
    const panel = document.getElementById('detail-' + id);
    const chevron = document.getElementById('chevron-' + id);
    const isOpen = panel.classList.toggle('open');
    chevron.style.transform = isOpen ? 'rotate(90deg)' : '';
}

document.getElementById('siSearch').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#siTable tbody .txn-row').forEach((row, i) => {
        const show = row.textContent.toLowerCase().includes(val);
        row.style.display = show ? '' : 'none';
        // also hide detail row
        const next = row.nextElementSibling;
        if (next) next.style.display = show ? '' : 'none';
    });
});
</script>
@endpush