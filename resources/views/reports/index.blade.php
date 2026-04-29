@extends('components.app-layout')

@section('title', 'Business Reports')
@section('subtitle', 'Sales analytics and performance overview')

@push('styles')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
    .report-tabs {
        display: flex; gap: 4px;
        background: #fff; border: 1px solid var(--gray-200);
        border-radius: 10px; padding: 5px;
        margin-bottom: 20px; width: fit-content;
    }

    .report-tab {
        padding: 9px 20px; border-radius: 7px; border: none;
        font-family: var(--font); font-size: 13px; font-weight: 600;
        color: var(--gray-600); cursor: pointer; background: none;
        transition: all 0.15s;
    }
    .report-tab.active { background: var(--red-primary); color: #fff; }

    .report-panel { display: none; }
    .report-panel.active { display: block; }

    .summary-bar {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;
    }

    .daily-row:hover td { background: #FEF2F2 !important; }

    .monthly-bar { position: relative; height: 24px; background: var(--gray-200); border-radius: 4px; overflow: hidden; }
    .monthly-bar-fill { height: 100%; background: var(--red-primary); border-radius: 4px; transition: width 0.6s ease; }

    .rank-badge {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700;
    }

    .rank-badge.top-3 {
        background: var(--red-primary); color: #fff;
    }

    .rank-badge.others {
        background: var(--gray-200); color: var(--gray-600);
    }

    .print-btn { display: flex; align-items: center; gap: 6px; }

    @media print {
        .sidebar, .topbar, .report-tabs, .print-btn { display: none !important; }
        .main { margin-left: 0; }
        .content { padding: 0; }
    }
</style>
@endpush

@section('topbar-actions')
<div style="display:flex;gap:10px;align-items:center;">
    <select class="form-control" id="reportMonth" style="width:160px;" onchange="changeMonth(this.value)">
        @for($m = 1; $m <= 12; $m++)
        <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create(null, $m)->format('F') }} {{ now()->year }}
        </option>
        @endfor
    </select>
    <button class="btn btn-secondary print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Print
    </button>
</div>
@endsection

@section('content')

<!-- Tabs -->
<div class="report-tabs">
    <button class="report-tab active" onclick="switchTab('daily', this)">
        <i class="fas fa-calendar-day"></i> Daily Sales
    </button>
    <button class="report-tab" onclick="switchTab('monthly', this)">
        <i class="fas fa-calendar-alt"></i> Monthly Summary
    </button>
    <button class="report-tab" onclick="switchTab('products', this)">
        <i class="fas fa-drumstick-bite"></i> Product Performance
    </button>
</div>

<!-- ─── DAILY PANEL ─── -->
<div class="report-panel active" id="panel-daily">
    <div class="summary-bar">
        <div class="stat-card">
            <div class="icon" style="background:#FEE2E2;"><i class="fas fa-peso-sign" style="color:var(--red-primary)"></i></div>
            <div class="value">₱{{ number_format($todaySales, 2) }}</div>
            <div class="label">Today's Revenue</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="background:#DBEAFE;"><i class="fas fa-receipt" style="color:#2563EB"></i></div>
            <div class="value">{{ $todayOrders }}</div>
            <div class="label">Orders Today</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="background:#D1FAE5;"><i class="fas fa-chart-line" style="color:var(--success)"></i></div>
            <div class="value">₱{{ number_format($todayAvg, 2) }}</div>
            <div class="label">Avg. Order Value</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="background:#FEF3C7;"><i class="fas fa-basket-shopping" style="color:#D97706"></i></div>
            <div class="value">{{ $todayItems }}</div>
            <div class="label">Items Sold</div>
        </div>
    </div>

    <div class="grid grid-2" style="margin-bottom:24px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Revenue — Last 14 Days</div>
            </div>
            <div class="card-body">
                <canvas id="dailyChart" height="200"></canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Today's Sales by Product</div>
            </div>
            <div class="card-body">
                <canvas id="todayPieChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Daily Sales Log</div>
            <div style="font-size:12px;color:var(--gray-600);">Last 14 days</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Orders</th>
                        <th>Items Sold</th>
                        <th>Revenue</th>
                        <th>vs Yesterday</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyReport as $i => $day)
                    @php
                        $prev = $dailyReport[$i + 1] ?? null;
                        $diff = $prev ? $day->revenue - $prev->revenue : 0;
                        $pct = $prev && $prev->revenue > 0 ? round(($diff / $prev->revenue) * 100, 1) : 0;
                    @endphp
                    <tr class="daily-row">
                        <td style="font-weight:600;">{{ \Carbon\Carbon::parse($day->sale_date)->format('M d, Y') }}</td>
                        <td style="color:var(--gray-600);">{{ \Carbon\Carbon::parse($day->sale_date)->format('l') }}</td>
                        <td>
                            <span class="badge badge-blue">{{ $day->orders }} orders</span>
                        </td>
                        <td style="font-size:13px;">{{ $day->items_sold }} items</td>
                        <td style="font-family:var(--font-mono);font-weight:700;color:var(--red-primary);">
                            ₱{{ number_format($day->revenue, 2) }}
                        </td>
                        <td>
                            @if($prev)
                                <span class="badge {{ $diff >= 0 ? 'badge-green' : 'badge-red' }}">
                                    {{ $diff >= 0 ? '+' : '' }}{{ $pct }}%
                                </span>
                            @else
                                <span class="badge badge-gray">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ─── MONTHLY PANEL ─── -->
<div class="report-panel" id="panel-monthly">
    <div class="summary-bar">
        <div class="stat-card">
            <div class="icon" style="background:#FEE2E2;"><i class="fas fa-peso-sign" style="color:var(--red-primary)"></i></div>
            <div class="value">₱{{ number_format($monthTotal, 2) }}</div>
            <div class="label">This Month's Revenue</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="background:#DBEAFE;"><i class="fas fa-receipt" style="color:#2563EB"></i></div>
            <div class="value">{{ $monthOrders }}</div>
            <div class="label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="background:#D1FAE5;"><i class="fas fa-chart-pie" style="color:var(--success)"></i></div>
            <div class="value">₱{{ number_format($monthAvg, 2) }}</div>
            <div class="label">Avg. Daily Revenue</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="background:#FEF3C7;">
                <i class="fas fa-{{ $monthGrowth >= 0 ? 'arrow-trend-up' : 'arrow-trend-down' }}" style="color:#D97706"></i>
            </div>
            <div class="value {{ $monthGrowth >= 0 ? '' : 'text-red' }}">{{ $monthGrowth >= 0 ? '+' : '' }}{{ $monthGrowth }}%</div>
            <div class="label">vs Last Month</div>
        </div>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
            <div class="card-title">Monthly Revenue Trend</div>
        </div>
        <div class="card-body">
            <canvas id="monthlyChart" height="140"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Monthly Summary</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                        <th>Performance</th>
                        <th>vs Previous</th>
                    </tr>
                </thead>
                <tbody>
                    @php $maxMonthRev = $monthlyReport->max('revenue') ?: 1; @endphp
                    @foreach($monthlyReport as $i => $month)
                    @php
                        $prev = $monthlyReport[$i + 1] ?? null;
                        $diff = $prev ? $month->revenue - $prev->revenue : 0;
                        $pct = $prev && $prev->revenue > 0 ? round(($diff / $prev->revenue) * 100, 1) : 0;
                        $barPct = ($month->revenue / $maxMonthRev) * 100;
                    @endphp
                    <tr>
                        <td style="font-weight:600;">{{ \Carbon\Carbon::create(null, $month->month)->format('F') }} {{ $month->year }}</td>
                        <td><span class="badge badge-blue">{{ $month->orders }}</span></td>
                        <td style="font-family:var(--font-mono);font-weight:700;color:var(--red-primary);">
                            ₱{{ number_format($month->revenue, 2) }}
                        </td>
                        <td style="width:200px;">
                            <div class="monthly-bar">
                                <div class="monthly-bar-fill" data-width="{{ $barPct }}"></div>
                            </div>
                        </td>
                        <td>
                            @if($prev)
                            <span class="badge {{ $diff >= 0 ? 'badge-green' : 'badge-red' }}">
                                {{ $diff >= 0 ? '+' : '' }}{{ $pct }}%
                            </span>
                            @else
                            <span class="badge badge-gray">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ─── PRODUCT PERFORMANCE PANEL ─── -->
<div class="report-panel" id="panel-products">
    <div class="grid grid-2" style="margin-bottom:24px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Revenue by Product</div>
                <div style="font-size:12px;color:var(--gray-600);">This month</div>
            </div>
            <div class="card-body">
                <canvas id="productBarChart" height="220"></canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Units Sold Breakdown</div>
            </div>
            <div class="card-body">
                <canvas id="productPieChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Product Performance This Month</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Units Sold</th>
                        <th>Revenue</th>
                        <th>Avg. Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productPerformance as $i => $perf)
                    <tr>
                        <td>
                            <div class="rank-badge {{ $i < 3 ? 'top-3' : 'others' }}">
                                {{ $i + 1 }}
                            </div>
                        </td>
                        <td style="font-weight:600;font-size:13px;">{{ $perf->product_name }}</td>
                        <td>
                            <span class="badge {{ $perf->category === 'Finished' ? 'badge-red' : 'badge-blue' }}">
                                {{ $perf->category }}
                            </span>
                        </td>
                        <td style="font-family:var(--font-mono);font-weight:600;">{{ number_format($perf->units_sold) }} {{ $perf->unit }}</td>
                        <td style="font-family:var(--font-mono);font-weight:700;color:var(--red-primary);">
                            ₱{{ number_format($perf->revenue, 2) }}
                        </td>
                        <td style="font-family:var(--font-mono);color:var(--gray-600);">
                            ₱{{ number_format($perf->avg_price, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:40px;color:var(--gray-600);">No sales data this month.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ─── Set dynamic widths from data attributes ───
document.querySelectorAll('.monthly-bar-fill[data-width]').forEach(el => {
    el.style.width = el.getAttribute('data-width') + '%';
});

function switchTab(id, btn) {
    document.querySelectorAll('.report-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.report-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + id).classList.add('active');
    btn.classList.add('active');
}

// ─── Chart Data ───
const dailyLabels = @json($dailyChartLabels);
const dailyRevenue = @json($dailyChartRevenue);
const productLabels = @json($productPerformance->pluck('product_name'));
const productRevenue = @json($productPerformance->pluck('revenue'));
const productUnits = @json($productPerformance->pluck('units_sold'));
const monthlyLabels = @json($monthlyChartLabels);
const monthlyRevenue = @json($monthlyChartRevenue);
const todayPieLabels = @json($todayPieLabels);
const todayPieData = @json($todayPieData);

const RED_PALETTE = ['#C0392B','#E74C3C','#FF6B6B','#922B21','#641E16','#F1948A','#FADBD8','#EC7063'];

// Daily Revenue Chart
new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Revenue',
            data: dailyRevenue,
            borderColor: '#C0392B',
            backgroundColor: 'rgba(192,57,43,0.08)',
            fill: true, tension: 0.4, borderWidth: 2.5,
            pointBackgroundColor: '#C0392B', pointRadius: 4
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: '#F3F4F6' }, ticks: { callback: v => '₱' + v.toLocaleString(), font: { family: 'Outfit', size: 11 } } },
            x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 } } }
        }
    }
});

// Today Pie
if (todayPieData.length > 0) {
    new Chart(document.getElementById('todayPieChart'), {
        type: 'doughnut',
        data: { labels: todayPieLabels, datasets: [{ data: todayPieData, backgroundColor: RED_PALETTE, borderWidth: 2 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { family: 'Outfit', size: 11 } } } }
        }
    });
}

// Monthly Chart
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: monthlyLabels,
        datasets: [{ label: 'Revenue', data: monthlyRevenue, backgroundColor: '#C0392B', borderRadius: 6 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: '#F3F4F6' }, ticks: { callback: v => '₱' + v.toLocaleString(), font: { family: 'Outfit', size: 11 } } },
            x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 } } }
        }
    }
});

// Product Bar
if (productLabels.length > 0) {
    new Chart(document.getElementById('productBarChart'), {
        type: 'bar',
        data: {
            labels: productLabels,
            datasets: [{ label: 'Revenue (₱)', data: productRevenue, backgroundColor: RED_PALETTE, borderRadius: 6 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: '#F3F4F6' }, ticks: { callback: v => '₱' + v.toLocaleString(), font: { family: 'Outfit', size: 11 } } },
                y: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 } } }
            }
        }
    });

    new Chart(document.getElementById('productPieChart'), {
        type: 'doughnut',
        data: { labels: productLabels, datasets: [{ data: productUnits, backgroundColor: RED_PALETTE, borderWidth: 2 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { family: 'Outfit', size: 11 }, boxWidth: 12 } } }
        }
    });
}
</script>
@endpush