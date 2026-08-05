<?php

namespace App\Exports\report;

use App\Models\Mst_company;
use App\Models\Mst_customer;
use App\Models\Mst_global;
use App\Models\Mst_supplier;
use App\Models\Tx_acceptance_plan_per_invoice;
use App\Models\Tx_acceptance_plan;
use App\Models\Tx_cash_flow;
use App\Models\Tx_payment_plan_per_rc_order;
use App\Models\Tx_payment_plan;
use DateInterval;
use DateTime;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportCashFlowExport implements FromView, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $period;
    protected $bank_id;
    protected $monthDays;
    protected $daysInMonth = [31,28,31,30,31,30,31,31,30,31,30,31];
    protected $MonthName = [
        'JANUARI',
        'FEBRUARI',
        'MARET',
        'APRIL',
        'MEI',
        'JUNI',
        'JULI',
        'AGUSTUS',
        'SEPTEMBER',
        'OKTOBER',
        'NOPEMBER',
        'DESEMBER'];

    public function __construct($period, $bank_id)
    {
        $this->period = $period;
        $this->bank_id = $bank_id;
    }

    public function view(): View
    {
        $startDateTimeObj = new DateTime('now');
        $start_datetime = $startDateTimeObj->format('Y-m-d H:i:s');
        $cashFlowRecords = [];
        $delRows = [];

        // delete last report by opener ID
        $delCashFlow = Tx_cash_flow::where('bank_id', $this->bank_id)
        ->where(function($query) {
            $query->where('created_by', Auth::user()->id)
            ->orWhereNull('created_by');
        })
        ->delete();
        // delete last report by opener ID - end

        $period = explode("-", $this->period);
        $monthPadded = sprintf('%02d', $period[0]);
        $year = $period[1];
        $stringTanggal = "{$year}-{$monthPadded}-01";

        $randomString = Str::random(6);
        if ($this->isLeapYear($period[1]) && $period[0]==2) {
            $this->monthDays = 29;
        } else {
            $this->monthDays = $this->daysInMonth[$period[0]-1];
        }

        $qCurrency = Mst_global::select('id', 'title_ind')->find(3);

        $companyName = Mst_company::where('id', 1)->value('name') ?? '';

        $qPaymentPlan = Tx_payment_plan::with('bank:id,coa_name')
        ->where([
            'payment_month' => $stringTanggal,
            'bank_id' => $this->bank_id,
            'is_draft' => 'N',
            'active' => 'Y',
        ])
        ->first();
        if ($qPaymentPlan){
            $rowInXls = 1;

            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '15',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ];
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 2,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => $qPaymentPlan->bank->coa_name,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '15',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'left',
            ];

            // $rowInXls++;    //2
            // $insRptCashFlow = Tx_cash_flow::create([
            //     'report_code' => $randomString,
            //     'row_number' => $rowInXls,
            //     'col_number' => 2,
            //     'period' => $stringTanggal,
            //     'bank_id' => $this->bank_id,
            //     'cell_values' => 'x',
            //     // 'cell_values' => 'PAJAK',
            //     'f_color' => 'red',
            //     'b_color' => '#ffffff',
            //     'font_size' => '12',
            //     'font_weight' => '700',
            //     'font_style' => 'normal',
            //     'text_align' => 'left',
            // ]);

            $rowInXls++;    //3
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 2,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => 'SALDO AWAL',
                'f_color' => '#000000',
                'b_color' => '#dbdbdb',
                'font_size' => '12',
                'font_weight' => '300',
                'font_style' => 'normal',
                'text_align' => 'left',
            ];
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 3,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => number_format($qPaymentPlan->beginning_balance, 0, "", ""),
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'right',
            ];
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 3+$this->monthDays,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => number_format($qPaymentPlan->beginning_balance, 0, "", ""),
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '300',
                'font_style' => 'normal',
                'text_align' => 'right',
            ];

            $rowInXls++;    //4
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => '01-'.$this->MonthName[$period[0]-1].'-'.$period[1],
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ];
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 2,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => number_format($qPaymentPlan->beginning_balance, 0, "", ""),
                'f_color' => '#000000',
                'b_color' => '#dbdbdb',
                'font_size' => '12',
                'font_weight' => '300',
                'font_style' => 'normal',
                'text_align' => 'right',
            ];

            // empty row
            $rowInXls++;    //5
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ];
            // empty row

            // customers - x
            $startCustomerDateTimeObj = new DateTime('now');
            $startCustomer_datetime = $startCustomerDateTimeObj->format('Y-m-d H:i:s');

            $dateObjectNow = new DateTime($stringTanggal);
            $dateObjectNext = new DateTime($stringTanggal);
            // Menambahkan 1 bulan (P = Period, 1 = Angka, M = Month)
            $dateObjectNext->add(new DateInterval('P1M'));

            $qPlanCust = Tx_acceptance_plan::where('acceptance_month', $stringTanggal)
            ->where('bank_id', $this->bank_id)
            ->where('is_draft', 'N')
            ->where('active', 'Y')
            ->first();

            // Ambil semua data sekaligus
            $allCustomerPayments = Tx_acceptance_plan_per_invoice::select([
                    'customer_id', 'plan_date', 'payment_date', 
                    'plan_accept', 'payment_total', 'payment_receipt_no'
                ])
                ->where('acceptance_plan_id', $qPlanCust?->id??0)
                ->where('active', 'Y')
                ->get()
                ->groupBy('customer_id');

            $qCustomers = Mst_customer::select('id', 'customer_unique_code', 'name')
            ->where('active', 'Y')
            ->whereExists(function($q1) use($dateObjectNow, $dateObjectNext, $qPlanCust){
                $q1->select(DB::raw(1))
                ->from('tx_acceptance_plan_per_invoices AS tx_appi')
                ->where('tx_appi.acceptance_plan_id', $qPlanCust?->id??0)
                ->whereColumn('tx_appi.customer_id', 'mst_customers.id')
                ->whereRaw('((tx_appi.plan_date>=\''.$dateObjectNow->format('Y-m-d').'\' AND tx_appi.plan_date<\''.$dateObjectNext->format('Y-m-d').'\') OR 
                    (tx_appi.payment_date>=\''.$dateObjectNow->format('Y-m-d').'\' AND tx_appi.payment_date<\''.$dateObjectNext->format('Y-m-d').'\'))')
                ->where('tx_appi.active', 'Y');
            })
            ->orderBy('name', 'asc')
            ->get();
            foreach ($qCustomers as $customer) {
                $rowInXls++;    //x

                $cashFlowRecords[] = [
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 2,
                    'period' => $stringTanggal,
                    'bank_id' => $this->bank_id,
                    'cell_values' => strtoupper($customer->customer_unique_code.' - '.$customer->name),
                    'f_color' => '#000000',
                    'b_color' => '#acb9ca',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ];

                $dayToValidateMonth = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]);
                $totalPerRow = 0;
                $lastCol = 0;
                for ($iDay=1;$iDay<=$this->monthDays;$iDay++){
                    $dayToValidate = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-'.(strlen($iDay)==1?'0'.$iDay:$iDay);

                    $customerPayments = $allCustomerPayments->get($customer->id) ?? collect();
                    $totalPlanPayment = $customerPayments->where('plan_date', $dayToValidate)
                        ->whereNull('payment_receipt_no')
                        ->sum('plan_accept');
                    $totalActualPayment = $customerPayments->where('payment_date', $dayToValidate)
                        ->whereNotNull('payment_receipt_no')
                        ->sum('payment_total');

                    $cashFlowRecords[] = [
                        'report_code' => $randomString,
                        'row_number' => $rowInXls,
                        'col_number' => 2 + $iDay,
                        'period' => $stringTanggal,
                        'bank_id' => $this->bank_id,
                        'cell_values' => $totalActualPayment>0?number_format($totalActualPayment, 0, "", ""):number_format($totalPlanPayment, 0, "", ""),
                        'f_color' => '#000000',
                        'b_color' => $totalActualPayment>0?'#8ea9db':'#ffffff',
                        'font_size' => '12',
                        'font_weight' => '300',
                        'font_style' => 'normal',
                        'text_align' => 'right',
                    ];

                    $totalPerRow += $totalActualPayment>0?$totalActualPayment:$totalPlanPayment;
                    $lastCol = 2 + $iDay;
                }

                $cashFlowRecords[] = [
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $stringTanggal,
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($totalPerRow, 0, "", ""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ];

                if ($totalPerRow==0){
                    $delRows[] = $rowInXls;
                }
            }
            // customers - x

            // empty row
            $rowInXls++;    //5
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ];
            // empty row

            // cash flow GJ+LJ (COA Bank 112x, COA Petty Cash 111x, COA Capital 31xx )
            $dayToValidateMonth = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]);

            // GJ
            $qGJd = DB::table('tx_general_journal_details AS tx_gjd')
            ->leftJoin('tx_general_journals AS tx_gj', function($join) {
                $join->on('tx_gjd.general_journal_id', '=', 'tx_gj.id')
                ->where('tx_gj.is_draft', '=', 'N')
                ->where('tx_gj.active', '=', 'Y');
            })
            ->leftJoin('mst_coas AS msc', function($join) {
                $join->on('tx_gjd.coa_id', '=', 'msc.id')
                ->where('msc.is_draft', '=', 'N')
                ->where('msc.active', '=', 'Y');
            })
            ->select(
                'msc.coa_code_complete AS coa_code_complete',
                'msc.coa_name AS coa_name',
            )
            ->whereExists(function($q) {
                // kumpulkan semua data jurnal detil sesuai COA yg dipilih di report CF dalam sebulan
                $q->from('tx_general_journal_details AS tx_gjd_1')
                ->select(DB::raw(1))
                ->whereColumn('tx_gjd_1.general_journal_id', 'tx_gjd.general_journal_id')
                ->where('tx_gjd_1.coa_id', '=', $this->bank_id)
                ->where('tx_gjd_1.active', 'Y');
            })
            ->whereRaw('(tx_gj.general_journal_date>=\''.$dateObjectNow->format('Y-m-d').'\' AND tx_gj.general_journal_date<\''.$dateObjectNext->format('Y-m-d').'\')')
            ->where('msc.id', '<>', $this->bank_id)
            ->where(function($q){
                $q->where('msc.coa_code_complete', 'LIKE', '111%')
                ->orWhere('msc.coa_code_complete', 'LIKE', '112%')
                ->orWhere('msc.coa_code_complete', 'LIKE', '31%');
            })
            ->where('tx_gjd.active', '=', 'Y')
            ->groupBy('msc.coa_code_complete', 'msc.coa_name');

            // LJ
            $qLJd = DB::table('tx_lokal_journal_details AS tx_ljd')
            ->leftJoin('tx_lokal_journals AS tx_lj', function($join) {
                $join->on('tx_ljd.lokal_journal_id', '=', 'tx_lj.id')
                ->where('tx_lj.is_draft', '=', 'N')
                ->where('tx_lj.active', '=', 'Y');
            })
            ->leftJoin('mst_coas AS msc', function($join) {
                $join->on('tx_ljd.coa_id', '=', 'msc.id')
                ->where('msc.is_draft', '=', 'N')
                ->where('msc.active', '=', 'Y');
            })
            ->select(
                'msc.coa_code_complete AS coa_code_complete',
                'msc.coa_name AS coa_name',
            )
            ->whereExists(function($q) {
                // kumpulkan semua data jurnal detil sesuai COA yg dipilih di report CF dalam sebulan
                $q->from('tx_lokal_journal_details AS tx_ljd_1')
                ->select(DB::raw(1))
                ->whereColumn('tx_ljd_1.lokal_journal_id', 'tx_ljd.lokal_journal_id')
                ->where('tx_ljd_1.coa_id', '=', $this->bank_id)
                ->where('tx_ljd_1.active', 'Y');
            })
            ->whereRaw('(tx_lj.general_journal_date>=\''.$dateObjectNow->format('Y-m-d').'\' AND tx_lj.general_journal_date<\''.$dateObjectNext->format('Y-m-d').'\')')
            ->where('msc.id', '<>', $this->bank_id)
            ->where(function($q){
                $q->where('msc.coa_code_complete', 'LIKE', '111%')
                ->orWhere('msc.coa_code_complete', 'LIKE', '112%')
                ->orWhere('msc.coa_code_complete', 'LIKE', '31%');
            })
            ->where('tx_ljd.active', '=', 'Y')
            ->groupBy('msc.coa_code_complete', 'msc.coa_name');

            $unionQuery = $qGJd->unionAll($qLJd);
            $qJournal01 = DB::table(DB::raw("({$unionQuery->toSql()}) as combined_transactions"))
            ->mergeBindings($unionQuery) // CRITICAL: Wajib untuk mengamankan data binding PDO dari kedua query
            ->select('coa_code_complete', 'coa_name')
            ->groupBy('coa_code_complete', 'coa_name')
            ->get();
            foreach($qJournal01 as $j01){
                $rowInXls++;

                // journal desc
                $cashFlowRecords[] = [
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 2,
                    'period' => $stringTanggal,
                    'bank_id' => $this->bank_id,
                    'cell_values' => strtoupper($j01->coa_code_complete.' - '.$j01->coa_name),
                    'f_color' => '#000000',
                    'b_color' => '#ffc0cb',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ];

                $totalPerRow = 0;
                $lastCol = 0;
                for ($iDay=1;$iDay<=$this->monthDays;$iDay++){
                    $dayToValidate = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-'.(strlen($iDay)==1?'0'.$iDay:$iDay);

                    $debitGJ = 0;
                    $kreditGJ = 0;

                    $qGJd_val = DB::table('tx_general_journal_details AS tx_gjd')
                    ->leftJoin('tx_general_journals AS tx_gj', function($join) {
                        $join->on('tx_gjd.general_journal_id', '=', 'tx_gj.id')
                        ->where('tx_gj.is_draft', '=', 'N')
                        ->where('tx_gj.active', '=', 'Y');
                    })
                    ->leftJoin('mst_coas AS msc', function($join) {
                        $join->on('tx_gjd.coa_id', '=', 'msc.id')
                        ->where('msc.is_draft', '=', 'N')
                        ->where('msc.active', '=', 'Y');
                    })
                    ->select(
                        'tx_gjd.debit AS debit',
                        'tx_gjd.kredit AS kredit',
                    )
                    ->whereExists(function($q) {
                        // kumpulkan semua data jurnal detil sesuai COA yg dipilih di report CF dalam sebulan
                        $q->from('tx_general_journal_details AS tx_gjd_1')
                        ->select(DB::raw(1))
                        ->whereColumn('tx_gjd_1.general_journal_id', 'tx_gjd.general_journal_id')
                        ->where('tx_gjd_1.coa_id', '=', $this->bank_id)
                        ->where('tx_gjd_1.active', 'Y');
                    })
                    ->where('tx_gj.general_journal_date', $dayToValidate)
                    ->where('msc.coa_code_complete', $j01->coa_code_complete)
                    ->where('tx_gjd.active', '=', 'Y');

                    $qLJd_val = DB::table('tx_lokal_journal_details AS tx_ljd')
                    ->leftJoin('tx_lokal_journals AS tx_lj', function($join) {
                        $join->on('tx_ljd.lokal_journal_id', '=', 'tx_lj.id')
                        ->where('tx_lj.is_draft', '=', 'N')
                        ->where('tx_lj.active', '=', 'Y');
                    })
                    ->leftJoin('mst_coas AS msc', function($join) {
                        $join->on('tx_ljd.coa_id', '=', 'msc.id')
                        ->where('msc.is_draft', '=', 'N')
                        ->where('msc.active', '=', 'Y');
                    })
                    ->select(
                        'tx_ljd.debit AS debit',
                        'tx_ljd.kredit AS kredit',
                    )
                    ->whereExists(function($q) {
                        // kumpulkan semua data jurnal detil sesuai COA yg dipilih di report CF dalam sebulan
                        $q->from('tx_lokal_journal_details AS tx_ljd_1')
                        ->select(DB::raw(1))
                        ->whereColumn('tx_ljd_1.lokal_journal_id', 'tx_ljd.lokal_journal_id')
                        ->where('tx_ljd_1.coa_id', '=', $this->bank_id)
                        ->where('tx_ljd_1.active', 'Y');
                    })
                    ->where('tx_lj.general_journal_date', $dayToValidate)
                    ->where('msc.coa_code_complete', $j01->coa_code_complete)
                    ->where('tx_ljd.active', '=', 'Y');

                    $unionQuery_val = $qGJd_val->unionAll($qLJd_val);
                    $qJournal01_val = DB::table(DB::raw("({$unionQuery_val->toSql()}) as combined_transactions"))
                    ->mergeBindings($unionQuery_val) // CRITICAL: Wajib untuk mengamankan data binding PDO dari kedua query
                    ->select('debit', 'kredit')
                    ->get();
                    foreach($qJournal01_val as $qJ01val){
                        $debitGJ += $qJ01val->debit?$qJ01val->debit*-1:0;
                        $kreditGJ += $qJ01val->kredit?$qJ01val->kredit:0;
                    }

                    // amount
                    if (($kreditGJ+$debitGJ)!=0){
                        $cashFlowRecords[] = [
                            'report_code' => $randomString,
                            'row_number' => $rowInXls,
                            'col_number' => 2 + $iDay,
                            'period' => $stringTanggal,
                            'bank_id' => $this->bank_id,
                            'cell_values' => number_format($kreditGJ+$debitGJ, 0, "", ""),
                            'f_color' => '#000000',
                            'b_color' => ($kreditGJ+$debitGJ)!=0?'#8ea9db':'#ffffff',
                            'font_size' => '12',
                            'font_weight' => '300',
                            'font_style' => 'normal',
                            'text_align' => 'right',
                        ];
                    }

                    $totalPerRow += ($kreditGJ+$debitGJ);
                    $lastCol = 2 + $iDay;
                }

                $cashFlowRecords[] = [
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $stringTanggal,
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($totalPerRow, 0, "", ""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ];

                if ($totalPerRow==0){
                    $delRows[] = $rowInXls;
                }
            }
            // cash flow GJ+LJ (COA Bank 112x, COA Petty Cash 111x, COA Capital 31xx )

            // empty row
            $rowInXls++;    //5
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ];
            // empty row

            // cash flow GJ+LJ (COA Expense 6x, COA Loans 32x, COA Other Expense 9x, COA Hutang 2x (kecuali 211x))
            $dayToValidateMonth = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]);

            // GJ
            $qGJd = DB::table('tx_general_journal_details AS tx_gjd')
            ->leftJoin('tx_general_journals AS tx_gj', function($join) {
                $join->on('tx_gjd.general_journal_id', '=', 'tx_gj.id')
                ->where('tx_gj.is_draft', '=', 'N')
                ->where('tx_gj.active', '=', 'Y');
            })
            ->leftJoin('mst_coas AS msc', function($join) {
                $join->on('tx_gjd.coa_id', '=', 'msc.id')
                ->where('msc.is_draft', '=', 'N')
                ->where('msc.active', '=', 'Y');
            })
            ->select(
                'msc.coa_code_complete AS coa_code_complete',
                'msc.coa_name AS coa_name',
            )
            ->whereExists(function($q) {
                // kumpulkan semua data jurnal detil sesuai COA yg dipilih di report CF dalam sebulan
                $q->from('tx_general_journal_details AS tx_gjd_1')
                ->select(DB::raw(1))
                ->whereColumn('tx_gjd_1.general_journal_id', 'tx_gjd.general_journal_id')
                ->where('tx_gjd_1.coa_id', '=', $this->bank_id)
                ->where('tx_gjd_1.active', 'Y');
            })
            ->whereRaw('(tx_gj.general_journal_date>=\''.$dateObjectNow->format('Y-m-d').'\' AND tx_gj.general_journal_date<\''.$dateObjectNext->format('Y-m-d').'\')')
            ->where('msc.id', '<>', $this->bank_id)
            ->where(function($q){
                $q->where('msc.coa_code_complete', 'LIKE', '6%')
                ->orWhere('msc.coa_code_complete', 'LIKE', '32%')
                ->orWhere('msc.coa_code_complete', 'LIKE', '9%')
                ->orWhere('msc.coa_code_complete', 'LIKE', '2%');
            })
            ->where('msc.coa_code_complete', 'NOT LIKE', '211%')
            ->where('tx_gjd.active', '=', 'Y')
            ->groupBy('msc.coa_code_complete', 'msc.coa_name');

            // LJ
            $qLJd = DB::table('tx_lokal_journal_details AS tx_ljd')
            ->leftJoin('tx_lokal_journals AS tx_lj', function($join) {
                $join->on('tx_ljd.lokal_journal_id', '=', 'tx_lj.id')
                ->where('tx_lj.is_draft', '=', 'N')
                ->where('tx_lj.active', '=', 'Y');
            })
            ->leftJoin('mst_coas AS msc', function($join) {
                $join->on('tx_ljd.coa_id', '=', 'msc.id')
                ->where('msc.is_draft', '=', 'N')
                ->where('msc.active', '=', 'Y');
            })
            ->select(
                'msc.coa_code_complete AS coa_code_complete',
                'msc.coa_name AS coa_name',
            )
            ->whereExists(function($q) {
                // kumpulkan semua data jurnal detil sesuai COA yg dipilih di report CF dalam sebulan
                $q->from('tx_lokal_journal_details AS tx_ljd_1')
                ->select(DB::raw(1))
                ->whereColumn('tx_ljd_1.lokal_journal_id', 'tx_ljd.lokal_journal_id')
                ->where('tx_ljd_1.coa_id', '=', $this->bank_id)
                ->where('tx_ljd_1.active', 'Y');
            })
            ->whereRaw('(tx_lj.general_journal_date>=\''.$dateObjectNow->format('Y-m-d').'\' AND tx_lj.general_journal_date<\''.$dateObjectNext->format('Y-m-d').'\')')
            ->where('msc.id', '<>', $this->bank_id)
            ->where(function($q){
                $q->where('msc.coa_code_complete', 'LIKE', '6%')
                ->orWhere('msc.coa_code_complete', 'LIKE', '32%')
                ->orWhere('msc.coa_code_complete', 'LIKE', '9%')
                ->orWhere('msc.coa_code_complete', 'LIKE', '2%');
            })
            ->where('msc.coa_code_complete', 'NOT LIKE', '211%')
            ->where('tx_ljd.active', '=', 'Y')
            ->groupBy('msc.coa_code_complete', 'msc.coa_name');

            $unionQuery = $qGJd->unionAll($qLJd);
            $qJournal01 = DB::table(DB::raw("({$unionQuery->toSql()}) as combined_transactions"))
            ->mergeBindings($unionQuery) // CRITICAL: Wajib untuk mengamankan data binding PDO dari kedua query
            ->select('coa_code_complete', 'coa_name')
            ->groupBy('coa_code_complete', 'coa_name')
            ->get();
            foreach($qJournal01 as $j01){
                $rowInXls++;

                // journal desc
                $cashFlowRecords[] = [
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 2,
                    'period' => $stringTanggal,
                    'bank_id' => $this->bank_id,
                    'cell_values' => strtoupper($j01->coa_code_complete.' - '.$j01->coa_name),
                    'f_color' => '#000000',
                    'b_color' => '#c6e0b4',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ];

                $totalPerRow = 0;
                $lastCol = 0;
                for ($iDay=1;$iDay<=$this->monthDays;$iDay++){
                    $dayToValidate = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-'.(strlen($iDay)==1?'0'.$iDay:$iDay);

                    $debitGJ = 0;
                    $kreditGJ = 0;

                    $qGJd_val = DB::table('tx_general_journal_details AS tx_gjd')
                    ->leftJoin('tx_general_journals AS tx_gj', function($join) {
                        $join->on('tx_gjd.general_journal_id', '=', 'tx_gj.id')
                        ->where('tx_gj.is_draft', '=', 'N')
                        ->where('tx_gj.active', '=', 'Y');
                    })
                    ->leftJoin('mst_coas AS msc', function($join) {
                        $join->on('tx_gjd.coa_id', '=', 'msc.id')
                        ->where('msc.is_draft', '=', 'N')
                        ->where('msc.active', '=', 'Y');
                    })
                    ->select(
                        'tx_gjd.debit AS debit',
                        'tx_gjd.kredit AS kredit',
                    )
                    ->whereExists(function($q) {
                        // kumpulkan semua data jurnal detil sesuai COA yg dipilih di report CF dalam sebulan
                        $q->from('tx_general_journal_details AS tx_gjd_1')
                        ->select(DB::raw(1))
                        ->whereColumn('tx_gjd_1.general_journal_id', 'tx_gjd.general_journal_id')
                        ->where('tx_gjd_1.coa_id', $this->bank_id)
                        ->where('tx_gjd_1.active', 'Y');
                    })
                    ->where('tx_gj.general_journal_date', $dayToValidate)
                    ->where('msc.coa_code_complete', $j01->coa_code_complete)
                    ->where('tx_gjd.active', '=', 'Y');

                    $qLJd_val = DB::table('tx_lokal_journal_details AS tx_ljd')
                    ->leftJoin('tx_lokal_journals AS tx_lj', function($join) {
                        $join->on('tx_ljd.lokal_journal_id', '=', 'tx_lj.id')
                        ->where('tx_lj.is_draft', '=', 'N')
                        ->where('tx_lj.active', '=', 'Y');
                    })
                    ->leftJoin('mst_coas AS msc', function($join) {
                        $join->on('tx_ljd.coa_id', '=', 'msc.id')
                        ->where('msc.is_draft', '=', 'N')
                        ->where('msc.active', '=', 'Y');
                    })
                    ->select(
                        'tx_ljd.debit AS debit',
                        'tx_ljd.kredit AS kredit',
                    )
                    ->whereExists(function($q) {
                        // kumpulkan semua data jurnal detil sesuai COA yg dipilih di report CF dalam sebulan
                        $q->from('tx_lokal_journal_details AS tx_ljd_1')
                        ->select(DB::raw(1))
                        ->whereColumn('tx_ljd_1.lokal_journal_id', 'tx_ljd.lokal_journal_id')
                        ->where('tx_ljd_1.coa_id', '=', $this->bank_id)
                        ->where('tx_ljd_1.active', 'Y');
                    })
                    ->where('tx_lj.general_journal_date', $dayToValidate)
                    ->where('msc.coa_code_complete', $j01->coa_code_complete)
                    ->where('tx_ljd.active', '=', 'Y');

                    $unionQuery_val = $qGJd_val->unionAll($qLJd_val);
                    $qJournal01_val = DB::table(DB::raw("({$unionQuery_val->toSql()}) as combined_transactions"))
                    ->mergeBindings($unionQuery_val) // CRITICAL: Wajib untuk mengamankan data binding PDO dari kedua query
                    ->select('debit', 'kredit')
                    ->get();
                    foreach($qJournal01_val as $qJ01val){
                        $debitGJ += $qJ01val->debit?$qJ01val->debit*-1:0;
                        $kreditGJ += $qJ01val->kredit?$qJ01val->kredit:0;
                    }

                    // amount
                    if (($kreditGJ+$debitGJ)!=0){
                        $cashFlowRecords[] = [
                            'report_code' => $randomString,
                            'row_number' => $rowInXls,
                            'col_number' => 2 + $iDay,
                            'period' => $stringTanggal,
                            'bank_id' => $this->bank_id,
                            'cell_values' => number_format($kreditGJ+$debitGJ, 0, "", ""),
                            'f_color' => '#000000',
                            'b_color' => ($kreditGJ+$debitGJ)!=0?'#8ea9db':'#ffffff',
                            'font_size' => '12',
                            'font_weight' => '300',
                            'font_style' => 'normal',
                            'text_align' => 'right',
                        ];
                    }

                    $totalPerRow += ($kreditGJ+$debitGJ);
                    $lastCol = 2 + $iDay;
                }

                $cashFlowRecords[] = [
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $stringTanggal,
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($totalPerRow, 0, "", ""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ];

                if ($totalPerRow==0){
                    $delRows[] = $rowInXls;
                }
            }
            // cash flow GJ+LJ (COA Expense 6x, COA Loans 32x, COA Other Expense 9x, COA Hutang 2x (kecuali 211x))

            // empty row
            $rowInXls++;    //5
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ];
            // empty row

            // foreach (array_chunk($cashFlowRecords, 500) as $chunk) {
            //     Tx_cash_flow::insert($chunk);
            // }

            // suppliers - x
            // Ekstrak format tanggal di luar query agar lebih rapi
            $startDate = $dateObjectNow->format('Y-m-d');
            $endDate   = $dateObjectNext->format('Y-m-d');

            // Ambil semua data sekaligus
            $allSupplierPayments = Tx_payment_plan_per_rc_order::where('payment_plan_id', $qPaymentPlan->id)
                ->where('active', 'Y')
                ->get()
                ->groupBy('supplier_id');

            $qSuppliers = Mst_supplier::where('active', 'Y')
                ->whereExists(function($q) use($qPaymentPlan, $startDate, $endDate) {
                    $q->selectRaw(1)
                    ->from('tx_payment_plan_per_rc_orders')
                    ->whereColumn('supplier_id', 'mst_suppliers.id')
                    ->where('payment_plan_id', $qPaymentPlan->id)
                    ->where('active', 'Y')
                    ->where(function ($query) use ($startDate, $endDate) {
                        // Group kondisi OR
                        $query->where(function ($qPlan) use ($startDate, $endDate) {
                            $qPlan->where('plan_date', '>=', $startDate)
                                    ->where('plan_date', '<', $endDate);
                        })->orWhere(function ($qActual) use ($startDate, $endDate) {
                            $qActual->where('actual_date', '>=', $startDate)
                                    ->where('actual_date', '<', $endDate);
                        });
                    });
                })
                ->orderBy('name', 'ASC')
                ->get();
            foreach($qSuppliers as $qS){
                $rowInXls++;    //x

                // supplier name
                $cashFlowRecords[] = [
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 2,
                    'period' => $stringTanggal,
                    'bank_id' => $this->bank_id,
                    'cell_values' => strtoupper($qS->supplier_code.' - '.$qS->entity_type->title_ind.' '.$qS->name),
                    'f_color' => '#000000',
                    'b_color' => '#ffe699',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ];
                $dayToValidateMonth = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]);

                $totalPerRow = 0;
                $lastCol = 0;
                for ($iDay=1;$iDay<=$this->monthDays;$iDay++){
                    $dayToValidate = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-'.(strlen($iDay)==1?'0'.$iDay:$iDay);

                    $supplierPayments = $allSupplierPayments->get($qS->id) ?? collect();
                    $totalPlanPayment = $supplierPayments->where('plan_date', $dayToValidate)
                        ->where('is_pv_approved', 'N')
                        ->sum('plan_pay');
                    $totalActualPayment = $supplierPayments->where('actual_date', $dayToValidate)
                        ->where('is_pv_approved', 'Y')
                        ->sum('actual_payment');

                    $cashFlowRecords[] = [
                        'report_code' => $randomString,
                        'row_number' => $rowInXls,
                        'col_number' => 2 + $iDay,
                        'period' => $stringTanggal,
                        'bank_id' => $this->bank_id,
                        'cell_values' => $totalActualPayment>0?number_format($totalActualPayment*-1, 0, "", ""):number_format($totalPlanPayment*-1, 0, "", ""),
                        'f_color' => '#000000',
                        'b_color' => $totalActualPayment>0?'#8ea9db':'#ffffff',
                        'font_size' => '12',
                        'font_weight' => '300',
                        'font_style' => 'normal',
                        'text_align' => 'right',
                    ];

                    $totalPerRow += $totalActualPayment>0?($totalActualPayment*-1):($totalPlanPayment*-1);
                    $lastCol = 2 + $iDay;
                }

                $cashFlowRecords[] = [
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $stringTanggal,
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($totalPerRow, 0, "", ""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ];

                if ($totalPerRow==0){
                    $delRows[] = $rowInXls;
                }
            }
            // suppliers - x

            // empty row
            $rowInXls++;    //x
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ];
            // empty row

            // empty row
            $rowInXls++;    //x
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ];
            // empty row

            // total row
            $rowInXls++;    //x
            $cashFlowRecords[] = [
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 2,
                'period' => $stringTanggal,
                'bank_id' => $this->bank_id,
                'cell_values' => 'T O T A L',
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ];

            // insert semua data
            foreach (array_chunk($cashFlowRecords, 500) as $chunk) {
                Tx_cash_flow::insert($chunk);
            }

            $lastTotPerDay = 0;
            for ($iDay=1;$iDay<=$this->monthDays;$iDay++){
                $totSumPerDay = 0;
                $qSumPerDay = Tx_cash_flow::selectRaw('SUM(CONVERT(cell_values, DECIMAL)) as total_per_day')
                ->where([
                    'report_code' => $randomString,
                    'col_number' => 2 + $iDay,
                ])
                ->first();
                if ($qSumPerDay){
                    $totSumPerDay = $qSumPerDay->total_per_day;
                }
                $insRptCashFlow = Tx_cash_flow::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 2 + $iDay,
                    'period' => $stringTanggal,
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($totSumPerDay+$lastTotPerDay, 0, "", ""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ]);
                $lastTotPerDay = ($totSumPerDay+$lastTotPerDay);
            }
            $qSumPerMonth = Tx_cash_flow::selectRaw('SUM(CONVERT(cell_values, DECIMAL)) as total_per_month')
            ->where([
                'report_code' => $randomString,
                'col_number' => 3+$this->monthDays,
            ])
            ->first();
            if ($qSumPerMonth->total_per_month!=0){
                $insRptCashFlow = Tx_cash_flow::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 3+$this->monthDays,
                    'period' => $stringTanggal,
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($lastTotPerDay, 0, "", ""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ]);
            }
            // total row - end

            // Log::debug($delRows);
            $collection_delRows = collect($delRows);
            if ($collection_delRows->isNotEmpty()) {
                // hapus yg total nya 0
                $delCashFlow = Tx_cash_flow::where('report_code', $randomString)
                ->whereIn('row_number', $collection_delRows)
                ->delete();
                // hapus yg total nya 0
            }

            // update report opener
            $updCashFlow = Tx_cash_flow::where([
                'report_code' => $randomString,
            ])
            ->update([
                'created_by' => Auth::user()->id,
            ]);
            // update report opener - end

            $data = [
                'period' => $this->period,
                'bank_id' => $this->bank_id,
                'monthDays' => $this->monthDays,
                'qCurrency' => $qCurrency,
                'randomString' => $randomString,
                'MonthName' => $this->MonthName,
                'companyName' => $companyName,
                'start_datetime' => $start_datetime,
                'startCustomer_datetime' => $startCustomer_datetime,
                // 'startGjLj01_datetime' => $startGjLj01_datetime,
                // 'startGjLj02_datetime' => $startGjLj02_datetime,
                // 'startSupplier_datetime' => $startSupplier_datetime,
            ];
            return view('rpt.cash-flow.cash-flow-xlsx', $data);
        }else{
            $data = [
                'period' => $this->period,
                'bank_id' => $this->bank_id,
                'monthDays' => $this->monthDays,
                'qCurrency' => $qCurrency,
                'randomString' => $randomString,
                'MonthName' => $this->MonthName,
            ];
            return view('rpt.cash-flow.cash-flow-empty-xlsx', $data);
        }    
    }

    public function styles(Worksheet $sheet)
    {
        $bgHeaderRange = '';
        switch ($this->monthDays) {
            case 28:
                $bgHeaderRange = 'AE';
                break;
            case 29:
                $bgHeaderRange = 'AF';
                break;
            case 30:
                $bgHeaderRange = 'AG';
                break;
            default:
                $bgHeaderRange = 'AH';
        }

        // get highest row info
        $lastHighestRow = $sheet->getHighestRow();
        // $sheet->setCellValue('D'.$lastHighestRow, "TOTAL");
        // $sheet->setCellValue('M'.$lastHighestRow,'=SUM(M7:M'.($lastHighestRow-1).')');

        // set background color
        $sheet->getStyle('A3:B3')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => '0070c0'],
        ]);
        $sheet->getStyle('C3:'.$bgHeaderRange.'3')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => 'ccffff'],
        ]);

        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        // set center
        $sheet->getStyle('A3:'.$bgHeaderRange.'3')->applyFromArray($styleArray);
        $sheet->getStyle('A')->applyFromArray($styleArray);

        // set border
        $sheet->getStyle('A3:'.$bgHeaderRange.$lastHighestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ]
        ]);

        // set text style
        return [
            // Style the first row as bold text.
            // 1 => ['font' => ['bold' => true]],
            // 2 => ['font' => ['bold' => true]],
            // 3 => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
            // '3' => ['font' => ['bold' => true]],
            // 'A3' => [
            //     'font' => [
            //         'bold' => true,
            //         'size' => 16,
            //     ]
            // ],
            // 'A1' => ['font' => ['bold' => true]],
            // 'N4' => ['font' => ['bold' => true]],
            // 'A4' => ['font' => ['bold' => true]],
            'A3:'.$bgHeaderRange.'3' => ['font' => ['bold' => true]],
            'A3:B3' => ['font' => [
                'color' => [
                    'rgb' => 'ffffff',
                ]
            ]],
            'C3:'.$bgHeaderRange.'3' => ['font' => [
                'color' => [
                    'rgb' => '0000000',
                ]
            ]],
            // 'D'.$lastHighestRow => ['font' => ['bold' => true]],
            // 'M'.$lastHighestRow => ['font' => ['bold' => true]],

            // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            // 'B' => NumberFormat::FORMAT_NUMBER,
            // 'C' => NumberFormat::FORMAT_NUMBER,
            // 'D' => NumberFormat::FORMAT_NUMBER,
            // 'E' => NumberFormat::FORMAT_NUMBER,
            // 'F' => NumberFormat::FORMAT_NUMBER,
            // 'G' => NumberFormat::FORMAT_NUMBER,
            // 'H' => NumberFormat::FORMAT_NUMBER,
            // 'I' => NumberFormat::FORMAT_NUMBER,
            // 'J' => NumberFormat::FORMAT_NUMBER,
            // 'K' => NumberFormat::FORMAT_NUMBER,
            // 'L' => NumberFormat::FORMAT_NUMBER,
            // 'M' => NumberFormat::FORMAT_NUMBER,
            // 'N' => NumberFormat::FORMAT_NUMBER,
            // 'O' => NumberFormat::FORMAT_NUMBER,
            // 'P' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    private function isLeapYear($year) {
        if (($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0)) {
            return true;
        } else {
            return false;
        }
    }
}
