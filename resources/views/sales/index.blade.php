@extends('components.app-layout')

@section('title', 'Sales History')
@section('subtitle', 'View and manage completed transactions')

@push('styles')
<style>
    .sales-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }

    .search-bar {
        display: flex; align-items: center; gap: 12px;
        background: #fff; border: 1px solid var(--gray-200);
        border-radius: 10px; padding: 12px 16px; margin-bottom: 16px;
    }

    .receipt-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 999; }
    .receipt-modal.show { display: flex; }

    .receipt-box {
        background: #fff; border-radius: 14px;
        width: 400px; max-height: 90vh; overflow-y: auto;
        box-shadow: 0 24px 64px rgba(0,0,0,0.15);
        padding: 28px;
    }

    .receipt-store { text-align: center; margin-bottom: 16px; }
    .receipt-store h2 { font-size: 18px; font-weight: 800; }
    .receipt-store p { font-size: 12px; color: var(--gray-600); }
    .dashed { border-top: 2px dashed var(--gray-200); margin: 12px 0; }

    .r-row { display: flex; justify-content: space-between; font-size: 13px; padding: 4px 0; }
    .r-row.bold { font-weight: 700; font-size: 15px; }
    .r-row.total { font-size: 17px; font-weight: 800; color: var(--red-primary); padding-top: 8px; }
</style>
@endpush

@section('content')

<div class="sales-stats">
    <div class="stat-card">
        <div class="icon" style="background:#FEE2E2;"><i class="fas fa-peso-sign" style="color:var(--red-primary)"></i></div>
        <div class="value">₱{{ number_format($totalSales, 2) }}</div>
        <div class="label">Total Sales Today</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:#DBEAFE;"><i class="fas fa-receipt" style="color:#2563EB"></i></div>
        <div class="value">{{ $totalOrders }}</div>
        <div class="label">Sale Orders</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:#D1FAE5;"><i class="fas fa-calculator" style="color:var(--success)"></i></div>
        <div class="value">₱{{ number_format($avgOrderValue, 2) }}</div>
        <div class="label">Average Order Value</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Recent Transactions</div>
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="position:relative;">
                <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--gray-600);font-size:12px;"></i>
                <input type="text" id="salesSearch" class="form-control" placeholder="Search by order #..." style="padding-left:30px;width:200px;">
            </div>
            <input type="date" class="form-control" id="dateFilter" style="width:160px;" value="{{ today()->format('Y-m-d') }}">
        </div>
    </div>
    <div class="table-wrap">
        <table id="salesTable">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date/Time</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Change</th>
                    <th>Cashier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td style="font-family:var(--font-mono);font-weight:700;color:var(--red-primary)">
                        P{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>
                        <div style="font-size:13px;font-weight:600;">{{ \Carbon\Carbon::parse($sale->date)->format('M d, Y') }}</div>
                        <div style="font-size:11px;color:var(--gray-600);">{{ \Carbon\Carbon::parse($sale->date)->format('h:i A') }}</div>
                    </td>
                    <td>
                        <span style="font-size:13px;">{{ $sale->details->count() }} item(s)</span>
                        <div style="font-size:11px;color:var(--gray-600);">
                            {{ $sale->details->take(2)->map(fn($d) => $d->product->product_name ?? 'Item')->join(', ') }}
                            {{ $sale->details->count() > 2 ? '...' : '' }}
                        </div>
                    </td>
                    <td style="font-family:var(--font-mono);font-weight:700;font-size:14px;">
                        ₱{{ number_format($sale->total_amount, 2) }}
                    </td>
                    <td>
                        <div style="font-size:13px;">₱{{ number_format($sale->cash_paid ?? $sale->total_amount, 2) }}</div>
                        <span class="badge badge-green" style="font-size:10px;">Cash</span>
                    </td>
                    <td style="font-family:var(--font-mono);font-size:13px;color:var(--success);">
                        ₱{{ number_format(($sale->cash_paid ?? $sale->total_amount) - $sale->total_amount, 2) }}
                    </td>
                    <td style="font-size:13px;">{{ $sale->employee->first_name ?? 'Cashier' }} {{ $sale->employee->last_name ?? '' }}</td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="viewReceipt({{ $sale->id }})">
                            <i class="fas fa-eye"></i> View Receipt
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:var(--gray-600);">
                        <i class="fas fa-receipt" style="font-size:32px;opacity:0.2;display:block;margin-bottom:12px;"></i>
                        No sales recorded yet today.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
    <div style="padding:16px;border-top:1px solid var(--gray-200);">
        {{ $sales->links() }}
    </div>
    @endif
</div>

<!-- Receipt Modal -->
<div class="receipt-modal" id="receiptModal">
    <div class="receipt-box">
        <div class="receipt-store">
            <div style="font-size:32px;margin-bottom:6px;">🍗</div>
            <h2>ATTACKERS</h2>
            <p style="font-weight:700;font-size:13px;">LECHON MANOK</p>
            <p>171 Main Street, Bunawan, Davao City</p>
            <p>Mobile: +63 917 123 4567</p>
        </div>
        <div class="dashed"></div>
        <div style="text-align:center;font-weight:700;letter-spacing:2px;color:var(--red-primary);font-size:13px;margin-bottom:12px;">OFFICIAL RECEIPT</div>
        <div id="receiptContent"></div>
        <div class="dashed"></div>
        <div style="text-align:center;font-size:11px;color:var(--gray-600);margin-top:12px;">
            Thank you for your order! Come back soon. 🍗
        </div>
        <div style="display:flex;gap:8px;margin-top:16px;">
            <button class="btn btn-secondary" style="flex:1;justify-content:center;" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn btn-primary" style="flex:1;justify-content:center;" onclick="document.getElementById('receiptModal').classList.remove('show')">
                Close
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const salesData = @json($sales->items());

function viewReceipt(id) {
    const sale = salesData.find(s => s.id === id);
    if (!sale) return;

    const cash = sale.cash_paid || sale.total_amount;
    const change = cash - sale.total_amount;
    const orderNum = 'P' + String(sale.id).padStart(5, '0');
    const date = new Date(sale.date);

    let html = `
        <div class="r-row"><span>Order No.</span><span style="font-weight:700;font-family:var(--font-mono)">${orderNum}</span></div>
        <div class="r-row"><span>Date</span><span>${date.toLocaleDateString('en-PH',{month:'short',day:'2-digit',year:'numeric'})}</span></div>
        <div class="r-row"><span>Time</span><span>${date.toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</span></div>
        <div class="r-row"><span>Cashier</span><span>${sale.employee ? sale.employee.first_name + ' ' + sale.employee.last_name : 'Cashier'}</span></div>
        <div class="dashed"></div>
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--gray-600);margin-bottom:8px;">Order Items</div>
    `;

    (sale.details || []).forEach(d => {
        const itemTotal = (d.price * d.quantity).toFixed(2);
        html += `
        <div class="r-row">
            <div><div>${d.product ? d.product.product_name : 'Item'}</div><div style="font-size:11px;color:var(--gray-600)">x${d.quantity} @ ₱${parseFloat(d.price).toFixed(2)}</div></div>
            <span style="font-family:var(--font-mono);font-weight:600;">₱${parseFloat(itemTotal).toLocaleString('en-PH',{minimumFractionDigits:2})}</span>
        </div>`;
    });

    html += `
        <div class="dashed"></div>
        <div class="r-row total"><span>TOTAL</span><span>₱${parseFloat(sale.total_amount).toLocaleString('en-PH',{minimumFractionDigits:2})}</span></div>
        <div class="r-row"><span>Cash</span><span style="font-family:var(--font-mono)">₱${parseFloat(cash).toLocaleString('en-PH',{minimumFractionDigits:2})}</span></div>
        <div class="r-row" style="color:var(--success);font-weight:700;"><span>Change</span><span style="font-family:var(--font-mono)">₱${Math.max(0,change).toLocaleString('en-PH',{minimumFractionDigits:2})}</span></div>
    `;

    document.getElementById('receiptContent').innerHTML = html;
    document.getElementById('receiptModal').classList.add('show');
}

document.getElementById('salesSearch').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#salesTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});

document.getElementById('receiptModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('show');
});
</script>
@endpush