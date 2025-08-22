@extends('layouts.vertical', ['title' => 'Booking Settings - Edit'])

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Edit Option</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('booking-settings.update', $option->id) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-control" required>
                        <option value="insurance" {{ $option->type=='insurance'?'selected':'' }}>Insurance</option>
                        <option value="damage_waiver" {{ $option->type=='damage_waiver'?'selected':'' }}>Damage Waiver</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Label</label>
                    <input type="text" name="label" class="form-control" value="{{ $option->label }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" value="{{ $option->price }}" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Description (optional)</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Shown under the option on the booking page to explain terms or coverage.">{{ $option->description }}</textarea>
                    <small class="text-muted">Keep it concise. Example: Covers accidental damage up to $500; does not include loss or theft.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Enabled</label>
                    <select name="enabled" class="form-control">
                        <option value="1" {{ $option->enabled?'selected':'' }}>Enabled</option>
                        <option value="0" {{ !$option->enabled?'selected':'' }}>Disabled</option>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary" type="submit">Save</button>
                <a href="{{ route('booking-settings.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection 