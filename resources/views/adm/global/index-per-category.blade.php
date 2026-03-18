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
            @if ((count($globals) >= 2 &&
                    ($uri == 'gender' || $uri == 'supplier-type' || $uri == 'delivery-type' || $uri == 'quantity-type')) ||
                    (count($globals) >= 1 && $uri == 'vat'))
                {{--  --}}
            @else
                <div class="col-12">
                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME') . '/' . $uri . '/create') }}" class="btn btn-light px-5"
                        style="margin-bottom: 15px;">Add New</a>
                </div>
            @endif
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
                                    <th>Title</th>
                                    @if($uri=='brand' || $uri=='currency' || $uri=='gender' || $uri=='entity-type' || $uri=='supplier-type' ||
                                        $uri=='part-type' || $uri=='part-category' || $uri=='weight-type' || $uri=='delivery-type' || $uri=='quantity-type')
                                        <th>Value (String)</th>
                                    @endif
                                    @if($uri=='vat')
                                        <th>Value (Numeric)</th>
                                    @endif
                                    <th>Order No</th>
                                    <th>Active</th>
                                    <th>Last Updated At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($globals as $g)
                                    <tr>
                                        <td>
                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME') . '/' . $uri . '/' . $g->id . '/edit') }}"
                                                style="text-decoration: underline;">{{ $g->title_ind }}</a>
                                        </td>
                                        @if($uri=='brand' || $uri=='currency' || $uri=='gender' || $uri=='entity-type' || $uri=='supplier-type' ||
                                            $uri=='part-type' || $uri=='part-category' || $uri=='weight-type' || $uri=='delivery-type' || $uri=='quantity-type')
                                            <td>
                                                @if (!is_null($g->string_val))
                                                    {{ $g->string_val }}
                                                @endif
                                            </td>
                                        @endif
                                        @if($uri=='vat')
                                            <td>
                                                @if (!is_null($g->numeric_val))
                                                    {{ $g->numeric_val }}
                                                @endif
                                            </td>
                                        @endif
                                        <td>{{ $g->order_no }}</td>
                                        <td>{{ $g->active }}</td>
                                        <td>{{ date_format(date_create($g->updated_at), 'd M Y H:i:s') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Title</th>
                                    @if($uri=='brand' || $uri=='currency' || $uri=='gender' || $uri=='entity-type' || $uri=='supplier-type' ||
                                        $uri=='part-type' || $uri=='part-category' || $uri=='weight-type' || $uri=='delivery-type' || $uri=='quantity-type')
                                        <th>Value (String)</th>
                                    @endif
                                    @if($uri=='vat')
                                        <th>Value (Numeric)</th>
                                    @endif
                                    <th>Order No</th>
                                    <th>Active</th>
                                    <th>Last Updated At</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    {{-- <hr />
                    <div class="input-group" style="margin-top: 15px;">
                        <a download="" href="{{ url(ENV('ADMIN_FOLDER_NAME') . '/mst-global/global-export-xlsx') }}"
                            class="btn btn-light px-5" style="margin-bottom: 15px;">Export to Excel</a>
                    </div>
                    <form action="{{ url(ENV('ADMIN_FOLDER_NAME') . '/mst-global/global-import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="input-group" style="margin-top: 15px;">
                            <input type="file" class="form-control @error('xlsx_file') is-invalid @enderror"
                                id="xlsx_file" name="xlsx_file" aria-describedby="inputGroupFileAddon04"
                                aria-label="Upload">
                            <button class="btn btn-light" type="submit" id="inputGroupFileAddon04">Import from
                                Excel</button>
                            @error('xlsx_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </form> --}}
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
