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
        <form name="form_del" id="form-del" action="{{ url('/del_courier?next_uri='.urlencode($folder)) }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <input type="hidden" name="all_ids" id="all_ids">
            <div class="col-12">
                <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/create') }}" class="btn btn-primary px-5" style="margin-bottom: 15px;">Add New</a>
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
                        <table id="courier" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    {{-- <th style="width: 5%;">Entity Type</th> --}}
                                    <th style="width: 25%;">Courier Name</th>
                                    <th style="width: 35%;">Address</th>
                                    <th style="width: 25%;">PIC</th>
                                    {{-- <th style="width: 10%;">Email</th>
                                    <th style="width: 5%;">Phone</th> --}}
                                    {{-- <th>Active</th>
                                    <th>Last Updated At</th> --}}
                                    <th style="width: 5%;">Action</th>
                                    <th style="width: 5%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($couriers as $s)
                                    <tr>
                                        {{-- <td>{{ $s->entity_type->title_ind }}</td> --}}
                                        <td>
                                            <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $s->name }}">
                                            <input type="hidden" name="courier_id{{ $i }}" id="courier_id{{ $i }}" value="{{ $s->id }}">
                                            {{ $s->name }}
                                        </td>
                                        <td>
                                            {!! $s->office_address.', '.($s->subdistrict->sub_district_name!='Other'?ucwords(strtolower($s->subdistrict->sub_district_name)).',':'').
                                            ($s->district->district_name!='Other'?$s->district->district_name.'<br />':'') .
                                            ($s->city->city_type!='Luar Negeri'?$s->city->city_type.' ':'') .
                                            $s->city->city_name.'<br />'.($s->province->province_name!='Other'?$s->province->province_name.'<br /> ':'') .
                                            ($s->subdistrict->post_code != '000000' ? $s->subdistrict->post_code : '') !!}
                                        </td>
                                        <td>{!! $s->pic1_name.'<br/>'.$s->pic1_phone.'<br/>'.$s->pic1_email !!}</td>
                                        {{-- <td>{{ $s->courier_email }}</td>
                                        <td>{{ $s->phone1 }}</td> --}}
                                        {{-- <td>{{ $s->active }}</td>
                                        <td>{{ date_format(date_create($s->updated_at), 'd M Y H:i:s') }}</td> --}}
                                        <td>
                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($s->slug)) }}" style="text-decoration: underline;">View</a>
                                            @if ($s->active=='Y')
                                                |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($s->slug).'/edit') }}" style="text-decoration: underline;">Edit</a>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $isNotFree = false;
                                                $q = \App\Models\Tx_delivery_order::where('courier_id','=',$s->id)
                                                ->first();
                                                if($q){$isNotFree = true;}

                                                $q = \App\Models\Tx_purchase_retur::where('courier_id','=',$s->id)
                                                ->first();
                                                if($q && !$isNotFree){$isNotFree = true;}

                                                $q = \App\Models\Tx_receipt_order::where('courier_id','=',$s->id)
                                                ->first();
                                                if($q && !$isNotFree){$isNotFree = true;}
                                            @endphp
                                            @if ($s->active=='Y' && !$isNotFree)
                                                <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                            @else
                                                {{-- {{ 'Deleted' }} --}}
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
                                    <th>Courier Name</th>
                                    <th>Address</th>
                                    <th>PIC</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                </tr>
                            </tfoot> --}}
                        </table>
                    </div>
                    {{-- <hr />
                    <div class="input-group" style="margin-top: 15px;">
                        <a download="" href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/courier-export-xlsx') }}"
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
        $('#courier').DataTable();

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
                            aId += $("#courier_id" + i).val()+',';
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
