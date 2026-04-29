@extends('components.app-layout')

@section('title', 'Production Batch #' . $production->production_id)
@section('subtitle', 'Raw materials used and finished products produced')

@section('topbar-actions')
<a href="{{ route('production.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back to Production
</a>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

    <!-- Info Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Batch Info</div>
            <span class="badge badge-green">Recorded</span>
        </div>
        <div class="card-body">
            @php
            $rows = [
                ['label' => 'Batch #', 'value' => '#' . $production->production_id, 'mono' => true, 'red' => true],
                ['label' => 'Date', 'value' => \Carbon\Carbon::parse($production->date)->format('F d, Y · h:i A')],
                ['label' => 'Employee', 'value' => ($production->employee->first_name ?? '—') . ' ' . ($production->employee->last_name ?? '')],
                ['label' => 'Role', 'value' => $production->employee->role ?? '—'],
                ['label' => 'Total Output', 'value' => $production->outputs->sum('quantity_produced') . ' units'],
            ];
            @endphp
            @foreach($rows as $row)
            <div style="display:flex;justify-content:space-between;align-items:baseline;padding:9px 0;border-bottom:1px solid var(--gray-100);">
                <span style="font-size:12px;color:var(--gray-600);">{{ $row['label'] }}</span>
                <span style="font-size:13px;font-weight:600;
                    {{ isset($row['mono']) && $row['mono'] ? 'font-family:var(--font-mono);' : '' }}
                    {{ isset($row['red']) && $row['red'] ? 'color:var(--red-primary);' : 'color:var(--gray-800);' }}">
                    {{ $row['value'] }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Details Grid -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- Raw Materials -->
        <div class="card">
            <div class="card-header">
                <div><div class="card-title"><i class="fas fa-wheat-awn" style="color:#2563EB"></i> Raw Materials Used</div></div>
                <span class="badge badge-info">{{ $production->rawMaterials->count() }} item(s)</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Unit</th>
                            <th>Qty Used</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($production->rawMaterials as $i => $rm)
                        <tr>
                            <td style="color:var(--gray-600);font-size:12px;">{{ $i + 1 }}</td>
                            <td style="font-weight:600;">{{ $rm->product->product_name ?? 'Unknown' }}</td>
                            <td style="color:var(--gray-600);font-size:13px;">{{ $rm->product->unit ?? '—' }}</td>
                            <td style="font-family:var(--font-mono);font-weight:700;color:#2563EB;">{{ $rm->quantity_used }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--gray-600);">No raw materials recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Outputs -->
        <div class="card">
            <div class="card-header">
                <div><div class="card-title"><i class="fas fa-drumstick-bite" style="color:var(--success)"></i> Finished Products Produced</div></div>
                <span class="badge badge-success">{{ $production->outputs->count() }} item(s)</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Unit</th>
                            <th>Qty Produced</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($production->outputs as $i => $out)
                        <tr>
                            <td style="color:var(--gray-600);font-size:12px;">{{ $i + 1 }}</td>
                            <td style="font-weight:600;">{{ $out->product->product_name ?? 'Unknown' }}</td>
                            <td style="color:var(--gray-600);font-size:13px;">{{ $out->product->unit ?? '—' }}</td>
                            <td>
                                <span style="font-family:var(--font-mono);font-weight:700;color:var(--success);background:#D1FAE5;padding:2px 10px;border-radius:4px;">
                                    {{ $out->quantity_produced }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--gray-600);">No outputs recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection
