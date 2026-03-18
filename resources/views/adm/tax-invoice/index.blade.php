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
        <form name="fp_search" id="fp_search" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/tax-invoice-search') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <div class="card">
                <div class="card-body">
                    <input type="text" class="form-control @error('fp_no_to_search') is-invalid @enderror" name="fp_no_to_search" id="fp_no_to_search" style="display:inline-block;width:200px;">
                    <a id="btn-search" class="btn btn-primary px-5" style="margin-bottom: 5px;">Search</a>
                </div>
            </div>
        </form>
        <form name="fp_del" id="fp_del" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/tax-invoice-cancel') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <input type="hidden" name="allId" id="allId">
            <div class="col-12">
                <a class="btn btn-primary px-5" href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
                <a id="btn-del-row" class="btn btn-danger px-5" style="margin-bottom: 15px;">Cancel</a>
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
                        <table class="table table-striped table-bordered" id="fp-list" style="width:100%">
                            <thead>
                                <tr>
                                    <th>FP No</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                    <th>Cancel</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                    $iDel = 0;
                                @endphp
                                @foreach ($fp_nos as $o)
                                    <tr>
                                        <td>
                                            {{ $o->prefiks_code.' '.$o->fp_no }}
                                            <input type="hidden" name="fp_no{{ $i }}" id="fp_no{{ $i }}" value="{{ $o->fp_no }}">
                                            <input type="hidden" name="fp_id_{{ $i }}" id="fp_id_{{ $i }}" value="{{ $o->id }}">
                                        </td>
                                        @php
                                            $isApplied = \App\Models\Tx_delivery_order::where([
                                                'tax_invoice_id'=>$o->id,
                                                'active'=>'Y',
                                            ])
                                            ->first();
                                        @endphp
                                        <td>
                                            {{-- @if ($o->active=='Y' && !$isApplied)
                                                <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                            @endif --}}
                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                            @if ($o->active=='Y')
                                                &nbsp;|&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">Edit</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($o->active=='Y')
                                                @if ($isApplied)
                                                    {{ 'Applied' }}
                                                @else
                                                    {{ 'Created' }}
                                                @endif
                                            @else
                                                Cancel
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            @if (!$isApplied && $o->active=='Y')
                                                <input type="checkbox" name="del{{ $iDel }}Check" id="del{{ $iDel }}Check">
                                                <input type="hidden" name="delTaxInvoice{{ $iDel }}" id="delTaxInvoice{{ $iDel }}" value="{{ $o->id }}">
                                                @php
                                                    $iDel += 1;
                                                @endphp
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
                                    <th>FP No</th>
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
        $('#fp-list').DataTable({
            "ordering": false,
        });
        $("#btn-del-row").click(function() {
            if(!confirm("FP No will be canceled.\nContinue?")){
                event.preventDefault();
            }else{
                let allId='';
                for(let j=0;j<{{ $iDel }};j++){
                    if ($('#del'+j+'Check').is(":checked")){
                        allId+=','+$('#delTaxInvoice'+j).val();
                    }
                }
                $("#allId").val(allId);
                $("#fp_del").submit();
            }
        });
        $("#btn-search").click(function() {
            if($("#fp_no_to_search").val()!=''){
                $("#fp_search").submit();
            }
        });
    });
</script>
@endsection
