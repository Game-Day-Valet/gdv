@extends('layouts.vertical', ['title' => 'Booking Settings'])

@section('content')
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Booking Settings</h4>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
            <li class="breadcrumb-item active">Booking</li>
        </ol>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Insurance & Damage Waiver Options</h5>
        <a href="{{ route('booking-settings.create') }}" class="btn btn-primary">Add Option</a>
    </div>
    <div class="card-body">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width:30px;"></th>
                    <th>Type</th>
                    <th>Label</th>
                    <th>Price</th>
                    <th>Enabled</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="settingsTbody">
                @forelse($options as $opt)
                    <tr data-id="{{ $opt->id }}">
                        <td class="drag-handle" style="cursor:move; text-align:center;">⇅</td>
                        <td>{{ \Illuminate\Support\Str::title(str_replace('_',' ',$opt->type)) }}</td>
                        <td>{{ $opt->label }}</td>
                        <td>{{ number_format($opt->price, 2) }}</td>
                        <td>
                            @if($opt->enabled)
                                <span class="badge bg-success">Enabled</span>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('booking-settings.edit', $opt->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('booking-settings.destroy', $opt->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No options defined</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Rental Booking Email Content</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('booking-settings.save-email-content') }}">
            @csrf
            <div class="mb-3">
                <label for="email_content" class="form-label">Email Paragraph Content</label>
                <textarea name="email_content" id="email_content" class="form-control" rows="5" placeholder="Enter confirmation paragraph..." required>{{ old('email_content', $emailContent) }}</textarea>
                <div class="form-text">This text appears in the booking confirmation email body.</div>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
 document.addEventListener('DOMContentLoaded', function(){
    const tbody = document.getElementById('settingsTbody');
    if(!tbody) return;
    const sortable = new Sortable(tbody, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: async function(){
            const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
            const orders = rows.map((row, idx) => ({ id: parseInt(row.getAttribute('data-id')), sort_order: idx }));
            try{
                await fetch('{{ route('booking-settings.reorder') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ orders })
                });
            }catch(e){ /* ignore */ }
        }
    });
 });
</script>
@endsection 