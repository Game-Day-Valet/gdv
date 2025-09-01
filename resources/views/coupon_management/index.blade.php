@extends('layouts.vertical', ['title' => 'Coupon List'])

@section('css')
    @vite(['node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css', 'node_modules/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css', 'node_modules/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css', 'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css', 'node_modules/datatables.net-select-bs5/css/select.bootstrap5.min.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Coupon Management</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Coupon Management</a></li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Coupon List</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('coupon-management.logs') }}" class="btn btn-outline-secondary">View Logs</a>
                        <a href="{{ route('coupon-management.create') }}" class="btn btn-primary" id="createButton">Create</a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Max Uses</th>
                                <th>Used</th>
                                <th>Starts At</th>
                                <th>Expires At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($coupons as $coupon)
                                <tr>
                                    <td>{{ $coupon->code }}</td>
                                    <td>{{ $coupon->type }}</td>
                                    <td>{{ number_format($coupon->value, 2) }}</td>
                                    <td>{{ $coupon->max_uses }}</td>
                                    <td>{{ $coupon->used }}</td>
                                    <td>{{ date('d-M-Y', strtotime($coupon->starts_at)) }}</td>
                                    <td>{{ date('d-M-Y', strtotime($coupon->expires_at)) }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('coupon-management.preview', $coupon->id) }}"
                                                class="btn btn-sm btn-info" target="_blank">Preview</a>
                                            <button type="button" class="btn btn-sm btn-success send-coupon-btn"
                                                    data-coupon-id="{{ $coupon->id }}"
                                                    data-coupon-code="{{ $coupon->code }}"
                                                    data-send-url="{{ route('coupon-management.send', $coupon->id) }}">Send</button>
                                            <a href="{{ route('coupon-management.edit', $coupon->id) }}"
                                                class="btn btn-sm btn-primary">Edit</a>
                                            <form action="{{ route('coupon-management.destroy', $coupon->id) }}" method="POST"
                                                class="delete-coupon-form" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="btn btn-sm btn-danger delete-coupon-btn">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite(['resources/js/pages/datatable.init.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Delete coupon confirmation
            document.querySelectorAll('.delete-coupon-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            btn.closest('form').submit();
                        }
                    });
                });
            });

            // Send coupon confirmation with AJAX
            document.querySelectorAll('.send-coupon-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    const couponId = btn.getAttribute('data-coupon-id');
                    const couponCode = btn.getAttribute('data-coupon-code');
                    const sendUrl = btn.getAttribute('data-send-url');

                    Swal.fire({
                        title: 'Send Coupon to Customers?',
                        text: `This will send the coupon "${couponCode}" to all eligible customers via email.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, send it!',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                            console.log('Sending coupon request:', { couponId, csrfToken });

                            return fetch(sendUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => {
                                console.log('Response status:', response.status, response.statusText);
                                console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                                if (!response.ok) {
                                    if (response.status === 302) {
                                        throw new Error('Authentication required. Please refresh the page and try again.');
                                    }
                                    return response.text().then(text => {
                                        console.log('Error response text:', text);
                                        try {
                                            const err = JSON.parse(text);
                                            throw new Error(err.message || `HTTP error! Status: ${response.status}`);
                                        } catch (e) {
                                            throw new Error(`HTTP error! Status: ${response.status} - ${text.substring(0, 100)}`);
                                        }
                                    });
                                }
                                return response.json();
                            })
                            .catch(error => {
                                console.error('Fetch error:', error);
                                Swal.showValidationMessage(`Request failed: ${error.message}`)
                            })
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (result.value && result.value.success && result.value.batch_id) {
                                const statusUrl = result.value.status_url;
                                const logsUrl = result.value.logs_url;
                                // Poll for status for a short while to show progress summary
                                let attempts = 0;
                                const poll = setInterval(() => {
                                    attempts++;
                                    fetch(statusUrl, { headers: { 'Accept': 'application/json' }})
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data && data.success && (data.status === 'completed' || data.status === 'failed')) {
                                                clearInterval(poll);
                                                Swal.fire({
                                                    title: data.status === 'completed' ? 'Emails Sent' : 'Emails Failed',
                                                    html: `Total: ${data.total_recipients}<br/>Sent: ${data.sent_count}<br/>Failed: ${data.failed_count}<br/><a href="${logsUrl}" target="_blank">View logs</a>`,
                                                    icon: data.status === 'completed' ? 'success' : 'error',
                                                    confirmButtonColor: data.status === 'completed' ? '#28a745' : '#dc3545'
                                                });
                                            } else if (attempts >= 20) { // ~20 * 1s = 20 seconds
                                                clearInterval(poll);
                                                Swal.fire({
                                                    title: 'Queued',
                                                    html: `The send job is still running in background. <a href="${logsUrl}" target="_blank">Open logs</a> to check later.`,
                                                    icon: 'info'
                                                });
                                            }
                                        })
                                        .catch(() => {});
                                }, 1000);
                            } else if (result.value && !result.value.success) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: result.value.message,
                                    icon: 'error',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        }
                    });
                });
            });
        });
    </script>
@endsection

