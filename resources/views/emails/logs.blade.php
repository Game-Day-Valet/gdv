@extends('layouts.vertical', ['title' => 'Email Logs'])

@section('content')
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Email Logs</h4>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Settings</a></li>
            <li class="breadcrumb-item active">Email Logs</li>
        </ol>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Overview</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6 col-md-3">
                <div class="p-3 bg-light rounded">
                    <div class="text-muted">Total</div>
                    <div class="fs-4 fw-semibold">{{ $summary['total'] ?? 0 }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="p-3 bg-light rounded">
                    <div class="text-muted">Sent</div>
                    <div class="fs-4 fw-semibold">{{ $summary['sent'] ?? 0 }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="p-3 bg-light rounded">
                    <div class="text-muted">Queued</div>
                    <div class="fs-4 fw-semibold">{{ $summary['queued'] ?? 0 }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="p-3 bg-light rounded">
                    <div class="text-muted">Failed</div>
                    <div class="fs-4 fw-semibold">{{ $summary['failed'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Logs</h5>
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control" placeholder="To email" style="max-width:220px">
            <select name="status" class="form-select" style="max-width:180px">
                <option value="">All Statuses</option>
                @foreach(['queued','sent','failed'] as $st)
                    <option value="{{ $st }}" {{ ($filters['status'] ?? '')===$st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <input type="date" name="start" value="{{ $filters['start'] ?? '' }}" class="form-control" style="max-width:160px">
            <input type="date" name="end" value="{{ $filters['end'] ?? '' }}" class="form-control" style="max-width:160px">
            <input type="number" min="1" max="1000" name="limit" value="{{ $filters['limit'] ?? 100 }}" class="form-control" style="max-width:120px" placeholder="Limit">
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
                        <th>Subject</th>
                        <th>Body</th>
                        <th>Status</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-muted" style="white-space:nowrap">{{ optional($log->sent_at ?? $log->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->to_email }}</td>
                            <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $log->subject }}">{{ $log->subject }}</td>
                            <td style="max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $log->body_preview }}">{{ $log->body_preview }}</td>
                            <td>
                                @php $s = $log->status; @endphp
                                <span class="badge bg-{{ $s==='sent' ? 'success' : ($s==='failed' ? 'danger' : 'secondary') }}">{{ ucfirst($s) }}</span>
                            </td>
                            <td>
                                @if($log->error_reason)
                                    <span class="text-danger" title="{{ $log->error_reason }}">{{ $log->error_reason }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No email logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="text-muted small">Showing up to the most recent {{ $filters['limit'] ?? 100 }} records.</div>
    </div>
</div>
@endsection


