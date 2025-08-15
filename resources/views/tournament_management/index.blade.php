@extends('layouts.vertical', ['title' => 'Tournament List'])

@section('css')
    @vite(['node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css', 'node_modules/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css', 'node_modules/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css', 'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css', 'node_modules/datatables.net-select-bs5/css/select.bootstrap5.min.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Tournament Management</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Tournament Management</a></li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tournament List</h5>
                    <a href="{{ route('tournament-management.create') }}" class="btn btn-primary" id="createButton">Create</a>
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
                                <th>Image</th>
                                <th>Sport</th>
                                <th>Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tournaments as $tournament)
                                <tr>
                                    <td>
                                        @if($tournament->image)
                                            <img src="{{  asset('storage/'.$tournament->image) }}" alt="{{ $tournament->name }}" class="img-thumbnail" width="50px" height="50px">
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    </td>
                                    <td>{{ $tournament->sport->name ?? '-' }}</td>
                                    <td>{{ $tournament->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($tournament->start_date)->format('d M Y') }}</td>
                                    <td>{{ $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date)->format('d M Y') : '-' }}</td>
                                    <td>{{ $tournament->location }}</td>
                                    <td>{{ \Illuminate\Support\Str::title($tournament->status->value) }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('tournament-management.edit', $tournament->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                            @can('super_admin')
                                            <form action="{{ route('tournament-management.destroy', $tournament->id) }}" method="POST" class="delete-tournament-form" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger delete-tournament-btn">Delete</button>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-tournament-btn').forEach(function(btn) {
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
        });
    </script>
@endsection
