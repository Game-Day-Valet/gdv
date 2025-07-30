@extends('layouts.vertical', ['title' => 'Create Coupon'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endsection

@section('content')
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Coupon Management</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Coupon Management</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Create Coupon</h5>
                </div>
                <div class="card-body">
                    <form class="row g-3" action="{{ route('coupon-management.store') }}" method="POST">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label for="code" class="form-label">Coupon Code</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" id="code" value="{{ old('code') }}" placeholder="Enter coupon code" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="type" class="form-label">Discount Type</label>
                            <select name="type" class="form-control @error('type') is-invalid @enderror" id="type" required>
                                <option value="">Select type</option>
                                <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percent" {{ old('type') == 'percent' ? 'selected' : '' }}>Percentage</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="value" class="form-label">Discount Value</label>
                            <input type="number" name="value" class="form-control @error('value') is-invalid @enderror" id="value" value="{{ old('value') }}" placeholder="Enter discount value" step="0.01" min="0" required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="max_uses" class="form-label">Maximum Uses (optional)</label>
                            <input type="number" name="max_uses" class="form-control @error('max_uses') is-invalid @enderror" id="max_uses" value="{{ old('max_uses') }}" placeholder="e.g. 100">
                            @error('max_uses')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="starts_at" class="form-label">Start Date</label>
                            <input type="date" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at" value="{{ old('starts_at') }}">
                            @error('starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="expires_at" class="form-label">Expiry Date</label>
                            <input type="date" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror" id="expires_at" value="{{ old('expires_at') }}">
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-4">
                            <button class="btn btn-primary" type="submit">Create Coupon</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection
