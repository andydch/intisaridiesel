@extends('layouts.app')

@section('style')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.global.breadcrumb-per-category')
            <!--end breadcrumb-->
            <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
            <hr />
            <div class="col-12">
                @if ($globalsCount==0)
                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/create') }}" class="btn btn-primary px-5" style="margin-bottom: 15px;">Add</a>
                @endif
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
                        <table id="global-mst-tbl" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Memo Limit</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($globals as $g)
                                    <tr>
                                        <td>{{ number_format($g->numeric_val,0,'.',',') }}</td>
                                        <td>
                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.urlencode($g->slug)) }}" style="text-decoration: underline;">View</a> |
                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.urlencode($g->slug).'/edit') }}" style="text-decoration: underline;">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            {{-- <tfoot>
                                <tr>
                                    <th>Memo Limit</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </tfoot> --}}
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
            $('#global-mst-tbl').DataTable();
        });
    </script>
@endsection
