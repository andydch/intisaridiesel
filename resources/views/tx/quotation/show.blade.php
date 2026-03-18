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
        @include('tx.' . $folder . '.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="border p-4 rounded">
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Quotation No</label>
                                <label for="" class="col-sm-9 col-form-label part-id">{{ $quotations->quotation_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="quotation_date" class="col-sm-3 col-form-label">Quotation Date</label>
                                <label for="quotation_date" class="col-sm-9 col-form-label">{{ date_format(date_create($quotations->quotation_date), 'd/m/Y') }}</label>
                            </div>
                            {{-- <div class="row mb-3">
                                <label for="supplier_id" class="col-sm-3 col-form-label">Supplier</label>
                                <div class="col-sm-9">
                                    <select
                                        class="form-select single-select @error('supplier_id') is-invalid @enderror"
                                        id="supplier_id" name="supplier_id">
                                        <option value="#">Choose...</option>
                                        @php
                                        $supplierId = (old('supplier_id')?old('supplier_id'):$quotations->supplier_id);
                                        @endphp
                                        @foreach ($suppliers as $p)
                                        <option @if ($supplierId==$p->id) {{ 'selected' }} @endif
                                            value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}
                            <div id="supplier_data" class="row mb-3">
                                <label for="supplier_data" class="col-sm-3 col-form-label">Supplier</label>
                                <div id="supplier_info" class="col-sm-9">
                                    @isset($supplierPics[0])
                                        {!!
                                        (!is_null($supplierPics[0]->entity_type)?$supplierPics[0]->entity_type->title_ind:'').' '.$supplierPics[0]->name.
                                        '<br />Address: '.$supplierPics[0]->office_address.
                                        ($supplierPics[0]->subdistrict->sub_district_name=='Other'?'':
                                        ', '.ucwords(strtolower($supplierPics[0]->subdistrict->sub_district_name))).
                                        ($supplierPics[0]->district->district_name=='Other'?'':
                                        ', '.$supplierPics[0]->district->district_name).
                                        ($supplierPics[0]->city->city_name=='Other'?'':
                                        '<br />'.($supplierPics[0]->city->city_type=='Luar
                                        Negeri'?'':$supplierPics[0]->city->city_type).' '.
                                        $supplierPics[0]->city->city_name).
                                        ($supplierPics[0]->province->province_name=='Other'?'':
                                        '<br />'.$supplierPics[0]->province->province_name).
                                        '<br />'.$supplierPics[0]->country->country_name.
                                        ($supplierPics[0]->subdistrict->post_code=='000000'?'':
                                        ' '.$supplierPics[0]->subdistrict->post_code)
                                        !!}
                                    @endisset
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Supplier PIC</label>
                                <label for="" class="col-sm-9 col-form-label">
                                    @php
                                        $supplierPic = $quotations->pic_idx;
                                    @endphp
                                    @foreach ($supplierPics as $p)
                                        @if ($supplierPic==1){{ $p->pic1_name }}@endif
                                        @if ($supplierPic==2){{ $p->pic2_name }}@endif
                                    @endforeach
                                </label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Header</label>
                                <label for="" class="col-sm-9 col-form-label">{!! $quotations->header !!}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Footer</label>
                                <label for="" class="col-sm-9 col-form-label">{!! $quotations->footer !!}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Remark</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $quotations->remark }}</label>
                            </div>
                        </div>
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
                                    <th scope="col" style="width: 2%;text-align:center;">#</th>
                                    <th scope="col" style="width: 15%;">Part Number</th>
                                    <th scope="col" style="width: 20%;">Part Name</th>
                                    <th scope="col" style="width: 5%;">Qty</th>
                                    <th scope="col" style="width: 38%;">Description</th>
                                    <th scope="col" style="width: 10%;">Final Cost</th>
                                    <th scope="col" style="width: 10%;">AVG Cost</th>
                                </tr>
                            </thead>
                            <tbody id="new-row">
                                @php
                                    $i=0;
                                @endphp
                                @foreach($quotationParts AS $mp)
                                    <tr id="row{{ $i }}">
                                        <th scope="row" style="text-align:right;">
                                            {{ $i + 1 }}.
                                            <input type="hidden" name="quotation_part_id_{{ $i }}"
                                                id="quotation_part_id_{{ $i }}" value="{{ $mp->id }}">
                                        </th>
                                        <td>
                                            @php
                                                $partNumber = $mp->part->part_number;
                                                if(strlen($partNumber)<11){
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                }else{
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                }
                                            @endphp
                                            <label for="" class="col-form-label">{{ $partNumber }}</label>
                                        </td>
                                        <td><label for="" class="col-form-label">{{ $mp->part->part_name }}</label></td>
                                        <td style="text-align: right;"><label for="" class="col-form-label">{{ $mp->qty }}</label></td>
                                        <td><label for="" class="col-form-label">{!! $mp->description !!}</label></td>
                                        <td style="text-align: right;"><label for="" class="col-form-label">{{ $qCurrency->string_val.number_format($mp->part->final_cost,0,'.',',') }}</label></td>
                                        <td style="text-align: right;"><label for="" class="col-form-label">{{ $qCurrency->string_val.number_format($mp->part->avg_cost,0,'.',',') }}</label></td>
                                    </tr>
                                    @php
                                        $i += 1;
                                    @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="card" style="margin-top: 15px;">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <input type="button" id="back-btn" class="btn btn-secondary px-5" value="Back">
                            </div>
                        </div>
                    </div>
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
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            // history.back();
            location.href = '{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}';
        });
    });
</script>
@endsection
