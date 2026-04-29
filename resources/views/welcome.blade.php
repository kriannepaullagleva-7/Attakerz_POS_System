@extends('components.app-layout')

@section('title', 'Dashboard')
@section('subtitle', 'Attackers Lechon Manok — Bunawan Branch')

@push('styles')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
    .dash-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .two-col   { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
    @media (max-width: 1100px) { .two-col { grid-template-columns: 1fr; } .dash-grid { grid-template-columns: repeat(2, 1fr); } }

    .trend { position: absolute; top: 16px; right: 16px; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 20px; }
    .trend.up   { background: #D1FAE5; color: #065F46; }
    .trend.down { background: #FEE2E2; color: #991B1B; }
    .trend.flat { background: var(--gray-100); color: var(--gray-600); }

    .best-seller-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--gray-100); }
    .best-seller-row:last-child { border-bottom: none; }
    .rank { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0; }
    .rank.gold   { background: #FEF3C7; color: #92400E; }
    .rank.silver { background: var(--gray-100); color: var(--gray-600); }

    .low-stock-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--gray-100); }
    .low-stock-item:last-child { border-bottom: none; }
</style>
@endpush

@section('content')

<!-- ─── STAT CARDS ─── -->
<div class="dash-grid">
    <div class="stat-card" style="position:relative;">
        <div class="icon" style="background:#FEE2E2;"><i class="fas fa-peso-sign" style="color:var(--red-primary)"></i></div>
        <div class="value">₱{{ number_format($todaySales, 0) }}</div>
        <div class="label">Today's Revenue</div>
        @if($salesTrend != 0)
        <span class="trend {{ $salesTrend >= 0 ? 'up' : 'down' }}">
            {{ $salesTrend >= 0 ? '+' : '' }}{{ $salesTrend }}%
        </span>
        @endif
    </div>
    <div class="stat-card" style="position:relative;">
        <div class="icon" style="background:#DBEAFE;"><i class="fas fa-receipt" style="color:#2563EB"></i></div>
        <div class="value">{{ $todayOrders }}</div>
        <div class="label">Today's Orders</div>
        @if($ordersTrend != 0)
        <span class="trend {{ $ordersTrend >= 0 ? 'up' : 'down' }}">
            {{ $ordersTrend >= 0 ? '+' : '' }}{{ $ordersTrend }}%
        </span>
        @endif
    </div>
    <div class="stat-card" style="position:relative;">
        <div class="icon" style="background:#D1FAE5;"><i class="fas fa-chart-line" style="color:var(--success)"></i></div>
        <div class="value">₱{{ number_format($weeklyAvg, 0) }}</div>
        <div class="label">Weekly Avg / Day</div>
        @if($weeklyGrowth != 0)
        <span class="trend {{ $weeklyGrowth >= 0 ? 'up' : 'down' }}">
            {{ $weeklyGrowth >= 0 ? '+' : '' }}{{ $weeklyGrowth }}%
        </span>
        @endif
    </div>
    <div class="stat-card" style="position:relative;">
        <div class="icon" style="background:#FEF3C7;"><i class="fas fa-triangle-exclamation" style="color:#D97706"></i></div>
        <div class="value">{{ $lowStockCount }}</div>
        <div class="label">Low Stock Alerts</div>
        @if($lowStockCount > 0)
        <span class="trend down">Action needed</span>
        @endif
    </div>
</div>

<!-- ─── MAIN CONTENT ─── -->
<div class="two-col">

    <!-- Left: Chart + Best Sellers -->
    <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- Revenue Chart -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="fas fa-chart-bar" style="color:var(--red-primary)"></i> 7-Day Revenue</div>
                    <div class="card-subtitle">Daily sales for the past week</div>
                </div>
            </div>
            <div class="card-body" style="padding:16px 20px;">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>

        <!-- Best Sellers -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-fire" style="color:var(--red-primary)"></i> Top Products This Month</div>
            </div>
            <div class="card-body">
                @forelse($bestSellers as $i => $item)
                <div class="best-seller-row">
                    <div class="rank {{ $i < 3 ? 'gold' : 'silver' }}">{{ $i + 1 }}</div>
                    <div style="flex:1;">
                        <div style="font-weight:600;font-size:13px;">{{ $item->product_name }}</div>
                        <div style="font-size:11px;color:var(--gray-600);">{{ $item->total_qty }} units sold</div>
                    </div>
                    <div style="font-family:var(--font-mono);font-weight:700;color:var(--red-primary);">
                        ₱{{ number_format($item->total_revenue, 2) }}
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:24px;color:var(--gray-600);font-size:13px;">
                    <i class="fas fa-receipt" style="font-size:28px;opacity:0.2;display:block;margin-bottom:8px;"></i>
                    No sales recorded this month yet.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right: Quick Nav + Low Stock -->
    <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Quick Actions</div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
                <a href="{{ route('pos') }}" class="btn btn-primary" style="justify-content:center;">
                    <i class="fas fa-cash-register"></i> Open POS
                </a>
                <a href="{{ route('stock-in.create') }}" class="btn btn-secondary" style="justify-content:center;">
                    <i class="fas fa-truck-ramp-box"></i> New Stock In
                </a>
                <a href="{{ route('production.index') }}" class="btn btn-secondary" style="justify-content:center;">
                    <i class="fas fa-fire-burner"></i> Log Production
                </a>
                <a href="{{ route('reports.index') }}" class="btn btn-secondary" style="justify-content:center;">
                    <i class="fas fa-chart-bar"></i> View Reports
                </a>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title"><i class="fas fa-triangle-exclamation" style="color:#D97706"></i> Low Stock Items</div>
                    <div class="card-subtitle">Items at or below border point</div>
                </div>
                <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="card-body" style="padding:8px 20px;">
                @forelse($lowStockItems as $item)
                <div class="low-stock-item">
                    <div>
                        <div style="font-weight:600;font-size:13px;">{{ $item->product_name }}</div>
                        <div style="font-size:11px;color:var(--gray-600);">Border: {{ $item->border_point }} {{ $item->unit }}</div>
                    </div>
                    <span class="badge {{ $item->quantity_on_hand <= 0 ? 'badge-red' : 'badge-yellow' }}">
                        {{ $item->quantity_on_hand }} {{ $item->unit }}
                    </span>
                </div>
                @empty
                <div style="text-align:center;padding:20px;color:var(--success);font-size:13px;">
                    <i class="fas fa-circle-check" style="margin-right:6px;"></i> All items are well-stocked!
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
const labels  = @json($chartLabels);
const revenue = @json($revenueData);

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Revenue (₱)',
            data: revenue,
            backgroundColor: 'rgba(192,57,43,0.15)',
            borderColor: '#C0392B',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: v => '₱' + v.toLocaleString('en-PH'),
                    font: { family: 'JetBrains Mono, monospace', size: 11 }
                },
                grid: { color: '#F3F4F6' }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
