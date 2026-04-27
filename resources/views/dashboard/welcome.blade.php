@extends('components.app-layout')

@section('title', 'Dashboard Overview')
@section('subtitle', 'Monitor your daily performance at a glance')

@push('styles')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
    .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .best-selling { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--gray-100); }
    .best-selling:last-child { border-bottom: none; }
    .bs-rank { 
        width: 28px; height: 28px; border-radius: 50%; 
        background: var(--red-primary); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0;
    }
    .bs-info { flex: 1; }
    .bs-name { font-size: 13px; font-weight: 600; color: var(--gray-800); }
    .bs-qty { font-size: 11px; color: var(--gray-600); }
    .bs-amount { font-size: 13px; font-weight: 700; color: var(--red-primary); font-family: var(--font-mono); }

    .low-stock-item { 
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 0; border-bottom: 1px solid var(--gray-100);
    }
    .low-stock-item:last-child { border-bottom: none; }
    .ls-name { font-size: 13px; font-weight: 600; color: var(--gray-800); }
    .ls-stock { font-size: 12px; color: var(--gray-600); }
    .progress-bar { 
        width: 80px; height: 6px; background: var(--gray-200); border-radius: 3px; overflow: hidden;
    }
    .progress-fill { height: 100%; border-radius: 3px; }
    .progress-fill.critical { background: #EF4444; }
    .progress-fill.low { background: #F59E0B; }
    .progress-fill.ok { background: var(--success); }

    @media (max-width: 1200px) { .stats-row { grid-template-columns: repeat(2, 1fr); } }
</style>
@endpush

@section('topbar-actions')
<a href="{{ route('pos') }}" class="btn btn-primary">
    <i class="fas fa-cash-register"></i> Open POS
</a>
@endsection

@section('content')

<!-- ─── STATS ───────────────────────────────────────── -->
<div class="stats-row">
    <div class="stat-card">
        <div class="icon" style="background:#FEE2E2;">
            <i class="fas fa-peso-sign" style="color:var(--red-primary)"></i>
        </div>
        <div class="trend {{ $salesTrend >= 0 ? 'up' : 'down' }}">
            {{ $salesTrend >= 0 ? '+' : '' }}{{ $salesTrend }}%
        </div>
        <div class="value">₱{{ number_format($todaySales, 2) }}</div>
        <div class="label">Today's Revenue</div>
    </div>

    <div class="stat-card">
        <div class="icon" style="background:#DBEAFE;">
            <i class="fas fa-shopping-bag" style="color:#2563EB"></i>
        </div>
        <div class="trend up">+{{ $ordersTrend }}%</div>
        <div class="value">{{ $todayOrders }}</div>
        <div class="label">Items Sold Today</div>
    </div>

    <div class="stat-card">
        <div class="icon" style="background:#D1FAE5;">
            <i class="fas fa-chart-line" style="color:var(--success)"></i>
        </div>
        <div class="value">₱{{ number_format($weeklyAvg, 2) }}</div>
        <div class="label">Weekly Average</div>
    </div>

    <div class="stat-card">
        <div class="icon" style="background:#FEF3C7;">
            <i class="fas fa-triangle-exclamation" style="color:#D97706"></i>
        </div>
        <div class="value">{{ $lowStockCount }}</div>
        <div class="label">Low Stock Alerts</div>
    </div>
</div>

<!-- ─── CHARTS ──────────────────────────────────────── -->
<div class="grid grid-2" style="margin-bottom:24px;">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Weekly Revenue</div>
                <div class="card-subtitle">Last 7 days performance</div>
            </div>
            <div class="trend up">+{{ $weeklyGrowth }}% vs last week</div>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="180"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Items Sold (Weekly)</div>
                <div class="card-subtitle">Units per day</div>
            </div>
        </div>
        <div class="card-body">
            <canvas id="itemsChart" height="180"></canvas>
        </div>
    </div>
</div>

<!-- ─── BEST SELLING + LOW STOCK ────────────────────── -->
<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-fire" style="color:var(--red-primary)"></i> Best Selling Products</div>
        </div>
        <div class="card-body">
            @forelse($bestSellers as $index => $item)
            <div class="best-selling">
                <div class="bs-rank">{{ $index + 1 }}</div>
                <div class="bs-info">
                    <div class="bs-name">{{ $item->product_name }}</div>
                    <div class="bs-qty">{{ $item->total_qty }} units sold</div>
                </div>
                <div class="bs-amount">₱{{ number_format($item->total_revenue, 2) }}</div>
            </div>
            @empty
            <p style="color:var(--gray-600);font-size:13px;text-align:center;padding:20px 0;">No sales data yet</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-triangle-exclamation" style="color:#D97706"></i> Low Stock Alerts</div>
            <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="card-body">
            @forelse($lowStockItems as $item)
            <div class="low-stock-item">
                <div>
                    <div class="ls-name">{{ $item->product_name }}</div>
                    <div class="ls-stock">{{ $item->quantity_on_hand }} {{ $item->unit }} remaining · Border: {{ $item->border_point ?? 10 }}</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="progress-bar">
                        <div class="progress-fill {{ $item->quantity_on_hand <= 5 ? 'critical' : ($item->quantity_on_hand <= 15 ? 'low' : 'ok') }}"
                             style="width:{{ min(100, ($item->quantity_on_hand / max(1, $item->border_point ?? 20)) * 100) }}%"></div>
                    </div>
                    <span class="badge {{ $item->quantity_on_hand <= 5 ? 'badge-red' : 'badge-yellow' }}">
                        {{ $item->quantity_on_hand <= 5 ? 'Critical' : 'Low' }}
                    </span>
                </div>
            </div>
            @empty
            <p style="color:var(--success);font-size:13px;text-align:center;padding:20px 0;">
                <i class="fas fa-circle-check"></i> All items sufficiently stocked!
            </p>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const chartDefaults = {
    labels: @json($chartLabels),
    borderRadius: 6,
    tension: 0.4,
};

// Revenue Chart
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: chartDefaults.labels,
        datasets: [{
            label: 'Revenue (₱)',
            data: @json($revenueData),
            borderColor: '#C0392B',
            backgroundColor: 'rgba(192,57,43,0.08)',
            borderWidth: 2.5,
            fill: true,
            pointBackgroundColor: '#C0392B',
            pointRadius: 4,
            tension: 0.4
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { 
                grid: { color: '#F3F4F6' },
                ticks: { font: { family: 'Outfit', size: 11 }, callback: v => '₱' + v.toLocaleString() }
            },
            x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 } } }
        }
    }
});

// Items Chart
new Chart(document.getElementById('itemsChart'), {
    type: 'bar',
    data: {
        labels: chartDefaults.labels,
        datasets: [{
            label: 'Items Sold',
            data: @json($itemsData),
            backgroundColor: '#C0392B',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: '#F3F4F6' }, ticks: { font: { family: 'Outfit', size: 11 } } },
            x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 } } }
        }
    }
});
</script>
@endpush