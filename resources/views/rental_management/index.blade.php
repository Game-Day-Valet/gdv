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

        .status-badge-non-clickable {
            opacity: 0.7;
            pointer-events: none;
        }

        .status-badge-non-clickable:hover::after {
            content: " (Status cannot be changed)";
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

        .status-field {
            display: none;
        }

        .status-field.show {
            display: block;
        }

        .image-preview {
            max-width: 100px;
            max-height: 100px;
            margin: 5px;
            border-radius: 5px;
        }

        .remove-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 18px;
            cursor: pointer;
            font-size: 12px;
        }

        .image-container {
            position: relative;
            display: inline-block;
            margin: 5px;
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
                        <table id="datatable" class="table table-bordered dt-responsive nowrap" style="min-width: 1400px;">
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
                                    <th>Estimated Delivery</th>
                                    <th>Assigned Manager</th>
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
                                                    'confirmed' => 'badge bg-info',
                                                    'out_for_delivery' => 'badge bg-primary',
                                                    'cancelled' => 'badge bg-danger',
                                                    'pending' => 'badge bg-warning',
                                                    default => 'badge bg-secondary'
                                                };

                                                $isClickable = !in_array($rental->status, ['delivered', 'cancelled']);
                                                $cursorStyle = $isClickable ? 'cursor: pointer;' : 'cursor: default;';
                                                $title = $isClickable ? 'Click to update rental status' : 'Status cannot be changed';
                                            @endphp
                                            <span class="{{ $statusClass }} {{ $isClickable ? 'update-status-badge' : 'status-badge-non-clickable' }}"
                                                  style="{{ $cursorStyle }}"
                                                  data-rental-id="{{ $rental->id }}"
                                                  data-current-status="{{ $rental->status ?? 'pending' }}"
                                                  data-clickable="{{ $isClickable ? 'true' : 'false' }}"
                                                  title="{{ $title }}">
                                                {{ ucfirst(str_replace('_', ' ', $rental->status ?? 'pending')) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($rental->estimated_delivery_time)
                                                {{ \Carbon\Carbon::parse($rental->estimated_delivery_time)->format('d M Y H:i') }}
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rental->assignedManager)
                                                <div>
                                                    <strong>{{ $rental->assignedManager->name }}</strong><br>
                                                    <small class="text-muted">{{ $rental->assignedManager->email }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
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
        <div class="modal-dialog modal-lg">
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
                                <option value="">Select Status</option>
                            </select>
                        </div>

                        <!-- Confirmed Status Fields -->
                        <div id="confirmedFields" class="status-field">
                            <div class="mb-3">
                                <label for="estimated_delivery_time" class="form-label">Estimated Delivery Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="estimated_delivery_time" id="estimated_delivery_time" class="form-control" required>
                                <small class="text-muted">Estimated delivery time is required when confirming a rental</small>
                            </div>
                            <div class="mb-3">
                                <label for="assigned_manager_id" class="form-label">Assign Manager <span class="text-danger">*</span></label>
                                <select name="assigned_manager_id" id="assigned_manager_id" class="form-select" required>
                                    <option value="">Select Manager</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Manager assignment is required when confirming a rental</small>
                            </div>
                        </div>

                        <!-- Delivered Status Fields -->
                        <div id="deliveredFields" class="status-field">
                            <div class="mb-3">
                                <label for="delivery_images" class="form-label">Delivery Images</label>
                                <input type="file" name="delivery_images[]" id="delivery_images" class="form-control" accept="image/*" multiple>
                                <small class="text-muted">Upload multiple images for delivery confirmation (JPEG, PNG, JPG, GIF - Max 2MB each)</small>
                            </div>
                            <div id="imagePreviewContainer" class="mb-3">
                                <!-- Image previews will be shown here -->
                            </div>
                        </div>

                        <!-- Cancelled Status Fields -->
                        <div id="cancelledFields" class="status-field">
                            <div class="mb-3">
                                <label for="cancellation_notes" class="form-label">Cancellation Notes</label>
                                <textarea name="cancellation_notes" id="cancellation_notes" class="form-control" rows="3" placeholder="Please provide a reason for cancellation..."></textarea>
                            </div>
                        </div>

                        <!-- General Notes Field -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">General Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any additional notes..."></textarea>
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
            let selectedImages = [];

            // Status Update
            document.querySelectorAll('.update-status-badge').forEach(function(badge) {
                badge.addEventListener('click', function() {
                    // Check if status is clickable
                    if (this.getAttribute('data-clickable') === 'false') {
                        return;
                    }

                    currentRentalId = this.getAttribute('data-rental-id');
                    const currentStatus = this.getAttribute('data-current-status');

                    // Clear form
                    document.getElementById('statusForm').reset();
                    hideAllStatusFields();
                    clearImagePreviews();

                    // Reset required attributes
                    document.getElementById('estimated_delivery_time').required = false;
                    document.getElementById('assigned_manager_id').required = false;
                    document.getElementById('cancellation_notes').required = false;

                    // Load available statuses
                    loadAvailableStatuses(currentRentalId, currentStatus);

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

                        // Status change handler
            document.getElementById('status').addEventListener('change', function() {
                const selectedStatus = this.value;
                hideAllStatusFields();

                // Reset required attributes
                document.getElementById('estimated_delivery_time').required = false;
                document.getElementById('assigned_manager_id').required = false;
                document.getElementById('cancellation_notes').required = false;

                if (selectedStatus === 'confirmed') {
                    document.getElementById('confirmedFields').classList.add('show');
                    document.getElementById('estimated_delivery_time').required = true;
                    document.getElementById('assigned_manager_id').required = true;
                } else if (selectedStatus === 'delivered') {
                    document.getElementById('deliveredFields').classList.add('show');
                } else if (selectedStatus === 'cancelled') {
                    document.getElementById('cancelledFields').classList.add('show');
                    document.getElementById('cancellation_notes').required = true;
                }
            });

            // Image upload handler
            document.getElementById('delivery_images').addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                selectedImages = files;
                displayImagePreviews(files);
            });

            // Save Status
            document.getElementById('saveStatusBtn').addEventListener('click', function() {
                const status = document.getElementById('status').value;
                const notes = document.getElementById('notes').value;
                const estimatedDeliveryTime = document.getElementById('estimated_delivery_time').value;
                const assignedManagerId = document.getElementById('assigned_manager_id').value;
                const cancellationNotes = document.getElementById('cancellation_notes').value;
                const deliveryImages = document.getElementById('delivery_images').files;

                if (!status) {
                    Swal.fire('Error!', 'Please select a status', 'error');
                    return;
                }

                // Validate required fields based on status
                if (status === 'confirmed' && !estimatedDeliveryTime) {
                    Swal.fire('Error!', 'Please provide estimated delivery time', 'error');
                    return;
                }

                if (status === 'confirmed' && !assignedManagerId) {
                    Swal.fire('Error!', 'Please select a manager', 'error');
                    return;
                }

                if (status === 'cancelled' && !cancellationNotes.trim()) {
                    Swal.fire('Error!', 'Please provide cancellation notes', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('status', status);
                formData.append('notes', notes);

                if (status === 'confirmed') {
                    formData.append('estimated_delivery_time', estimatedDeliveryTime);
                    if (assignedManagerId) {
                        formData.append('assigned_manager_id', assignedManagerId);
                    }
                }

                if (status === 'cancelled') {
                    formData.append('notes', cancellationNotes);
                }

                if (status === 'delivered' && deliveryImages.length > 0) {
                    for (let i = 0; i < deliveryImages.length; i++) {
                        formData.append('images[]', deliveryImages[i]);
                    }
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

            // Helper functions
            function loadAvailableStatuses(rentalId, currentStatus) {
                fetch(`/rental-management/${rentalId}/available-statuses`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const statusSelect = document.getElementById('status');
                            statusSelect.innerHTML = '<option value="">Select Status</option>';

                            data.available_statuses.forEach(status => {
                                const option = document.createElement('option');
                                option.value = status;
                                option.textContent = status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                                statusSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Failed to load available statuses:', error);
                    });
            }

            function hideAllStatusFields() {
                document.querySelectorAll('.status-field').forEach(field => {
                    field.classList.remove('show');
                });
            }

            function displayImagePreviews(files) {
                const container = document.getElementById('imagePreviewContainer');
                container.innerHTML = '';

                files.forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imageContainer = document.createElement('div');
                            imageContainer.className = 'image-container';
                            imageContainer.innerHTML = `
                                <img src="${e.target.result}" class="image-preview" alt="Preview">
                                <span class="remove-image" onclick="removeImage(${index})">&times;</span>
                            `;
                            container.appendChild(imageContainer);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            function clearImagePreviews() {
                document.getElementById('imagePreviewContainer').innerHTML = '';
                selectedImages = [];
            }

            // Global function for removing images
            window.removeImage = function(index) {
                selectedImages.splice(index, 1);
                displayImagePreviews(selectedImages);

                // Update the file input
                const dt = new DataTransfer();
                selectedImages.forEach(file => dt.items.add(file));
                document.getElementById('delivery_images').files = dt.files;
            };

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
