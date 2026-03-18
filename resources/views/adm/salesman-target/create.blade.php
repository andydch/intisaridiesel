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
                <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
                        @csrf
                        <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <span for="target_year" class="col-sm-3 col-form-label">Target Year*</span>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('target_year') is-invalid @enderror" id="target_year" name="target_year">
                                            <option value="#">Choose...</option>
                                            @php
                                                $date=date_create(now());
                                                $yearSelect = old('target_year')?old('target_year'):0;
                                            @endphp
                                            @foreach ($years as $year)
                                                <option @if ($yearSelect==$year->year){{ 'selected' }}@endif value="{{ $year->year }}">{{ $year->year }}</option>
                                            @endforeach
                                        </select>
                                        @error('target_year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="branch_id" class="col-sm-3 col-form-label">Branch*</span>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $date=date_create(now());
                                                $branchId = old('branch_id')?old('branch_id'):0;
                                            @endphp
                                            @foreach ($branches as $b)
                                                <option @if ($branchId==$b->id){{ 'selected' }}@endif value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span class="col-sm-3 col-form-label">Sales Target*</span>
                                    <div class="col-sm-9" style="margin: auto;">
                                        <input type="hidden" id="sales_target" name="sales_target" class="form-control @error('sales_target') is-invalid @enderror" maxlength="20"
                                            style="text-align: right;" value="@if (old('sales_target')){{ old('sales_target') }}@endif">
                                        <span for="" id="sales_target_tmp">@if (old('sales_target')){{ $qCurrency->string_val.number_format(old('sales_target'),0,'.',',') }}@endif</span>
                                        @error('sales_target')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="card" style="margin-top: 15px;">
                            <div class="card-body">
                                <input type="hidden" id="totalRow" name="totalRow" value="@if(old('totalRow')){{ old('totalRow') }}@else{{ $totalRow }}@endif">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr style="width: 100%;">
                                            <th scope="col" style="width: 2%;text-align:center;">#</th>
                                            <th scope="col" style="width: 55%;">Salesman</th>
                                            <th scope="col" style="width: 40%;">Sales Target ({{ $qCurrency->string_val }})</th>
                                            <th scope="col" style="width: 3%;">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody id="new-row">
                                    @php
                                        $totalSalesTarget = 0;
                                    @endphp
                                    @if (old('totalRow'))
                                        @for ($i=0;$i<old('totalRow');$i++)
                                            @if (old('salesman_id'.$i))
                                                <tr id="row{{ $i }}">
                                                    <th scope="row" style="text-align:right;">{{ $i+1 }}.</th>
                                                    <td>
                                                        <select class="form-select single-select @error('salesman_id'.$i) is-invalid @enderror" id="salesman_id{{ $i }}" name="salesman_id{{ $i }}">
                                                            <option value="#">Choose...</option>
                                                            @php
                                                                $salesmanId = old('salesman_id'.$i) ? old('salesman_id'.$i) : 0;
                                                            @endphp
                                                            @foreach ($salesmans as $sa)
                                                                <option @if ($salesmanId==$sa->user_id){{ 'selected' }}@endif value="{{ $sa->user_id }}">{{ $sa->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('salesman_id'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control @error('sales_target_per_branch'.$i) is-invalid @enderror" id="sales_target_per_branch_tmp{{ $i }}"
                                                            name="sales_target_per_branch_tmp{{ $i }}" maxlength="20" onchange="totSalesTarget();" onkeyup="formatSalesTargetPerSales(this.value,{{ $i }});" style="text-align: right;"
                                                            value="@if (old('sales_target_per_branch'.$i)){{ number_format(old('sales_target_per_branch'.$i),0,'.',',') }}@endif" />
                                                        <input type="hidden" name="sales_target_per_branch{{ $i }}" id="sales_target_per_branch{{ $i }}"
                                                            value="@if (old('sales_target_per_branch'.$i)){{ number_format(old('sales_target_per_branch'.$i),0,'.','') }}@endif" />
                                                        @error('sales_target_per_branch'.$i)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td style="text-align: center;"><input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}"></td>
                                                </tr>
                                                @php
                                                    $totalSalesTarget += old('sales_target_per_branch'.$i);
                                                @endphp
                                            @endif
                                        @endfor
                                    @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" style="text-align: right;">Total</td>
                                            <td style="text-align: right;">
                                                <label for="" id="total_sales_target">{{ $qCurrency->string_val.number_format($totalSalesTarget,0,'.',',') }}</label>
                                                <input type="hidden" name="total_sales_target_ori" id="total_sales_target_ori" value="{{ $totalSalesTarget }}">
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </tfoot>
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
                                        <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                        <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
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
        function formatSalesTargetPerSales(val,idx){
            if(val===''){return false;}
            let SalesTargetVal = val.replaceAll(",","");
            if(!isNaN(SalesTargetVal)){
                $("#sales_target_per_branch_tmp"+idx).val(parseFloat(SalesTargetVal).numberFormat(0,'.',','));
                $("#sales_target_per_branch"+idx).val(SalesTargetVal);
            }
        }
        function totSalesTarget(){
            let totSalesTarget = 0;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if (!isNaN($("#sales_target_per_branch"+i).val())) {
                    totSalesTarget+=parseFloat($("#sales_target_per_branch"+i).val());
                }
            }
            $("#total_sales_target").text("{{ $qCurrency->string_val }}"+parseFloat(totSalesTarget).numberFormat(0,'.',','));
            $("#total_sales_target_ori").val(totSalesTarget);
        }
        function dispSalesman(branch_id,idx){
            var fd = new FormData();
            fd.append('branch_id', branch_id);
            $.ajax({
                url: "{{ url('/disp_salesman_per_branch') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].salesmans;
                    let totOrder = o.length;
                    if (totOrder > 0) {
                        for (let i = 0; i < totOrder; i++) {
                            optionText = o[i].name;
                            optionValue = o[i].user_id;
                            $("#salesman_id"+idx).append(`<option value="${optionValue}">${optionText}</option>`);
                        }
                    }
                }
            });
        }
        $(document).ready(function() {
            $("#save").click(function() {
                if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
                    event.preventDefault();
                }else{
                    $("#submit-form").submit();
                }
            });
            $("#back-btn").click(function() {
                history.back();
            });

            $("#branch_id").change(function() {
                $("#totalRow").val(0);
                $("#new-row").empty();

                var fd = new FormData();
                fd.append('branch_id', $("#branch_id").val());
                fd.append('year', $("#target_year").val());
                $.ajax({
                    url: "{{ url('/disp_salestarget_per_branch_per_year') }}",
                    type: "POST",
                    enctype: "application/x-www-form-urlencoded",
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (res) {
                        let o = res[0].branch_sales_target;
                        $("#sales_target").val(parseFloat(o.sales_target_per_branch).numberFormat(0,'.',''));
                        $("#sales_target_tmp").text('{{ $qCurrency->string_val }}'+parseFloat(o.sales_target_per_branch).numberFormat(0,'.',','));
                    }
                });
            });
            $("#btn-add-row").click(function() {
                if($("#branch_id").val()==='#'){
                    alert('Please select a valid branch');
                    return false;
                }

                let totalRow = $("#totalRow").val();
                let rowNo = (parseInt(totalRow)+1);
                let vHtml = '<tr id="row'+totalRow+'">'+
                    '<th scope="row" style="text-align:right;">'+rowNo+'.</th>'+
                    '<td>'+
                    '<select class="form-select single-select" id="salesman_id'+totalRow+'" name="salesman_id'+totalRow+'">'+
                    '<option value="#">Choose...</option>'+
                    '</select>'+
                    '</td>'+
                    '<td><input type="text" class="form-control" id="sales_target_per_branch_tmp'+totalRow+'" name="sales_target_per_branch_tmp'+totalRow+'" '+
                    'onchange="totSalesTarget();" onkeyup="formatSalesTargetPerSales(this.value,'+totalRow+');" maxlength="20" style="text-align: right;" value="" />'+
                    '<input type="hidden" name="sales_target_per_branch'+totalRow+'" id="sales_target_per_branch'+totalRow+'" />'+
                    '</td>'+
                    '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'"></td>'+
                    '</tr>';
                $("#new-row").append(vHtml);
                $("#totalRow").val(rowNo);

                dispSalesman($("#branch_id").val(),totalRow);

                $('.single-select').select2({
                    theme: 'bootstrap4',
                    width: $(this).data('width') ? $(this).data('width'):$(this).hasClass('w-100') ? '100%':'style',
                    placeholder: $(this).data('placeholder'),
                    allowClear: Boolean($(this).data('allow-clear')),
                });
            });
            $("#btn-del-row").click(function() {
                for (i = 0; i < $("#totalRow").val(); i++) {
                    if ($("#rowCheck"+i).is(':checked')) {
                        $("#row"+i).remove();
                    }
                }

                totSalesTarget();
            });

            $('.single-select').select2({
                theme: 'bootstrap4',
                width: $(this).data('width') ? $(this).data('width'):$(this).hasClass('w-100') ? '100%':'style',
                placeholder: $(this).data('placeholder'),
                allowClear: Boolean($(this).data('allow-clear')),
            });
        });
    </script>
@endsection
