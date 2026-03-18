<?php

namespace App\Exports\report;

use DateTime;
use App\Models\Mst_global;
use App\Models\Tx_invoice;
use App\Models\Mst_company;
use App\Models\Tx_kwitansi;
use Illuminate\Support\Str;
use App\Models\Mst_customer;
use App\Models\Mst_supplier;
use App\Models\Tx_cash_flow_2026;
use App\Models\Tx_nota_retur;
use App\Models\Tx_payment_plan;
use App\Models\Tx_payment_voucher;
use App\Models\Tx_tagihan_supplier;
use App\Models\V_cash_flow_journal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_nota_retur_non_tax;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Tx_payment_receipt_invoice;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ReportCashFlowDbgExport implements FromView, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $period;
    protected $bank_id;
    protected $monthDays;
    protected $daysInMonth = [31,28,31,30,31,30,31,31,30,31,30,31];
    protected $MonthName = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOPEMBER','DESEMBER'];

    public function __construct($period, $bank_id)
    {
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);

        $this->period = $period;
        $this->bank_id = $bank_id;
    }

    public function view(): View
    {
        $startDateTimeObj = new DateTime('now');
        $start_datetime = $startDateTimeObj->format('Y-m-d H:i:s');

        // delete last report by opener ID
        $updCashFlow = Tx_cash_flow_2026::where(function($query) {
            $query->where('created_by', Auth::user()->id)
            ->orWhere('created_by', 'IS', null);
        })
        ->delete();
        // delete last report by opener ID - end

        $period = explode("-", $this->period);
        $randomString = Str::random(6);
        if ($this->isLeapYear($period[1]) && $period[0]==2) {
            $this->monthDays = 29;
        } else {
            $this->monthDays = $this->daysInMonth[$period[0]-1];
        }

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $company = Mst_company::whereRaw('id=1')
        ->first();
        $companyName = $company?$company->name:'';

        $qPaymentPlan = Tx_payment_plan::where([
            'payment_month' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
            'bank_id' => $this->bank_id,
            'is_draft' => 'N',
            'active' => 'Y',
        ])
        ->first();
        if ($qPaymentPlan){
            $rowInXls = 1;

            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '15',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ]);
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 2,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => $qPaymentPlan->bank->coa_name,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '15',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'left',
            ]);

            // $rowInXls++;    //2
            // $insRptCashFlow = Tx_cash_flow_2026::create([
            //     'report_code' => $randomString,
            //     'row_number' => $rowInXls,
            //     'col_number' => 2,
            //     'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
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
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 2,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => 'SALDO AWAL',
                'f_color' => '#000000',
                'b_color' => '#dbdbdb',
                'font_size' => '12',
                'font_weight' => '300',
                'font_style' => 'normal',
                'text_align' => 'left',
            ]);
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 3,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => number_format($qPaymentPlan->beginning_balance,0,"",""),
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'right',
            ]);
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 3+$this->monthDays,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => number_format($qPaymentPlan->beginning_balance,0,"",""),
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '300',
                'font_style' => 'normal',
                'text_align' => 'right',
            ]);

            $rowInXls++;    //4
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => '01-'.$this->MonthName[$period[0]-1].'-'.$period[1],
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ]);
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 2,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => number_format($qPaymentPlan->beginning_balance,0,"",""),
                'f_color' => '#000000',
                'b_color' => '#dbdbdb',
                'font_size' => '12',
                'font_weight' => '300',
                'font_style' => 'normal',
                'text_align' => 'right',
            ]);

            // empty row
            $rowInXls++;    //5
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ]);
            // empty row

            // customers - x
            $startCustomerDateTimeObj = new DateTime('now');
            $startCustomer_datetime = $startCustomerDateTimeObj->format('Y-m-d H:i:s');

            $qCustomers = Mst_customer::where(function($q) use($period){
                $q->whereIn('id', function($query) use($period) {
                    $query->select('customer_id')
                    ->from('tx_invoices')
                    ->whereRaw('DATE_FORMAT(invoice_date, "%Y-%m")=\''.$period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'\'')
                    ->where([
                        'payment_to_id' => $this->bank_id,
                        'is_draft' => 'N',
                        'active' => 'Y',
                    ]);
                })
                ->orWhereIn('id', function($query) use($period) {
                    $query->select('customer_id')
                    ->from('tx_kwitansis')
                    ->whereRaw('DATE_FORMAT(kwitansi_date, "%Y-%m")=\''.$period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'\'')
                    ->where([
                        'payment_to_id' => $this->bank_id,
                        'is_draft' => 'N',
                        'active' => 'Y',
                    ]);
                })
                ->orWhereIn('id', function($query) use($period) {
                    $query->select('customer_id')
                    ->from('tx_payment_receipts')
                    ->whereRaw('payment_receipt_no IS NOT null')
                    ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'\'')
                    ->where([
                        'coa_id' => $this->bank_id,
                        'is_draft' => 'N',
                        'active' => 'Y',
                    ]);
                });
            })
            ->where([
                'active' => 'Y',
            ])
            ->orderBy('name', 'asc')
            ->get();
            foreach ($qCustomers as $customer) {
                $rowInXls++;    //x

                // customer name
                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 2,
                    'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                    'bank_id' => $this->bank_id,
                    'cell_values' => strtoupper($customer->customer_unique_code.' - '.$customer->name),
                    'f_color' => '#000000',
                    'b_color' => '#acb9ca',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ]);
                $dayToValidateMonth = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]);

                // actual payment bulan ini, terlepas INV/KWI terjadi bulan2 sebelumnya
                $totalPaymentPlan = Tx_payment_receipt_invoice::whereIn('payment_receipt_id', function($q) use($customer){
                    $q->select('id')
                    ->from('tx_payment_receipts')
                    ->where([
                        'customer_id' => $customer->id,
                        'coa_id' => $this->bank_id,
                        'is_draft' => 'N',
                        'active' => 'Y',
                    ]);
                })
                ->where(function($q) use($dayToValidateMonth, $customer){
                    $q->whereIn('invoice_no', function($q1) use($dayToValidateMonth, $customer){
                        $q1->select('invoice_no')
                        ->from('tx_invoices')
                        ->whereRaw('DATE_FORMAT(invoice_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                        ->where([
                            'customer_id' => $customer->id,
                            'payment_to_id' => $this->bank_id,
                            'is_draft' => 'N',
                            'active' => 'Y',
                        ]);
                    })
                    ->orWhereIn('invoice_no', function($q1) use($dayToValidateMonth, $customer){
                        $q1->select('kwitansi_no')
                        ->from('tx_kwitansis')
                        ->whereRaw('DATE_FORMAT(kwitansi_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                        ->where([
                            'customer_id' => $customer->id,
                            'payment_to_id' => $this->bank_id,
                            'is_draft' => 'N',
                            'active' => 'Y',
                        ]);
                    });
                })
                ->where('active', '=', 'Y')
                ->sum('total_payment_after_vat');
                // actual payment terkait invoice & kwitansi bulan ini

                $totalPerRow = 0;

                // actual payment bulan ini
                $qTotalPaymentActualPerDay = Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts AS tx_pr', 'tx_payment_receipt_invoices.payment_receipt_id', '=', 'tx_pr.id')
                ->selectRaw('tx_pr.payment_date, SUM(tx_payment_receipt_invoices.total_payment_after_vat) AS total_payment_per_day')
                ->where('tx_payment_receipt_invoices.active', '=', 'Y')
                ->whereRaw('DATE_FORMAT(tx_pr.payment_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->where('tx_pr.customer_id', '=', $customer->id)
                ->where('tx_pr.coa_id', '=', $this->bank_id)
                ->where('tx_pr.active', '=', 'Y')
                ->groupBy('tx_pr.payment_date')
                ->orderBy('tx_pr.payment_date', 'ASC')
                ->get();
                foreach($qTotalPaymentActualPerDay as $qT){
                    $row = $rowInXls;
                    $col = 2+intval(date('d', strtotime($qT->payment_date)));

                    $qCell = Tx_cash_flow_2026::where([
                        'report_code' => $randomString,
                        'row_number' => $row,
                        'col_number' => $col,
                    ])
                    ->first();
                    if ($qCell){
                        $updRptCashFlow = Tx_cash_flow_2026::where([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                        ])
                        ->update([
                            'cell_values' => $qT->total_payment_per_day,
                            'b_color' => $qT->total_payment_per_day>0?'#8ea9db':'#ffffff',
                        ]);
                    }else{
                        $insRptCashFlow = Tx_cash_flow_2026::create([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                            'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                            'bank_id' => $this->bank_id,
                            'cell_values' => number_format($qT->total_payment_per_day,0,"",""),
                            'f_color' => '#000000',
                            'b_color' => $qT->total_payment_per_day>0?'#8ea9db':'#ffffff',
                            'font_size' => '12',
                            'font_weight' => '300',
                            'font_style' => 'normal',
                            'text_align' => 'right',
                        ]);
                    }

                    $totalPaymentPlanThisDay = 0;
                    $totalPerRow += $qT->total_payment_per_day>0?$qT->total_payment_per_day:$totalPaymentPlanThisDay;
                }

                // plan bulan ini - billing process
                $qSumPlanBillingProcess = Tx_invoice::selectRaw('invoice_date,SUM(do_grandtotal_vat) AS total_plan_billing_process')
                ->whereRaw('DATE_FORMAT(invoice_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->where([
                    'customer_id' => $customer->id,
                    'payment_to_id' => $this->bank_id,
                    'is_draft' => 'N',
                    'active' => 'Y',
                ])
                ->groupBy('invoice_date')
                ->orderBy('invoice_date', 'ASC')
                ->get();
                foreach($qSumPlanBillingProcess as $qS){
                    $row = $rowInXls;
                    $col = 2+intval(date('d', strtotime($qS->invoice_date)));

                    $totRetur = Tx_nota_retur::whereRaw('approved_by IS NOT NULL')
                    ->where([
                        'customer_id' => $customer->id,
                        'active' => 'Y',
                    ])
                    ->whereIn('id', function($q) use($customer, $dayToValidateMonth){
                        $q->select('nota_retur_id')
                        ->from('tx_nota_retur_parts')
                        ->whereIn('sales_order_part_id', function($q1) use($customer, $dayToValidateMonth){
                            $q1->select('sales_order_part_id')
                            ->from('tx_delivery_order_parts')
                            ->whereIn('delivery_order_id', function($q2) use($customer, $dayToValidateMonth){
                                $q2->select('tx_invd.fk_id')
                                ->from('tx_invoice_details AS tx_invd')
                                ->leftJoin('tx_invoices AS tx_inv', 'tx_invd.invoice_id', '=', 'tx_inv.id')
                                ->whereRaw('DATE_FORMAT(tx_inv.invoice_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                                ->where([
                                    'tx_inv.customer_id' => $customer->id,
                                    'tx_inv.payment_to_id' => $this->bank_id,
                                    'tx_inv.is_draft' => 'N',
                                    'tx_inv.active' => 'Y',
                                    'tx_invd.active' => 'Y',
                                ]);
                            })
                            ->where('active', '=', 'Y');
                        })
                        ->where('active', '=', 'Y');
                    })
                    ->sum('total_after_vat');

                    $totalPaymentPlanThisDay = $qS->total_plan_billing_process-$totRetur-$totalPaymentPlan;
                    if ($totalPaymentPlanThisDay<0){
                        // plan payment < actual payment
                        $totalPaymentPlanThisDay = 0;
                        $totalPaymentPlan = $totalPaymentPlan-$qS->total_plan_billing_process-$totRetur;
                    }else{
                        // plan payment > actual payment
                        $totalPaymentPlan = 0;
                    }

                    $totalPaymentActualPerDay = 0;
                    $qCell = Tx_cash_flow_2026::where([
                        'report_code' => $randomString,
                        'row_number' => $row,
                        'col_number' => $col,
                    ])
                    ->first();
                    if ($qCell){
                        $totalPaymentActualPerDay = $qCell->cell_values;
                        $updRptCashFlow = Tx_cash_flow_2026::where([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                        ])
                        ->update([
                            'cell_values' => $qCell->cell_values>0?$qCell->cell_values:$totalPaymentPlanThisDay,
                            'b_color' => $qCell->cell_values>0?'#8ea9db':'#ffffff',
                        ]);
                    }else{
                        $insRptCashFlow = Tx_cash_flow_2026::create([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                            'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                            'bank_id' => $this->bank_id,
                            'cell_values' => number_format($totalPaymentPlanThisDay,0,"",""),
                            'f_color' => '#000000',
                            'b_color' => '#ffffff',
                            'font_size' => '12',
                            'font_weight' => '300',
                            'font_style' => 'normal',
                            'text_align' => 'right',
                        ]);
                    }

                    $totalPerRow += $totalPaymentActualPerDay>0?$totalPaymentActualPerDay:$totalPaymentPlanThisDay;
                }

                // plan bulan ini - proses tagihan
                $qSumPlanProsesTagihan = Tx_kwitansi::selectRaw('kwitansi_date,SUM(np_total) AS total_plan_proses_tagihan')
                ->whereRaw('DATE_FORMAT(kwitansi_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->where([
                    'customer_id' => $customer->id,
                    'payment_to_id' => $this->bank_id,
                    'is_draft' => 'N',
                    'active' => 'Y',
                ])
                ->groupBy('kwitansi_date')
                ->orderBy('kwitansi_date', 'ASC')
                ->get();
                foreach($qSumPlanProsesTagihan as $qS){
                    $row = $rowInXls;
                    $col = 2+intval(date('d', strtotime($qS->kwitansi_date)));

                    $totReturNonTax = Tx_nota_retur_non_tax::whereRaw('approved_by IS NOT NULL')
                    ->where([
                        'customer_id' => $customer->id,
                        'active' => 'Y',
                    ])
                    ->whereIn('id', function($q) use($customer, $dayToValidateMonth){
                        $q->select('nota_retur_id')
                        ->from('tx_nota_retur_part_non_taxes')
                        ->whereIn('surat_jalan_part_id', function($q1) use($customer, $dayToValidateMonth){
                            $q1->select('sales_order_part_id')
                            ->from('tx_delivery_order_non_tax_parts')
                            ->whereIn('delivery_order_id', function($q2) use($customer, $dayToValidateMonth){
                                $q2->select('tx_kwd.np_id')
                                ->from('tx_kwitansi_details AS tx_kwd')
                                ->leftJoin('tx_kwitansis AS tx_kw', 'tx_kwd.kwitansi_id', '=', 'tx_kw.id')
                                ->whereRaw('DATE_FORMAT(tx_kw.kwitansi_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                                ->where([
                                    'tx_kw.customer_id' => $customer->id,
                                    'tx_kw.payment_to_id' => $this->bank_id,
                                    'tx_kw.is_draft' => 'N',
                                    'tx_kw.active' => 'Y',
                                    'tx_kwd.active' => 'Y',
                                ]);
                            })
                            ->where('active', '=', 'Y');
                        })
                        ->where('active', '=', 'Y');
                    })
                    ->sum('total_price');

                    $totalPaymentPlanThisDay = $qS->total_plan_proses_tagihan-$totReturNonTax-$totalPaymentPlan;
                    if ($totalPaymentPlanThisDay<0){
                        // plan payment < actual payment
                        $totalPaymentPlanThisDay = 0;
                        $totalPaymentPlan = $totalPaymentPlan-$qS->total_plan_proses_tagihan-$totReturNonTax;
                    }else{
                        // plan payment > actual payment
                        $totalPaymentPlan = 0;
                    }

                    $totalPaymentActualPerDay = 0;
                    $qCell = Tx_cash_flow_2026::where([
                        'report_code' => $randomString,
                        'row_number' => $row,
                        'col_number' => $col,
                    ])
                    ->first();
                    if ($qCell){
                        $totalPaymentActualPerDay = $qCell->cell_values;
                        $updRptCashFlow = Tx_cash_flow_2026::where([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                        ])
                        ->update([
                            'cell_values' => $qCell->cell_values>0?$qCell->cell_values:$totalPaymentPlanThisDay,
                            'b_color' => $qCell->cell_values>0?'#8ea9db':'#ffffff',
                        ]);
                    }else{
                        $insRptCashFlow = Tx_cash_flow_2026::create([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                            'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                            'bank_id' => $this->bank_id,
                            'cell_values' => number_format($totalPaymentPlanThisDay,0,"",""),
                            'f_color' => '#000000',
                            'b_color' => '#ffffff',
                            'font_size' => '12',
                            'font_weight' => '300',
                            'font_style' => 'normal',
                            'text_align' => 'right',
                        ]);
                    }

                    $totalPerRow += $totalPaymentActualPerDay>0?$totalPaymentActualPerDay:$totalPaymentPlanThisDay;
                }
                $lastCol = 2+$this->monthDays;

                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($totalPerRow,0,"",""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ]);

                if ($totalPerRow==0){
                    // hapus yg total nya 0
                    $updCashFlow = Tx_cash_flow_2026::where([
                        'report_code' => $randomString,
                        'row_number' => $rowInXls,
                    ])
                    ->delete();
                    // hapus yg total nya 0

                    $rowInXls--;
                }
            }
            // customers - x

            // empty row
            $rowInXls++;    //5
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ]);
            // empty row

            // cash flow GJ+LJ (COA Bank 112x, COA Petty Cash 111x, COA Capital 31xx )
            $startGjLj01DateTimeObj = new DateTime('now');
            $startGjLj01_datetime = $startGjLj01DateTimeObj->format('Y-m-d H:i:s');

            $dayToValidateMonth = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]);
            $qJournal01 = V_cash_flow_journal::select(
                'coa_code_complete',
                'coa_name',
            )
            ->whereIn('journal_id', function($q) use($dayToValidateMonth){
                $q->select('journal_id')
                ->from('v_cash_flow_journal')
                ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->where('coa_id', '=', $this->bank_id);
            })
            ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
            ->where(function($q){
                $q->where('coa_code_complete', 'LIKE', '111%')
                ->orWhere('coa_code_complete', 'LIKE', '112%')
                ->orWhere('coa_code_complete', 'LIKE', '31%');
            })
            ->where('coa_id', '<>', $this->bank_id)
            ->groupBy('coa_code_complete')
            ->groupBy('coa_name')
            ->orderBy('coa_code_complete', 'ASC')
            ->get();
            foreach($qJournal01 as $j01){
                $rowInXls++;

                // journal desc
                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 2,
                    'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                    'bank_id' => $this->bank_id,
                    'cell_values' => strtoupper($j01->coa_code_complete.' - '.$j01->coa_name),
                    'f_color' => '#000000',
                    'b_color' => '#c6e0b4',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ]);

                $totalPerRow = 0;
                // plus
                $qSumKredit01 = V_cash_flow_journal::selectRaw('general_journal_date, SUM(kredit) AS total_kredit')
                ->whereIn('journal_id', function($q) use($dayToValidateMonth){
                    $q->select('journal_id')
                    ->from('v_cash_flow_journal')
                    ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                    ->where('coa_id', '=', $this->bank_id)
                    ->where('debit', '>', 0);
                })
                ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->where('coa_code_complete', '=', $j01->coa_code_complete)
                ->where('coa_id', '<>', $this->bank_id)
                ->where('debit', '=', 0)
                ->groupBy('general_journal_date')
                ->orderBy('general_journal_date', 'ASC')
                ->get();
                foreach($qSumKredit01 as $sK){
                    $row = $rowInXls;
                    $col = 2+intval(date('d', strtotime($sK->general_journal_date)));

                    $cellVal = 0;
                    $qCell = Tx_cash_flow_2026::where([
                        'report_code' => $randomString,
                        'row_number' => $row,
                        'col_number' => $col,
                    ])
                    ->first();
                    if ($qCell){
                        $cellVal = $qCell->cell_values;
                        $updRptCashFlow = Tx_cash_flow_2026::where([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                        ])
                        ->update([
                            'cell_values' => number_format($qCell->cell_values+$sK->total_kredit,0,"",""),
                            'b_color' => ($qCell->cell_values+$sK->total_kredit)>0?'#8ea9db':'#ffffff',
                        ]);
                    }else{
                        $insRptCashFlow = Tx_cash_flow_2026::create([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                            'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                            'bank_id' => $this->bank_id,
                            'cell_values' => number_format($sK->total_kredit,0,"",""),
                            'f_color' => '#000000',
                            'b_color' => '#ffffff',
                            'font_size' => '12',
                            'font_weight' => '300',
                            'font_style' => 'normal',
                            'text_align' => 'right',
                        ]);
                    }

                    $totalPerRow += $cellVal+$sK->total_kredit;
                }

                // minus
                $qSumDebet01 = V_cash_flow_journal::selectRaw('general_journal_date, SUM(debit) AS total_debit')
                ->whereIn('journal_id', function($q) use($dayToValidateMonth){
                    $q->select('journal_id')
                    ->from('v_cash_flow_journal')
                    ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                    ->where('coa_id', '=', $this->bank_id)
                    ->where('kredit', '>', 0);
                })
                ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->where('coa_code_complete', '=', $j01->coa_code_complete)
                ->where('coa_id', '<>', $this->bank_id)
                ->where('kredit', '=', 0)
                ->groupBy('general_journal_date')
                ->orderBy('general_journal_date', 'ASC')
                ->get();
                foreach($qSumDebet01 as $sD){
                    $row = $rowInXls;
                    $col = 2+intval(date('d', strtotime($sD->general_journal_date)));

                    $cellVal = 0;
                    $qCell = Tx_cash_flow_2026::where([
                        'report_code' => $randomString,
                        'row_number' => $row,
                        'col_number' => $col,
                    ])
                    ->first();
                    if ($qCell){
                        $cellVal = $qCell->cell_values;
                        $updRptCashFlow = Tx_cash_flow_2026::where([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                        ])
                        ->update([
                            'cell_values' => number_format($qCell->cell_values-$sD->total_debit,0,"",""),
                            'b_color' => ($qCell->cell_values-$sD->total_debit)>0?'#8ea9db':'#ffffff',
                        ]);
                    }else{
                        $insRptCashFlow = Tx_cash_flow_2026::create([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                            'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                            'bank_id' => $this->bank_id,
                            'cell_values' => number_format($sD->total_debit*-1,0,"",""),
                            'f_color' => '#000000',
                            'b_color' => '#ffffff',
                            'font_size' => '12',
                            'font_weight' => '300',
                            'font_style' => 'normal',
                            'text_align' => 'right',
                        ]);
                    }

                    $totalPerRow += ($cellVal-$sD->total_debit);
                }
                $lastCol = 2+$this->monthDays;

                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($totalPerRow,0,"",""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ]);

                if ($totalPerRow==0){
                    // hapus yg total nya 0
                    $updCashFlow = Tx_cash_flow_2026::where([
                        'report_code' => $randomString,
                        'row_number' => $rowInXls,
                    ])
                    ->delete();
                    // hapus yg total nya 0

                    $rowInXls--;
                }
            }
            // cash flow GJ+LJ (COA Bank 112x, COA Petty Cash 111x, COA Capital 31xx )

            // empty row
            $rowInXls++;    //5
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ]);
            // empty row

            // cash flow GJ+LJ (COA Expense 6x, COA Loans 32x, COA Other Expense 9x, COA Hutang 2x (kecuali 211x))
            $startGjLj02DateTimeObj = new DateTime('now');
            $startGjLj02_datetime = $startGjLj02DateTimeObj->format('Y-m-d H:i:s');

            $dayToValidateMonth = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]);
            $qJournal01 = V_cash_flow_journal::select(
                'coa_code_complete',
                'coa_name',
            )
            ->whereIn('journal_id', function($q) use($dayToValidateMonth){
                $q->select('journal_id')
                ->from('v_cash_flow_journal')
                ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->where('coa_id', '=', $this->bank_id);
            })
            ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
            ->where(function($q){
                $q->where('coa_code_complete', 'LIKE', '6%')
                ->orWhere('coa_code_complete', 'LIKE', '32%')
                ->orWhere('coa_code_complete', 'LIKE', '9%')
                ->orWhere('coa_code_complete', 'LIKE', '2%');
            })
            ->where('coa_code_complete', 'NOT LIKE', '211%')
            ->where('coa_id', '<>', $this->bank_id)
            ->groupBy('coa_code_complete')
            ->groupBy('coa_name')
            ->orderBy('coa_code_complete', 'ASC')
            ->get();
            foreach($qJournal01 as $j01){
                $rowInXls++;

                // journal desc
                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 2,
                    'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                    'bank_id' => $this->bank_id,
                    'cell_values' => strtoupper($j01->coa_code_complete.' - '.$j01->coa_name),
                    'f_color' => '#000000',
                    'b_color' => '#bdd7ee',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ]);

                $totalPerRow = 0;
                // plus
                $qSumKredit01 = V_cash_flow_journal::selectRaw('general_journal_date, SUM(kredit) AS total_kredit')
                ->whereIn('journal_id', function($q) use($dayToValidateMonth){
                    $q->select('journal_id')
                    ->from('v_cash_flow_journal')
                    ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                    ->where('coa_id', '=', $this->bank_id)
                    ->where('debit', '>', 0);
                })
                ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->where('coa_code_complete', '=', $j01->coa_code_complete)
                ->where('coa_id', '<>', $this->bank_id)
                ->where('debit', '=', 0)
                ->groupBy('general_journal_date')
                ->orderBy('general_journal_date', 'ASC')
                ->get();
                foreach($qSumKredit01 as $qSK){
                    $row = $rowInXls;
                    $col = 2+intval(date('d', strtotime($qSK->general_journal_date)));

                    // minus
                    $sumDebet01 = V_cash_flow_journal::whereIn('journal_id', function($q) use($qSK){
                        $q->select('journal_id')
                        ->from('v_cash_flow_journal')
                        ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m-%d")=\''.$qSK->general_journal_date.'\'')
                        ->where('coa_id', '=', $this->bank_id)
                        ->where('kredit', '>', 0);
                    })
                    ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m-%d")=\''.$qSK->general_journal_date.'\'')
                    ->where('coa_code_complete', '=', $j01->coa_code_complete)
                    ->where('coa_id', '<>', $this->bank_id)
                    ->where('kredit', '=', 0)
                    ->sum('debit');

                    $insRptCashFlow = Tx_cash_flow_2026::create([
                        'report_code' => $randomString,
                        'row_number' => $row,
                        'col_number' => $col,
                        'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                        'bank_id' => $this->bank_id,
                        'cell_values' => number_format($qSK->total_kredit-$sumDebet01,0,"",""),
                        'f_color' => '#000000',
                        'b_color' => ($qSK->total_kredit-$sumDebet01)!=0?'#8ea9db':'#ffffff',
                        'font_size' => '12',
                        'font_weight' => '300',
                        'font_style' => 'normal',
                        'text_align' => 'right',
                    ]);

                    $totalPerRow += ($qSK->total_kredit-$sumDebet01);
                }

                // minus
                $sumDebet01 = V_cash_flow_journal::selectRaw('general_journal_date, SUM(debit) AS total_debit')
                ->whereIn('journal_id', function($q) use($dayToValidateMonth){
                    $q->select('journal_id')
                    ->from('v_cash_flow_journal')
                    ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                    ->where('coa_id', '=', $this->bank_id)
                    ->where('kredit', '>', 0);
                })
                ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->whereNotIn('general_journal_date', function($q) use($dayToValidateMonth, $j01){
                    $q->select('general_journal_date')
                    ->from('v_cash_flow_journal')
                    ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                    ->whereIn('journal_id', function($q) use($dayToValidateMonth){
                        $q->select('journal_id')
                        ->from('v_cash_flow_journal')
                        ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                        ->where('coa_id', '=', $this->bank_id)
                        ->where('debit', '>', 0);
                    })
                    ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                    ->where('coa_code_complete', '=', $j01->coa_code_complete)
                    ->where('coa_id', '<>', $this->bank_id)
                    ->where('debit', '=', 0)
                    ->groupBy('general_journal_date');
                })
                ->where('coa_code_complete', '=', $j01->coa_code_complete)
                ->where('coa_id', '<>', $this->bank_id)
                ->where('kredit', '=', 0)
                ->groupBy('general_journal_date')
                ->orderBy('general_journal_date', 'ASC')
                ->get();
                foreach($sumDebet01 as $sD){
                    $row = $rowInXls;
                    $col = 2+intval(date('d', strtotime($sD->general_journal_date)));

                    $insRptCashFlow = Tx_cash_flow_2026::create([
                        'report_code' => $randomString,
                        'row_number' => $row,
                        'col_number' => $col,
                        'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                        'bank_id' => $this->bank_id,
                        'cell_values' => number_format($sD->total_debit*-1,0,"",""),
                        'f_color' => '#000000',
                        'b_color' => ($sD->total_debit*-1)!=0?'#8ea9db':'#ffffff',
                        'font_size' => '12',
                        'font_weight' => '300',
                        'font_style' => 'normal',
                        'text_align' => 'right',
                    ]);

                    $totalPerRow += ($sD->total_debit*-1);
                }
                $lastCol = 2+$this->monthDays;

                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($totalPerRow,0,"",""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ]);

                if ($totalPerRow==0){
                    // hapus yg total nya 0
                    $updCashFlow = Tx_cash_flow_2026::where([
                        'report_code' => $randomString,
                        'row_number' => $rowInXls,
                    ])
                    ->delete();
                    // hapus yg total nya 0

                    $rowInXls--;
                }
            }
            // cash flow GJ+LJ (COA Expense 6x, COA Loans 32x, COA Other Expense 9x, COA Hutang 2x (kecuali 211x))

            // empty row
            $rowInXls++;    //5
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ]);
            // empty row

            // suppliers - x
            $startSupplierDateTimeObj = new DateTime('now');
            $startSupplier_datetime = $startSupplierDateTimeObj->format('Y-m-d H:i:s');

            $qSuppliers = Mst_supplier::where(function($q) use($period){
                $q->whereIn('id', function($q1) use($period){
                    $q1->select('supplier_id')
                    ->from('tx_tagihan_suppliers')
                    ->whereRaw('DATE_FORMAT(tagihan_supplier_date, "%Y-%m")=\''.$period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'\'')
                    ->where([
                        'bank_id' => $this->bank_id,
                        'active' => 'Y',
                    ]);
                })
                ->orWhereIn('id', function($q1) use($period){
                    $q1->select('supplier_id')
                    ->from('tx_payment_vouchers')
                    ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'\'')
                    ->whereRaw('approved_by IS NOT NULL')
                    ->where([
                        'is_draft' => 'N',
                        'active' => 'Y',
                    ]);
                });
            })
            ->where([
                'active' => 'Y',
            ])
            ->orderBy('name','ASC')
            ->get();
            foreach($qSuppliers as $qS){
                $rowInXls++;    //x

                // supplier name
                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 2,
                    'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                    'bank_id' => $this->bank_id,
                    'cell_values' => strtoupper($qS->supplier_code.' - '.$qS->name),
                    'f_color' => '#000000',
                    'b_color' => '#ffe699',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ]);
                $dayToValidateMonth = $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]);

                // total actual bayar bulan ini
                $sumPembayaranSupplierBulanIni = Tx_payment_voucher::where('supplier_id', '=', $qS->id)
                ->where('coa_id', '=', $this->bank_id)
                ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->whereRaw('approved_by IS NOT NULL')
                ->where('is_draft', '=', 'N')
                ->where('active', '=', 'Y')
                ->sum('payment_total_after_vat');
                // total actual bayar bulan ini

                $totalPerRow = 0;
                // plan bayar bulan ini
                $qSumTagihanSupplierPerDay = Tx_tagihan_supplier::selectRaw('tagihan_supplier_date,SUM(grandtotal_price) AS total_tagihan_supplier')
                ->whereRaw('DATE_FORMAT(tagihan_supplier_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->where('supplier_id', '=', $qS->id)
                ->where('bank_id', '=', $this->bank_id)
                ->where('active', '=', 'Y')
                ->groupBy('tagihan_supplier_date')
                ->orderBy('tagihan_supplier_date', 'ASC')
                ->get();
                foreach($qSumTagihanSupplierPerDay as $qSt){
                    $row = $rowInXls;
                    $col = 2+intval(date('d', strtotime($qSt->tagihan_supplier_date)));

                    $sumPembayaranSupplierThisDay = $qSt->total_tagihan_supplier-$sumPembayaranSupplierBulanIni;
                    if ($sumPembayaranSupplierThisDay<0){
                        // plan tagihan hari ini < actual tagihan bulan ini
                        $sumPembayaranSupplierThisDay = 0;
                        $sumPembayaranSupplierBulanIni = $sumPembayaranSupplierBulanIni-$qSt->total_tagihan_supplier;
                    }else{
                        // plan tagihan hari ini >= actual tagihan bulan ini
                        $sumPembayaranSupplierBulanIni = 0;
                    }

                    $sumPembayaranSupplierPerDay = Tx_payment_voucher::where('supplier_id', '=', $qS->id)
                    ->where('coa_id', '=', $this->bank_id)
                    ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m-%d")=\''.$qSt->tagihan_supplier_date.'\'')
                    ->whereRaw('approved_by IS NOT NULL')
                    ->where('is_draft', '=', 'N')
                    ->where('active', '=', 'Y')
                    ->sum('payment_total_after_vat');

                    $insRptCashFlow = Tx_cash_flow_2026::create([
                        'report_code' => $randomString,
                        'row_number' => $row,
                        'col_number' => $col,
                        'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                        'bank_id' => $this->bank_id,
                        'cell_values' => $sumPembayaranSupplierPerDay>0?number_format($sumPembayaranSupplierPerDay*-1,0,"",""):number_format($sumPembayaranSupplierThisDay*-1,0,"",""),
                        'f_color' => '#000000',
                        'b_color' => $sumPembayaranSupplierPerDay>0?'#8ea9db':'#ffffff',
                        'font_size' => '12',
                        'font_weight' => '300',
                        'font_style' => 'normal',
                        'text_align' => 'right',
                    ]);
                    $totalPerRow += ($sumPembayaranSupplierPerDay>0?($sumPembayaranSupplierPerDay*-1):($sumPembayaranSupplierThisDay*-1));
                }
                // plan bayar bulan ini

                // actual bayar bulan ini
                $qSumPembayaranSupplierPerDay = Tx_payment_voucher::selectRaw('payment_date,SUM(payment_total_after_vat) AS total_payment_supplier')
                ->where('supplier_id', '=', $qS->id)
                ->where('coa_id', '=', $this->bank_id)
                ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->whereNotIn('payment_date', function($q) use($dayToValidateMonth, $qS){
                    $q->select('tagihan_supplier_date')
                    ->from('tx_tagihan_suppliers')
                    ->whereRaw('DATE_FORMAT(tagihan_supplier_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                    ->where('supplier_id', '=', $qS->id)
                    ->where('bank_id', '=', $this->bank_id)
                    ->where('active', '=', 'Y')
                    ->groupBy('tagihan_supplier_date');
                })
                ->whereRaw('approved_by IS NOT NULL')
                ->where('is_draft', '=', 'N')
                ->where('active', '=', 'Y')
                ->groupBy('payment_date')
                ->orderBy('payment_date', 'ASC')
                ->get();
                foreach($qSumPembayaranSupplierPerDay as $qSP){
                    $row = $rowInXls;
                    $col = 2+intval(date('d', strtotime($qSP->payment_date)));

                    $cellVal = 0;
                    $qCell = Tx_cash_flow_2026::where([
                        'report_code' => $randomString,
                        'row_number' => $row,
                        'col_number' => $col,
                    ])
                    ->first();
                    if ($qCell){
                        $cellVal = $qCell->cell_values>0?$qCell->cell_values*-1:$qCell->cell_values;
                        $updRptCashFlow = Tx_cash_flow_2026::where([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                        ])
                        ->update([
                            'cell_values' => $qSP->total_payment_supplier>0?number_format($qSP->total_payment_supplier*-1,0,"",""):number_format($qSP->total_payment_supplier,0,"",""),
                            'b_color' => '#8ea9db',
                        ]);
                    }else{
                        $insRptCashFlow = Tx_cash_flow_2026::create([
                            'report_code' => $randomString,
                            'row_number' => $row,
                            'col_number' => $col,
                            'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                            'bank_id' => $this->bank_id,
                            'cell_values' => number_format($qSP->total_payment_supplier*-1,0,"",""),
                            'f_color' => '#000000',
                            'b_color' => '#8ea9db',
                            'font_size' => '12',
                            'font_weight' => '300',
                            'font_style' => 'normal',
                            'text_align' => 'right',
                        ]);
                    }

                    $totalPerRow += ($qSP->total_payment_supplier>0?($qSP->total_payment_supplier*-1):$qSP->total_payment_supplier);
                }
                // actual bayar bulan ini
                $lastCol = 2+$this->monthDays;

                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($totalPerRow,0,"",""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ]);

                if ($totalPerRow==0){
                    // hapus yg total nya 0
                    $updCashFlow = Tx_cash_flow_2026::where([
                        'report_code' => $randomString,
                        'row_number' => $rowInXls,
                    ])
                    ->delete();
                    // hapus yg total nya 0

                    $rowInXls--;
                }
            }
            // suppliers - x

            // empty row
            $rowInXls++;    //x
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ]);
            // empty row

            // empty row
            $rowInXls++;    //x
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 1,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => null,
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ]);
            // empty row

            // total row
            $rowInXls++;    //x
            $insRptCashFlow = Tx_cash_flow_2026::create([
                'report_code' => $randomString,
                'row_number' => $rowInXls,
                'col_number' => 2,
                'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                'bank_id' => $this->bank_id,
                'cell_values' => 'T O T A L',
                'f_color' => '#000000',
                'b_color' => '#ffffff',
                'font_size' => '12',
                'font_weight' => '700',
                'font_style' => 'normal',
                'text_align' => 'center',
            ]);

            $lastTotPerDay = 0;
            for ($iDay=1;$iDay<=$this->monthDays;$iDay++){
                $totSumPerDay = 0;
                $qSumPerDay = Tx_cash_flow_2026::selectRaw('SUM(CONVERT(cell_values, DECIMAL)) as total_per_day')
                ->where([
                    'report_code' => $randomString,
                    'col_number' => 2+$iDay,
                ])
                ->first();
                if ($qSumPerDay){
                    $totSumPerDay = $qSumPerDay->total_per_day;
                }
                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 2+$iDay,
                    'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($totSumPerDay+$lastTotPerDay,0,"",""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ]);
                $lastTotPerDay = ($totSumPerDay+$lastTotPerDay);
            }
            $qSumPerMonth = Tx_cash_flow_2026::selectRaw('SUM(CONVERT(cell_values, DECIMAL)) as total_per_month')
            ->where([
                'report_code' => $randomString,
                'col_number' => 3+$this->monthDays,
            ])
            ->first();
            if ($qSumPerMonth->total_per_month!=0){
                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 3+$this->monthDays,
                    'period' => $period[1].'-'.(strlen($period[0])==1?'0'.$period[0]:$period[0]).'-01',
                    'bank_id' => $this->bank_id,
                    'cell_values' => number_format($lastTotPerDay,0,"",""),
                    'f_color' => '#000000',
                    'b_color' => '#ffffff',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'right',
                ]);
            }
            // total row - end

            // update report opener
            $updCashFlow = Tx_cash_flow_2026::where([
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
                // 'start_datetime' => $start_datetime,
                // 'startCustomer_datetime' => $startCustomer_datetime,
                // 'startGjLj01_datetime' => $startGjLj01_datetime,
                // 'startGjLj02_datetime' => $startGjLj02_datetime,
                // 'startSupplier_datetime' => $startSupplier_datetime,
            ];
            return view('rpt.cash-flow.cash-flow-xlsx-dbg', $data);
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
