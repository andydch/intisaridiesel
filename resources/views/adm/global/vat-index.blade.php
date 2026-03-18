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
                                    <th>VAT Title</th>
                                    <th>VAT Value</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($globals as $g)
                                    <tr>
                                        <td>
                                            {{ $g->title_ind }}
                                            <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $g->title_ind }}">
                                            <input type="hidden" name="vat_id{{ $i }}" id="vat_id{{ $i }}" value="{{ $g->id }}">
                                        </td>
                                        <td>{{ $g->numeric_val }}</td>
                                        <td>
                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.urlencode($g->slug)) }}" style="text-decoration: underline;">View</a> |
                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.urlencode($g->slug).'/edit') }}" style="text-decoration: underline;">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            {{-- <tfoot>
                                <tr>
                                    <th>VAT Title</th>
                                    <th>VAT Value</th>
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
