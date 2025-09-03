@extends('layouts.vertical', ['title' => 'Sport List'])

@section('css')
@vite(['node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css', 'node_modules/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css', 'node_modules/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css', 'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css', 'node_modules/datatables.net-select-bs5/css/select.bootstrap5.min.css'])
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Sport Management</h4>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="javascript: void(0);">Sport Management</a></li>
            <li class="breadcrumb-item active">List</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Sport List</h5>
                <a href="{{ route('sport-management.create') }}" class="btn btn-primary" id="createButton">Create</a>
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
                <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap" style="min-width: 1000px;">
                    <thead>
                        <tr>
                            @can('super_admin')
                            <th style="width:32px;"></th>
                            @endcan
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="sportsTbody">
                        @foreach ($sports as $sport)
                        <tr data-id="{{ $sport->id }}">
                            @can('super_admin')
                            <td class="drag-handle" style="cursor:move; text-align:center;">⇅</td>
                            @endcan
                            <td style="width:70px;">
                                @if($sport->image)
                                    <img src="{{ asset('storage/'.$sport->image) }}" alt="{{ $sport->name }}" style="height:50px;width:50px;object-fit:cover;border-radius:8px;">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $sport->name }}</td>
                            <td>{{ $sport->description ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::title($sport->status->value) }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('sport-management.edit', $sport->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    @can('super_admin')
                                    <form action="{{ route('sport-management.destroy', $sport->id) }}" method="POST" class="delete-sport-form" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger delete-sport-btn">Delete</button>
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
@endsection

@section('script')
@vite(['resources/js/pages/datatable.init.js'])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-sport-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
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
        // Drag reorder (super admin only)
        @can('super_admin')
        const tbody = document.getElementById('sportsTbody');
        if(tbody && typeof Sortable !== 'undefined'){
            const sortable = new Sortable(tbody, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: async function(){
                    // Only send the moved row delta to avoid touching other rows unnecessarily
                    const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
                    const orders = rows.map((row, idx) => ({ id: parseInt(row.getAttribute('data-id')), sort_order: idx }))
                                         .filter(o => !isNaN(o.id));
                    try{
                        await fetch('{{ route('sport-management.reorder') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ orders })
                        });
                    }catch(e){ /* ignore */ }
                }
            });
        }
        @endcan
    });
</script>
@endsection