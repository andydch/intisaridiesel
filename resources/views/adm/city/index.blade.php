@extends('layouts.app')

@section('style')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.city.breadcrumb')
            <!--end breadcrumb-->
            <h6 class="mb-0 text-uppercase">City</h6>
            <hr />
            <form name="form_del" id="form-del" action="{{ url('/del_cityparam?next_uri='.urlencode($uri)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                <input type="hidden" name="all_ids" id="all_ids">
                <div class="col-12">
                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/city/create') }}" class="btn btn-primary px-5" style="margin-bottom: 15px;">Add New</a>
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
                            <table id="city" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">City Name</th>
                                        <th style="width: 15%;">City Type</th>
                                        <th style="width: 25%;">Province</th>
                                        <th style="width: 20%;">Country</th>
                                        <th style="width: 5%;">Action</th>
                                        <th style="width: 5%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($city as $c)
                                        <tr>
                                            <td>
                                                {{ $c->city_name }}&nbsp;(ID: {{ $c->id }})
                                                <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $c->city_name }}">
                                                <input type="hidden" name="city_id{{ $i }}" id="city_id{{ $i }}" value="{{ $c->id }}">
                                            </td>
                                            <td>{{ $c->city_type }}</td>
                                            <td>
                                                @if ($c->province_id == 9999)
                                                    {{ '-' }}
                                                @else
                                                    {{ $c->province->province_name.' (ID: '.$c->province_id.')' }}
                                                @endif
                                            </td>
                                            <td>{{ $c->country->country_name.' (ID: '.$c->country_id.')' }}</td>
                                            {{-- <td>{{ $c->active }}</td>
                                            <td>{{ date_format(date_create($c->updated_at), 'd M Y H:i:s') }}</td> --}}
                                            <td>
                                                <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.$c->id) }}" style="text-decoration: underline;">View</a>
                                                @if ($c->active=='Y')
                                                    |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.$c->id.'/edit') }}" style="text-decoration: underline;">Edit</a>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($c->active=='Y')
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
                                        <th>City Name</th>
                                        <th>City Type</th>
                                        <th>Province</th>
                                        <th>Country</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                    </tr>
                                </tfoot> --}}
                            </table>
                        </div>
                        {{-- <hr />
                        <div class="input-group" style="margin-top: 15px;">
                            <a download="" href="{{ url(ENV('ADMIN_FOLDER_NAME').'/city/city-export-xlsx') }}"
                                class="btn btn-primary px-5" style="margin-bottom: 15px;">Export to Excel</a>
                        </div>
                        <form action="{{ url(ENV('ADMIN_FOLDER_NAME').'/city/city-import') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="input-group" style="margin-top: 15px;">
                                <input type="file" class="form-control @error('xlsx_file') is-invalid @enderror"
                                    id="xlsx_file" name="xlsx_file" aria-describedby="inputGroupFileAddon04"
                                    aria-label="Upload">
                                <button class="btn btn-primary" type="submit" id="inputGroupFileAddon04">Import from
                                    Excel</button>
                                @error('xlsx_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </form> --}}
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
            $('#city').DataTable();

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
                                aId += $("#city_id" + i).val()+',';
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
