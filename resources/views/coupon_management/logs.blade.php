@extends('layouts.vertical', ['title' => 'Coupon Send Logs'])

@section('content')
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Coupon Send Logs</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Batches</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Coupon Code</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Sent</th>
                                    <th>Failed</th>
                                    <th>Started</th>
                                    <th>Finished</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $b)
                                    <tr>
                                        <td>{{ $b->coupon?->code ?? '-' }}</td>
                                        <td>{{ ucfirst($b->status) }}</td>
                                        <td>{{ $b->total_recipients }}</td>
                                        <td>{{ $b->sent_count }}</td>
                                        <td>{{ $b->failed_count }}</td>
                                        <td>{{ $b->started_at ? $b->started_at->format('d M Y H:i') : '-' }}</td>
                                        <td>{{ $b->finished_at ? $b->finished_at->format('d M Y H:i') : '-' }}</td>
                                        <td>{{ $b->message }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $batches->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection


