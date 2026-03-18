@extends('layouts.app')

@section('style')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.menu.breadcrumb')
            <!--end breadcrumb-->
            <h6 class="mb-0 text-uppercase">Menu Access</h6>
            <hr />
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
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Is Verified?</th>
                                    <th>Last Updated At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $u)
                                    <tr>
                                        <td><a href="{{ url(ENV('ADMIN_FOLDER_NAME') . '/menu/' . $u->id . '/edit') }}"
                                                style="text-decoration: underline;">{{ $u->name }}</a>
                                            (ID: {{ $u->id }})
                                        </td>
                                        <td>{{ $u->email }}</td>
                                        <td>
                                            @if (is_null($u->email_verified_at))
                                                {{ 'not verified' }}
                                            @else
                                                {{ 'verified' }}
                                            @endif
                                        </td>
                                        <td>{{ date_format(date_create($u->updated_at), 'd M Y H:i:s') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Is Verified?</th>
                                    <th>Last Updated At</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end page wrapper -->
@endsection

@section('script')
    <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#menuAccess').DataTable();
        });
    </script>
@endsection
