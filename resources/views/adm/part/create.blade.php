@extends('layouts.app')

@section('style')
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
        @include('adm.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <form action="{{ url(ENV('ADMIN_FOLDER_NAME') . '/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
                            @csrf
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Part Number*</span>
                                <input type="text" id="part_no" name="part_no"
                                    class="form-control @error('part_no') is-invalid @enderror" maxlength="255"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if (old('part_no')) {{ old('part_no') }} @endif">
                                @error('part_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Part Name*</span>
                                <input type="text" id="partName" name="partName"
                                    class="form-control @error('partName') is-invalid @enderror" maxlength="255"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if (old('partName')) {{ old('partName') }} @endif">
                                @error('partName')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3" style="margin-top: 15px;">
                                <label class="input-group-text" for="partType_id">Part Type*</label>
                                <select class="form-select single-select @error('partType_id') is-invalid @enderror"
                                    id="partType_id" name="partType_id">
                                    <option value="#">Choose...</option>
                                    @php
                                    $partTypeId = 0;
                                    @endphp
                                    @if (old('partType_id'))
                                    @php
                                    $partTypeId = old('partType_id');
                                    @endphp
                                    @endif
                                    @foreach ($partType as $p)
                                    <option @if ($partTypeId==$p->id) {{ 'selected' }} @endif
                                        value="{{ $p->id }}">{{ $p->title_ind }}</option>
                                    @endforeach
                                </select>
                                @error('partType_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3" style="margin-top: 15px;">
                                <label class="input-group-text" for="partCategory_id">Part Category*</label>
                                <select class="form-select single-select @error('partCategory_id') is-invalid @enderror"
                                    id="partCategory_id" name="partCategory_id">
                                    <option value="#">Choose...</option>
                                    @php
                                    $partCategoryId = 0;
                                    @endphp
                                    @if (old('partCategory_id'))
                                    @php
                                    $partCategoryId = old('partCategory_id');
                                    @endphp
                                    @endif
                                    @foreach ($partCategory as $p)
                                    <option @if ($partCategoryId==$p->id) {{ 'selected' }} @endif
                                        value="{{ $p->id }}">{{ $p->title_ind }}</option>
                                    @endforeach
                                </select>
                                @error('partCategory_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3" style="margin-top: 15px;">
                                <label class="input-group-text" for="partCategory_id">Brand*</label>
                                <select class="form-select single-select @error('brand_id') is-invalid @enderror"
                                    id="brand_id" name="brand_id">
                                    <option value="#">Choose...</option>
                                    @php
                                    $brandId = 0;
                                    @endphp
                                    @if (old('brand_id'))
                                    @php
                                    $brandId = old('brand_id');
                                    @endphp
                                    @endif
                                    @foreach ($brand as $p)
                                    <option @if ($brandId==$p->id) {{ 'selected' }} @endif
                                        value="{{ $p->id }}">{{ $p->title_ind }}</option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="brand-type" class="input-group mb-3" style="margin-top: 15px">
                                <span class="input-group-text">Brand Type</span>
                                <div class="input-group-text">
                                    <input type="hidden" id="brand_type_count" name="brand_type_count"
                                        value="@if(old('brand_type_count')){{ old('brand_type_count') }}@endif">
                                    <table>
                                        <tbody id="brand-type-item">
                                            @if (old('brand_id'))
                                                @php
                                                $i = 0;
                                                $vHtml = '';
                                                @endphp

                                                @foreach ($queryBrandType as $q)
                                                    @if($i==0)
                                                        @php
                                                        $vHtml.='<tr>' ;
                                                        @endphp
                                                    @endif

                                                    @php
                                                        $checked = '';
                                                    @endphp
                                                    @if (old('brand_type_'.$i))
                                                    @php
                                                        $checked = 'checked';
                                                    @endphp
                                                    @endif
                                                    <td style="padding-left: 15px;padding-right: 15px;">
                                                        <input id="brand_type_{{ $i }}" name="brand_type_{{ $i }}"
                                                            class="form-check-input" {{ $checked }} type="checkbox"
                                                            value="{{ $q->id }}">&nbsp;{{ $q->brand_type }}
                                                    </td>

                                                    @if ($i>0 && ($i+1)%4==0)
                                                        @php
                                                        $vHtml .= '</tr><tr>';
                                                        @endphp
                                                    @endif

                                                    @php
                                                    $i += 1;
                                                    @endphp

                                                    @php
                                                    $vHtml .= '</tr>';
                                                    @endphp

                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Weight*</span>
                                <input type="text" id="weight" name="weight"
                                    class="form-control @error('weight') is-invalid @enderror" maxlength="12"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if (old('weight')) {{ old('weight') }} @endif">
                                @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3" style="margin-top: 15px;">
                                <label class="input-group-text" for="weight_id">Weight Type*</label>
                                <select class="form-select single-select @error('weight_id') is-invalid @enderror"
                                    id="weight_id" name="weight_id">
                                    <option value="#">Choose...</option>
                                    @php
                                    $weightId = 0;
                                    @endphp
                                    @if (old('weight_id'))
                                    @php
                                    $weightId = old('weight_id');
                                    @endphp
                                    @endif
                                    @foreach ($weightType as $p)
                                    <option @if ($weightId==$p->id) {{ 'selected' }} @endif
                                        value="{{ $p->id }}">{{ $p->title_ind }}</option>
                                    @endforeach
                                </select>
                                @error('weight_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3" style="margin-top: 15px;">
                                <label class="input-group-text" for="quantity_id">Quantity Type*</label>
                                <select class="form-select single-select @error('quantity_id') is-invalid @enderror"
                                    id="quantity_id" name="quantity_id">
                                    <option value="#">Choose...</option>
                                    @php
                                    $quantityId = 0;
                                    @endphp
                                    @if (old('quantity_id'))
                                    @php
                                    $quantityId = old('quantity_id');
                                    @endphp
                                    @endif
                                    @foreach ($quantityType as $p)
                                    <option @if ($quantityId==$p->id) {{ 'selected' }} @endif
                                        value="{{ $p->id }}">{{ $p->title_ind }}</option>
                                    @endforeach
                                </select>
                                @error('quantity_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Max Stock</span>
                                <input type="text" id="max_stock" name="max_stock"
                                    class="form-control @error('max_stock') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if (old('max_stock')) {{ old('max_stock') }} @endif">
                                @error('max_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Safety Stock*</span>
                                <input type="text" id="safety_stock" name="safety_stock"
                                    class="form-control @error('safety_stock') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if (old('safety_stock')) {{ old('safety_stock') }} @endif">
                                @error('safety_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Price List</span>
                                <input type="text" id="price_list" name="price_list"
                                    class="form-control @error('price_list') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if(old('price_list')){{ old('price_list') }}@endif">
                                @error('price_list')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Final Price</span>
                                <input type="text" id="final_price" name="final_price"
                                    class="form-control @error('final_price') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if(old('final_price')){{ old('final_price') }}@endif">
                                @error('final_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}
                            {{-- <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">AVG Cost</span>
                                <input type="text" id="avg_cost" name="avg_cost"
                                    class="form-control @error('avg_cost') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if(old('avg_cost')){{ old('avg_cost') }}@endif">
                                @error('avg_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}
                            {{-- <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Initial Cost</span>
                                <input type="text" id="initial_cost" name="initial_cost"
                                    class="form-control @error('initial_cost') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if(old('initial_cost')){{ old('initial_cost') }}@endif">
                                @error('initial_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}
                            {{-- <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Final Cost</span>
                                <input type="text" id="final_cost" name="final_cost"
                                    class="form-control @error('final_cost') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if(old('final_cost')){{ old('final_cost') }}@endif">
                                @error('final_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}
                            {{-- <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Total Cost</span>
                                <input type="text" id="total_cost" name="total_cost"
                                    class="form-control @error('total_cost') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if(old('total_cost')){{ old('total_cost') }}@endif">
                                @error('total_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}
                            {{-- <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Total Sales</span>
                                <input type="text" id="total_sales" name="total_sales"
                                    class="form-control @error('total_sales') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if(old('total_sales')){{ old('total_sales') }}@endif">
                                @error('total_sales')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}
                            <div id="part-subtitution" class="input-group mb-3" style="margin-top: 15px">
                                <span class="input-group-text">Part Subtitution</span>
                                <div class="input-group-text">
                                    <table>
                                        <tbody id="part-subtitution-item">
                                            <input type="hidden" name="part_count" id="part_count" value="{{ $queryOtherPartCount }}">
                                            @php
                                                $i = 0;
                                                $vHtml = '';
                                            @endphp
                                            @foreach ($queryOtherPart as $q)
                                                @if($i==0)
                                                    @php
                                                        $vHtml .= '<tr>';
                                                    @endphp
                                                @endif

                                                @php
                                                    $checked = '';
                                                @endphp
                                                @if (old('part_sub'.$i))
                                                    @php
                                                        $checked = 'checked';
                                                    @endphp
                                                @endif
                                                <td style="padding-left: 15px;padding-right: 15px;">
                                                    <input id="part_sub{{ $i }}" name="part_sub{{ $i }}"
                                                        class="form-check-input" type="checkbox" {{ $checked }}
                                                        value="{{ $q->id }}">&nbsp;{{ $q->part_name }}
                                                </td>

                                                @if ($i>0 && ($i+1)%4==0)
                                                    @php
                                                        $vHtml .= '</tr><tr>';
                                                    @endphp
                                                @endif
                                                @php
                                                    $i += 1;
                                                @endphp
                                            @endforeach
                                            @php
                                                $vHtml .= '</tr>';
                                            @endphp
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="input-group mb-3" style="margin-top: 15px;">
                                <span class="input-group-text">Active</span>
                                <div class="input-group-text">
                                    @if (old('active'))
                                        @if (old('active')=='on')
                                            @php
                                                $checked = 'checked';
                                            @endphp
                                        @else
                                            @php
                                                $checked = '';
                                            @endphp
                                        @endif
                                    @else
                                        @php
                                            $checked = 'checked';
                                        @endphp
                                    @endif
                                    <input class="form-check-input" type="checkbox" id="active" name="active"
                                        aria-label="Active" {{ $checked }}>
                                </div>
                            </div>
                            <div class="input-group">
                                <input type="submit" class="btn btn-light px-5" style="margin-top: 15px;"
                                    value="Submit">
                            </div>
                        </form>
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
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        @if (!old('brand_id'))
        $("#brand-type").hide();
        @endif

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : ($(this).hasClass('w-100') ? '100%' : 'style'),
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });

        $("#part_no").keyup(function() {
            // console.log($("#part_no").val());
            if($("#part_no").val().trim().length>5){
                let lastPartNoStr = $("#part_no").val().trim();
                let tmpPartNoStr = lastPartNoStr;
                // console.log(lastPartNoStr.substring(5, 6));
                if(lastPartNoStr.substring(5, 6)==='-'){
                    tmpPartNoStr = tmpPartNoStr.substring(0, 5)+""+tmpPartNoStr.substring(6, tmpPartNoStr.length);
                    // console.log("result: "+tmpPartNoStr);
                }
                let newPartNoStr = tmpPartNoStr.substring(0, 5)+"-"+tmpPartNoStr.substring(5, tmpPartNoStr.length);
                // console.log("new string "+newPartNoStr);
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

        $('#brand_id').change(function() {
            $("#brand-type").hide();
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
                        $('#brand_type_count').val(totBrandType);
                        $("#brand-type-item").empty();
                        $("#brand-type").show();
                        for (let i = 0; i < totBrandType; i++) {
                            if(i==0){
                                vHtml += '<tr>';
                            }
                            vHtml += '<td style="padding-left: 15px;padding-right: 15px;">'+
                            '<input id="brand_type_'+i+'" name="brand_type_'+i+'" class="form-check-input" '+
                            'type="checkbox" value="'+o[i].id+'">&nbsp;'+o[i].brand_type+
                            '</td>';
                            if(i>0 && ((i+1)%4==0)){
                                vHtml += '</tr><tr>';
                            }
                        }
                        vHtml += '</tr>';
                        $("#brand-type-item").append(vHtml);
                    }
                },
            });
        });
    });
</script>
@endsection
