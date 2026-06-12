@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
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
    .dtp-btn-ok, .dtp-btn-cancel {
        color: white !important;
    }
    td {
        padding: 5px;
    }
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include(ENV('REPORT_FOLDER_NAME').'.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <hr />
        <form name="submit_form" id="submit-form" action="{{ url('/'.ENV('REPORT_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <div class="card">
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if (session('status-error'))
                        <div class="alert alert-danger">{{ session('status-error') }}</div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-xl-6">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row mb-3">
                                        <label for="coa_id" class="col-sm-3 col-form-label">COA No</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('coa_id') is-invalid @enderror"
                                                id="coa_id" name="coa_id">
                                                <option value="">Choose...</option>
                                                @php
                                                    $coa_id = (old('coa_id')?old('coa_id'):$coa_id)
                                                @endphp
                                                @foreach ($coas as $j)
                                                    <option @if($coa_id==$j->id){{ 'selected' }}@endif
                                                        value="{{ $j->id }}">{{ $j->coa_code_complete.' - '.$j->coa_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('coa_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="date_start" class="col-sm-3 col-form-label">Period</label>
                                        <div class="col-sm-3">
                                            <input readonly type="text" class="form-control @error('date_start') is-invalid @enderror" maxlength="10"
                                                id="date_start" name="date_start" placeholder="Start Date"
                                                value="@if (old('date_start')){{ old('date_start') }}@else{{ (isset($reqs)?$reqs->date_start:'') }}@endif">
                                            @error('date_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-sm-3">
                                            <input readonly type="text" class="form-control @error('date_end') is-invalid @enderror" maxlength="10"
                                                id="date_end" name="date_end" placeholder="End Date"
                                                value="@if (old('date_end')){{ old('date_end') }}@else{{ (isset($reqs)?$reqs->date_end:'') }}@endif">
                                            @error('date_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <input type="button" id="download-report" class="btn btn-primary px-5" value="Download Report">
                            <input type="button" id="back-btn" class="btn btn-danger px-5" value="Back">
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="view_mode" id="view_mode">
        </form>
    </div>
    <div class="table-responsive" style="padding:20px;">
        <table style="width:1024px;">
            <thead>
                <tr>
                    <th colspan="7">
                        {!! ($qCompany?$qCompany->name:'') !!}
                    </th>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                </tr>
                <tr>
                    <th colspan="7" style="text-align: center;">TRANSACTION PER ACCOUNT</th>
                </tr>
                <tr>
                    <th colspan="7" style="text-align: center;">PERIOD:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $dt_s = explode("-",$date_start);
                    $dt_e = explode("-",$date_end);
                    $totalDebit = 0;
                    $totalKredit = 0;
                    $totalAll = 0;

                    $branches = \App\Models\Mst_branch::where([
                        'active'=>'Y',
                    ])
                    ->orderBy('name','ASC')
                    ->get();
                @endphp
                @foreach ($branches as $branch)
                    @php
                        $qCoa = \App\Models\Mst_coa::where([
                            'id'=>$coa_id
                        ])
                        ->first();

                        $genJd = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                        ->leftJoin('mst_coas as m_coa','tx_general_journal_details.coa_id','=','m_coa.id')
                        ->leftJoin('userdetails as usr_d','tx_gj.created_by','=','usr_d.user_id')
                        ->select(
                            'tx_gj.general_journal_no as general_journal_no',
                            'tx_gj.general_journal_date as general_journal_date',
                            'tx_gj.module_no',
                            'tx_gj.id as id_1',
                            'tx_general_journal_details.id as id_2',
                            'tx_general_journal_details.description as description',
                            'tx_general_journal_details.debit as debit',
                            'tx_general_journal_details.kredit as kredit',
                            'm_coa.coa_code_complete',
                            'm_coa.coa_name',
                            'usr_d.branch_id as branch_id',
                        )
                        ->where([
                            'tx_general_journal_details.coa_id'=>$coa_id,
                            'tx_general_journal_details.active'=>'Y',
                            'tx_gj.is_wt_for_appr'=>'N',
                            'tx_gj.is_draft'=>'N',
                            'tx_gj.active'=>'Y',
                        ])
                        ->whereRaw('tx_gj.general_journal_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                        ->whereRaw('tx_gj.general_journal_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                        ->orderBy('tx_gj.general_journal_date','DESC');

                        $lokJd = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                        ->leftJoin('mst_coas as m_coa','tx_lokal_journal_details.coa_id','=','m_coa.id')
                        ->leftJoin('userdetails as usr_d','tx_lj.created_by','=','usr_d.user_id')
                        ->select(
                            'tx_lj.general_journal_no as general_journal_no',
                            'tx_lj.general_journal_date as general_journal_date',
                            'tx_lj.module_no',
                            'tx_lj.id as id_1',
                            'tx_lokal_journal_details.id as id_2',
                            'tx_lokal_journal_details.description as description',
                            'tx_lokal_journal_details.debit as debit',
                            'tx_lokal_journal_details.kredit as kredit',
                            'm_coa.coa_code_complete',
                            'm_coa.coa_name',
                            'usr_d.branch_id as branch_id',
                        )
                        ->where([
                            'tx_lokal_journal_details.coa_id'=>$coa_id,
                            'tx_lokal_journal_details.active'=>'Y',
                            'tx_lj.is_wt_for_appr'=>'N',
                            'tx_lj.is_draft'=>'N',
                            'tx_lj.active'=>'Y',
                        ])
                        ->whereRaw('tx_lj.general_journal_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                        ->whereRaw('tx_lj.general_journal_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                        ->orderBy('tx_lj.general_journal_date','DESC')
                        ->union($genJd)
                        ->orderBy('general_journal_date','DESC')
                        ->get();

                        $firstRow = 1;
                    @endphp
                    @if ($lokJd)
                        @foreach ($lokJd as $journal)
                            @php
                                $branch_id = 0;
                            @endphp
                            @if (strpos("J-".$journal->module_no,env('P_FAKTUR'))>0)
                                {{-- faktur --}}
                                @php
                                    $qFaktur = \App\Models\Tx_delivery_order::where([
                                        'delivery_order_no'=>$journal->module_no,
                                        'branch_id'=>$branch->id,
                                    ])
                                    ->first();
                                    if ($qFaktur){$branch_id = $qFaktur->branch_id;}
                                @endphp
                            @endif
                            @if (strpos("J-".$journal->module_no,env('P_NOTA_RETUR'))>0)
                                {{-- nota retur --}}
                                @php
                                    $qNotaRetur = \App\Models\Tx_nota_retur::where([
                                        'nota_retur_no'=>$journal->module_no,
                                        'branch_id'=>$branch->id,
                                    ])
                                    ->first();
                                    if ($qNotaRetur){$branch_id = $qNotaRetur->branch_id;}
                                @endphp
                            @endif
                            @if (strpos("J-".$journal->module_no,env('P_NOTA_PENJUALAN'))>0)
                                {{-- nota penjualan --}}
                                @php
                                    $qNotaPenjualan = \App\Models\Tx_delivery_order_non_tax::where([
                                        'delivery_order_no'=>$journal->module_no,
                                        'branch_id'=>$branch->id,
                                    ])
                                    ->first();
                                    if ($qNotaPenjualan){$branch_id = $qNotaPenjualan->branch_id;}
                                @endphp
                            @endif
                            @if (strpos("J-".$journal->module_no,env('P_RETUR'))>0)
                                {{-- retur --}}
                                @php
                                    $qRetur = \App\Models\Tx_nota_retur_non_tax::where([
                                        'nota_retur_no'=>$journal->module_no,
                                        'branch_id'=>$branch->id,
                                    ])
                                    ->first();
                                    if ($qRetur){$branch_id = $qRetur->branch_id;}
                                @endphp
                            @endif
                            @if (strpos("J-".$journal->module_no,env('P_RECEIPT_ORDER'))>0)
                                {{-- receipt order --}}
                                @php
                                    $qRO = \App\Models\Tx_receipt_order::where([
                                        'receipt_no'=>$journal->module_no,
                                        'branch_id'=>$branch->id,
                                    ])
                                    ->first();
                                    if ($qRO){$branch_id = $qRO->branch_id;}
                                @endphp
                            @endif
                            @if (strpos("J-".$journal->module_no,env('P_PAYMENT_RECEIPT'))>0)
                                {{-- payment receipt / pembayaran customer --}}
                                @php
                                    $qPembCust = \App\Models\Tx_payment_receipt::leftJoin('userdetails as usr_d','tx_payment_receipts.created_by','=','usr_d.user_id')
                                    ->select(
                                        'usr_d.branch_id',
                                    )
                                    ->where([
                                        'tx_payment_receipts.payment_receipt_no'=>$journal->module_no,
                                        'usr_d.branch_id'=>$branch->id,
                                    ])
                                    ->first();
                                    if ($qPembCust){$branch_id = $qPembCust->branch_id;}
                                @endphp
                            @endif
                            @if (strpos("J-".$journal->module_no,env('P_PAYMENT_VOUCHER'))>0)
                                {{-- payment voucher / pembayaran supplier --}}
                                @php
                                    $qPembSupp = \App\Models\Tx_payment_voucher::leftJoin('userdetails as usr_d','tx_payment_vouchers.created_by','=','usr_d.user_id')
                                    ->select(
                                        'usr_d.branch_id',
                                    )
                                    ->where([
                                        'tx_payment_vouchers.payment_voucher_no'=>$journal->module_no,
                                        'usr_d.branch_id'=>$branch->id,
                                    ])
                                    ->first();
                                    if ($qPembSupp){$branch_id = $qPembSupp->branch_id;}
                                @endphp
                            @endif
                            @if (strpos("J-".$journal->module_no,env('P_STOCK_ADJUSTMENT'))>0)
                                {{-- stock adjusment --}}
                                @php
                                    $qStockAdj = \App\Models\Tx_stock_adjustment::where([
                                        'stock_adj_no'=>$journal->module_no,
                                        'branch_id'=>$branch->id,
                                    ])
                                    ->first();
                                    if ($qStockAdj){$branch_id = $qStockAdj->branch_id;}
                                @endphp
                            @endif
                            @if (strpos("J-".$journal->module_no,env('P_STOCK_TRANSFER'))>0)
                                {{-- stock transfer --}}
                                @php
                                    $qStockTrf = \App\Models\Tx_stock_transfer::where([
                                        'stock_transfer_no'=>$journal->module_no,
                                        'branch_to_id'=>$branch->id,
                                    ])
                                    ->first();
                                    if ($qStockTrf){$branch_id = $qStockTrf->branch_to_id;}
                                @endphp
                            @endif
                            @if ($branch_id==0)
                                @php
                                    $branch_id = $journal->branch_id;
                                @endphp
                            @endif
                            @if ($branch_id==$branch->id)
                                @if ($firstRow==1)
                                    <tr>
                                        <td style="text-align: center;">{{ ($qCoa?$qCoa->coa_code_complete:'') }}</td>
                                        <td>{{ ($qCoa?$qCoa->coa_name:'') }}</td>
                                        <td>{{ $branch->name }}</td>
                                        <td colspan="3">&nbsp;</td>
                                        <td style="text-align: center;">{{ date("d-M-Y") }}</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;font-weight: bold;width: 10%;">DATE</td>
                                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;font-weight: bold;width: 10%;">DOCUMENT NO</td>
                                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;font-weight: bold;width: 20%;">DESCRIPTION</td>
                                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;font-weight: bold;width: 15%;">DEBET</td>
                                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;font-weight: bold;width: 15%;">KREDIT</td>
                                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;font-weight: bold;width: 15%;">TOTAL</td>
                                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;font-weight: bold;width: 10%;">PAYMENT</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="text-align: center;border:1px solid black;">{{ date_format(date_create($journal->general_journal_date),"d-M-Y") }}</td>
                                    @php
                                        $link = '';
                                        if (substr($journal->general_journal_no, 0, 1)=='G'){
                                            $link = url(ENV('TRANSACTION_FOLDER_NAME').'/general-journal/'.$journal->general_journal_no);
                                        }
                                        if (substr($journal->general_journal_no, 0, 1)=='N'){
                                            $link = url(ENV('TRANSACTION_FOLDER_NAME').'/lokal-journal/'.$journal->general_journal_no);
                                        }
                                    @endphp
                                    <td style="text-align: center;border:1px solid black;">
                                        <a href="{{ $link }}" target="_new" style="text-decoration:underline;">{{ $journal->general_journal_no }}</a>
                                    </td>
                                    <td style="text-align: left;border:1px solid black;">{{ $journal->description }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format($journal->debit, 0, '.', ',') }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format($journal->kredit, 0, '.', ',') }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format(($journal->debit>0?$journal->debit:$journal->kredit), 0, '.', ',') }}</td>
                                    @php
                                        $totalDebit += $journal->debit;
                                        $totalKredit += $journal->kredit;
                                        $totalAll += ($journal->debit>0?$journal->debit:$journal->kredit);

                                        $qJdtl = [];
                                    @endphp
                                    @if (strpos("J-".$journal->general_journal_no,env('P_GENERAL_JURNAL'))>0)
                                        {{-- general journal --}}
                                        @if ($journal->debit>0)
                                            @php
                                                $qJdtl = \App\Models\Tx_general_journal_detail::leftJoin('mst_coas as m_coa','tx_general_journal_details.coa_id','=','m_coa.id')
                                                ->select(
                                                    'm_coa.coa_code_complete',
                                                    'm_coa.coa_name',
                                                )
                                                ->whereRaw('tx_general_journal_details.id<>'.$journal->id_2)
                                                ->where([
                                                    'tx_general_journal_details.general_journal_id'=>$journal->id_1,
                                                    'tx_general_journal_details.kredit'=>$journal->debit,
                                                ])
                                                ->first();
                                            @endphp
                                        @endif
                                        @if ($journal->kredit>0)
                                            @php
                                                $qJdtl = \App\Models\Tx_general_journal_detail::leftJoin('mst_coas as m_coa','tx_general_journal_details.coa_id','=','m_coa.id')
                                                ->select(
                                                    'm_coa.coa_code_complete',
                                                    'm_coa.coa_name',
                                                )
                                                ->whereRaw('tx_general_journal_details.id<>'.$journal->id_2)
                                                ->where([
                                                    'tx_general_journal_details.general_journal_id'=>$journal->id_1,
                                                    'tx_general_journal_details.kredit'=>$journal->debit,
                                                ])
                                                ->first();
                                            @endphp
                                        @endif
                                    @endif
                                    @if (strpos("J-".$journal->general_journal_no,env('P_LOKAL_JURNAL'))>0)
                                        {{-- lokal journal --}}
                                        @if ($journal->debit>0)
                                            @php
                                                $qJdtl = \App\Models\Tx_lokal_journal_detail::leftJoin('mst_coas as m_coa','tx_lokal_journal_details.coa_id','=','m_coa.id')
                                                ->select(
                                                    'm_coa.coa_code_complete',
                                                    'm_coa.coa_name',
                                                )
                                                ->whereRaw('tx_lokal_journal_details.id<>'.$journal->id_2)
                                                ->where([
                                                    'tx_lokal_journal_details.lokal_journal_id'=>$journal->id_1,
                                                    'tx_lokal_journal_details.kredit'=>$journal->debit,
                                                ])
                                                ->first();
                                            @endphp
                                        @endif
                                        @if ($journal->kredit>0)
                                            @php
                                                $qJdtl = \App\Models\Tx_lokal_journal_detail::leftJoin('mst_coas as m_coa','tx_lokal_journal_details.coa_id','=','m_coa.id')
                                                ->select(
                                                    'm_coa.coa_code_complete',
                                                    'm_coa.coa_name',
                                                )
                                                ->whereRaw('tx_lokal_journal_details.id<>'.$journal->id_2)
                                                ->where([
                                                    'tx_lokal_journal_details.lokal_journal_id'=>$journal->id_1,
                                                    'tx_lokal_journal_details.kredit'=>$journal->debit,
                                                ])
                                                ->first();
                                            @endphp
                                        @endif
                                    @endif
                                    <td style="text-align: center;border:1px solid black;">{{ ($qJdtl?$qJdtl->coa_name:'-') }}</td>
                                </tr>
                            @endif
                            @php
                                $firstRow += 1;
                            @endphp
                        @endforeach
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3" style="border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;text-align:center;font-weight:700;">TOTAL</td>
                    <td style="border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;text-align:right;font-weight:700;">{{ number_format($totalDebit, 0, '.', ',') }}</td>
                    <td style="border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;text-align:right;font-weight:700;">{{ number_format($totalKredit, 0, '.', ',') }}</td>
                    <td style="border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;text-align:right;font-weight:700;">{{ number_format($totalAll, 0, '.', ',') }}</td>
                    <td style="border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">&nbsp;</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#retur-penjualan-detail").DataTable({
            'ordering': false,
        });

        $("#generate-report").click(function() {
            if(!confirm("Data for Report will be generated.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('V');
                $("#submit-form").submit();
            }
        });
        $("#download-report").click(function() {
            // if(!confirm("Data for Report will be saved as Excel.\nContinue?")){
            if(!confirm("Data for Report will be generated.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('P');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            history.back();
        });

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();
        $(function() {
            // $('#date-time').bootstrapMaterialDatePicker({
            //     format: 'YYYY-MM-DD HH:mm'
            // });
            $('#date_start').bootstrapMaterialDatePicker({
                time: false,
                format: 'DD-MM-YYYY'
            });
            $('#date_end').bootstrapMaterialDatePicker({
                time: false,
                format: 'DD-MM-YYYY'
            });
            // $('#time').bootstrapMaterialDatePicker({
            //     date: false,
            //     format: 'HH:mm'
            // });
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection