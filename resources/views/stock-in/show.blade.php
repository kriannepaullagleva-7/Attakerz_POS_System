@extends('components.app-layout')

@section('title', 'Stock In #ST' . str_pad($stockIn->id, 5, '0', STR_PAD_LEFT))
@section('subtitle', 'Transaction details and received items')

@section('topbar-actions')
<a href="{{ route('stock-in.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back to List
</a>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start;">

    <!-- Info Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Transaction Info</div>
            <span class="badge badge-green">Completed</span>
        </div>
        <div class="card-body">
            @php
            $rows = [
                ['label' => 'Transaction #', 'value' => 'ST' . str_pad($stockIn->id, 5, '0', STR_PAD_LEFT), 'mono' => true, 'red' => true],
                ['label' => 'Date', 'value' => \Carbon\Carbon::parse($stockIn->date)->format('F d, Y · h:i A')],
                ['label' => 'Supplier', 'value' => $stockIn->supplier->supplier_name ?? '—'],
                ['label' => 'Contact', 'value' => $stockIn->supplier->contact_number ?? '—'],
                ['label' => 'Received By', 'value' => ($stockIn->employee->first_name ?? '—') . ' ' . ($stockIn->employee->last_name ?? '')],
                ['label' => 'Role', 'value' => $stockIn->employee->role ?? '—'],
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

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;background:var(--red-primary);border-radius:10px;padding:14px 16px;">
                <span style="color:rgba(255,255,255,0.8);font-size:13px;font-weight:600;">Total Cost</span>
                <span style="color:#fff;font-size:20px;font-weight:800;font-family:var(--font-mono);">
                    ₱{{ number_format($stockIn->total_cost, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Items -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="fas fa-boxes-stacked" style="color:var(--red-primary)"></i> Items Received</div>
                <div class="card-subtitle">{{ $stockIn->details->count() }} product(s) in this delivery</div>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Cost / Unit</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockIn->details as $i => $detail)
                    <tr>
                        <td style="color:var(--gray-600);font-size:12px;">{{ $i + 1 }}</td>
                        <td>
                            <div style="font-weight:600;font-size:13px;">{{ $detail->product->product_name ?? 'Unknown' }}</div>
                            <div style="font-size:11px;color:var(--gray-600);">per {{ $detail->product->unit ?? 'pc' }}</div>
                        </td>
                        <td>
                        <span class="badge {{ ($detail->product->category ?? '') === 'finished' ? 'badge-red' : 'badge-blue' }}">
                                {{ ucfirst($detail->product->category ?? '—') }}
                            </span>
                        </td>
                        <td style="font-family:var(--font-mono);font-weight:600;">
                            {{ $detail->quantity }} {{ $detail->product->unit ?? '' }}
                        </td>
                        <td style="font-family:var(--font-mono);">₱{{ number_format($detail->cost_per_unit, 2) }}</td>
                        <td style="font-family:var(--font-mono);font-weight:700;color:var(--red-primary);">
                            ₱{{ number_format($detail->quantity * $detail->cost_per_unit, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:var(--gray-50);">
                        <td colspan="5" style="padding:12px 16px;font-weight:700;font-size:13px;text-align:right;">TOTAL</td>
                        <td style="padding:12px 16px;font-family:var(--font-mono);font-weight:800;font-size:15px;color:var(--red-primary);">
                            ₱{{ number_format($stockIn->total_cost, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>

@endsection