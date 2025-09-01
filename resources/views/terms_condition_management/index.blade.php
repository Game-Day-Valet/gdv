@extends('layouts.vertical', ['title' => 'Terms & Conditions List'])

@section('css')
    @vite(['node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css', 'node_modules/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css', 'node_modules/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css', 'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css', 'node_modules/datatables.net-select-bs5/css/select.bootstrap5.min.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">Terms & Conditions Management</h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Terms & Conditions Management</a></li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Terms & Conditions List</h5>
                    <div class="d-flex align-items-center gap-2">
                        @php $tFirst = $termsConditions->first(); $tColor = $tFirst->color ?? '#ffffff'; @endphp
                        @if($tFirst)
                        <button type="button" class="btn btn-light border d-flex align-items-center gap-2" id="termsColorBtn" title="Change page theme color">
                            <span class="rounded-circle" id="termsColorSwatch" style="width:16px;height:16px;display:inline-block;border:1px solid #ccc;background: {{ $tColor }};"></span>
                            <span class="d-none d-sm-inline">Theme color</span>
                        </button>
                        <input type="color" id="termsColorInput" value="{{ $tColor }}" class="visually-hidden">
                        @endif
                        <a href="{{ route('terms-condition-management.create') }}" class="btn btn-primary" id="createButton">Create</a>
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
                    <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($termsConditions as $terms)
                                <tr>
                                    <td>{{ $terms->title }}</td>
                                    <td>{{ Str::limit(strip_tags($terms->description), 90) }}</td>
                                    <td>
                                        @if((int) $terms->status === 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $terms->created_at ? $terms->created_at->format('d-M-Y') : '-' }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('terms-condition-management.show', $terms->id) }}"
                                                class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('terms-condition-management.edit', $terms->id) }}"
                                                class="btn btn-sm btn-primary">Edit</a>
                                            <form action="{{ route('terms-condition-management.destroy', $terms->id) }}" method="POST"
                                                class="delete-terms-form" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="btn btn-sm btn-danger delete-terms-btn">Delete</button>
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
@endsection

@section('script')
    @vite(['resources/js/pages/datatable.init.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            (function(){
                const btn = document.getElementById('termsColorBtn');
                const input = document.getElementById('termsColorInput');
                const swatch = document.getElementById('termsColorSwatch');
                if(btn && input){
                    btn.addEventListener('click', ()=> input.click());
                    input.addEventListener('input', async function(){
                        const id = {{ $tFirst->id ?? 'null' }};
                        if(!id){ return; }
                        const color = input.value || '#ffffff';
                        if(swatch){ swatch.style.background = color; }
                        try{
                            const resp = await fetch(`{{ url('admin/terms-condition-management') }}/${id}`, { method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:(()=>{ const f=new FormData(); f.append('_method','PUT'); f.append('color', color); f.append('title','{{ $tFirst->title ?? '' }}'); f.append('description','{{ Str::limit(strip_tags($tFirst->description ?? ''), 90) }}'); f.append('status','{{ $tFirst->status ?? 1 }}'); return f; })() });
                            if(!resp.ok){ throw new Error(); }
                        }catch(e){ Swal && Swal.fire('Error', 'Failed to save color', 'error'); }
                    });
                }
            })();
            document.querySelectorAll('.delete-terms-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
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


