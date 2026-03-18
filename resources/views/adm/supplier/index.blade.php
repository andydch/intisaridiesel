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
        {{-- <form name="form_del" id="form-del" action="{{ url('/del_supplier?next_uri='.urlencode($folder)) }}" method="POST" enctype="application/x-www-form-urlencoded"> --}}
            <input type="hidden" name="all_ids" id="all_ids">
            @csrf
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
                        <table id="supplier" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">Supplier Code</th>
                                    <th style="width: 30%;">Supplier</th>
                                    <th style="width: 30%;">Office Address</th>
                                    <th style="width: 20%;">PIC</th>
                                    <th style="width: 5%;">Action</th>
                                    <th style="width: 5%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($suppliers as $s)
                                    <tr>
                                        {{-- <td>{{ $s->supplier_type->title_ind }}</td>
                                        <td>@if ($s->entity_type){{ $s->entity_type->title_ind }}@endif </td> --}}
                                        <td>{{ strtoupper($s->supplier_code) }}</td>
                                        <td>
                                            <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $s->name }}">
                                            <input type="hidden" name="customer_id{{ $i }}" id="customer_id{{ $i }}" value="{{ $s->id }}">
                                            {{ $s->name }}
                                        </td>
                                        <td>
                                            {!! $s->office_address.', '.
                                            ($s->subdistrict->sub_district_name!='Other'?ucwords(strtolower($s->subdistrict->sub_district_name)).',':'').
                                            ($s->district->district_name!='Other'?$s->district->district_name.'<br />':'').
                                            (($s->city?$s->city->city_type:'-')!='Luar Negeri'?($s->city?$s->city->city_type:'-').' ':'').
                                            ($s->city?$s->city->city_name:'-').'<br />'.($s->province->province_name!='Other'?$s->province->province_name.'<br />':'').
                                            ($s->country->country_name!='Other'?$s->country->country_name.' ':'').($s->subdistrict->post_code != '000000' ? $s->subdistrict->post_code : '') !!}
                                        </td>
                                        <td>{!! $s->pic1_name.'<br/>'.$s->pic1_phone.'<br/>'.$s->pic1_email !!}</td>
                                        <td>
                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($s->slug)) }}" style="text-decoration: underline;">View</a>
                                            @if ($s->active=='Y')
                                                |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($s->slug).'/edit') }}" style="text-decoration: underline;">Edit</a>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $isNotFree = false;
                                                $q = \App\Models\Tx_payment_voucher::where('supplier_id','=',$s->id)
                                                ->first();
                                                if($q){$isNotFree = true;}

                                                $q = \App\Models\Tx_purchase_memo::where('supplier_id','=',$s->id)
                                                ->first();
                                                if($q && !$isNotFree){$isNotFree = true;}

                                                $q = \App\Models\Tx_purchase_order::where('supplier_id','=',$s->id)
                                                ->first();
                                                if($q && !$isNotFree){$isNotFree = true;}

                                                $q = \App\Models\Tx_purchase_quotation::where('supplier_id','=',$s->id)
                                                ->first();
                                                if($q && !$isNotFree){$isNotFree = true;}

                                                $q = \App\Models\Tx_purchase_retur::where('supplier_id','=',$s->id)
                                                ->first();
                                                if($q && !$isNotFree){$isNotFree = true;}

                                                $q = \App\Models\Tx_receipt_order::where('supplier_id','=',$s->id)
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
                                    <th>Supplier Code</th>
                                    <th>Supplier</th>
                                    <th>Office Address</th>
                                    <th>PIC</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                </tr>
                            </tfoot> --}}
                        </table>
                    </div>
                    <hr />
                    @if (Auth::user()->id==1)

                    @endif
                    <div class="input-group" style="margin-top: 15px;">
                        <a download="" href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/supplier-export-xlsx') }}"
                            class="btn btn-primary px-5" style="margin-bottom: 15px;">Export to Excel</a>
                    </div>
                    <form action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$folder.'-import') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group" style="margin-top: 15px;">
                            <input type="file" class="form-control @error('xlsx_file') is-invalid @enderror" id="xlsx_file"
                                name="xlsx_file" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                            <button class="btn btn-primary" type="submit" id="inputGroupFileAddon04">Import from
                                Excel</button>
                            @error('xlsx_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
        {{-- </form> --}}
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#supplier').DataTable({
            "ordering": false,
        });

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
                            aId += $("#customer_id" + i).val()+',';
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
