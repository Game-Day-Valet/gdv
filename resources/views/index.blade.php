@extends('layouts.vertical', ['title' => 'Dashboard'])

@section('content')
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
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-14 mb-1">Total Rentals</div>
                        </div>
                        <div class="d-flex align-items-baseline mb-2">
                            <div class="fs-22 mb-0 me-2 fw-semibold text-black">{{ number_format($stats['total_rentals']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-14 mb-1">Pending Rentals</div>
                        </div>
                        <div class="d-flex align-items-baseline mb-2">
                            <div class="fs-22 mb-0 me-2 fw-semibold text-black">{{ number_format($stats['pending_rentals']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-14 mb-1">Delivered Rentals</div>
                        </div>
                        <div class="d-flex align-items-baseline mb-2">
                            <div class="fs-22 mb-0 me-2 fw-semibold text-black">{{ number_format($stats['delivered_rentals']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-14 mb-1">Returned Rentals</div>
                        </div>
                        <div class="d-flex align-items-baseline mb-2">
                            <div class="fs-22 mb-0 me-2 fw-semibold text-black">{{ number_format($stats['returned_rentals']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
<!-- Top 5 Rental Bundles -->
<div class="col-md-8 col-xl-8"> <!-- changed from 6 to 8 -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center">
                <div class="border border-dark rounded-2 me-2 widget-icons-sections">
                    <i data-feather="package" class="widgets-icons"></i>
                </div>
                <h5 class="card-title mb-0">Top 5 Rental Bundles</h5>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-traffic mb-0">
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
                            <td>{{ number_format($bundle['total_rentals']) }}</td>
                            <td>
                                @if(!empty($bundle['items']))
                                    <ul class="mb-0 ps-3">
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
    <div class="col-md-4 col-xl-4"> <!-- changed from 6 to 4 -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="border border-dark rounded-2 me-2 widget-icons-sections">
                        <i data-feather="star" class="widgets-icons"></i>
                    </div>
                    <h5 class="card-title mb-0">Top 5 Rental Items</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-traffic mb-0">
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
                                <td>{{ number_format($item['total_rentals']) }}</td>
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
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Today's Tournaments</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive table-responsive nowrap mb-0">
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
                                        <img src="{{ asset('storage/'.$tournament->image) }}" alt="{{ $tournament->name }}" class="img-thumbnail" width="50px" height="50px">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                                <td>{{ $tournament->sport->name ?? '-' }}</td>
                                <td>{{ $tournament->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($tournament->start_date)->format('d M Y') }}</td>
                                <td>{{ $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date)->format('d M Y') : '-' }}</td>
                                <td>{{ $tournament->location }}</td>
                                <td>{{ \Illuminate\Support\Str::title($tournament->status->value) }}</td>
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
