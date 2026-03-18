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
            <form name="form_del" id="form-del" action="{{ url('/del_brandtypeparam') }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                <input type="hidden" name="all_ids" id="all_ids">
                <div class="col-12">
                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/create') }}" class="btn btn-primary px-5" style="margin-bottom: 15px;">Add</a>
                    <a id="btn-del-row" class="btn btn-danger px-5" style="margin-bottom: 15px;">Delete</a>
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
                                        <th style="width: 45%;">Brand</th>
                                        <th style="width: 45%;">{{ $title }} Name</th>
                                        <th style="width: 5%;">Action</th>
                                        <th style="width: 5%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($brand_types as $g)
                                        <tr>
                                            <td>{{ $g->brand->title_ind }}</td>
                                            <td>
                                                {{ $g->brand_type }}
                                                <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $g->brand_type }}">
                                                <input type="hidden" name="brandtype_id{{ $i }}" id="brandtype_id{{ $i }}" value="{{ $g->id }}">
                                            </td>
                                            <td>
                                                <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.$g->id) }}" style="text-decoration: underline;">View</a>
                                                @if ($g->active=='Y')
                                                    |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.$g->id.'/edit') }}" style="text-decoration: underline;">Edit</a>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($g->active=='Y')
                                                    <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @else
                                                    {{ 'Deleted' }}
                                                    <input type="Hidden" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @endif
                                            </td>
                                        </tr>
                                        @php
                                            $i += 1;
                                        @endphp
                                    @endforeach
                                </tbody>
                                {{-- <tfoot>
                                    <tr>
                                        <th>Brand</th>
                                        <th>{{ $title }} Name</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                    </tr>
                                </tfoot> --}}
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
            $('#global-mst-tbl').DataTable();

            $("#btn-del-row").click(function() {
                let rowNo = '';
                for (i = 0; i < {{ $rowCount }}; i++) {
                    if ($("#delRow" + i).is(':checked')) {
                        rowNo += '- '+$("#title_caption" + i).val()+'\n';
                    }
                }
                if(rowNo!=''){
                    let msg = 'The following {{ $title }} will be deleted.\n'+rowNo+'\nProcess cannot be undone. Continue?';
                    if(!confirm(msg)){
                        event.preventDefault();
                    }else{
                        let aId = '';
                        for (i = 0; i < {{ $rowCount }}; i++) {
                            if ($("#delRow" + i).is(':checked')) {
                                aId += $("#brandtype_id" + i).val()+',';
                            }
                        }
                        if(aId!==''){
                            $("#all_ids").val(aId);
                            $("#form-del").submit();
                        }
                    }
                }
            });
        });
    </script>
@endsection
