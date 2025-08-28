@extends('layouts.vertical', ['title' => 'Item List'])

@section('css')
@vite(['node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css', 'node_modules/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css', 'node_modules/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css', 'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css', 'node_modules/datatables.net-select-bs5/css/select.bootstrap5.min.css'])
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table-container table {
        min-width: 800px;
    }
    
    .table th, .table td {
        white-space: nowrap;
        vertical-align: top;
    }
    
    .table th:nth-child(2), .table td:nth-child(2) {
        white-space: normal;
        max-width: 150px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .description-text {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
        max-height: 2.4em;
    }
</style>
@endsection

@section('content')
<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Item Management</h4>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="javascript: void(0);">Item Management</a></li>
            <li class="breadcrumb-item active">List</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Item List</h5>
                <a href="{{ route('item-management.create') }}" class="btn btn-primary" id="createButton">Create</a>
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
                <div class="table-container">
                    <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap">
                        <thead>
                            <tr>
                                @can('super_admin')
                                <th style="width:32px;"></th>
                                @endcan
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTbody">
                            @foreach ($items as $item)
                            <tr data-id="{{ $item->id }}">
                                @can('super_admin')
                                <td class="drag-handle" style="cursor:move; text-align:center;">⇅</td>
                                @endcan
                                <td>{{ $item->name }}</td>
                                <td>
                                    <div class="description-text" title="{{ $item->description ?? 'No description' }}">
                                        {{ $item->description ?? '-' }}
                                    </div>
                                </td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->stock ?? '-' }}</td>
                                <td>
                                    @if ($item->image_url)
                                        <a href="{{ $item->image_url }}" target="_blank">
                                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" style="max-width: 100px; max-height: 100px;">
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Str::title($item->status->value) }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('item-management.edit', $item->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('item-management.destroy', $item->id) }}" method="POST" class="delete-item-form" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger delete-item-btn">Delete</button>
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
</div>
@endsection

@section('script')
@vite(['resources/js/pages/datatable.init.js'])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-item-btn').forEach(function(btn) {
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

        @can('super_admin')
        const tbody = document.getElementById('itemsTbody');
        const sortable = new Sortable(tbody, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: async function(){
                const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
                const orders = rows.map((row, idx) => ({ id: parseInt(row.getAttribute('data-id')), sort_order: idx }));
                try{
                    await fetch('{{ route('item-management.reorder') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ orders })
                    });
                }catch(e){ Swal.fire('Error', 'Failed to save order', 'error'); }
            }
        });
        @endcan
    });
</script>
@endsection