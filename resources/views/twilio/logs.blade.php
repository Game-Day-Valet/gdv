@extends('layouts.vertical', ['title' => 'Twilio Logs'])

@section('content')
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Twilio SMS Logs</h4>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Settings</a></li>
            <li class="breadcrumb-item active">Twilio Logs</li>
        </ol>
    </div>
</div>

@if($error)
    <div class="alert alert-danger">{{ $error }}</div>
@endif

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Overview</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6 col-md-2">
                <div class="p-3 bg-light rounded">
                    <div class="text-muted">Total</div>
                    <div class="fs-4 fw-semibold">{{ $summary['total'] }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-md-2">
                <div class="p-3 bg-light rounded">
                    <div class="text-muted">Delivered</div>
                    <div class="fs-4 fw-semibold">{{ $summary['delivered'] }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-md-2">
                <div class="p-3 bg-light rounded">
                    <div class="text-muted">Sent</div>
                    <div class="fs-4 fw-semibold">{{ $summary['sent'] }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-md-2">
                <div class="p-3 bg-light rounded">
                    <div class="text-muted">Queued</div>
                    <div class="fs-4 fw-semibold">{{ $summary['queued'] }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-md-2">
                <div class="p-3 bg-light rounded">
                    <div class="text-muted">Failed</div>
                    <div class="fs-4 fw-semibold">{{ $summary['failed'] }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-md-2">
                <div class="p-3 bg-light rounded">
                    <div class="text-muted">Undelivered</div>
                    <div class="fs-4 fw-semibold">{{ $summary['undelivered'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Logs</h5>
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="to" value="{{ $filters['to'] }}" class="form-control" placeholder="To (E.164 e.g. +15551234567)" style="max-width:220px">
            <select name="status" class="form-select" style="max-width:180px">
                <option value="">All Statuses</option>
                @foreach(['queued','sent','delivered','failed','undelivered'] as $st)
                    <option value="{{ $st }}" {{ $filters['status']===$st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <input type="date" name="start" value="{{ $filters['start'] }}" class="form-control" style="max-width:160px">
            <input type="date" name="end" value="{{ $filters['end'] }}" class="form-control" style="max-width:160px">
            <input type="number" min="1" max="1000" name="limit" value="{{ $filters['limit'] }}" class="form-control" style="max-width:120px" placeholder="Limit">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>To</th>
                        <th>From</th>
                        <th>Status</th>
                        <th>Body</th>
                        <th>Error</th>
                        <th>SID</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $m)
                        <tr>
                            <td class="text-muted" style="white-space:nowrap">{{ $m['date_sent'] ?? $m['date_created'] }}</td>
                            <td>{{ $m['to'] }}</td>
                            <td>{{ $m['from'] }}</td>
                            <td>
                                @php $s = $m['status']; @endphp
                                <span class="badge bg-{{ in_array($s,['delivered']) ? 'success' : (in_array($s,['failed','undelivered']) ? 'danger' : 'secondary') }}">{{ ucfirst($s) }}</span>
                            </td>
                            <td style="max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $m['body'] }}">{{ $m['body'] }}</td>
                            <td>
                                @if($m['error_code'])
                                    <span class="text-danger" title="{{ $m['error_message'] }}">{{ $m['error_code'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-family:monospace">{{ $m['sid'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No logs found for current filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="text-muted small">Showing up to the most recent {{ $filters['limit'] }} messages. Narrow with filters for more precise results.</div>
    </div>
</div>
@endsection


