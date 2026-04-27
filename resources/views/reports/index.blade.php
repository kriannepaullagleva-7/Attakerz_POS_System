<x-app-layout>
    <div class="container-fluid">
        <div class="mb-4">
            <h2><i class="bi bi-bar-chart"></i> Sales Reports</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-day"></i> Daily Sales</h5>
                    </div>
                    @if($daily->isEmpty())
                        <div class="card-body text-muted text-center">
                            <p><i class="bi bi-info-circle"></i> No sales data available</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th class="text-end">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daily as $d)
                                        <tr>
                                            <td>{{ $d->d }}</td>
                                            <td class="text-end fw-bold text-success">₱{{ number_format($d->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-month"></i> Monthly Sales</h5>
                    </div>
                    @if($monthly->isEmpty())
                        <div class="card-body text-muted text-center">
                            <p><i class="bi bi-info-circle"></i> No sales data available</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Month</th>
                                        <th class="text-end">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthly as $m)
                                        <tr>
                                            <td>
                                                @php
                                                    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                                    echo $months[$m->m - 1] ?? 'Unknown';
                                                @endphp
                                            </td>
                                            <td class="text-end fw-bold text-success">₱{{ number_format($m->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
