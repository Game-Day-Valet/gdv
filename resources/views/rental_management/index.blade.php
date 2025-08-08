@extends('layouts.vertical', ['title' => 'Rental Management'])

@section('css')
    @vite(['node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css', 'node_modules/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css', 'node_modules/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css', 'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css', 'node_modules/datatables.net-select-bs5/css/select.bootstrap5.min.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .update-status-badge,
        .update-payment-badge {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .update-status-badge:hover,
        .update-payment-badge:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .update-status-badge:hover::after,
        .update-payment-badge:hover::after {
            content: " (Click to update)";
            font-size: 0.8em;
            opacity: 0.8;
        }

        /* Modal backdrop styling */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5) !important;
        }

        .modal-backdrop.show {
            opacity: 1 !important;
        }
    </style>
@endsection

@section('content')
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Rental Management</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Rental Management</a></li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rental List</h5>
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
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table id="datatable" class="table table-bordered dt-responsive nowrap" style="min-width: 1200px;">
                            <thead>
                                <tr>
                                      <th>User</th>
                                    <th>Tournament</th>
                                    <th>Team Name</th>
                                    <th>Coach</th>
                                    <th>Field</th>
                                    <th>Rental Date</th>
                                    <th>Total Amount</th>
                                    <th>Payment Status</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rentals as $rental)
                                    <tr>
                                         <td>
                                            <div>
                                                <strong>{{ $rental->user->name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $rental->user->email ?? 'N/A' }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $rental->tournament->name ?? 'N/A' }}</td>
                                        <td>{{ $rental->team_name }}</td>
                                        <td>{{ $rental->coach_name }}</td>
                                        <td>{{ $rental->field_number }}</td>
                                        <td>{{ $rental->rental_date ? \Carbon\Carbon::parse($rental->rental_date)->format('d M Y') : 'N/A' }}</td>
                                        <td>
                                            @if($rental->total_amount)
                                                ${{ number_format($rental->total_amount, 2) }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $paymentStatusClass = match($rental->payment_status) {
                                                    'completed' => 'badge bg-success',
                                                    'pending' => 'badge bg-warning',
                                                    'paid' => 'badge bg-purple',
                                                    default => 'badge bg-secondary'
                                                };
                                            @endphp
                                            <span class="{{ $paymentStatusClass }} update-payment-badge"
                                                  style="cursor: pointer;"
                                                  data-rental-id="{{ $rental->id }}"
                                                  data-current-payment="{{ $rental->payment_status ?? 'pending' }}"
                                                  title="Click to update payment status">
                                                {{ ucfirst($rental->payment_status ?? 'pending') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = match($rental->status) {
                                                    'delivered' => 'badge bg-success',
                                                    'picked_up' => 'badge bg-info',
                                                    'returned' => 'badge bg-primary',
                                                    'pending' => 'badge bg-warning',
                                                    default => 'badge bg-secondary'
                                                };
                                            @endphp
                                            <span class="{{ $statusClass }} update-status-badge"
                                                  style="cursor: pointer;"
                                                  data-rental-id="{{ $rental->id }}"
                                                  data-current-status="{{ $rental->status ?? 'pending' }}"
                                                  title="Click to update rental status">
                                                {{ ucfirst($rental->status ?? 'pending') }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($rental->created_at)->format('d M Y H:i') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('rental-management.show', $rental->id) }}" class="btn btn-sm btn-info">View</a>
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
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Update Rental Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="statusForm">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="delivered">Delivered</option>
                                <option value="picked_up">Picked Up</option>
                                <option value="returned">Returned</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any notes about this status change..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status_image" class="form-label">Image (Optional)</label>
                            <input type="file" name="image" id="status_image" class="form-control" accept="image/*">
                            <small class="text-muted">Upload an image related to this status change (JPEG, PNG, JPG, GIF - Max 2MB)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveStatusBtn">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Update Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Update Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="savePaymentBtn">Update Payment</button>
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
            let currentRentalId = null;

            // Status Update
            document.querySelectorAll('.update-status-badge').forEach(function(badge) {
                badge.addEventListener('click', function() {
                    currentRentalId = this.getAttribute('data-rental-id');
                    const currentStatus = this.getAttribute('data-current-status');

                    document.getElementById('status').value = currentStatus;
                    document.getElementById('notes').value = ''; // Clear notes field
                    document.getElementById('status_image').value = ''; // Clear image field

                    // Use Bootstrap modal properly
                    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                    modal.show();
                });
            });

            // Payment Update
            document.querySelectorAll('.update-payment-badge').forEach(function(badge) {
                badge.addEventListener('click', function() {
                    currentRentalId = this.getAttribute('data-rental-id');
                    const currentPayment = this.getAttribute('data-current-payment');

                    document.getElementById('payment_status').value = currentPayment;

                    // Use Bootstrap modal properly
                    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                    modal.show();
                });
            });

            // Save Status
            document.getElementById('saveStatusBtn').addEventListener('click', function() {
                const status = document.getElementById('status').value;
                const notes = document.getElementById('notes').value;
                const imageFile = document.getElementById('status_image').files[0];

                const formData = new FormData();
                formData.append('status', status);
                formData.append('notes', notes);
                if (imageFile) {
                    formData.append('image', imageFile);
                }
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                fetch(`/rental-management/${currentRentalId}/update-status`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Success!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error!', 'Failed to update status', 'error');
                })
                .finally(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
                    if (modal) {
                        modal.hide();
                    }
                });
            });

            // Save Payment
            document.getElementById('savePaymentBtn').addEventListener('click', function() {
                const paymentStatus = document.getElementById('payment_status').value;

                fetch(`/rental-management/${currentRentalId}/update-payment-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ payment_status: paymentStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Success!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error!', 'Failed to update payment status', 'error');
                })
                .finally(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                    if (modal) {
                        modal.hide();
                    }
                });
            });

            // Close modals
            document.querySelectorAll('.btn-close, .btn-secondary').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    // Let Bootstrap handle the modal closing
                    const modalElement = this.closest('.modal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                    }
                });
            });
        });
    </script>
@endsection
