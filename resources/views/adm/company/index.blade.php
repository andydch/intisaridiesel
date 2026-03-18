@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('adm.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <hr />
        <form name="form_del" id="form-del" action="{{ url('/del_company?next_uri='.urlencode($folder)) }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            @if ($companyCount<2)
            <div class="col-12">
                <a class="btn btn-primary px-5" href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
                {{-- <a id="btn-del-row" class="btn btn-danger px-5" style="margin-bottom: 15px;">Delete</a> --}}
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
                        <table class="table table-striped table-bordered" id="company" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th style="width: 5%;">Action</th>
                                    {{-- <th style="width: 5%;">Status</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($companies as $c)
                                    <tr>
                                        <td>
                                            {{ $c->name }}
                                            <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $c->name }}">
                                            <input type="hidden" name="company_id{{ $i }}" id="company_id{{ $i }}" value="{{ $c->id }}">
                                        </td>
                                        <td>
                                            {!! $c->office_address.', ' .
                                            ($c->subdistrict->sub_district_name != 'Other' ?
                                            ucwords(strtolower($c->subdistrict->sub_district_name)) .',' : '') .
                                            ($c->district->district_name != 'Other' ? $c->district->district_name.'<br />' :
                                            '') .
                                            ($c->city->city_type != 'Luar Negeri' ? $c->city->city_type.' ' : '') .
                                            $c->city->city_name .
                                            '<br />' .
                                            ($c->province->province_name != 'Other' ? $c->province->province_name.'<br /> ' :
                                            '') .
                                            ($c->subdistrict->post_code != '000000' ? $c->subdistrict->post_code : '') !!}
                                        </td>
                                        <td>{{ $c->company_email }}</td>
                                        <td>{{ $c->phone1 }}</td>
                                        {{-- <td>{{ $c->active }}</td>
                                        <td>{{ date_format(date_create($c->updated_at), 'd M Y H:i:s') }}</td> --}}
                                        <td>
                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($c->slug)) }}" style="text-decoration: underline;">View</a>
                                            @if ($c->active=='Y')
                                                |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($c->slug).'/edit') }}" style="text-decoration: underline;">Edit</a>
                                            @endif
                                        </td>
                                        {{-- <td>
                                            <input type="Hidden" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                        </td> --}}
                                    </tr>
                                    @php
                                        $i += 1;
                                    @endphp
                                @endforeach
                            </tbody>
                            {{-- <tfoot>
                                <tr>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot> --}}
                        </table>
                    </div>
                    {{-- <hr />
                    @if ($companyCount==0)
                    <div class="input-group" style="margin-top: 15px;">
                        <a download="" href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/company-export-xlsx') }}"
                            class="btn btn-light px-5" style="margin-bottom: 15px;">Export to Excel</a>
                    </div>
                    <form action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$folder.'-import') }}"
                        method="POST" enctype="multipart/form-data">
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
                    @endif --}}
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
        $('#company').DataTable();

        $("#btn-del-row").click(function() {
            let rowNo = '';
            for (i = 0; i < {{ $companyCount }}; i++) {
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
                    for (i = 0; i < {{ $companyCount }}; i++) {
                        if ($("#delRow" + i).is(':checked')) {
                            aId += $("#company_id" + i).val()+',';
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
