@extends('layouts.vertical', ['title' => 'Rental Management'])

@section('css')
    @vite(['node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css', 'node_modules/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css', 'node_modules/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css', 'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css', 'node_modules/datatables.net-select-bs5/css/select.bootstrap5.min.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .update-status-badge {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .update-status-badge:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .update-status-badge:hover::after {
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
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">Rental List</h5>
                        @if(auth()->user()->hasRole('manager'))
                            <small class="text-muted">Showing only rentals assigned to you</small>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        @if(request()->routeIs('rental-management.pending'))
                            <a href="{{ route('rental-management.index') }}" class="btn btn-sm btn-success">Show Paid</a>
                        @else
                            <a href="{{ route('rental-management.pending') }}" class="btn btn-sm btn-warning">Show Pending</a>
                        @endif
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
                    
                    @if(auth()->user()->hasRole('manager'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Manager Access:</strong> You can only view and manage rentals that have been assigned to you by the admin.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <div class="table-responsive" style="overflow-x: auto;">
                        <div class="d-flex justify-content-between mb-2">
                            <div></div>
                            <div>
                                <button id="archiveSelected" class="btn btn-sm btn-warning">Archive Selected</button>
                                <a href="{{ route('rental-archive.index') }}" class="btn btn-sm btn-outline-secondary">View Archive</a>
                            </div>
                        </div>
                        <table id="datatable" class="table table-bordered dt-responsive nowrap" style="min-width: 1400px;">
                            <thead>
                                <tr>
                                    <th style="width:28px;"><input type="checkbox" id="selectAllRows"></th>
                                    @can('super_admin')
                                    <th style="width:32px;"></th>
                                    @endcan
                                      <th>User</th>
                                    <th>Tournament</th>
                                    <th>Team Name /Age Group</th>
                                    <th>Coach</th>
                                    
                                    <th>Total Amount</th>
                                    <th>Tax Rate</th>
                                    <th>Tax Amount</th>
                                    <th>Payment Status</th>
                                    <!-- <th>Booking Days</th> -->
                                    <th>Status</th>
                                    <!-- <th>Estimated Delivery</th> -->
                                    <th>Assigned Manager</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="rentalsTbody">
                                @foreach ($rentals as $rental)
                                    <tr data-id="{{ $rental->id }}">
                                        <td><input type="checkbox" class="rowSelect" value="{{ $rental->id }}"></td>
                                        @can('super_admin')
                                        <td class="drag-handle" style="cursor:move; text-align:center;">⇅</td>
                                        @endcan
                                         <td>
                                            <div>
                                                <strong>{{ $rental->user->name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $rental->user->email ?? 'N/A' }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $rental->tournament->name ?? 'N/A' }}</td>
                                        <td>{{ $rental->team_name_with_age_group }}</td>
                                        <td>{{ $rental->coach_name }}</td>
                                        
                                        <td>
                                            @if($rental->total_amount)
                                                ${{ number_format($rental->total_amount, 2) }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!is_null($rental->tax_rate))
                                                {{ number_format((float)$rental->tax_rate, 2) }}%
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!is_null($rental->tax_amount))
                                                ${{ number_format((float)$rental->tax_amount, 2) }}
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
                                            <span class="{{ $paymentStatusClass }}">
                                                {{ ucfirst($rental->payment_status ?? 'pending') }}
                                            </span>
                                        </td>
                                        <!-- <td>
                                            @if($rental->booking_days)
                                                {{ $rental->booking_days }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td> -->
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

                                                $paymentAllows = in_array($rental->payment_status, ['paid', 'completed']);
                                                $isClickable = $paymentAllows && !in_array($rental->status, ['delivered', 'cancelled']);
                                                $cursorStyle = $isClickable ? 'cursor: pointer;' : 'cursor: not-allowed;';
                                                $title = $paymentAllows
                                                    ? ($isClickable ? 'Click to update rental status' : 'Status cannot be changed')
                                                    : 'Not allowed until payment completed';
                                            @endphp
                                            <span class="{{ $statusClass }} {{ $isClickable ? 'update-status-badge' : 'status-badge-non-clickable' }}"
                                                  style="{{ $cursorStyle }}"
                                                  data-rental-id="{{ $rental->id }}"
                                                  data-current-status="{{ $rental->status ?? 'pending' }}"
                                                  data-clickable="{{ $isClickable ? 'true' : 'false' }}"
                                                  data-payment-status="{{ $rental->payment_status ?? 'pending' }}"
                                                  title="{{ $title }}">
                                                {{ ucfirst(str_replace('_', ' ', $rental->status ?? 'pending')) }}
                                            </span>
                                        </td>
                                        <!-- <td>
                                            @if($rental->estimated_delivery_time)
                                                {{ \Carbon\Carbon::parse($rental->estimated_delivery_time)->format('d M Y H:i') }}
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </td> -->
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
                                                @can('super_admin')
                                                <form method="POST" action="{{ route('rental-management.destroy', $rental->id) }}" onsubmit="return confirmDelete(event)" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                                @endcan
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
                            <!-- Estimated delivery time disabled -->
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

@endsection

@section('script')
    @vite(['resources/js/pages/datatable.init.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        function confirmDelete(e){
            e.preventDefault();
            const form = e.target;
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will permanently delete the rental booking.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
            return false;
        }
        document.addEventListener('DOMContentLoaded', function () {
            // Archive selected
            const selectAll = document.getElementById('selectAllRows');
            const archiveBtn = document.getElementById('archiveSelected');
            selectAll?.addEventListener('change', ()=>{
                document.querySelectorAll('.rowSelect').forEach(cb=>cb.checked = selectAll.checked);
            });
            archiveBtn?.addEventListener('click', async ()=>{
                const ids = Array.from(document.querySelectorAll('.rowSelect:checked')).map(cb=>parseInt(cb.value));
                if(ids.length===0){ return; }
                const ok = await confirmArchive('Are you sure you want to archive selected bookings?');
                if(!ok) return;
                try{
                    const res = await fetch('{{ route('rental-archive.archive') }}', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({ids}) });
                    if(res.ok){ location.reload(); }
                }catch(e){ Swal.fire('Error','Failed to archive','error'); }
            });

            async function confirmArchive(msg){
                return new Promise(resolve=>{
                    const wrap = document.createElement('div');
                    wrap.style.cssText='position:fixed;inset:0;background:rgba(17,24,39,.55);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:10550;';
                    wrap.innerHTML = `
                    <div style="background:#fff;border-radius:16px;max-width:460px;width:92%;padding:22px 20px;border:1px solid #e5e7eb;box-shadow:0 18px 40px rgba(17,24,39,.18);">
                      <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                        <div style="width:40px;height:40px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;color:#b45309;">
                          <i class=\"fas fa-box-archive\"></i>
                        </div>
                        <div style="font-weight:700;font-size:16px;">Archive selected bookings?</div>
                      </div>
                      <div style="color:#6b7280;margin-bottom:14px;">${msg}</div>
                      <div style="display:flex;gap:10px;justify-content:flex-end;">
                        <button id="aCancel" class="btn btn-light" style="border:1px solid #e5e7eb;">Cancel</button>
                        <button id="aOk" class="btn btn-primary" style="background-color:#3b82f6;border-color:#3b82f6;">Archive</button>
                      </div>
                    </div>`;
                    document.body.appendChild(wrap);
                    wrap.querySelector('#aCancel').addEventListener('click', ()=>{ wrap.remove(); resolve(false); });
                    wrap.querySelector('#aOk').addEventListener('click', ()=>{ wrap.remove(); resolve(true); });
                });
            }
            console.log('DOM loaded, initializing rental management...');
            let currentRentalId = null;
            let selectedImages = [];

            // Status Update
            const statusBadges = document.querySelectorAll('.update-status-badge');
            console.log('Found', statusBadges.length, 'status badges');

            statusBadges.forEach(function(badge) {
                console.log('Adding click listener to status badge:', badge);
                badge.addEventListener('click', function() {
                    console.log('Status badge clicked!');
                    console.log('Data attributes:', {
                        rentalId: this.getAttribute('data-rental-id'),
                        currentStatus: this.getAttribute('data-current-status'),
                        clickable: this.getAttribute('data-clickable'),
                        paymentStatus: this.getAttribute('data-payment-status')
                    });

                    // Block if payment is pending
                    const paymentStatus = this.getAttribute('data-payment-status');
                    if (paymentStatus && paymentStatus.toLowerCase() === 'pending') {
                        Swal.fire('Not allowed', 'Not allowed until payment completed', 'warning');
                        return;
                    }

                    // Check if status is clickable
                    if (this.getAttribute('data-clickable') === 'false') {
                        console.log('Status is not clickable, returning');
                        return;
                    }

                    currentRentalId = this.getAttribute('data-rental-id');
                    const currentStatus = this.getAttribute('data-current-status');

                    // Clear form
                    document.getElementById('statusForm').reset();
                    hideAllStatusFields();
                    clearImagePreviews();

                    // Reset required attributes
                    // Estimated delivery disabled
                    document.getElementById('assigned_manager_id').required = false;
                    document.getElementById('cancellation_notes').required = false;

                    // Load available statuses
                    loadAvailableStatuses(currentRentalId, currentStatus);

                    // Use Bootstrap modal properly
                    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                    console.log('Showing status modal for rental:', currentRentalId);
                    modal.show();

                    // Add event listener for when modal is shown
                    document.getElementById('statusModal').addEventListener('shown.bs.modal', function () {
                        console.log('Status modal shown, checking if statuses were loaded');
                        const statusSelect = document.getElementById('status');
                        console.log('Status select options count:', statusSelect.options.length);
                        console.log('Status select innerHTML:', statusSelect.innerHTML);
                    });
                });
            });

            // Status change handler
            document.getElementById('status').addEventListener('change', function() {
                const selectedStatus = this.value;
                console.log('Status changed to:', selectedStatus);

                hideAllStatusFields();

                // Reset required attributes
                // Estimated delivery disabled
                document.getElementById('assigned_manager_id').required = false;
                document.getElementById('cancellation_notes').required = false;

                if (selectedStatus === 'confirmed') {
                    console.log('Showing confirmed fields');
                    document.getElementById('confirmedFields').classList.add('show');
                    document.getElementById('assigned_manager_id').required = true;
                } else if (selectedStatus === 'delivered') {
                    console.log('Showing delivered fields');
                    document.getElementById('deliveredFields').classList.add('show');
                } else if (selectedStatus === 'cancelled') {
                    console.log('Showing cancelled fields');
                    document.getElementById('cancelledFields').classList.add('show');
                    document.getElementById('cancellation_notes').required = true;
                }

                console.log('Status fields after change:', {
                    confirmed: document.getElementById('confirmedFields').classList.contains('show'),
                    delivered: document.getElementById('deliveredFields').classList.contains('show'),
                    cancelled: document.getElementById('cancelledFields').classList.contains('show')
                });
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
                // const estimatedDeliveryTime = null; // disabled
                const assignedManagerId = document.getElementById('assigned_manager_id').value;
                const cancellationNotes = document.getElementById('cancellation_notes').value;
                const deliveryImages = document.getElementById('delivery_images').files;

                if (!status) {
                    Swal.fire('Error!', 'Please select a status', 'error');
                    return;
                }

                // Validate required fields based on status
                // No estimated delivery validation

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

                fetch(`/admin/rental-management/${currentRentalId}/update-status`, {
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

            // Helper functions
            function loadAvailableStatuses(rentalId, currentStatus) {
                console.log('Loading available statuses for rental:', rentalId, 'current status:', currentStatus);

                const url = `/admin/rental-management/${rentalId}/available-statuses`;
                console.log('Fetching from URL:', url);

                fetch(url)
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Available statuses data:', data);

                        if (data.success) {
                            const statusSelect = document.getElementById('status');
                            statusSelect.innerHTML = '<option value="">Select Status</option>';

                            if (data.available_statuses && data.available_statuses.length > 0) {
                                data.available_statuses.forEach(status => {
                                    const option = document.createElement('option');
                                    option.value = status;
                                    option.textContent = status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                    statusSelect.appendChild(option);
                                });
                                console.log('Loaded', data.available_statuses.length, 'status options');
                            } else {
                                console.log('No available statuses returned');
                                statusSelect.innerHTML = '<option value="">No statuses available</option>';
                            }
                        } else {
                            console.error('API returned error:', data.message);
                            const statusSelect = document.getElementById('status');
                            statusSelect.innerHTML = '<option value="">Error loading statuses</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Failed to load available statuses:', error);
                        const statusSelect = document.getElementById('status');
                        statusSelect.innerHTML = '<option value="">Error loading statuses</option>';
                    });
            }

            function hideAllStatusFields() {
                console.log('Hiding all status fields');
                document.querySelectorAll('.status-field').forEach(field => {
                    field.classList.remove('show');
                    console.log('Hidden field:', field.id);
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

            // Drag and drop reorder for rentals
            @can('super_admin')
            const tbody = document.getElementById('rentalsTbody');
            if (tbody && typeof Sortable !== 'undefined') {
                const sortable = new Sortable(tbody, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: async function(){
                        const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
                        const orders = rows.map((row, idx) => ({ id: parseInt(row.getAttribute('data-id')), sort_order: idx }));
                        try{
                            await fetch('{{ route('rental-management.reorder') }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ orders })
                            });
                        }catch(e){ Swal.fire('Error', 'Failed to save order', 'error'); }
                    }
                });
            }
            @endcan

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

                        // Test modal functionality
            console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
            if (typeof bootstrap !== 'undefined') {
                console.log('Bootstrap Modal available:', typeof bootstrap.Modal !== 'undefined');
            }

            // Test if we can find the modal element
            const statusModal = document.getElementById('statusModal');
            console.log('Status modal element found:', !!statusModal);
            if (statusModal) {
                console.log('Status modal classes:', statusModal.className);
            }

            // Test function for manual modal testing
            window.testModal = function() {
                console.log('Testing modal manually...');
                const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                modal.show();

                // Simulate loading statuses
                setTimeout(() => {
                    loadAvailableStatuses(1, 'pending');
                }, 1000);
            };
        });
    </script>
@endsection
