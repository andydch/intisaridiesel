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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$salesQuo->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            @if (session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="border p-4 rounded">
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Sales Quotation No</label>
                                    <label for="" class="col-sm-9 col-form-label part-id">{{ $salesQuo->sales_quotation_no }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Date</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($salesQuo->sales_quotation_date), 'd/m/Y') }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="customer_id" class="col-sm-3 col-form-label">Customer*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $customerId = (old('customer_id')?old('customer_id'):$salesQuo->customer_id);
                                            @endphp
                                            @foreach ($customers as $p)
                                            <option @if ($customerId==$p->id) {{ 'selected' }} @endif
                                                value="{{ $p->id }}">{{ $p->customer_unique_code.' - '.(!is_null($p->entity_type)?$p->entity_type->title_ind:'').' '.$p->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div id="customer_data" class="row mb-3">
                                    <label for="customer_data" class="col-sm-3 col-form-label">Information</label>
                                    <div id="customer_info" class="col-sm-9">
                                        @isset($customerPics[0])
                                        {!!
                                            (!is_null($customerPics[0]->customer_type)?$customerPics[0]->customer_type->title_ind:'').$customerPics[0]->name.
                                            '<br />Address: '.$customerPics[0]->office_address.
                                            ($customerPics[0]->subdistrict->sub_district_name=='Other'?'':
                                            ', '.ucwords(strtolower($customerPics[0]->subdistrict->sub_district_name))).
                                            ($customerPics[0]->district->district_name=='Other'?'':
                                            ', '.$customerPics[0]->district->district_name).
                                            ($customerPics[0]->city->city_name=='Other'?'':
                                            '<br />'.($customerPics[0]->city->city_type=='Luar
                                            Negeri'?'':$customerPics[0]->city->city_type).' '.
                                            $customerPics[0]->city->city_name).
                                            ($customerPics[0]->province->province_name=='Other'?'':
                                            '<br />'.$customerPics[0]->province->province_name).
                                            '<br />'.$customerPics[0]->province->country->country_name.
                                            ($customerPics[0]->subdistrict->post_code=='000000'?'':
                                            ' '.$customerPics[0]->subdistrict->post_code)
                                        !!}
                                        @endisset
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="customer_pic" class="col-sm-3 col-form-label">Customer PIC*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('customer_pic') is-invalid @enderror"
                                            id="customer_pic" name="customer_pic">
                                            <option value="#">Choose...</option>
                                            @php
                                                $customerPic = (old('customer_pic')?old('customer_pic'):$salesQuo->pic_idx);
                                            @endphp
                                            @foreach ($customerPics as $p)
                                                <option @if ($customerPic==1) {{ 'selected' }} @endif value="1">
                                                    {{ $p->pic1_name }}
                                            </option>
                                            @if (!is_null($p->pic2_name))
                                                <option @if ($customerPic==2) {{ 'selected' }} @endif value="2">
                                                    {{ $p->pic2_name }}
                                                </option>
                                            @endif
                                            @endforeach
                                        </select>
                                        @error('customer_pic')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <input type="hidden" name="is_director" id="is_director" value="{{ $userLogin->is_director }}">
                                @if ($userLogin->is_director=='Y')
                                <div class="row mb-3">
                                    <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $branchId = (old('branch_id')?old('branch_id'):$salesQuo->branch_id);
                                            @endphp
                                            @foreach ($branches as $p)
                                                <option @if($branchId==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @else
                                    <input type="hidden" id="branch_id" name="branch_id" value="@if(old('branch_id')){{ old('branch_id') }}@else{{ $salesQuo->branch_id }}@endif">
                                @endif
                                <div class="row mb-3">
                                    <label for="customer_pic" class="col-sm-3 col-form-label">Header*</label>
                                    <div class="col-sm-9">
                                        <textarea name="salesHeader" id="salesHeader" rows="3" maxlength="1000" style="width: 100%;"
                                            class="form-control @error('salesHeader') is-invalid @enderror">@if (old('salesHeader')){{ old('salesHeader') }}@else{{ $salesQuo->header }}@endif</textarea>
                                        @error('salesHeader')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="customer_pic" class="col-sm-3 col-form-label">Footer*</label>
                                    <div class="col-sm-9">
                                        <textarea name="salesFooter" id="salesFooter" rows="3" maxlength="1000" style="width: 100%;"
                                            class="form-control @error('salesFooter') is-invalid @enderror">@if (old('salesFooter')){{ old('salesFooter') }}@else{{ $salesQuo->footer }}@endif</textarea>
                                        @error('salesFooter')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="customer_pic" class="col-sm-3 col-form-label">Remark</label>
                                    <div class="col-sm-9">
                                        <textarea name="salesRemark" id="salesRemark" rows="3" maxlength="1000" style="width: 100%;"
                                            class="form-control @error('salesRemark') is-invalid @enderror">@if (old('salesRemark')){{ old('salesRemark') }}@else{{ $salesQuo->remark }}@endif</textarea>
                                        @error('salesRemark')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
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
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 25%;">Parts Name</th>
                                        <th scope="col" style="width: 8%;">Part Type</th>
                                        <th scope="col" style="width: 8%;">Qty</th>
                                        <th scope="col" style="width: 12%;">Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 12%;">Description</th>
                                        <th scope="col" style="width: 10%;">AVG Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Final Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Pricelist ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 2%;text-align:center;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $iRow = 1;
                                    @endphp
                                    @if (old('totalRow'))
                                        @for ($i = 0; $i < $totRow; $i++)
                                            @if (old('salesQuo_part_id_'.$i))
                                                <tr id="row{{ $i }}">
                                                    <th scope="row" id="sales_quotation_row_number{{ $i }}" style="text-align:right;">{{ $iRow }}.</th>
                                                    @php
                                                        $partNo = \App\Models\Mst_part::where([
                                                            'id' => old('part_id'.$i),
                                                        ])
                                                        ->first();
                                                    @endphp
                                                    <td>
                                                        <input type="hidden" name="salesQuo_part_id_{{ $i }}" id="salesQuo_part_id_{{ $i }}" value="{{ old('salesQuo_part_id_'.$i) }}">
                                                        <select onchange="dispPartRef(this.value, {{ $i }});"
                                                            class="form-select partsAjax @error('part_id'.$i) is-invalid @enderror"
                                                            id="part_id{{ $i }}" name="part_id{{ $i }}">
                                                            <option value="#">Choose...</option>
                                                            @php
                                                                $partId = old('part_id'.$i);
                                                                $partList = \App\Models\Mst_part::where([
                                                                    'id' => $partId,
                                                                ])
                                                                ->get();
                                                            @endphp
                                                            @foreach ($partList as $pr)
                                                                @php
                                                                    $partNumber = $pr->part_number;
                                                                    if(strlen($partNumber)<11){
                                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                                    }else{
                                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                                    }
                                                                @endphp
                                                                <option @if ($partId==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $partNumber.' : '.$pr->part_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('part_id'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label id="part_type-{{ $i }}" for=""
                                                        class="col-form-label">{{ (is_null($partNo)?'':$partNo->part_type->title_ind) }}</label>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control @error('qty'.$i) is-invalid @enderror"
                                                            id="qty{{ $i }}" name="qty{{ $i }}" maxlength="7" value="@if (old('qty'.$i)){{ old('qty'.$i) }}@endif" style="text-align: right;"/>
                                                        @error('qty'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="text" onkeyup="formatAmount($(this));"
                                                            class="form-control @error('price'.$i) is-invalid @enderror"
                                                            id="price{{ $i }}" name="price{{ $i }}" maxlength="64"
                                                            value="@if (old('price'.$i)){{ old('price'.$i) }}@endif" style="text-align: right;"/>
                                                        @error('price'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <textarea name="desc_part{{ $i }}" id="desc_part{{ $i }}" rows="3" style="width: 100%;">@if (old('desc_part'.$i)){{ old('desc_part'.$i) }}@endif</textarea>
                                                        @error('desc_part'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label id="final-cost-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->avg_cost,0,'.',',')) }}</label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label id="final-price-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->final_price,0,'.',',')) }}</label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <label id="price_list-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->price_list,0,'.',',')) }}</label>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                                    </td>
                                                </tr>
                                                @php
                                                    $iRow++;
                                                @endphp
                                            @endif
                                        @endfor

                                    @else

                                        @php
                                            $i=0;
                                        @endphp
                                        @foreach ($querySalesQuoPart as $q)
                                            <tr id="row{{ $i }}">
                                                <th scope="row" id="sales_quotation_row_number{{ $i }}" style="text-align:right;">{{ $i+1 }}.</th>
                                                @php
                                                    $partNo = \App\Models\Mst_part::where([
                                                        'id' => $q->part_id,
                                                        ])
                                                        ->first();
                                                        @endphp
                                                <td>
                                                    <input type="hidden" name="salesQuo_part_id_{{ $i }}" id="salesQuo_part_id_{{ $i }}" value="{{ $q->id }}">
                                                    <select onchange="dispPartRef(this.value, {{ $i }});"
                                                        class="form-select partsAjax @error('part_id'.$i) is-invalid @enderror"
                                                        id="part_id{{ $i }}" name="part_id{{ $i }}">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $partId = $q->part_id;
                                                            $partList = \App\Models\Mst_part::where([
                                                                'id' => $partId,
                                                            ])
                                                            ->get();
                                                        @endphp
                                                        @foreach ($partList as $pr)
                                                            @php
                                                                $partNumber = $pr->part_number;
                                                                if(strlen($partNumber)<11){
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                                }else{
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                                }
                                                            @endphp
                                                            <option @if ($partId==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $partNumber.' : '.$pr->part_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('part_id'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: right;"><label id="part_type-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':$partNo->part_type->title_ind) }}</label></td>
                                                <td>
                                                    <input type="text" class="form-control @error('qty'.$i) is-invalid @enderror"
                                                        id="qty{{ $i }}" name="qty{{ $i }}" maxlength="7" value="{{ $q->qty }}" style="text-align: right;"/>
                                                    @error('qty'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text" onkeyup="formatAmount($(this));"
                                                        class="form-control @error('price'.$i) is-invalid @enderror"
                                                        id="price{{ $i }}" name="price{{ $i }}" maxlength="64"
                                                        value="{{ number_format($q->price_part,0,'.',',') }}" style="text-align: right;"/>
                                                    @error('price'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <textarea class="form-control @error('price'.$i) is-invalid @enderror" name="desc_part{{ $i }}" id="desc_part{{ $i }}"
                                                        rows="3" style="width: 100%;">{{ $q->description }}</textarea>
                                                    @error('desc_part'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td style="text-align: right;">
                                                    <label id="final-cost-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->avg_cost,0,'.',',')) }}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <label id="final-price-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->final_price,0,'.',',')) }}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <label id="price_list-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->price_list,0,'.',',')) }}</label>
                                                </td>
                                                <td style="text-align: center;">
                                                    <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                                </td>
                                            </tr>
                                            @php
                                                $i+= 1;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                            <div class="input-group">
                                <input type="button" id="btn-add-row" class="btn btn-primary px-5" style="margin-top: 15px;" value="Add Row">
                                <input type="button" id="btn-del-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    @if ($salesQuo->is_draft=='Y')
                                        <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                    @endif
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    @if ($salesQuo->created_by==Auth::user()->id && $salesQuo->active=='Y' && is_null($salesQuo->sales_order))
                                        <input type="hidden" name="quotationId" id="quotationId">
                                        <input type="button" id="del-btn" class="btn btn-danger px-5" value="Delete">
                                    @endif
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

@section('script')
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script src="https://cdn.tiny.cloud/1/{{ ENV('TINYMCEKEY') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#salesHeader,#salesFooter'
});
</script>
<script>
    function formatAmount(elm){
        let amount = elm.val().replaceAll(',','');
        if(amount===''){elm.val('');return false;}
        if(isNaN(amount)){elm.val('');return false;}
        amount = parseFloat(amount).numberFormat(0,'.',',');
        elm.val(amount);

        // set cursor position
        console.log(elm.val().length);
        // if(elm.val().length>=3){
        //     elm.selectRange(elm.val().length-3); // set cursor position
        // }
    }

    function dispPartRef(part_id, idx){
        var fd = new FormData();
        fd.append('part_id', part_id);
        @if ($userLogin->is_director=='Y')
        if($("#branch_id option:selected").val()==='#'){
            alert('Please select a valid branch');
            $("#new-row").empty();
            $("#totalRow").val(0);
            return false;
        }
        fd.append('branch_id', $("#branch_id option:selected").val());
        @endif
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
                $('#part_type-'+idx).text(o[0].part_type_name);
                $('#final-cost-'+idx).text(parseFloat(o[0].avg_cost).numberFormat(0,'.',','));
                $('#final-price-'+idx).text(parseFloat(o[0].final_price).numberFormat(0,'.',','));
                if(o[0].price_list!==null){
                    $('#price_list-'+idx).text(parseFloat(o[0].price_list).numberFormat(0,'.',','));
                }else{
                    $('#price_list-'+idx).text('{{ $qCurrency->string_val }}0');
                }
            },
        });
    }

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

    $(document).ready(function() {
        $("#save-as-draft").click(function() {
            if(!confirm("Data will be saved to database with DRAFT status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);
                
                $("#is_draft").val('Y');
                $("#submit-form").submit();
            }
        });
        $("#save").click(function() {
            if(!confirm("Data will be saved to database with CREATED status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);
                
                $("#is_draft").val('N');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            $(':input[type="button"]').prop('disabled', true);
            
            // history.back();
            location.href = '{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}';
        });
        @if ($salesQuo->created_by==Auth::user()->id && $salesQuo->active=='Y' && is_null($salesQuo->sales_order))
            $("#del-btn").click(function() {
                let msg = 'The following Sales Quotation Numbers will be canceled.\n{{ $salesQuo->sales_quotation_no }}\nContinue?';
                if(!confirm(msg)){
                    event.preventDefault();
                }else{
                    $("#quotationId").val('{{ $salesQuo->id }}');
                    $("input[name='_method']").val('POST');
                    $('#submit-form').attr('method', "POST");
                    $('#submit-form').attr('action', "{{ url('/del_sales_quotation') }}");
                    $("#submit-form").submit();
                }
            });
        @endif

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();

        $(function() {
            $('#date-time').bootstrapMaterialDatePicker({
                format: 'YYYY-MM-DD HH:mm'
            });
            $('#quotation_date').bootstrapMaterialDatePicker({
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
        });

        $("#btn-add-row").click(function() {
            let totalRow = $("#totalRow").val();
            let rowNo = (parseInt(totalRow)+1);
            let vHtml = '<tr id="row'+totalRow+'">'+
                '<th scope="row" id="sales_quotation_row_number'+totalRow+'" style="text-align:right;">'+rowNo+'.</th>'+
                '<td>'+
                    '<input type="hidden" name="salesQuo_part_id_'+totalRow+'" id="salesQuo_part_id_'+totalRow+'" value="0">'+
                    '<select onchange="dispPartRef(this.value, '+totalRow+');" class="form-select partsAjax" id="part_id'+totalRow+'" name="part_id'+totalRow+'">'+
                        '<option value="#">Choose...</option>'+
                    '</select>'+
                '</td>'+
                '<td style="text-align: right;"><label id="part_type-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
                '<td><input type="text" class="form-control" id="qty'+totalRow+'" name="qty'+totalRow+'" maxlength="7" style="text-align: right;" value="" /></td>'+
                '<td><input type="text" onkeyup="formatAmount($(this));" class="form-control" id="price'+totalRow+'" name="price'+totalRow+'" maxlength="64" style="text-align: right;" value="" /></td>'+
                '<td><textarea class="form-control" name="desc_part'+totalRow+'" id="desc_part'+totalRow+'" rows="3" style="width: 100%;"></textarea></td>'+
                '<td style="text-align: right;"><label id="final-cost-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
                '<td style="text-align: right;"><label id="final-price-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
                '<td style="text-align: right;"><label id="price_list-'+totalRow+'" for="" class="col-form-label">---</label></td>'+
                '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'"></td>'+
                '</tr>';
            $("#new-row").append(vHtml);
            $("#totalRow").val(rowNo);

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#sales_quotation_row_number"+i).text()){
                    $("#sales_quotation_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end

            setPartsToDropdown();
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row"+i).remove();
                }
            }

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#sales_quotation_row_number"+i).text()){
                    $("#sales_quotation_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
        setPartsToDropdown();

        $('#customer_id').change(function() {
            $("#customer_pic").empty();
            $("#customer_pic").append(
                `<option value="#">Choose...</option>`
            );

            dispCustomerPic('customer_id', '#customer_id option:selected', '{{ url("disp_customer_pic") }}', '#customer_pic');
        });
    });
</script>
@endsection
