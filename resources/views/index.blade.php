@extends('layouts.vertical', ['title' => 'Dashboard'])

@section('content')
<style>
/* Light, theme-friendly polish (non-intrusive) */
.kpi-card{border:1px solid #eef2f7;border-radius:14px}
.kpi-title{font-size:12px;color:#6b7280;margin-bottom:6px}
.kpi-value{font-size:26px;font-weight:800;color:#111827;margin:0}
.kpi-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;color:#4f46e5}
.section-chip{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;background:#f3f4f6;color:#374151;font-size:12px}
.section-chip .dot{width:8px;height:8px;border-radius:50%;background:#4f46e5}
.section-meta{font-size:12px;color:#9ca3af}
.card-soft{border:1px solid #eef2f7;border-radius:14px}
.card-soft .card-header{background:#f8fafc;border-bottom:1px solid #eef2f7;border-top-left-radius:14px;border-top-right-radius:14px}
.table thead th{font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.02em}
.table-hover>tbody>tr:hover{background:#f9fafb}
.badge-count{background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;padding:2px 8px;border-radius:999px;font-size:12px}
.muted{color:#6b7280}
.list-compact{margin:0;padding-left:18px}
.list-compact li{margin:0 0 4px 0}
.rounded-table{border-collapse:separate;border-spacing:0 6px}
.rounded-table tbody tr{background:#fff}
.rounded-table tbody tr td:first-child,.rounded-table thead tr th:first-child{border-top-left-radius:10px;border-bottom-left-radius:10px}
.rounded-table tbody tr td:last-child,.rounded-table thead tr th:last-child{border-top-right-radius:10px;border-bottom-right-radius:10px}
.thumb-sm{width:52px;height:52px;object-fit:cover}
.cell-title{font-weight:600;color:#111827}
.cell-sub{font-size:12px;color:#6b7280}
</style>
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Dashboard</h4>
    </div>
</div>

<!-- start row -->
<div class="row">
    <div class="col-md-12 col-xl-12">
        <div class="row g-3">
            <div class="col-md-6 col-xl-3">
                <div class="card kpi-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="kpi-title">Total Rentals</div>
                                <p class="kpi-value">{{ number_format($stats['total_rentals']) }}</p>
                            </div>
                            <div class="kpi-icon"><i class="fas fa-box"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card kpi-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="kpi-title">Pending Rentals</div>
                                <p class="kpi-value">{{ number_format($stats['pending_rentals']) }}</p>
                            </div>
                            <div class="kpi-icon"><i class="fas fa-hourglass-half"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card kpi-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="kpi-title">Delivered Rentals</div>
                                <p class="kpi-value">{{ number_format($stats['delivered_rentals']) }}</p>
                            </div>
                            <div class="kpi-icon"><i class="fas fa-truck"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card kpi-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="kpi-title">Returned Rentals</div>
                                <p class="kpi-value">{{ number_format($stats['returned_rentals']) }}</p>
                            </div>
                            <div class="kpi-icon"><i class="fas fa-undo"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
<!-- Top 5 Rental Bundles -->
    <div class="col-md-8 col-xl-8 d-flex"> <!-- changed from 6 to 8 -->
    <div class="card card-soft w-100 h-100">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="section-chip"><span class="dot"></span> Top 5 Rental Bundles</span>
                </div>
                <div class="section-meta">Last 30 days</div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Bundle Name</th>
                            <th>Total Rentals</th>
                            <th>Items</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($stats['top_rental_bundles'] as $index => $bundle)
                        <tr>
                            <td>{{ $bundle['name'] }}</td>
                            <td><span class="badge-count">{{ number_format($bundle['total_rentals']) }}</span></td>
                            <td>
                                @if(!empty($bundle['items']))
                                    <ul class="list-compact">
                                        @foreach($bundle['items'] as $item)
                                            <li>{{ $item['name'] }} <span class="text-muted">({{ number_format($item['price'], 2) }})</span></li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">No items</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No rental bundles found</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Top 5 Rental items -->
    <div class="col-md-4 col-xl-4 d-flex"> <!-- changed from 6 to 4 -->
        <div class="card card-soft w-100 h-100">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="section-chip"><span class="dot"></span> Top 5 Rental Items</span>
                    <div class="section-meta">Last 30 days</div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Total Rentals</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse(array_slice($stats['top_rental_items'], 0, 5) as $index => $item)
                            <tr>
                                <td>{{ $item['name'] }}</td>
                                <td><span class="badge-count">{{ number_format($item['total_rentals']) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No rental items found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card card-soft">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="section-chip"><span class="dot"></span> Today's Tournaments</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover dt-responsive table-responsive nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Sport</th>
                                <th>Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($todaysTournaments as $tournament)
                            <tr>
                                <td>
                                    @if($tournament->image)
                                        <img src="{{ asset('storage/'.$tournament->image) }}" alt="{{ $tournament->name }}" class="thumb-sm rounded">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                                <td>{{ $tournament->sport->name ?? '-' }}</td>
                                <td>{{ $tournament->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($tournament->start_date)->format('d M Y') }}</td>
                                <td>{{ $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date)->format('d M Y') : '-' }}</td>
                                <td>{{ $tournament->location }}</td>
                                <td><span class="badge bg-success-subtle text-success">{{ \Illuminate\Support\Str::title($tournament->status->value) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No tournaments today</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<!-- End Top 5 Rental Bundles -->

@endsection

@section('script')
    <script src="https://apexcharts.com/samples/assets/stock-prices.js"></script>
    <!-- Removed analytics-dashboard.init.js to prevent ApexCharts errors -->
@endsection
