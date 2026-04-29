@extends('components.app-layout')

@section('title', 'Sale #P' . str_pad($sale->id, 5, '0', STR_PAD_LEFT))
@section('subtitle', 'Transaction receipt details')

@section('topbar-actions')
<a href="{{ route('sales.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back to Sales
</a>
<button class="btn btn-secondary" onclick="window.print()">
    <i class="fas fa-print"></i> Print
</button>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

    <!-- Receipt Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Transaction Info</div>
            <span class="badge badge-green">Completed</span>
        </div>
        <div class="card-body">
            @php
            $cash = $sale->cash_paid ?? $sale->total_amount;
            $change = $cash - $sale->total_amount;
            $rows = [
                ['label' => 'Order #', 'value' => 'P' . str_pad($sale->id, 5, '0', STR_PAD_LEFT), 'mono' => true, 'red' => true],
                ['label' => 'Date', 'value' => \Carbon\Carbon::parse($sale->date)->format('F d, Y')],
                ['label' => 'Time', 'value' => \Carbon\Carbon::parse($sale->date)->format('h:i A')],
                ['label' => 'Cashier', 'value' => ($sale->employee->first_name ?? 'Cashier') . ' ' . ($sale->employee->last_name ?? '')],
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

            <div style="margin-top:16px;border-top:2px dashed var(--gray-200);padding-top:14px;">
                <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:6px;">
                    <span style="color:var(--gray-600);">Total Amount</span>
                    <span style="font-weight:800;font-family:var(--font-mono);font-size:18px;color:var(--red-primary);">₱{{ number_format($sale->total_amount, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                    <span style="color:var(--gray-600);">Cash Paid</span>
                    <span style="font-family:var(--font-mono);">₱{{ number_format($cash, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;background:#D1FAE5;border-radius:8px;padding:8px 12px;margin-top:8px;">
                    <span style="color:#065F46;font-weight:600;">Change</span>
                    <span style="font-family:var(--font-mono);font-weight:700;color:#065F46;">₱{{ number_format(max(0, $change), 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title"><i class="fas fa-receipt" style="color:var(--red-primary)"></i> Order Items</div>
                <div class="card-subtitle">{{ $sale->details->count() }} item(s) in this order</div>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sale->details as $i => $detail)
                    <tr>
                        <td style="color:var(--gray-600);font-size:12px;">{{ $i + 1 }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $detail->product->product_name ?? 'Unknown' }}</div>
                            <div style="font-size:11px;color:var(--gray-600);">per {{ $detail->product->unit ?? 'pc' }}</div>
                        </td>
                        <td style="font-family:var(--font-mono);">₱{{ number_format($detail->unit_price, 2) }}</td>
                        <td style="font-family:var(--font-mono);font-weight:600;">{{ $detail->quantity }}</td>
                        <td style="font-family:var(--font-mono);font-weight:700;color:var(--red-primary);">
                            ₱{{ number_format($detail->unit_price * $detail->quantity, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--gray-600);">No items found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:var(--gray-50);">
                        <td colspan="4" style="padding:12px 16px;font-weight:700;font-size:13px;text-align:right;">TOTAL</td>
                        <td style="padding:12px 16px;font-family:var(--font-mono);font-weight:800;font-size:15px;color:var(--red-primary);">
                            ₱{{ number_format($sale->total_amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>

@endsection
