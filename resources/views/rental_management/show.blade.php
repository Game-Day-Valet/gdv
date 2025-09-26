@extends('layouts.vertical', ['title' => 'Rental Details'])

@section('css')
    <style>
        .status-badge {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }
        .info-card {
            border-left: 4px solid #007bff;
        }
        .payment-card {
            border-left: 4px solid #28a745;
        }
        .delivery-card {
            border-left: 4px solid #ffc107;
        }

        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-marker {
            position: absolute;
            left: -22px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #007bff;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #007bff;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #007bff;
        }

        .timeline-content img {
            transition: transform 0.2s ease;
        }

        .timeline-content img:hover {
            transform: scale(1.05);
        }

        .status-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .status-image-item {
            position: relative;
            text-align: center;
        }

        .status-image-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .status-image-item img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
            <h4 class="fs-18 fw-semibold m-0">Rental Details</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="{{ route('rental-management.index') }}">Rental Management</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Rental #{{ $rental->id }}</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('rental-management.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card info-card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>User:</strong></div>
                                        <div class="col-8">{{ $rental->user->name ?? $rental->full_name }} ({{ $rental->user->email ?? $rental->email }})</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Tournament:</strong></div>
                                        <div class="col-8">{{ $rental->tournament->name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Team Name/Age Group:</strong></div>
                                        <div class="col-8">{{ $rental->team_name_with_age_group }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Rental Email:</strong></div>
                                        <div class="col-8">{{ $rental->email ?? 'N/A' }}</div>
                                    </div>
                                    <!-- <div class="row mb-2">
                                        <div class="col-4"><strong>Booking Days:</strong></div>
                                        <div class="col-8">{{ $rental->booking_days ?? 'N/A' }}</div>
                                    </div> -->
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Coach:</strong></div>
                                        <div class="col-8">{{ $rental->coach_name }}</div>
                                    </div>
                                    
                                    <div class="row mb-2">
                                        
                                    </div>
                                    <div class="row mb-2">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="col-md-6">
                            <div class="card payment-card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Payment Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Total Amount:</strong></div>
                                        <div class="col-8">
                                            @if($rental->total_amount)
                                                ${{ number_format($rental->total_amount, 2) }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Tax Rate:</strong></div>
                                        <div class="col-8">
                                            @if(!is_null($rental->tax_rate))
                                                {{ number_format((float)$rental->tax_rate, 2) }}%
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Tax Amount:</strong></div>
                                        <div class="col-8">
                                            @if(!is_null($rental->tax_amount))
                                                ${{ number_format((float)$rental->tax_amount, 2) }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Payment Method:</strong></div>
                                        <div class="col-8">{{ ucfirst($rental->payment_method ?? 'N/A') }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Payment Status:</strong></div>
                                        <div class="col-8">
                                            @php
                                                $paymentStatusClass = match($rental->payment_status) {
                                                    'completed' => 'badge bg-success',
                                                    'pending' => 'badge bg-warning',
                                                    default => 'badge bg-secondary'
                                                };
                                            @endphp
                                            <span class="status-badge {{ $paymentStatusClass }}">
                                                {{ ucfirst($rental->payment_status ?? 'pending') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Promo Code:</strong></div>
                                        <div class="col-8">{{ $rental->promo_code ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Insurance:</strong></div>
                                        <div class="col-8">
                                            @if(!is_null($rental->insurance_option) && (float) $rental->insurance_option > 0)
                                                ${{ number_format((float) $rental->insurance_option, 2) }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Damage Waiver:</strong></div>
                                        <div class="col-8">
                                            @if(!is_null($rental->damage_waiver) && (float) $rental->damage_waiver > 0)
                                                ${{ number_format((float) $rental->damage_waiver, 2) }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Delivery Information -->
                        <div class="col-md-6">
                            <div class="card delivery-card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Delivery Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Status:</strong></div>
                                        <div class="col-8">
                                            @php
                                                $statusClass = match($rental->status) {
                                                    'delivered' => 'badge bg-success',
                                                    'confirmed' => 'badge bg-info',
                                                    'out_for_delivery' => 'badge bg-primary',
                                                    'cancelled' => 'badge bg-danger',
                                                    'pending' => 'badge bg-warning',
                                                    default => 'badge bg-secondary'
                                                };
                                            @endphp
                                            <span class="status-badge {{ $statusClass }}">
                                                {{ ucfirst(str_replace('_', ' ', $rental->status ?? 'pending')) }}
                                            </span>
                                        </div>
                                    </div>
                                    <!-- <div class="row mb-2">
                                        <div class="col-4"><strong>Estimated Delivery:</strong></div>
                                        <div class="col-8">
                                            @if($rental->estimated_delivery_time)
                                                {{ \Carbon\Carbon::parse($rental->estimated_delivery_time)->format('d M Y H:i') }}
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </div>
                                    </div> -->
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Assigned Manager:</strong></div>
                                        <div class="col-8">
                                            @if($rental->assignedManager)
                                                {{ $rental->assignedManager->name }} ({{ $rental->assignedManager->email }})
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Delivery Assigned To:</strong></div>
                                        <div class="col-8">{{ $rental->delivery_assigned_to ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Created:</strong></div>
                                        <div class="col-8">{{ \Carbon\Carbon::parse($rental->created_at)->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items and Bundles -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Items & Bundles</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Summary -->
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div class="text-center p-2 bg-light rounded">
                                                <h6 class="mb-1">{{ $rental->items ? count($rental->items) : 0 }}</h6>
                                                <small class="text-muted">Items</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-2 bg-light rounded">
                                                <h6 class="mb-1">{{ $rental->bundles ? count($rental->bundles) : 0 }}</h6>
                                                <small class="text-muted">Bundles</small>
                                            </div>
                                        </div>
                                    </div>

                                    @if($rental->items && count($rental->items) > 0)
                                        <h6>Items:</h6>
                                        <ul class="list-unstyled">
                                            @foreach($rental->items as $rentalItem)
                                                @php
                                                    $item = $rental->items_with_data[$rentalItem['item_id']] ?? null;
                                                @endphp
                                                @if($item)
                                                    <li class="mb-2 p-2 border rounded">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong>{{ $item->name }}</strong><br>
                                                                <small class="text-muted">Price: ${{ number_format($item->price, 2) }}</small>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="badge bg-primary">Qty: {{ $rentalItem['quantity'] ?? 1 }}</span>
                                                                <br>
                                                                <small class="text-muted">Total: ${{ number_format($item->price * ($rentalItem['quantity'] ?? 1), 2) }}</small>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @else
                                                    <li class="mb-1 text-muted">
                                                        <strong>Item ID:</strong> {{ $rentalItem['item_id'] ?? 'N/A' }}
                                                        <strong>Quantity:</strong> {{ $rentalItem['quantity'] ?? 'N/A' }}
                                                        <small>(Item not found)</small>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted">No items selected</p>
                                    @endif

                                    @if($rental->bundles && count($rental->bundles) > 0)
                                        <h6 class="mt-3">Bundles:</h6>
                                        <ul class="list-unstyled">
                                            @foreach($rental->bundles as $b)
                                                @php
                                                    $bundleId = is_array($b) && isset($b['bundle_id']) ? (int) $b['bundle_id'] : (int) $b;
                                                    $qty = is_array($b) && isset($b['quantity']) ? (int) $b['quantity'] : 1;
                                                    $bundle = optional($rental->bundles_with_data) ? $rental->bundles_with_data->get($bundleId) : null;
                                                @endphp
                                                @if($bundle)
                                                    <li class="mb-2 p-2 border rounded">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong>{{ $bundle->name }}</strong><br>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="badge bg-primary">Qty: {{ $qty }}</span>
                                                                <br>
                                                                <span class="badge bg-success">${{ number_format($bundle->price * max(1, $qty), 2) }}</span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @else
                                                    <li class="mb-1 text-muted">
                                                        Bundle ID: {{ $bundleId }} <small>(Bundle not found)</small>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted">No bundles selected</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Log -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Status History</h6>
                                </div>
                                <div class="card-body">
                                    @if($rental->statusLogs && count($rental->statusLogs) > 0)
                                        <div class="timeline">
                                            @foreach($rental->statusLogs as $log)
                                                <div class="timeline-item">
                                                    <div class="timeline-marker"></div>
                                                                                                    <div class="timeline-content">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">{{ ucfirst(str_replace('_', ' ', $log->status)) }}</h6>
                                                            @if($log->notes)
                                                                <p class="text-muted mb-1">{{ $log->notes }}</p>
                                                            @endif
                                                            @if($log->image_paths && count($log->image_paths) > 0)
                                                                <div class="mb-2">
                                                                    <h6 class="mb-2">Status Images:</h6>
                                                                    <div class="status-images-grid">
                                                                        @foreach($log->image_paths as $imagePath)
                                                                            <div class="status-image-item">
                                                                                <img src="{{ asset('storage/' . $imagePath) }}"
                                                                                     alt="Status Image"
                                                                                     style="cursor: pointer;"
                                                                                     onclick="openImageModal('{{ asset('storage/' . $imagePath) }}', '{{ ucfirst(str_replace('_', ' ', $log->status)) }}')">
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            <small class="text-muted">
                                                                Updated by: {{ $log->updatedBy->name ?? 'System' }}
                                                            </small>
                                                        </div>
                                                        <div class="text-end">
                                                            <small class="text-muted">
                                                                {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">No status history available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Instructions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Special Instructions:</h6>
                                            <p>{{ $rental->instructions ?? 'No special instructions' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Return Instructions:</h6>
                                            <p>{{ $rental->return_instruction ?? 'No return instructions' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Photos and Reviews -->
                    @if($rental->photos && count($rental->photos) > 0)
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Photos</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($rental->photos as $photo)
                                                <div class="col-md-3 mb-2">
                                                    <img src="{{ asset('storage/' . $photo->photo_path) }}"
                                                         alt="Rental Photo" class="img-thumbnail" style="max-width: 200px;">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($rental->reviews && count($rental->reviews) > 0)
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Reviews</h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach($rental->reviews as $review)
                                            <div class="border-bottom pb-2 mb-2">
                                                <div class="d-flex justify-content-between">
                                                    <strong>Rating: {{ $review->rating }}/5</strong>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($review->created_at)->format('d M Y') }}</small>
                                                </div>
                                                <p class="mb-1">{{ $review->comment }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Status Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Status Image" class="img-fluid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
function openImageModal(imageUrl, status) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('imageModalLabel').textContent = status + ' - Image';
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
</script>
@endsection
