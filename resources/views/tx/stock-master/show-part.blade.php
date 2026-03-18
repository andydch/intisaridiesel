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

    .part-id {
        font-size: large !important;
        font-weight: 700;
    }
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('tx.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri_folder.'/'.urlencode($parts->slug)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="border p-4 rounded">
                                <div class="row mb-3">
                                    @php
                                        $partNumber = $parts->part_number;
                                        if(strlen($partNumber)<11){
                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                        }else{
                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                        }
                                    @endphp
                                    <label for="" class="col-sm-3 col-form-label">Part Number</label>
                                    <label for="" class="col-sm-9 col-form-label part-id">{{ $partNumber }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Part Name</label>
                                    <label for="" class="col-sm-9 col-form-label part-id">{{ $parts->part_name }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Part Type</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $parts->part_type->title_ind }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Part Category</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $parts->part_category->title_ind }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Brand</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $parts->brand->title_ind }}</label>
                                </div>
                                {{-- <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Part Brand (Merk)</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $parts->part_brand }}</label>
                                </div> --}}
                                <div id="brand-type-new" class="row mb-3">
                                    <label for="brand_id" class="col-sm-3 col-form-label">Brand Type</label>
                                    <div class="col-sm-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <input type="hidden" id="totalBrandTypeRow" name="totalBrandTypeRow" value="{{ $totBrandTypeRow }}">
                                                <table class="table table-bordered mb-0">
                                                    <thead>
                                                        <tr style="width: 100%;">
                                                            <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                            <th scope="col" style="width: 94%;">Brand Type</th>
                                                            {{-- <th scope="col" style="width: 3%;">Delete</th> --}}
                                                        </tr>
                                                    </thead>
                                                    <tbody id="brand-type-new-row">
                                                        @if (old('totalBrandTypeRow'))
                                                            {{-- empty --}}
                                                        @else
                                                            @php
                                                                $i = 0;
                                                            @endphp
                                                            @foreach ($qPartBrandTypes as $qB)
                                                                <tr id="row{{ $i }}">
                                                                    <th scope="row" style="text-align:right;">
                                                                        <label id="" for="" class="col-form-label">{{ $i+1 }}.</label>
                                                                        <input type="hidden" name="brand_type_id_row{{ $i }}" id="brand_type_id_row{{ $i }}" value="{{ $qB->id }}">
                                                                    </th>
                                                                    <td>
                                                                        <label id="" for="" class="col-form-label">{{ $qB->brand_type->brand_type }}</label>
                                                                    </td>
                                                                    {{-- <td style="text-align: center;">
                                                                        <input type="checkbox" id="rowCheck{{ $i }}" name="rowCheck{{ $i }}" value="{{ $i }}">
                                                                    </td> --}}
                                                                </tr>
                                                                @php
                                                                    $i += 1;
                                                                @endphp
                                                            @endforeach
                                                        @endif
                                                    </tbody>
                                                </table>
                                                {{-- <div class="input-group">
                                                    <input type="button" id="btn-add-brand-type-row" class="btn btn-light px-5" style="margin-top: 15px;" value="Add Row">
                                                    <input type="button" id="btn-del-brand-type-row" class="btn btn-light px-5" style="margin-top: 15px;" value="Remove Row">
                                                </div> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Qty Type</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ $parts->quantity_type->title_ind }}</label>
                                        </div>
                                        @php
                                            $oh = 0;
                                            $qQty = \App\Models\Tx_qty_part::where([
                                                'part_id' => $parts->id,
                                                'branch_id' => $reqs->br_id,
                                            ])
                                            ->first();
                                            if ($qQty){
                                                $oh = $qQty->qty;
                                            }

                                            $qtySO = \App\Models\Tx_sales_order_part::leftJoin('tx_sales_orders AS txso','tx_sales_order_parts.order_id','=','txso.id')
                                            ->leftJoin('userdetails AS usr','tx_sales_order_parts.created_by','=','usr.user_id')
                                            ->whereNotIn('txso.id', function ($query) {
                                                $query->select('tx_do_parts.sales_order_id')
                                                ->from('tx_delivery_order_parts as tx_do_parts')
                                                ->where('tx_do_parts.active','=','Y');
                                            })
                                            ->whereRaw('txso.sales_order_no NOT LIKE \'%Draft%\'')
                                            ->where([
                                                'tx_sales_order_parts.part_id'=>$parts->id,
                                                'tx_sales_order_parts.active'=>'Y',
                                                'txso.branch_id'=>$reqs->br_id,
                                                'txso.need_approval'=>'N',
                                                'txso.active'=>'Y',
                                            ])
                                            ->sum('tx_sales_order_parts.qty');
                                        @endphp
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">OH</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ $oh }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">SO</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ $qtySO }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Max Stock</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ $parts->max_stock }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Safety Stock</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ $parts->safety_stock }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">AVG Cost</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ number_format($parts->avg_cost,0,'.',',') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Initial Cost</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ number_format($parts->initial_cost,0,'.',',') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Price List</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ number_format($parts->price_list,0,'.',',') }}</label>
                                        </div>
                                        {{-- <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Weight Type</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ !is_null($parts->weight_unit)?$parts->weight_unit->title_ind:'' }}</label>
                                        </div> --}}
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Weight</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ $parts->weight.' '.(!is_null($parts->weight_unit)?$parts->weight_unit->title_ind:'') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Nama Cabang</label>
                                            <label for="" class="col-sm-8 col-form-label">
                                                @isset($reqs)
                                                    @php
                                                        $branch = \App\Models\Mst_branch::where('id','=',$reqs->br_id)
                                                        ->first();
                                                    @endphp
                                                    @if ($branch)
                                                        {{ $branch->name }}
                                                    @endif
                                                @endisset
                                            </label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Final Price</label>
                                            <label for="" class="col-sm-8 col-form-label">
                                                {{-- {{ number_format($parts->final_price,0,'.',',') }} --}}
                                                @isset($reqs)
                                                    @php
                                                        $qSOpart = \App\Models\Tx_sales_order_part::leftJoin('tx_sales_orders as txso','tx_sales_order_parts.order_id','=','txso.id')
                                                        ->leftJoin('userdetails as usr','txso.created_by','=','usr.user_id')
                                                        ->selectRaw('IFNULL(tx_sales_order_parts.price,0) AS last_final_price')
                                                        ->where('tx_sales_order_parts.part_id','=',$parts->id)
                                                        // gunakan kode cabang user ketika cabang SO kosong
                                                        // jika cabang SO ada maka gunakan kode cabang SO
                                                        ->whereRaw('((usr.branch_id='.$reqs->br_id.' AND txso.branch_id IS null) OR txso.branch_id='.$reqs->br_id.')')
                                                        // ---
                                                        ->where('tx_sales_order_parts.active','=','Y')
                                                        // ->where('txso.need_approval','=','N')
                                                        ->where('txso.active','=','Y')
                                                        ->orderBy('txso.created_at','DESC')
                                                        ->first();
                                                    @endphp
                                                    @if ($qSOpart)
                                                        {{ number_format($qSOpart->last_final_price,0,'.',',') }}
                                                    @endif
                                                @endisset
                                            </label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Final Cost</label>
                                            <label for="" class="col-sm-8 col-form-label">
                                                {{-- {{ number_format($parts->final_cost,0,'.',',') }} --}}
                                                @isset($reqs)
                                                    @php
                                                        $purchase_ro_final_cost = 0;
                                                        $purchase_ro_qty_no_partial_final_cost = 0;

                                                        $q1 = \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                                        ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                                        ->selectRaw('IFNULL(tx_receipt_order_parts.final_cost,0) AS purchase_ro_final_cost')
                                                        ->where('tx_receipt_order_parts.part_id','=',$parts->id)
                                                        // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                                                        ->whereRaw('((usr.branch_id='.$reqs->br_id.' AND tx_ro.branch_id IS null) OR tx_ro.branch_id='.$reqs->br_id.')')
                                                        ->where('tx_receipt_order_parts.final_cost','>',0)
                                                        ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                                                        ->where('tx_receipt_order_parts.active','=','Y')
                                                        ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                        ->where('tx_ro.active','=','Y')
                                                        ->orderBy('tx_ro.created_at','DESC')
                                                        ->orderBy('tx_receipt_order_parts.created_at','DESC')
                                                        ->first();

                                                        $q2 = \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                                        ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                                                        ->selectRaw('IFNULL(tx_receipt_order_parts.final_cost,0) AS purchase_ro_qty_no_partial_final_cost')
                                                        ->where('tx_receipt_order_parts.part_id','=',$parts->id)
                                                        // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                                                        ->whereRaw('((usr.branch_id='.$reqs->br_id.' AND tx_ro.branch_id IS null) OR tx_ro.branch_id='.$reqs->br_id.')')
                                                        ->where('tx_receipt_order_parts.final_cost','>',0)
                                                        ->where('tx_receipt_order_parts.is_partial_received','=','N')
                                                        ->where('tx_receipt_order_parts.active','=','Y')
                                                        ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                        ->where('tx_ro.active','=','Y')
                                                        ->orderBy('tx_ro.created_at','DESC')
                                                        ->orderBy('tx_receipt_order_parts.created_at','DESC')
                                                        ->first();
                                                    @endphp
                                                    @if ($q1)
                                                        @php
                                                            $purchase_ro_final_cost = $q1->purchase_ro_final_cost;
                                                        @endphp
                                                    @endif
                                                    @if ($q2)
                                                        @php
                                                            $purchase_ro_qty_no_partial_final_cost = $q2->purchase_ro_qty_no_partial_final_cost;
                                                        @endphp
                                                    @endif
                                                    @if ($purchase_ro_final_cost>0)
                                                        {{ number_format($purchase_ro_final_cost,0,'.',',') }}
                                                    @else
                                                        {{ number_format($purchase_ro_qty_no_partial_final_cost,0,'.',',') }}
                                                    @endif
                                                @endisset
                                            </label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Total Cost</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ number_format($parts->total_cost,0,'.',',') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Total Price</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ number_format($parts->total_sales,0,'.',',') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">FOB Currency</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ (!is_null($parts->fobCurr)?$parts->fobCurr->string_val:'') }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-4 col-form-label">Final FOB</label>
                                            <label for="" class="col-sm-8 col-form-label">{{ number_format($parts->final_fob,0,'.',',') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-0 text-uppercase">Part Subtitution</h6>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <input type="hidden" id="totalPartSubsRow" name="totalPartSubsRow" value="{{ $totPartSubsRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 25%;">Part Number</th>
                                        <th scope="col" style="width: 25%;">Part Name</th>
                                        <th scope="col" style="width: 25%;">Brand</th>
                                        <th scope="col" style="width: 20%;">Part Type</th>
                                        {{-- <th scope="col" style="width: 3%;">Delete</th> --}}
                                    </tr>
                                </thead>
                                <tbody id="part-subs-row">
                                    @if (old('totalPartSubsRow'))
                                        {{-- empty --}}
                                    @else
                                        @php
                                            $i = 0;
                                        @endphp
                                        @foreach ($OtherPart as $Op)
                                            <tr id="rowPartSubs{{ $i }}">
                                                <th scope="row" style="text-align:right;">
                                                    <label id="" for="" class="col-form-label">{{ $i+1 }}.</label>
                                                    <input type="hidden" name="part_subs_id_row{{ $i }}" id="part_subs_id_row{{ $i }}" value="{{ $Op->id }}">
                                                </th>
                                                @php
                                                    $partInfo = \App\Models\Mst_part::where([
                                                        'id' => $Op->part_other_id,
                                                    ])
                                                    ->first();
                                                @endphp
                                                <td>
                                                    <label id="" for="" class="col-form-label">{{ $Op->part_other->part_number }}</label>
                                                </td>
                                                <td>
                                                    <label id="" for="" class="col-form-label">{{ $Op->part_other->part_name }}</label>
                                                </td>
                                                <td>
                                                    <label id="brand-name-{{ $i }}" for="" class="col-form-label">{{ $partInfo->brand->title_ind }}</label>
                                                </td>
                                                <td>
                                                    <label id="part-type-name-{{ $i }}" for="" class="col-form-label">{{ $partInfo->part_type->title_ind }}</label>
                                                </td>
                                                {{-- <td style="text-align: center;">
                                                    <input type="checkbox" id="rowCheckPartSubs{{ $i }}" name="rowCheckPartSubs{{ $i }}" value="{{ $i }}">
                                                </td> --}}
                                            </tr>
                                            @php
                                                $i += 1;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                            {{-- <div class="input-group">
                                <input type="button" id="btn-add-part-subs-row" class="btn btn-light px-5" style="margin-top: 15px;" value="Add Row">
                                <input type="button" id="btn-del-part-subs-row" class="btn btn-light px-5" style="margin-top: 15px;" value="Remove Row">
                            </div> --}}
                        </div>
                    </div>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    {{-- <input type="submit" id="save" class="btn btn-light px-5" value="Save"> --}}
                                    <input type="button" id="back-btn" class="btn btn-secondary px-5" value="Back">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!--end row-->
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            history.back();
        });
    });
</script>
@endsection
