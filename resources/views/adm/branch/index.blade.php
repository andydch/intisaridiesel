@extends('layouts.app')

@section('style')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.branch.breadcrumb')
            <!--end breadcrumb-->
            <h6 class="mb-0 text-uppercase">Branch</h6>
            <hr />
            <form name="form_del" id="form-del" action="{{ url('/del_branch?next_uri='.urlencode($uri)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                <input type="hidden" name="all_ids" id="all_ids">
                @csrf
                <div class="col-12">
                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/branch/create') }}" class="btn btn-primary px-5" style="margin-bottom: 15px;">Add New</a>
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
                            <table id="district" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Initial</th>
                                        <th style="width: 30%;">Branch Name</th>
                                        <th style="width: 20%;">Province</th>
                                        {{-- <th style="width: 25%;">Address</th> --}}
                                        <th style="width: 20%;">Phone</th>
                                        {{-- <th style="width: 5%;">Phone 2</th> --}}
                                        <th style="width: 5%;">Action</th>
                                        <th style="width: 5%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($branches as $b)
                                        <tr>
                                            <td>{{ $b->initial }}</td>
                                            <td>
                                                <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $b->name }}">
                                                <input type="hidden" name="branch_id{{ $i }}" id="branch_id{{ $i }}" value="{{ $b->id }}">
                                                {{ $b->name }}
                                            </td>
                                            <td>
                                                {{ $b->province->province_name }}
                                            </td>
                                            {{-- <td>
                                                {!! $b->address.', '.ucwords(strtolower($b->subdistrict->sub_district_name)).', '.
                                                $b->district->district_name.'<br/>'.$b->city->city_type.' '.$b->city->city_name.'<br/>'.
                                                $b->province->province_name.' '.$b->subdistrict->post_code !!}
                                            </td> --}}
                                            <td>{{ $b->phone1.(!is_null($b->phone2)?', '.$b->phone2:'') }}</td>
                                            {{-- <td>{{ $b->phone2 }}</td> --}}
                                            {{-- <td>{{ $b->active }}</td>
                                            <td>{{ date_format(date_create($b->updated_at), 'd M Y H:i:s') }}</td> --}}
                                            <td>
                                                <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.urlencode($b->slug)) }}" style="text-decoration: underline;">View</a>
                                                @if ($b->active=='Y')
                                                    |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.urlencode($b->slug).'/edit') }}" style="text-decoration: underline;">Edit</a>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($b->active=='Y')
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
                                        <th>Initial</th>
                                        <th>Branch Name</th>
                                        <th>Province</th>
                                        <th>Phone</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                    </tr>
                                </tfoot> --}}
                            </table>
                        </div>
                        {{-- <hr />
                        <div class="input-group" style="margin-top: 15px;">
                            <a download="" href="{{ url(ENV('ADMIN_FOLDER_NAME').'/branch/branch-export-xlsx') }}"
                                class="btn btn-light px-5" style="margin-bottom: 15px;">Export to Excel</a>
                        </div>
                        <form action="{{ url(ENV('ADMIN_FOLDER_NAME').'/branch/branch-import') }}" method="POST"
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
            $('#district').DataTable();

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
                                aId += $("#branch_id" + i).val()+',';
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
