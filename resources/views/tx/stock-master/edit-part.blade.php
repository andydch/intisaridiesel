@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
{{-- <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"> --}}

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
            @if (session('status-error'))
                <div class="alert alert-danger">
                    {{ session('status-error') }}
                </div>
            @endif
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri_folder.'/'.urlencode($parts->slug)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    @if($errors->any())
                    Error:
                    {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                    @endif
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
                                    {{-- <div class="col-sm-7">
                                        <input type="text" class="form-control @error('part_number') is-invalid @enderror"
                                            maxlength="255" id="part_number" name="part_number" placeholder="Enter Part Number"
                                            value="@if(old('part_number')){{ old('part_number') }}@else{{ $parts->part_number }}@endif">
                                        @error('part_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div> --}}
                                </div>
                                <div class="row mb-3">
                                    <label for="partName" class="col-sm-3 col-form-label">Part Name*</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control @error('partName') is-invalid @enderror"
                                            maxlength="255" id="partName" name="partName" placeholder="Enter Part Name"
                                            value="@if(old('partName')){{ old('partName') }}@else{{ $parts->part_name }}@endif">
                                        @error('partName')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="partType_id" class="col-sm-3 col-form-label">Part Type*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('partType_id') is-invalid @enderror" id="partType_id" name="partType_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $partType_id = (old('partType_id')?old('partType_id'):$parts->part_type_id);
                                            @endphp
                                            @foreach ($partType as $p)
                                                <option @if ($partType_id==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->title_ind }}</option>
                                            @endforeach
                                        </select>
                                        @error('partType_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="partCategory_id" class="col-sm-3 col-form-label">Part Category*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('partCategory_id') is-invalid @enderror" id="partCategory_id" name="partCategory_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $partCategory_id = (old('partCategory_id')?old('partCategory_id'):$parts->part_category_id);
                                            @endphp
                                            @foreach ($partCategory as $p)
                                                <option @if ($partCategory_id==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->title_ind }}</option>
                                            @endforeach
                                        </select>
                                        @error('partCategory_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="brand_id" class="col-sm-3 col-form-label">Brand*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $brand_id = (old('brand_id')?old('brand_id'):$parts->brand_id);
                                            @endphp
                                            @foreach ($brand as $p)
                                                <option @if ($brand_id==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->title_ind }}</option>
                                            @endforeach
                                        </select>
                                        @error('brand_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
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
                                                            <th scope="col" style="width: 3%;">Delete</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="brand-type-new-row">
                                                        @if (old('totalBrandTypeRow'))
                                                            @for ($i=0;$i<old('totalBrandTypeRow');$i++)
                                                                @if (old('brand_type_id_'.$i))
                                                                    <tr id="row{{ $i }}">
                                                                        <th scope="row" style="text-align:right;">
                                                                            <label id="" for="" class="col-form-label">{{ $i+1 }}.</label>
                                                                            <input type="hidden" name="brand_type_id_row{{ $i }}" id="brand_type_id_row{{ $i }}"
                                                                                value="@if (old('brand_type_id')){{ old('brand_type_id') }}@else{{ 0 }}@endif">
                                                                        </th>
                                                                        <td>
                                                                            <select class="form-select single-select @error('brand_type_id_'.$i) is-invalid @enderror"
                                                                                id="brand_type_id_{{ $i }}" name="brand_type_id_{{ $i }}">
                                                                                <option value="#">Choose...</option>
                                                                                @php
                                                                                    $brand_type_id = old('brand_type_id_'.$i) ? old('brand_type_id_'.$i) : 0;
                                                                                @endphp
                                                                                @foreach ($queryBrandType as $pr)
                                                                                    <option @if ($brand_type_id==$pr->id){{ 'selected' }}@endif
                                                                                        value="{{ $pr->id }}">{{ $pr->brand_type }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            @error('brand_type_id_'.$i)
                                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                            @enderror
                                                                        </td>
                                                                        <td style="text-align: center;">
                                                                            <input type="checkbox" id="rowCheck{{ $i }}" name="rowCheck{{ $i }}" value="{{ $i }}">
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endfor
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
                                                                        <select class="form-select single-select @error('brand_type_id_'.$i) is-invalid @enderror"
                                                                            id="brand_type_id_{{ $i }}" name="brand_type_id_{{ $i }}">
                                                                            <option value="#">Choose...</option>
                                                                            @php
                                                                                $brand_type_id = $qB->brand_type_id;
                                                                            @endphp
                                                                            @foreach ($queryBrandType as $pr)
                                                                                <option @if ($brand_type_id==$pr->id){{ 'selected' }}@endif
                                                                                    value="{{ $pr->id }}">{{ $pr->brand_type }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('brand_type_id_'.$i)
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </td>
                                                                    <td style="text-align: center;">
                                                                        <input type="checkbox" id="rowCheck{{ $i }}" name="rowCheck{{ $i }}" value="{{ $i }}">
                                                                    </td>
                                                                </tr>
                                                                @php
                                                                    $i += 1;
                                                                @endphp
                                                            @endforeach
                                                        @endif
                                                    </tbody>
                                                </table>
                                                <div class="input-group">
                                                    <input type="button" id="btn-add-brand-type-row" class="btn btn-primary px-5" style="margin-top: 15px;" value="Add Row">
                                                    <input type="button" id="btn-del-brand-type-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="weight" class="col-sm-3 col-form-label">Weight</label>
                                    <div class="col-sm-3">
                                        <input type="text" class="form-control @error('weight') is-invalid @enderror" style="text-align: right;"
                                            maxlength="12" id="weight" name="weight" placeholder="Enter Weight"
                                            value="@if (old('weight')){{ old('weight') }}@else{{ $parts->weight }}@endif">
                                        @error('weight')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <label for="weight_id" class="col-sm-3 col-form-label">Weight Type</label>
                                    <div class="col-sm-3">
                                        <select class="form-select single-select @error('weight_id') is-invalid @enderror"
                                            id="weight_id" name="weight_id">
                                            <option value="0">Choose...</option>
                                            @php
                                                $weight_id = (old('weight_id')?old('weight_id'):$parts->weight_id);
                                            @endphp
                                            @foreach ($weightType as $p)
                                            <option @if ($weight_id==$p->id) {{ 'selected' }} @endif
                                                value="{{ $p->id }}">{{ $p->title_ind }}</option>
                                            @endforeach
                                        </select>
                                        @error('weight_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="quantity_id" class="col-sm-3 col-form-label">Quantity Type*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('quantity_id') is-invalid @enderror"
                                            id="quantity_id" name="quantity_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $quantity_id = (old('quantity_id')?old('quantity_id'):$parts->quantity_type_id);
                                            @endphp
                                            @foreach ($quantityType as $p)
                                                <option @if ($quantity_id==$p->id) {{ 'selected' }} @endif
                                                    value="{{ $p->id }}">{{ $p->title_ind }}</option>
                                            @endforeach
                                        </select>
                                        @error('quantity_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                {{-- <div class="row mb-3">
                                    <label for="max_stock" class="col-sm-3 col-form-label">Max Stock</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control @error('max_stock') is-invalid @enderror"
                                            maxlength="12" id="max_stock" name="max_stock" placeholder="Enter Max Stock"
                                            value="@if (old('max_stock')){{ old('max_stock') }}@else{{ $parts->max_stock }}@endif">
                                        @error('max_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="safety_stock" class="col-sm-3 col-form-label">Safety Stock</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control @error('safety_stock') is-invalid @enderror"
                                            maxlength="12" id="safety_stock" name="safety_stock" placeholder="Enter Safety Stock"
                                            value="@if (old('safety_stock')){{ old('safety_stock') }}@else{{ $parts->safety_stock }}@endif">
                                        @error('safety_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div> --}}
                                <div class="row mb-3">
                                    <label for="safety_stock" class="col-sm-3 col-form-label">Min/Max Stock</label>
                                    <div class="col-sm-9">
                                        <div class="card">
                                            <div class="card-body">
                                                <input type="hidden" id="qMinMaxStockCount" name="qMinMaxStockCount" value="{{ $qMinMaxStockCount }}">
                                                <table class="table table-bordered mb-0">
                                                    <thead>
                                                        <tr style="width: 100%;">
                                                            <th scope="col" style="text-align:center;">Branch</th>
                                                            <th scope="col" style="text-align:center;">Min Stock</th>
                                                            <th scope="col" style="text-align:center;">Max Stock</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $i = 0;
                                                        @endphp
                                                        @foreach ($qMinMaxStock as $MinMax)
                                                            <tr id="row{{ $i }}">
                                                                <td style="text-align: center;">
                                                                    <label id="" for="branch_id{{ $i }}" class="col-form-label">{{ $MinMax->branch_name }}</label>
                                                                    <input type="hidden" name="branch_id{{ $i }}" id="branch_id{{ $i }}" value="{{ $MinMax->branch_id }}">
                                                                </td>
                                                                <td style="text-align: right;">
                                                                    @if ($qUser->is_director=='Y' || $qUser->branch_id==$MinMax->branch_id)
                                                                        <input type="text" class="form-control @error('min_stock'.$i) is-invalid @enderror"
                                                                            maxlength="12" id="min_stock{{ $i }}" name="min_stock{{ $i }}" placeholder="Min Stock"
                                                                            value="@if(old('min_stock'.$i)){{ old('min_stock'.$i) }}@else{{ $MinMax->min_qty }}@endif"
                                                                            style="text-align: right;">
                                                                    @else
                                                                        <label id="" for="min_stock{{ $i }}" class="col-form-label">{{ $MinMax->min_qty }}</label>
                                                                        <input type="hidden" id="min_stock{{ $i }}" name="min_stock{{ $i }}" value="{{ $MinMax->min_qty }}">
                                                                    @endif
                                                                    @error('min_stock'.$i)
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td style="text-align: right;">
                                                                    @if ($qUser->is_director=='Y' || $qUser->branch_id==$MinMax->branch_id)
                                                                        <input type="text" class="form-control @error('max_stock'.$i) is-invalid @enderror"
                                                                            maxlength="12" id="max_stock{{ $i }}" name="max_stock{{ $i }}" placeholder="Max Stock"
                                                                            value="@if(old('max_stock'.$i)){{ old('max_stock'.$i) }}@else{{ $MinMax->max_qty }}@endif"
                                                                            style="text-align: right;">
                                                                    @else
                                                                        <label id="" for="max_stock{{ $i }}" class="col-form-label">{{ $MinMax->max_qty }}</label>
                                                                        <input type="hidden" id="max_stock{{ $i }}" name="max_stock{{ $i }}" value="{{ $MinMax->max_qty }}">
                                                                    @endif
                                                                    @error('max_stock'.$i)
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $i += 1;
                                                            @endphp
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="price_list" class="col-sm-3 col-form-label">Price List</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control @error('price_list') is-invalid @enderror" style="text-align: right;"
                                            maxlength="64" id="price_list" name="price_list" placeholder="Enter Price List"
                                            value="@if (old('price_list')){{ number_format(old('price_list'),0,'.','') }}@else{{ ($parts->price_list==0)?'':number_format($parts->price_list,0,'.','') }}@endif">
                                        @error('price_list')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                        <th scope="col" style="width: 50%;">Part Number</th>
                                        {{-- <th scope="col" style="width: 25%;">Part Name</th> --}}
                                        <th scope="col" style="width: 25%;">Brand</th>
                                        <th scope="col" style="width: 20%;">Part Type</th>
                                        <th scope="col" style="width: 3%;">Delete</th>
                                    </tr>
                                </thead>
                                <tbody id="part-subs-row">
                                    @if (old('totalPartSubsRow'))
                                        @for ($i=0;$i<old('totalPartSubsRow');$i++)
                                            @if (old('part_no_'.$i))
                                                <tr id="rowPartSubs{{ $i }}">
                                                    <th scope="row" style="text-align:right;">
                                                        <label id="" for="" class="col-form-label">{{ $i+1 }}.</label>
                                                        <input type="hidden" name="part_subs_id_row{{ $i }}" id="part_subs_id_row{{ $i }}"
                                                            value="@if (old('part_subs_id')){{ old('part_subs_id') }}@else{{ 0 }}@endif">
                                                    </th>
                                                    @php
                                                        $partInfo = \App\Models\Mst_part::where([
                                                            'id' => old('part_no_'.$i),
                                                        ])
                                                        ->first();
                                                    @endphp
                                                    <td>
                                                        <select class="form-select partsAjax @error('part_no_'.$i) is-invalid @enderror"
                                                            id="part_no_{{ $i }}" name="part_no_{{ $i }}" onchange="dispPartRef_PartNo(this.value, {{ $i }});">
                                                            <option value="#">Choose...</option>
                                                            @php
                                                                $partId = old('part_no_'.$i) ? old('part_no_'.$i) : 0;
                                                                $partNumber = $partInfo->part_number;
                                                                if(strlen($partNumber)<11){
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                                }else{
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                                }
                                                            @endphp
                                                            <option {{ 'selected' }} value="{{ $partInfo->id }}">{{ $partNumber }} : {{ $partInfo->part_name }}</option>
                                                        </select>
                                                        @error('part_no_'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <label id="brand-name-{{ $i }}" for="" class="col-form-label">{{ (!is_null($partInfo)?$partInfo->brand->title_ind:'') }}</label>
                                                    </td>
                                                    <td>
                                                        <label id="part-type-name-{{ $i }}" for="" class="col-form-label">{{ (!is_null($partInfo)?$partInfo->part_type->title_ind:'') }}</label>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <input type="checkbox" id="rowCheckPartSubs{{ $i }}" name="rowCheckPartSubs{{ $i }}" value="{{ $i }}">
                                                    </td>
                                                </tr>
                                            @endif
                                        @endfor
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
                                                    <select class="form-select partsAjax @error('part_no_'.$i) is-invalid @enderror"
                                                        id="part_no_{{ $i }}" name="part_no_{{ $i }}" onchange="dispPartRef_PartNo(this.value, {{ $i }});">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $partId = $Op->part_other_id;
                                                            $partNumber = $partInfo->part_number;
                                                            if(strlen($partNumber)<11){
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                            }else{
                                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                            }
                                                        @endphp
                                                        <option {{ 'selected' }} value="{{ $partInfo->id }}">{{ $partNumber }} : {{ $partInfo->part_name }}</option>
                                                    </select>
                                                    @error('part_no_'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <label id="brand-name-{{ $i }}" for="" class="col-form-label">{{ $partInfo->brand->title_ind }}</label>
                                                </td>
                                                <td>
                                                    <label id="part-type-name-{{ $i }}" for="" class="col-form-label">{{ $partInfo->part_type->title_ind }}</label>
                                                </td>
                                                <td style="text-align: center;">
                                                    <input type="checkbox" id="rowCheckPartSubs{{ $i }}" name="rowCheckPartSubs{{ $i }}" value="{{ $i }}">
                                                </td>
                                            </tr>
                                            @php
                                                $i += 1;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                            <div class="input-group">
                                <input type="button" id="btn-add-part-subs-row" class="btn btn-primary px-5" style="margin-top: 15px;" value="Add Row">
                                <input type="button" id="btn-del-part-subs-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row">
                            </div>
                        </div>
                    </div>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="submit" id="save" class="btn btn-primary px-5" value="Save">
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
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

@php
    $brandTypeHtml = '';
@endphp
@foreach($queryBrandType as $p)
    @php
        $brandTypeHtml .= '<option value="'.$p->id.'">'.$p->brand_type .'</option>';
    @endphp
@endforeach

@section('script')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function setPartsToDropdown(){
        $('.partsAjax').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
            placeholder: {
                id: "#",
                placeholder: "Choose..."
            },
            language: {
                inputTooShort: function (args) {
                    return "4 or more characters.";
                },
                noResults: function () {
                    return "Not Found.";
                },
                searching: function () {
                    return "Searching...";
                }
            },
            minimumInputLength: 4,
            ajax: {
                url: function (params) {
                    return '{{ url('/parts-json/?pnm=') }}'+params.term;
                },
                processResults: function (data) {
                return {
                    results: $.map(data.items, function (item) {
                        return {
                            text: item.part_name,
                            id: item.id
                        }
                    })
                };
            }}
        });
    }

    function addBrandType(){
        let totalBrandTypeRow = $("#totalBrandTypeRow").val();
        let rowNo = (parseInt(totalBrandTypeRow)+1);
        let vHtml = '<tr id="row'+totalBrandTypeRow+'">'+
            '<th scope="row" style="text-align:right;">'+
            '<label id="" for="" class="col-form-label">'+rowNo+'.</label>'+
            '<input type="hidden" name="brand_type_id_row'+totalBrandTypeRow+'" id="brand_type_id_row'+totalBrandTypeRow+'" value="0">'+
            '</th>'+
            '<td>'+
            '<select class="form-select single-select" id="brand_type_id_'+totalBrandTypeRow+'" name="brand_type_id_'+totalBrandTypeRow+'">'+
            '<option value="#">Choose...</option>{!! $brandTypeHtml !!}'+
            '</select>'+
            '</td>'+
            '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalBrandTypeRow+'" name="rowCheck'+totalBrandTypeRow+'" value="'+totalBrandTypeRow+'"></td>'+
            '</tr>';
        $("#brand-type-new-row").append(vHtml);
        $("#totalBrandTypeRow").val(rowNo);
        $("#brand_type_id_"+totalBrandTypeRow).empty();

        var fd = new FormData();
        fd.append('brand_id', $('#brand_id option:selected').val());
        $.ajax({
            url: '{{ url("disp_brand_type_item") }}',
            type: 'POST',
            enctype: 'application/x-www-form-urlencoded',
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                let o = res[0].brand_type;
                let totBrandType = o.length;
                let vHtml = '';
                if(totBrandType>0){
                    for (let i = 0; i < totBrandType; i++) {
                        optionText = o[i].brand_type;
                        optionValue = o[i].id;
                        $("#brand_type_id_"+totalBrandTypeRow).append(`<option value="${optionValue}">${optionText}</option>`);
                    }
                }
            },
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    }

    function addPart(){
        let totalRow = $("#totalPartSubsRow").val();
        let rowNo = (parseInt(totalRow)+1);
        let vHtml = '<tr id="rowPartSubs'+totalRow+'">'+
            '<th scope="row" style="text-align:right;">'+
            '<label id="" for="" class="col-form-label">'+rowNo+'.</label>'+
            '<input type="hidden" name="part_subs_id_row'+totalRow+'" id="part_subs_id_row'+totalRow+'" value="0">'+
            '</th>'+
            '<td>'+
                '<select onchange="dispPartRef_PartNo(this.value, '+totalRow+');" class="form-select partsAjax" id="part_no_'+totalRow+'" name="part_no_'+totalRow+'">'+
                    '<option value="#">Choose...</option>'+
                '</select>'+
            '</td>'+
            '<td><label id="brand-name-'+totalRow+'" for="" class="col-form-label"></label></td>'+
            '<td><label id="part-type-name-'+totalRow+'" for="" class="col-form-label"></label></td>'+
            '<td style="text-align:center;"><input type="checkbox" id="rowCheckPartSubs'+totalRow+'" name="rowCheckPartSubs'+totalRow+'" value="'+totalRow+'"></td>'+
            '</tr>';
        $("#part-subs-row").append(vHtml);
        $("#totalPartSubsRow").val(rowNo);

        setPartsToDropdown();
    }

    function dispPartRef_PartNo(part_id, idx){
        var fd = new FormData();
        fd.append('part_id', part_id);
        $.ajax({
            url: "{{ url('/disp_quotation_part_ref_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].parts;
                if (o.length>0){
                    $('#brand-name-'+idx).text(o[0].brand_name);
                    $('#part-type-name-'+idx).text(o[0].part_type_name);
                }else{
                    $('#brand-name-'+idx).text('-');
                    $('#part-type-name-'+idx).text('-');
                }
            },
        });
    }

    function dispPartRef(part_id, idx){
        var fd = new FormData();
        fd.append('part_id', part_id);
        $.ajax({
            url: "{{ url('/disp_quotation_part_ref_info') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].parts;
                // $("#part_no_"+idx).val(o[0].part_id).change();
                $('#brand-name-'+idx).text(o[0].brand_name);
                $('#part-type-name-'+idx).text(o[0].part_type_name);
            },
        });
    }

    $(document).ready(function() {
        $("#save").click(function() {
            if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);

                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            $(':input[type="button"]').prop('disabled', true);
            
            history.back();
        });

        $("#btn-add-brand-type-row").click(function() {
            if($('#brand_id option:selected').val()==='#'){
                alert('Please select a valid Brand.');
                $("#brand_id").focus();
            }else{
                addBrandType();
            }
        });
        $("#btn-del-brand-type-row").click(function() {
            for (i = 0; i < $("#totalBrandTypeRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row"+i).remove();
                }
            }
        });
        $("#btn-add-part-subs-row").click(function() {
            addPart();
        });
        $("#btn-del-part-subs-row").click(function() {
            for (i = 0; i < $("#totalPartSubsRow").val(); i++) {
                if ($("#rowCheckPartSubs"+i).is(':checked')) {
                    $("#rowPartSubs"+i).remove();
                }
            }
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : ($(this).hasClass('w-100') ? '100%' : 'style'),
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
        setPartsToDropdown();

        $("#partName").keyup(function() {
            let partName = $("#partName").val();
            $("#partName").val(partName.toUpperCase());
        });

        $("#part_no").keyup(function() {
            if($("#part_no").val().trim().length>5){
                let lastPartNoStr = $("#part_no").val().trim();
                let tmpPartNoStr = lastPartNoStr;
                if(lastPartNoStr.substring(5, 6)==='-'){
                    tmpPartNoStr = tmpPartNoStr.substring(0, 5)+""+tmpPartNoStr.substring(6, tmpPartNoStr.length);
                }
                let newPartNoStr = tmpPartNoStr.substring(0, 5)+"-"+tmpPartNoStr.substring(5, tmpPartNoStr.length);
                $("#part_no").val(newPartNoStr);

                var partNo = $("#part_no").val();
                var len = partNo.length;
                // Mostly for Web Browsers
                if (partNo.setSelectionRange) {
                    partNo.focus();
                    partNo.setSelectionRange(len, len);
                } else if (partNo.createTextRange) {
                    var t = partNo.createTextRange();
                    t.collapse(true);
                    t.moveEnd('character', len);
                    t.moveStart('character', len);
                    t.select();
                }
            }
        });

        // $("#price_list").keyup(function() {
        //     let priceList = $("#price_list").val().replaceAll(',','');
        //     if(priceList===''){$("#price_list").val('');return false;}
        //     if(isNaN(priceList)){$("#price_list").val('');return false;}
        //     priceList = parseFloat(priceList).numberFormat(0,'.',',');

        //     $("#price_list").val(priceList);
        // });

        $('#brand_id').change(function() {
            $("#brand-type-new-row").empty();
            $("#totalBrandTypeRow").val(0);
        });
    });
</script>
@endsection
