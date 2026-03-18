@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('adm.' . $folder . '.breadcrumb')
        <!--end breadcrumb-->
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <hr />
        <div class="col-12">
            <a href="{{ url(ENV('ADMIN_FOLDER_NAME') . '/' . $folder . '/create') }}" class="btn btn-light px-5"
                style="margin-bottom: 15px;">Add New</a>
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
                    <table id="district" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Part No</th>
                                <th>Name</th>
                                <th>Part Type</th>
                                <th>Part Category</th>
                                <th>Brand</th>
                                <th>Active</th>
                                <th>Last Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($parts as $b)
                            <tr>
                                <td>{{ $b->part_number }}</td>
                                <td>
                                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME') . '/' . $folder . '/' . $b->id . '/edit') }}"
                                        style="text-decoration: underline;">{{ $b->part_name }}</a>
                                </td>
                                <td>{{ $b->part_type->title_ind }}</td>
                                <td>{{ $b->part_category->title_ind }}</td>
                                <td>{{ $b->brand->title_ind }}</td>
                                <td>{{ $b->active }}</td>
                                <td>{{ date_format(date_create($b->updated_at), 'd M Y H:i:s') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Part No</th>
                                <th>Name</th>
                                <th>Part Type</th>
                                <th>Part Category</th>
                                <th>Brand</th>
                                <th>Active</th>
                                <th>Last Updated At</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <hr />
                <div class="input-group" style="margin-top: 15px;">
                    <a download="" href="{{ url(ENV('ADMIN_FOLDER_NAME') . '/part/part-export-xlsx') }}"
                        class="btn btn-light px-5" style="margin-bottom: 15px;">Export to Excel</a>
                </div>
                <form action="{{ url(ENV('ADMIN_FOLDER_NAME') . '/part/part-import') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="input-group" style="margin-top: 15px;">
                        <input type="file" class="form-control @error('xlsx_file') is-invalid @enderror" id="xlsx_file"
                            name="xlsx_file" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                        <button class="btn btn-light" type="submit" id="inputGroupFileAddon04">Import from
                            Excel</button>
                        @error('xlsx_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
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
            $('#district').DataTable();
        });
</script>
@endsection
