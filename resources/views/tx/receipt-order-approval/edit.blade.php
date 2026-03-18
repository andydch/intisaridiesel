@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<style>
    .select2-selection {
        height: 38px !important;
        font-size: 1rem;
    }
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('tx.' . $folder . '.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <form id="saveData" action="{{ url(ENV('TRANSACTION_FOLDER_NAME') . '/' . $folder.'/'.$receipts->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="order_date" class="col-sm-3 col-form-label">Order Date*</label>
                                <div class="col-sm-9">
                                    <input type="hidden" class="form-control @error('order_date') is-invalid @enderror" maxlength="10" id="order_date" name="order_date" placeholder="Enter Order Date" value="@if (old('order_date')){{ old('order_date') }}@else{{ $receipts->receipt_date }}@endif">
                                    <label for="order_date" class="col-sm-9 col-form-label">{{ $receipts->receipt_date }}</label>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="p_no" class="col-sm-3 col-form-label">Purchase No*</label>
                                <div class="col-sm-9">
                                    <input type="hidden" name="p_no" id="p_no" value="{{ $receipts->po_or_pm_no }}">
                                    <label for="p_no" class="col-sm-9 col-form-label">{{ $receipts->po_or_pm_no }}</label>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="supplier_doc_no" class="col-sm-3 col-form-label">Supplier Doc No*</label>
                                <div class="col-sm-9">
                                    <input type="hidden" class="form-control @error('supplier_doc_no') is-invalid @enderror" maxlength="255" id="supplier_doc_no" name="supplier_doc_no" placeholder="Enter Supplier Doc Number" value="@if (old('supplier_doc_no')){{ old('supplier_doc_no') }}@else{{ $receipts->supplier_doc_no }}@endif">
                                    <label for="supplier_doc_no" class="col-sm-9 col-form-label">{{ $receipts->supplier_doc_no }}</label>
                                </div>
                            </div>
                            <div id="supplier_data" class="row mb-3">
                                <label for="supplier_data" class="col-sm-3 col-form-label">Information</label>
                                <input type="hidden" name="supplier_id" id="supplier_id" value="{{ $receipts->supplier_id }}">
                                <input type="hidden" name="currency_id" id="currency_id" value="{{ $receipts->currency_id }}">
                                <div id="supplier_info" class="col-sm-9">
                                    @if(old('p_no'))
                                        Supplier Info:<br/>{!! $qS->entity_type_name.' '.$qS->supplier_name.'<br/>'.$qS->supplier_type_name !!}
                                        <br/><br/>
                                        Currency: {!! $qS->currency_name !!}
                                        <br/><br/>
                                        Shipping Address: {!! $qS->branch_name.'<br/>'.$qS->branch_address !!}
                                    @else
                                        Supplier Info:<br/>{!! $receipts->supplier->entity_type->title_ind.' '.$receipts->supplier_name.'<br/>'.$receipts->supplier->supplier_type->title_ind !!}
                                        <br/><br/>
                                        Currency: {!! $receipts->currency->title_ind !!}
                                        <br/><br/>
                                        Shipping Address: {!! $qS->branch_name.'<br/>'.$qS->branch_address !!}
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="branch_id" class="col-sm-3 col-form-label">Branch*</label>
                                <div class="col-sm-9">
                                    <input type="hidden" class="form-control @error('branch_id') is-invalid @enderror" maxlength="255" id="branch_id" name="branch_id" placeholder="Enter Supplier Doc Number" value="@if (old('branch_id')){{ old('branch_id') }}@else{{ $receipts->branch_id }}@endif">
                                    <label for="branch_id" class="col-sm-9 col-form-label">{{ $receipts->branch->name }}</label>
                                </div>
                            </div>
                            {{-- <div class="row mb-3">
                                <label for="active" class="col-sm-3 col-form-label">Active</label>
                                <div class="col-sm-9">
                                    @php
                                        $active = $receipts->active;
                                    @endphp
                                    @if (old('active') == 'on')
                                        @php
                                            $active = 'Y';
                                        @endphp
                                    @endif
                                    <input class="form-check-input" type="checkbox" id="active" name="active" aria-label="Active" @if($active=='Y'){{ 'checked' }}@endif>
                                </div>
                            </div> --}}
                            @if (!is_null($receipts->approved_by))
                                <div class="row mb-3">
                                    <label for="branch_id" class="col-sm-3 col-form-label">Approved By</label>
                                    <div class="col-sm-9">
                                        <label for="branch_id" class="col-sm-9 col-form-label">{{ $receipts->approvedBy->name }} at {{ date_format(date_create($receipts->approved_at), 'd M Y H:i:s') }}</label>
                                    </div>
                                </div>
                            @else
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <input type="submit" class="btn btn-light px-5" style="margin-top: 15px;" value="Approve">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-0 text-uppercase">Part Detail</h6>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                                $totRow = $totalRow;
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 5%;text-align:center;">#</th>
                                        <th scope="col" style="width: 30%;text-align:center;">Part Name</th>
                                        <th scope="col" style="width: 5%;text-align:center;">Qty</th>
                                        <th scope="col" style="width: 20%;text-align:center;">Price</th>
                                        <th scope="col" style="width: 20%;text-align:center;">Total</th>
                                        <th scope="col" style="width: 20%;text-align:center;">Total(+VAT)</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $lastIdx = 0;
                                        $i=0;
                                        $totalWoVAT = 0;
                                        $totalWiVAT = 0;
                                    @endphp
                                    @foreach ($receiptParts as $rPart)
                                        <tr id="row{{ $i }}">
                                            <th scope="row" style="text-align:right;">
                                                <label for="" class="col-form-label">{{ $i + 1 }}.</label>
                                            </th>
                                            <td>
                                                <input type="hidden" name="part_id{{ $i }}" id="part_id{{ $i }}" value="{{ $rPart->part_id }}">
                                                <input type="hidden" name="part_name{{ $i }}" id="part_name{{ $i }}" value="{{ $rPart->part->part_name }}">
                                                @php
                                                    $partNumber = $rPart->part->->part_number;
                                                    if(strlen($partNumber)<11){
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                    }else{
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                    }
                                                @endphp
                                                <label for="part_name{{ $i }}" class="col-form-label">{{ $partNumber.' : '.$rPart->part->part_name }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <input type="hidden" class="form-control" id="qty{{ $i }}" name="qty{{ $i }}" value="{{ $rPart->qty }}" maxlength="5" />
                                                <label for="qty{{ $i }}" class="col-form-label">{{ $rPart->qty }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <input type="hidden" class="form-control" id="part_price{{ $i }}" name="part_price{{ $i }}" value="{{ number_format($rPart->part_price,0,'.',',') }}" maxlength="22" />
                                                <label for="part_price{{ $i }}" class="col-form-label">{{ number_format($rPart->part_price,0,'.',',') }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" class="col-form-label">{{ number_format($rPart->qty * $rPart->part_price,0,'.',',') }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                @php
                                                    $total = ($rPart->qty * $rPart->part_price)+(($rPart->qty * $rPart->part_price)*ENV('VAT')/100)
                                                @endphp
                                                <label for="" class="col-form-label">{{ number_format($total,0,'.',',') }}</label>
                                            </td>
                                        </tr>
                                        @php
                                            $totalWoVAT += ($rPart->qty * $rPart->part_price);
                                            $totalWiVAT += $total;
                                            $i+=1;
                                        @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th scope="col" colspan="4" style="text-align: right;">
                                            <label for="" class="col-form-label">Total</label>
                                        </th>
                                        <th scope="col" style="text-align: right;">
                                            <label for="" class="col-form-label">{{ number_format($totalWoVAT,0,'.',',') }}</label>
                                        </th>
                                        <th scope="col" style="text-align: right;">
                                            <label for="" class="col-form-label">{{ number_format($totalWiVAT,0,'.',',') }}</label>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                            {{-- <div class="input-group">
                                <input type="button" id="btn-add-row" class="btn btn-light px-5" style="margin-top: 15px;" value="Add Row">
                                <input type="button" id="btn-del-row" class="btn btn-light px-5" style="margin-top: 15px;" value="Remove Row">
                            </div> --}}
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end row-->
</div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}">
</script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#saveData').submit(function(event){
            let msg = 'You will provide approval regarding the documents currently on display. After this the process cannot be undone. Continue?';
            if(!confirm(msg)){
                event.preventDefault();
            }
        });
    });
</script>
@endsection
