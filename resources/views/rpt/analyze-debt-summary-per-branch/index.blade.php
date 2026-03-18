@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
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
    .dtp-btn-ok, .dtp-btn-cancel {
        color: white !important;
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
                                        <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                                id="branch_id" name="branch_id">
                                                <option value="#">Choose...</option>
                                                @php
                                                    $p_Id = (old('branch_id')?old('branch_id'):(isset($reqs)?$reqs->branch_id:''));
                                                @endphp
                                                <option @if ($p_Id==0){{ 'selected=""' }}@endif value="0">All</option>
                                                @foreach ($branches as $branch)
                                                    <option @if ($p_Id==$branch->id) {{ 'selected' }} @endif
                                                        value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
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
                            {{-- <input type="button" id="generate-report" class="btn btn-primary px-5" value="Generate"> --}}
                            <input type="button" id="download-report" class="btn btn-primary px-5" value="Download Report">
                            <input type="button" id="back-btn" class="btn btn-danger px-5" value="Back">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" style="display: none;">
                <div class="card-body">
                    <div class="table-responsive">
                        @isset($reqs)
                        <table class="table table-striped table-bordered" id="retur-penjualan-detail" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;width:25%;">SUPPLIER NAME</th>
                                    <th style="text-align: center;">TOTAL THIS MONTH ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">LAST MO ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">LAST 2 MO ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">LAST 3 MO ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">MORE 3 MO ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">TOTAL ({{ $qCurrency->string_val }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalAmount = 0;
                                    $totalThisMonthAmount = 0;
                                    $totalLastMonthAmount = 0;
                                    $totalLast2MonthAmount = 0;
                                    $totalLast3MonthAmount = 0;
                                    $totalMore3MonthAmount = 0;

                                    $timezoneNow = new DateTimeZone('Asia/Jakarta');
                                    $date_local_now = new DateTime();
                                    $date_local_now->setTimeZone($timezoneNow);
                                    // $this->datetimeNow = $date_local_now->format('YmdHisA');

                                    $branches = \App\Models\Mst_branch::when($reqs->branch_id!='0', function($q) use($reqs) {
                                        $q->where('id','=',$reqs->branch_id);
                                    })
                                    ->where('active','=','Y')
                                    ->orderBy('name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($branches as $branch)
                                    <tr>
                                        <td style="font-weight:700;">{{ $branch->name }}</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @php
                                        $q_tx_ro = \App\Models\Tx_receipt_order::leftJoin('mst_suppliers as m_sp','tx_receipt_orders.supplier_id','=','m_sp.id')
                                        ->select(
                                            'm_sp.id as supplier_id',
                                            'm_sp.name as supplier_name',
                                        )
                                        ->selectRaw('SUM(tx_receipt_orders.invoice_amount) as tot_amount')
                                        ->whereNotIn('tx_receipt_orders.id', function ($q01) {
                                            // buang RO yg PV detail berstatus full payment dan aktif
                                            $q01->select('receipt_order_id')
                                            ->from('tx_payment_voucher_invoices')
                                            ->whereIn('payment_voucher_id', function ($q02) {
                                                // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                $q02->select('id')
                                                ->from('tx_payment_vouchers')
                                                ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                ->where('active','=','Y');
                                            })
                                            ->where('is_full_payment','=','Y')
                                            ->where('active','=','Y');
                                        })
                                        ->whereRaw('tx_receipt_orders.receipt_no NOT LIKE \'%Draft%\'')
                                        ->where([
                                            'tx_receipt_orders.branch_id'=>$branch->id,
                                            'tx_receipt_orders.active'=>'Y',
                                        ])
                                        ->orderBy('m_sp.name','ASC')
                                        ->groupBy('m_sp.id')
                                        ->groupBy('m_sp.name')
                                        ->get();
                                    @endphp
                                    @foreach ($q_tx_ro as $ro)
                                        <tr>
                                            <td>{{ $ro->supplier_name }}</td>
                                            <td style="text-align: right;">
                                                @php
                                                    $this_month=date_create(date("Y-m-d"));
                                                    date_add($this_month,date_interval_create_from_date_string("0 months"));
                                                    $q_tx_ro_this_month = \App\Models\Tx_receipt_order::leftJoin('mst_suppliers as m_sp','tx_receipt_orders.supplier_id','=','m_sp.id')
                                                    ->selectRaw('SUM(tx_receipt_orders.invoice_amount) as tot_amount_this_month')
                                                    ->whereNotIn('tx_receipt_orders.id', function ($q01) {
                                                        // buang RO yg PV detail berstatus full payment dan aktif
                                                        $q01->select('receipt_order_id')
                                                        ->from('tx_payment_voucher_invoices')
                                                        ->whereIn('payment_voucher_id', function ($q02) {
                                                            // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                            $q02->select('id')
                                                            ->from('tx_payment_vouchers')
                                                            ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                            ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                            ->where('active','=','Y');
                                                        })
                                                        ->where('is_full_payment','=','Y')
                                                        ->where('active','=','Y');
                                                    })
                                                    ->whereRaw('DATE_FORMAT(tx_receipt_orders.receipt_date, "%Y-%m")='.date_format($this_month,"Y-m"))
                                                    ->whereRaw('tx_receipt_orders.receipt_no NOT LIKE \'%Draft%\'')
                                                    ->where([
                                                        'tx_receipt_orders.supplier_id'=>$ro->supplier_id,
                                                        'tx_receipt_orders.branch_id'=>$branch->id,
                                                        'tx_receipt_orders.active'=>'Y',
                                                    ])
                                                    ->groupBy('m_sp.id')
                                                    ->groupBy('m_sp.name')
                                                    ->first();

                                                    $notPaidAmount = \App\Models\Tx_payment_voucher_invoice::where([
                                                        'is_full_payment'=>'N',
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereIn('receipt_order_id', function ($q01) use ($ro,$branch,$this_month) {
                                                        $q01->select('id')
                                                        ->from('tx_receipt_orders')
                                                        ->whereNotIn('id', function ($q02) {
                                                            // buang RO yg PV detail berstatus full payment dan aktif
                                                            $q02->select('receipt_order_id')
                                                            ->from('tx_payment_voucher_invoices')
                                                            ->whereIn('payment_voucher_id', function ($q03) {
                                                                // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                                $q03->select('id')
                                                                ->from('tx_payment_vouchers')
                                                                ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                                ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                                ->where('active','=','Y');
                                                            })
                                                            ->where('is_full_payment','=','Y')
                                                            ->where('active','=','Y');
                                                        })
                                                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                                                        ->whereRaw('receipt_date<=\''.date_format($this_month,"Y-m-d").'\'')
                                                        ->where([
                                                            'supplier_id'=>$ro->supplier_id,
                                                            'branch_id'=>$branch->id,
                                                            'active'=>'Y',
                                                        ]);
                                                    })
                                                    ->sum('total_payment');
                                                @endphp
                                                @if ($q_tx_ro_this_month)
                                                    {{ number_format(($q_tx_ro_this_month->tot_amount_this_month),0,'.',',') }}
                                                    @php
                                                        $totalThisMonthAmount += ($q_tx_ro_this_month->tot_amount_this_month);
                                                    @endphp
                                                @else
                                                    {{ 0 }}
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                @php
                                                    $last_month=date_create(date("Y-m-d"));
                                                    date_add($last_month,date_interval_create_from_date_string("-1 months"));
                                                    $q_tx_ro_last_month = \App\Models\Tx_receipt_order::leftJoin('mst_suppliers as m_sp','tx_receipt_orders.supplier_id','=','m_sp.id')
                                                    ->selectRaw('SUM(tx_receipt_orders.invoice_amount) as tot_amount_last_month')
                                                    ->whereNotIn('tx_receipt_orders.id', function ($q01) {
                                                        // buang RO yg PV detail berstatus full payment dan aktif
                                                        $q01->select('receipt_order_id')
                                                        ->from('tx_payment_voucher_invoices')
                                                        ->whereIn('payment_voucher_id', function ($q02) {
                                                            // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                            $q02->select('id')
                                                            ->from('tx_payment_vouchers')
                                                            ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                            ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                            ->where('active','=','Y');
                                                        })
                                                        ->where('is_full_payment','=','Y')
                                                        ->where('active','=','Y');
                                                    })
                                                    ->whereRaw('DATE_FORMAT(tx_receipt_orders.receipt_date, "%Y-%m")='.date_format($last_month,"Y-m"))
                                                    ->whereRaw('tx_receipt_orders.receipt_no NOT LIKE \'%Draft%\'')
                                                    ->where([
                                                        'tx_receipt_orders.supplier_id'=>$ro->supplier_id,
                                                        'tx_receipt_orders.branch_id'=>$branch->id,
                                                        'tx_receipt_orders.active'=>'Y',
                                                    ])
                                                    ->groupBy('m_sp.id')
                                                    ->groupBy('m_sp.name')
                                                    ->first();

                                                    $notPaidAmount = \App\Models\Tx_payment_voucher_invoice::where([
                                                        'is_full_payment'=>'N',
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereIn('receipt_order_id', function ($q01) use ($ro,$branch,$last_month) {
                                                        $q01->select('id')
                                                        ->from('tx_receipt_orders')
                                                        ->whereNotIn('id', function ($q02) {
                                                            // buang RO yg PV detail berstatus full payment dan aktif
                                                            $q02->select('receipt_order_id')
                                                            ->from('tx_payment_voucher_invoices')
                                                            ->whereIn('payment_voucher_id', function ($q03) {
                                                                // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                                $q03->select('id')
                                                                ->from('tx_payment_vouchers')
                                                                ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                                ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                                ->where('active','=','Y');
                                                            })
                                                            ->where('is_full_payment','=','Y')
                                                            ->where('active','=','Y');
                                                        })
                                                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                                                        ->whereRaw('receipt_date<=\''.date_format($last_month,"Y-m-d").'\'')
                                                        ->where([
                                                            'supplier_id'=>$ro->supplier_id,
                                                            'branch_id'=>$branch->id,
                                                            'active'=>'Y',
                                                        ]);
                                                    })
                                                    ->sum('total_payment');
                                                @endphp
                                                @if ($q_tx_ro_last_month)
                                                    {{ number_format(($q_tx_ro_last_month->tot_amount_last_month-$notPaidAmount),0,'.',',') }}
                                                    @php
                                                        $totalLastMonthAmount += ($q_tx_ro_last_month->tot_amount_last_month-$notPaidAmount);
                                                    @endphp
                                                @else
                                                    {{ 0 }}
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                @php
                                                    $last_2month=date_create(date("Y-m-d"));
                                                    date_add($last_2month,date_interval_create_from_date_string("-2 months"));
                                                    $q_tx_ro_last_2month = \App\Models\Tx_receipt_order::leftJoin('mst_suppliers as m_sp','tx_receipt_orders.supplier_id','=','m_sp.id')
                                                    ->selectRaw('SUM(tx_receipt_orders.invoice_amount) as tot_amount_2last_month')
                                                    ->whereNotIn('tx_receipt_orders.id', function ($q01) {
                                                        // buang RO yg PV detail berstatus full payment dan aktif
                                                        $q01->select('receipt_order_id')
                                                        ->from('tx_payment_voucher_invoices')
                                                        ->whereIn('payment_voucher_id', function ($q02) {
                                                            // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                            $q02->select('id')
                                                            ->from('tx_payment_vouchers')
                                                            ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                            ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                            ->where('active','=','Y');
                                                        })
                                                        ->where('is_full_payment','=','Y')
                                                        ->where('active','=','Y');
                                                    })
                                                    ->whereRaw('DATE_FORMAT(tx_receipt_orders.receipt_date, "%Y-%m")='.date_format($last_2month,"Y-m"))
                                                    ->whereRaw('tx_receipt_orders.receipt_no NOT LIKE \'%Draft%\'')
                                                    ->where([
                                                        'tx_receipt_orders.supplier_id'=>$ro->supplier_id,
                                                        'tx_receipt_orders.branch_id'=>$branch->id,
                                                        'tx_receipt_orders.active'=>'Y',
                                                    ])
                                                    ->groupBy('m_sp.id')
                                                    ->groupBy('m_sp.name')
                                                    ->first();

                                                    $notPaidAmount = \App\Models\Tx_payment_voucher_invoice::where([
                                                        'is_full_payment'=>'N',
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereIn('receipt_order_id', function ($q01) use ($ro,$branch,$last_2month) {
                                                        $q01->select('id')
                                                        ->from('tx_receipt_orders')
                                                        ->whereNotIn('id', function ($q02) {
                                                            // buang RO yg PV detail berstatus full payment dan aktif
                                                            $q02->select('receipt_order_id')
                                                            ->from('tx_payment_voucher_invoices')
                                                            ->whereIn('payment_voucher_id', function ($q03) {
                                                                // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                                $q03->select('id')
                                                                ->from('tx_payment_vouchers')
                                                                ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                                ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                                ->where('active','=','Y');
                                                            })
                                                            ->where('is_full_payment','=','Y')
                                                            ->where('active','=','Y');
                                                        })
                                                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                                                        ->whereRaw('receipt_date<=\''.date_format($last_2month,"Y-m-d").'\'')
                                                        ->where([
                                                            'supplier_id'=>$ro->supplier_id,
                                                            'branch_id'=>$branch->id,
                                                            'active'=>'Y',
                                                        ]);
                                                    })
                                                    ->sum('total_payment');
                                                @endphp
                                                @if ($q_tx_ro_last_2month)
                                                    {{ number_format(($q_tx_ro_last_2month->tot_amount_last_2month-$notPaidAmount),0,'.',',') }}
                                                    @php
                                                        $totalLast2MonthAmount += ($q_tx_ro_last_2month->tot_amount_last_2month-$notPaidAmount);
                                                    @endphp
                                                @else
                                                    {{ 0 }}
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                @php
                                                    $last_3month=date_create(date("Y-m-d"));
                                                    date_add($last_3month,date_interval_create_from_date_string("-3 months"));
                                                    $q_tx_ro_last_3month = \App\Models\Tx_receipt_order::leftJoin('mst_suppliers as m_sp','tx_receipt_orders.supplier_id','=','m_sp.id')
                                                    ->selectRaw('SUM(tx_receipt_orders.invoice_amount) as tot_amount_last_3month')
                                                    ->whereNotIn('tx_receipt_orders.id', function ($q01) {
                                                        // buang RO yg PV detail berstatus full payment dan aktif
                                                        $q01->select('receipt_order_id')
                                                        ->from('tx_payment_voucher_invoices')
                                                        ->whereIn('payment_voucher_id', function ($q02) {
                                                            // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                            $q02->select('id')
                                                            ->from('tx_payment_vouchers')
                                                            ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                            ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                            ->where('active','=','Y');
                                                        })
                                                        ->where('is_full_payment','=','Y')
                                                        ->where('active','=','Y');
                                                    })
                                                    ->whereRaw('DATE_FORMAT(tx_receipt_orders.receipt_date, "%Y-%m")='.date_format($last_3month,"Y-m"))
                                                    ->whereRaw('tx_receipt_orders.receipt_no NOT LIKE \'%Draft%\'')
                                                    ->where([
                                                        'tx_receipt_orders.supplier_id'=>$ro->supplier_id,
                                                        'tx_receipt_orders.branch_id'=>$branch->id,
                                                        'tx_receipt_orders.active'=>'Y',
                                                    ])
                                                    ->groupBy('m_sp.id')
                                                    ->groupBy('m_sp.name')
                                                    ->first();

                                                    $notPaidAmount = \App\Models\Tx_payment_voucher_invoice::where([
                                                        'is_full_payment'=>'N',
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereIn('receipt_order_id', function ($q01) use ($ro,$branch,$last_3month) {
                                                        $q01->select('id')
                                                        ->from('tx_receipt_orders')
                                                        ->whereNotIn('id', function ($q02) {
                                                            // buang RO yg PV detail berstatus full payment dan aktif
                                                            $q02->select('receipt_order_id')
                                                            ->from('tx_payment_voucher_invoices')
                                                            ->whereIn('payment_voucher_id', function ($q03) {
                                                                // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                                $q03->select('id')
                                                                ->from('tx_payment_vouchers')
                                                                ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                                ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                                ->where('active','=','Y');
                                                            })
                                                            ->where('is_full_payment','=','Y')
                                                            ->where('active','=','Y');
                                                        })
                                                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                                                        ->whereRaw('receipt_date<=\''.date_format($last_3month,"Y-m-d").'\'')
                                                        ->where([
                                                            'supplier_id'=>$ro->supplier_id,
                                                            'branch_id'=>$branch->id,
                                                            'active'=>'Y',
                                                        ]);
                                                    })
                                                    ->sum('total_payment');
                                                @endphp
                                                @if ($q_tx_ro_last_3month)
                                                    {{ number_format(($q_tx_ro_last_3month->tot_amount_last_3month-$notPaidAmount),0,'.',',') }}
                                                    @php
                                                        $totalLast3MonthAmount += ($q_tx_ro_last_3month->tot_amount_last_3month-$notPaidAmount);
                                                    @endphp
                                                @else
                                                    {{ 0 }}
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                @php
                                                    $more_3month=date_create(date("Y-m-d"));
                                                    // date_add($more_3month,date_interval_create_from_date_string("-4 months"));
                                                    $q_tx_ro_more_3month = \App\Models\Tx_receipt_order::leftJoin('mst_suppliers as m_sp','tx_receipt_orders.supplier_id','=','m_sp.id')
                                                    ->selectRaw('SUM(tx_receipt_orders.invoice_amount) as tot_amount_more_3month')
                                                    ->whereNotIn('tx_receipt_orders.id', function ($q01) {
                                                        // buang RO yg PV detail berstatus full payment dan aktif
                                                        $q01->select('receipt_order_id')
                                                        ->from('tx_payment_voucher_invoices')
                                                        ->whereIn('payment_voucher_id', function ($q02) {
                                                            // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                            $q02->select('id')
                                                            ->from('tx_payment_vouchers')
                                                            ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                            ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                            ->where('active','=','Y');
                                                        })
                                                        ->where('is_full_payment','=','Y')
                                                        ->where('active','=','Y');
                                                    })
                                                    ->whereRaw('tx_receipt_orders.receipt_date<=\''.date_format($more_3month,"Y-m-d").'\'')
                                                    ->whereRaw('tx_receipt_orders.receipt_no NOT LIKE \'%Draft%\'')
                                                    ->where([
                                                        'tx_receipt_orders.supplier_id'=>$ro->supplier_id,
                                                        'tx_receipt_orders.branch_id'=>$branch->id,
                                                        'tx_receipt_orders.active'=>'Y',
                                                    ])
                                                    ->groupBy('m_sp.id')
                                                    ->groupBy('m_sp.name')
                                                    ->first();

                                                    $notPaidAmount = \App\Models\Tx_payment_voucher_invoice::where([
                                                        'is_full_payment'=>'N',
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereIn('receipt_order_id', function ($q01) use ($ro,$branch,$more_3month) {
                                                        $q01->select('id')
                                                        ->from('tx_receipt_orders')
                                                        ->whereNotIn('id', function ($q02) {
                                                            // buang RO yg PV detail berstatus full payment dan aktif
                                                            $q02->select('receipt_order_id')
                                                            ->from('tx_payment_voucher_invoices')
                                                            ->whereIn('payment_voucher_id', function ($q03) {
                                                                // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                                $q03->select('id')
                                                                ->from('tx_payment_vouchers')
                                                                ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                                ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                                ->where('active','=','Y');
                                                            })
                                                            ->where('is_full_payment','=','Y')
                                                            ->where('active','=','Y');
                                                        })
                                                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                                                        ->whereRaw('receipt_date<=\''.date_format($more_3month,"Y-m-d").'\'')
                                                        ->where([
                                                            'supplier_id'=>$ro->supplier_id,
                                                            'branch_id'=>$branch->id,
                                                            'active'=>'Y',
                                                        ]);
                                                    })
                                                    ->sum('total_payment');
                                                @endphp
                                                @if ($q_tx_ro_more_3month)
                                                    {{ number_format(($q_tx_ro_more_3month->tot_amount_more_3month-$notPaidAmount),0,'.',',') }}
                                                    @php
                                                        $totalMore3MonthAmount += ($q_tx_ro_more_3month->tot_amount_more_3month-$notPaidAmount);
                                                    @endphp
                                                @else
                                                    {{ 0 }}
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                @php
                                                    $notPaidAmount = \App\Models\Tx_payment_voucher_invoice::where([
                                                        'is_full_payment'=>'N',
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereIn('receipt_order_id', function ($q01) use ($ro,$branch) {
                                                        $q01->select('id')
                                                        ->from('tx_receipt_orders')
                                                        ->whereNotIn('id', function ($q02) {
                                                            // buang RO yg PV detail berstatus full payment dan aktif
                                                            $q02->select('receipt_order_id')
                                                            ->from('tx_payment_voucher_invoices')
                                                            ->whereIn('payment_voucher_id', function ($q03) {
                                                                // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                                                $q03->select('id')
                                                                ->from('tx_payment_vouchers')
                                                                ->whereRaw('payment_voucher_no NOT LIKE \'%Draft%\'')
                                                                ->whereRaw('(approved_by IS NOT NULL OR canceled_by IS NOT NULL)')
                                                                ->where('active','=','Y');
                                                            })
                                                            ->where('is_full_payment','=','Y')
                                                            ->where('active','=','Y');
                                                        })
                                                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                                                        ->where([
                                                            'supplier_id'=>$ro->supplier_id,
                                                            'branch_id'=>$branch->id,
                                                            'active'=>'Y',
                                                        ]);
                                                    })
                                                    ->sum('total_payment');
                                                    $invoice_amount = $ro->tot_amount-$notPaidAmount;
                                                @endphp
                                                {{ number_format($invoice_amount,0,'.',',') }}
                                                @php
                                                    $totalAmount += $invoice_amount;
                                                @endphp
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="text-align: center;font-weight:700;">TOTAL</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalThisMonthAmount,0,'.',',') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalLastMonthAmount,0,'.',',') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalLast2MonthAmount,0,'.',',') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalLast3MonthAmount,0,'.',',') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalMore3MonthAmount,0,'.',',') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalAmount,0,'.',',') }}</td>
                                </tr>
                            </tbody>
                        </table>
                        @endisset
                    </div>
                </div>
            </div>
            <input type="hidden" name="view_mode" id="view_mode">
        </form>
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
            if(!confirm("Data for Report will be saved as Excel.\nContinue?")){
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
