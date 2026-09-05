@extends('layouts.app')

@section('style')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.user-management.breadcrumb')
            <!--end breadcrumb-->
            <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
            <hr />
            <form name="form_del" id="form-del" action="{{ url('/del_supplier?next_uri='.urlencode($folder)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                <div class="col-12">
                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/create') }}" class="btn btn-primary px-5" style="margin-bottom: 15px;">Add New</a>
                    {{-- <a id="btn-del-row" class="btn btn-danger px-5" style="margin-bottom: 15px;">Delete</a> --}}
                </div>
                <div class="card">
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                        @if (session('status-error'))
                            <div class="alert alert-danger">
                                {{ session('status-error') }}
                            </div>
                        @endif
                        <div class="table-responsive">
                            <table id="menuAccess" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Initial</th>
                                        <th>Name</th>
                                        <th>Username/Email</th>
                                        <th>Phone</th>
                                        <th>Section</th>
                                        <th>Branch</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($users as $u)
                                        <tr>
                                            <td>{{ $u->initial }}</td>
                                            <td>{{ $u->name }}</td>
                                            <td>{{ $u->email }}</td>
                                            <td>{{ $u->phone1 }}</td>
                                            <td>{{ $u->section_name }}</td>
                                            <td>{{ $u->branch_name }}</td>
                                            <td>
                                                @if ($u->active=='Y')
                                                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($u->slug)) }}" style="text-decoration: underline;">View</a> |
                                                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($u->slug)).'/edit' }}" style="text-decoration: underline;">Edit</a>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($u->active=='Y')
                                                    <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @else
                                                    {{ 'Not Active' }}
                                                    <input type="Hidden" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @endif
                                            </td>
                                        </tr>
                                        @php
                                            $i += 1;
                                        @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Initial</th>
                                        <th>Name</th>
                                        <th>Username/Email</th>
                                        <th>Phone</th>
                                        <th>Section</th>
                                        <th>Branch</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!--end page wrapper -->
@endsection

@section('script')
    <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#menuAccess').DataTable({
                'ordering':false,
            });
        });
    </script>
@endsection
