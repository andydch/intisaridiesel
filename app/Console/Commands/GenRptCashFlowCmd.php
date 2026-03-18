<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mst_coa;
use App\Models\Tx_cash_flow_2026;
use App\Models\Tx_payment_plan;
use App\Models\Mst_customer;
use App\Models\Tx_payment_receipt_invoice;
use App\Models\Tx_nota_retur;
use App\Models\Tx_invoice;
use App\Models\Tx_kwitansi;
use App\Models\Tx_nota_retur_non_tax;
use App\Models\V_cash_flow_journal;
use App\Models\Mst_supplier;
use App\Models\Tx_payment_voucher;
use App\Models\Tx_tagihan_supplier;
use DateTime;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GenRptCashFlowCmd extends Command
{
    protected $monthDays;
    protected $daysInMonth = [31,28,31,30,31,30,31,31,30,31,30,31];
    protected $MonthName = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOPEMBER','DESEMBER'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GenReport:CashFlow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate data untuk report cash flow';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 3600);

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $startDateTimeObj = new DateTime('now');
        $start_datetime = $startDateTimeObj->format('Y-m-d H:i:s');

        // $year = '2025';
        $year = date('Y');
        for($iMonth=1;$iMonth<=12;$iMonth++){
            echo $year.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'-01<br/>';
            $period = $year.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'-01';

            $coas = Mst_coa::whereIn('id', function($q) use($period){
                $q->select('bank_id')
                ->from('tx_payment_plans')
                ->whereRaw('payment_month=\''.$period.'\'')
                ->where('is_draft', '=', 'N')
                ->where('active', '=', 'Y');
            })
            ->where('active', '=', 'Y')
            ->orderBy('coa_name', 'ASC')
            ->get();
            foreach($coas as $coa){
                echo $coa->coa_name.'<br/>';
                $randomString = $this->genRandomString();
                echo $randomString.'<br/>';

                // generate data utk report
                $this->genCashFlow($period, $coa->id, $randomString);
                // generate data utk report
            }
            echo '<br/>';
        }

        $stopDateTimeObj = new DateTime('now');
        $stop_datetime = $stopDateTimeObj->format('Y-m-d H:i:s');

        $interval = $startDateTimeObj->diff($stopDateTimeObj);
        echo 'Total waktu proses: '.$interval->i.' menit '.$interval->s.' detik';

        return Command::SUCCESS;
    }

    private function genCashFlow($period, $bank_id, $randomString){
        //hapus data dg bank ID dan periode yg sama
        $delCashFlow = Tx_cash_flow_2026::where('period', '=', $period)
        ->where('bank_id', '=', $bank_id)
        ->delete();
        //hapus data dg bank ID dan periode yg sama

        $periodArr = explode("-", $period);
        if ($this->isLeapYear($periodArr[0]) && $periodArr[1]==2) {
            $this->monthDays = 29;
        } else {
            $this->monthDays = $this->daysInMonth[$periodArr[1]-1];
        }

        $qPaymentPlan = Tx_payment_plan::where([
            'payment_month' => $period,
            'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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
            //     'period' => $period,
            //     'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
                'cell_values' => '01-'.$this->MonthName[$periodArr[1]-1].'-'.$periodArr[0],
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
                'period' => $period,
                'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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

            $qCustomers = Mst_customer::where(function($q) use($periodArr, $bank_id){
                $q->whereIn('id', function($query) use($periodArr, $bank_id) {
                    $query->select('customer_id')
                    ->from('tx_invoices')
                    ->whereRaw('DATE_FORMAT(invoice_date, "%Y-%m")=\''.$periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]).'\'')
                    ->where([
                        'payment_to_id' => $bank_id,
                        'is_draft' => 'N',
                        'active' => 'Y',
                    ]);
                })
                ->orWhereIn('id', function($query) use($periodArr, $bank_id) {
                    $query->select('customer_id')
                    ->from('tx_kwitansis')
                    ->whereRaw('DATE_FORMAT(kwitansi_date, "%Y-%m")=\''.$periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]).'\'')
                    ->where([
                        'payment_to_id' => $bank_id,
                        'is_draft' => 'N',
                        'active' => 'Y',
                    ]);
                })
                ->orWhereIn('id', function($query) use($periodArr, $bank_id) {
                    $query->select('customer_id')
                    ->from('tx_payment_receipts')
                    ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]).'\'')
                    ->where([
                        'coa_id' => $bank_id,
                        'is_draft' => 'N',
                        'active' => 'Y',
                    ]);
                });
            })
            ->where('active', '=', 'Y')
            ->orderBy('name', 'asc')
            ->get();
            foreach ($qCustomers as $customer) {
                $rowInXls++;    //x

                // customer name
                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => 2,
                    'period' => $period,
                    'bank_id' => $bank_id,
                    'cell_values' => strtoupper($customer->customer_unique_code.' - '.$customer->name),
                    'f_color' => '#000000',
                    'b_color' => '#acb9ca',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ]);
                $dayToValidateMonth = $periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]);

                // actual payment terkait invoice & kwitansi bulan ini
                $totalPaymentPlan = Tx_payment_receipt_invoice::whereIn('payment_receipt_id', function($q) use($customer, $bank_id){
                    $q->select('id')
                    ->from('tx_payment_receipts')
                    ->where([
                        'customer_id' => $customer->id,
                        'coa_id' => $bank_id,
                        'is_draft' => 'N',
                        'active' => 'Y',
                    ]);
                })
                ->where(function($q) use($dayToValidateMonth, $customer, $bank_id){
                    $q->whereIn('invoice_no', function($q1) use($dayToValidateMonth, $customer, $bank_id){
                        $q1->select('invoice_no')
                        ->from('tx_invoices')
                        ->whereRaw('DATE_FORMAT(invoice_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                        ->where([
                            'customer_id' => $customer->id,
                            'payment_to_id' => $bank_id,
                            'is_draft' => 'N',
                            'active' => 'Y',
                        ]);
                    })
                    ->orWhereIn('invoice_no', function($q1) use($dayToValidateMonth, $customer, $bank_id){
                        $q1->select('kwitansi_no')
                        ->from('tx_kwitansis')
                        ->whereRaw('DATE_FORMAT(kwitansi_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                        ->where([
                            'customer_id' => $customer->id,
                            'payment_to_id' => $bank_id,
                            'is_draft' => 'N',
                            'active' => 'Y',
                        ]);
                    });
                })
                ->where('active', '=', 'Y')
                ->sum('total_payment_after_vat');
                // actual payment terkait invoice & kwitansi bulan ini

                $totalPerRow = 0;
                $lastCol = 0;
                for ($iDay=1;$iDay<=$this->monthDays;$iDay++){
                    $dayToValidate = $periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]).'-'.(strlen($iDay)==1?'0'.$iDay:$iDay);

                    $totalPaymentActualPerDay = Tx_payment_receipt_invoice::whereIn('payment_receipt_id', function($q) use($dayToValidate, $customer, $bank_id){
                        $q->select('id')
                        ->from('tx_payment_receipts')
                        ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                        ->where([
                            'customer_id' => $customer->id,
                            'coa_id' => $bank_id,
                            'is_draft' => 'N',
                            'active' => 'Y',
                        ]);
                    })
                    ->where('active', '=', 'Y')
                    ->sum('total_payment_after_vat');

                    $sumPlanBillingProcess = Tx_invoice::whereRaw('DATE_FORMAT(invoice_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                    ->where([
                        'customer_id' => $customer->id,
                        'payment_to_id' => $bank_id,
                        'is_draft' => 'N',
                        'active' => 'Y',
                    ])
                    ->sum('do_grandtotal_vat');

                    $totRetur = Tx_nota_retur::whereRaw('approved_by IS NOT NULL')
                    ->where([
                        'customer_id' => $customer->id,
                        'active' => 'Y',
                    ])
                    ->whereIn('id', function($q) use($customer, $bank_id){
                        $q->select('nota_retur_id')
                        ->from('tx_nota_retur_parts')
                        ->whereIn('sales_order_part_id', function($q1) use($customer, $bank_id){
                            $q1->select('sales_order_part_id')
                            ->from('tx_delivery_order_parts')
                            ->whereIn('delivery_order_id', function($q2) use($customer, $bank_id){
                                $q2->select('tx_invd.fk_id')
                                ->from('tx_invoice_details AS tx_invd')
                                ->leftJoin('tx_invoices AS tx_inv', 'tx_invd.invoice_id', '=', 'tx_inv.id')
                                ->where([
                                    'tx_inv.customer_id' => $customer->id,
                                    'tx_inv.payment_to_id' => $bank_id,
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

                    $sumPlanProsesTagihan = Tx_kwitansi::whereRaw('DATE_FORMAT(kwitansi_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                    ->where([
                        'customer_id' => $customer->id,
                        'payment_to_id' => $bank_id,
                        'is_draft' => 'N',
                        'active' => 'Y',
                    ])
                    ->sum('np_total');

                    $totReturNonTax = Tx_nota_retur_non_tax::whereRaw('approved_by IS NOT NULL')
                    ->where([
                        'customer_id' => $customer->id,
                        'active' => 'Y',
                    ])
                    ->whereIn('id', function($q) use($customer, $bank_id){
                        $q->select('nota_retur_id')
                        ->from('tx_nota_retur_part_non_taxes')
                        ->whereIn('surat_jalan_part_id', function($q1) use($customer, $bank_id){
                            $q1->select('sales_order_part_id')
                            ->from('tx_delivery_order_non_tax_parts')
                            ->whereIn('delivery_order_id', function($q2) use($customer, $bank_id){
                                $q2->select('tx_kwd.np_id')
                                ->from('tx_kwitansi_details AS tx_kwd')
                                ->leftJoin('tx_kwitansis AS tx_kw', 'tx_kwd.kwitansi_id', '=', 'tx_kw.id')
                                ->where([
                                    'tx_kw.customer_id' => $customer->id,
                                    'tx_kw.payment_to_id' => $bank_id,
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

                    $totalPaymentPlanThisDay = (($sumPlanBillingProcess+$sumPlanProsesTagihan)-($totRetur+$totReturNonTax))-$totalPaymentPlan;
                    if ($totalPaymentPlanThisDay<0){
                        // plan payment < actual payment
                        $totalPaymentPlanThisDay = 0;
                        $totalPaymentPlan = $totalPaymentPlan-(($sumPlanBillingProcess+$sumPlanProsesTagihan)-($totRetur+$totReturNonTax));
                    }else{
                        // plan payment > actual payment
                        $totalPaymentPlan = 0;
                    }

                    $insRptCashFlow = Tx_cash_flow_2026::create([
                        'report_code' => $randomString,
                        'row_number' => $rowInXls,
                        'col_number' => 2+$iDay,
                        'period' => $period,
                        'bank_id' => $bank_id,
                        'cell_values' => $totalPaymentActualPerDay>0?number_format($totalPaymentActualPerDay,0,"",""):number_format($totalPaymentPlanThisDay,0,"",""),
                        'f_color' => '#000000',
                        'b_color' => $totalPaymentActualPerDay>0?'#8ea9db':'#ffffff',
                        'font_size' => '12',
                        'font_weight' => '300',
                        'font_style' => 'normal',
                        'text_align' => 'right',
                    ]);

                    $totalPerRow += $totalPaymentActualPerDay>0?$totalPaymentActualPerDay:$totalPaymentPlanThisDay;
                    $lastCol = 2+$iDay;
                }

                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $period,
                    'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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

            $dayToValidateMonth = $periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]);
            $qJournal01 = V_cash_flow_journal::select(
                'coa_code_complete',
                'coa_name',
            )
            ->whereIn('journal_id', function($q) use($dayToValidateMonth, $bank_id){
                $q->select('journal_id')
                ->from('v_cash_flow_journal')
                ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->where('coa_id', '=', $bank_id);
            })
            ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
            ->where(function($q){
                $q->where('coa_code_complete', 'LIKE', '111%')
                ->orWhere('coa_code_complete', 'LIKE', '112%')
                ->orWhere('coa_code_complete', 'LIKE', '31%');
            })
            ->where('coa_id', '<>', $bank_id)
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
                    'period' => $period,
                    'bank_id' => $bank_id,
                    'cell_values' => strtoupper($j01->coa_code_complete.' - '.$j01->coa_name),
                    'f_color' => '#000000',
                    'b_color' => '#c6e0b4',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ]);

                $totalPerRow = 0;
                $lastCol = 0;
                for ($iDay=1;$iDay<=$this->monthDays;$iDay++){
                    $dayToValidate = $periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]).'-'.(strlen($iDay)==1?'0'.$iDay:$iDay);

                    // plus
                    $sumKredit01 = V_cash_flow_journal::whereIn('journal_id', function($q) use($dayToValidate, $bank_id){
                        $q->select('journal_id')
                        ->from('v_cash_flow_journal')
                        ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                        ->where('coa_id', '=', $bank_id)
                        ->where('debit', '>', 0);
                    })
                    ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                    ->where('coa_code_complete', '=', $j01->coa_code_complete)
                    // ->where(function($q){
                    //     $q->where('coa_code_complete', 'LIKE', '111%')
                    //     ->orWhere('coa_code_complete', 'LIKE', '112%')
                    //     ->orWhere('coa_code_complete', 'LIKE', '31%');
                    // })
                    ->where('coa_id', '<>', $bank_id)
                    ->where('debit', '=', 0)
                    ->sum('kredit');

                    // minus
                    $sumDebet01 = V_cash_flow_journal::whereIn('journal_id', function($q) use($dayToValidate, $bank_id){
                        $q->select('journal_id')
                        ->from('v_cash_flow_journal')
                        ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                        ->where('coa_id', '=', $bank_id)
                        ->where('kredit', '>', 0);
                    })
                    ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                    ->where('coa_code_complete', '=', $j01->coa_code_complete)
                    // ->where(function($q){
                    //     $q->where('coa_code_complete', 'LIKE', '111%')
                    //     ->orWhere('coa_code_complete', 'LIKE', '112%')
                    //     ->orWhere('coa_code_complete', 'LIKE', '31%');
                    // })
                    ->where('coa_id', '<>', $bank_id)
                    ->where('kredit', '=', 0)
                    ->sum('debit');

                    // amount
                    $insRptCashFlow = Tx_cash_flow_2026::create([
                        'report_code' => $randomString,
                        'row_number' => $rowInXls,
                        'col_number' => 2+$iDay,
                        'period' => $period,
                        'bank_id' => $bank_id,
                        'cell_values' => number_format($sumKredit01-$sumDebet01,0,"",""),
                        'f_color' => '#000000',
                        'b_color' => ($sumKredit01-$sumDebet01)!=0?'#8ea9db':'#ffffff',
                        'font_size' => '12',
                        'font_weight' => '300',
                        'font_style' => 'normal',
                        'text_align' => 'right',
                    ]);

                    $totalPerRow += ($sumKredit01-$sumDebet01);
                    $lastCol = 2+$iDay;
                }

                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $period,
                    'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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

            $dayToValidateMonth = $periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]);
            $qJournal01 = V_cash_flow_journal::select(
                'coa_code_complete',
                'coa_name',
            )
            ->whereIn('journal_id', function($q) use($dayToValidateMonth, $bank_id){
                $q->select('journal_id')
                ->from('v_cash_flow_journal')
                ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->where('coa_id', '=', $bank_id);
            })
            ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
            ->where(function($q){
                $q->where('coa_code_complete', 'LIKE', '6%')
                ->orWhere('coa_code_complete', 'LIKE', '32%')
                ->orWhere('coa_code_complete', 'LIKE', '9%')
                ->orWhere('coa_code_complete', 'LIKE', '2%');
            })
            ->where('coa_code_complete', 'NOT LIKE', '211%')
            ->where('coa_id', '<>', $bank_id)
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
                    'period' => $period,
                    'bank_id' => $bank_id,
                    'cell_values' => strtoupper($j01->coa_code_complete.' - '.$j01->coa_name),
                    'f_color' => '#000000',
                    'b_color' => '#bdd7ee',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ]);

                $totalPerRow = 0;
                $lastCol = 0;
                for ($iDay=1;$iDay<=$this->monthDays;$iDay++){
                    $dayToValidate = $periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]).'-'.(strlen($iDay)==1?'0'.$iDay:$iDay);

                    // plus
                    $sumKredit01 = V_cash_flow_journal::whereIn('journal_id', function($q) use($dayToValidate, $bank_id){
                        $q->select('journal_id')
                        ->from('v_cash_flow_journal')
                        ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                        ->where('coa_id', '=', $bank_id)
                        ->where('debit', '>', 0);
                    })
                    ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                    ->where('coa_code_complete', '=', $j01->coa_code_complete)
                    ->where('coa_id', '<>', $bank_id)
                    ->where('debit', '=', 0)
                    ->sum('kredit');

                    // minus
                    $sumDebet01 = V_cash_flow_journal::whereIn('journal_id', function($q) use($dayToValidate, $bank_id){
                        $q->select('journal_id')
                        ->from('v_cash_flow_journal')
                        ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                        ->where('coa_id', '=', $bank_id)
                        ->where('kredit', '>', 0);
                    })
                    ->whereRaw('DATE_FORMAT(general_journal_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                    ->where('coa_code_complete', '=', $j01->coa_code_complete)
                    ->where('coa_id', '<>', $bank_id)
                    ->where('kredit', '=', 0)
                    ->sum('debit');

                    // amount
                    $insRptCashFlow = Tx_cash_flow_2026::create([
                        'report_code' => $randomString,
                        'row_number' => $rowInXls,
                        'col_number' => 2+$iDay,
                        'period' => $period,
                        'bank_id' => $bank_id,
                        'cell_values' => number_format($sumKredit01-$sumDebet01,0,"",""),
                        'f_color' => '#000000',
                        'b_color' => ($sumKredit01-$sumDebet01)!=0?'#8ea9db':'#ffffff',
                        'font_size' => '12',
                        'font_weight' => '300',
                        'font_style' => 'normal',
                        'text_align' => 'right',
                    ]);

                    $totalPerRow += ($sumKredit01-$sumDebet01);
                    $lastCol = 2+$iDay;
                }

                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $period,
                    'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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

            $qSuppliers = Mst_supplier::where(function($q) use($periodArr, $bank_id){
                $q->whereIn('id', function($q1) use($periodArr, $bank_id){
                    $q1->select('supplier_id')
                    ->from('tx_tagihan_suppliers')
                    ->whereRaw('DATE_FORMAT(tagihan_supplier_date, "%Y-%m")=\''.$periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]).'\'')
                    ->where([
                        'bank_id' => $bank_id,
                        'active' => 'Y',
                    ]);
                })
                ->orWhereIn('id', function($q1) use($periodArr, $bank_id){
                    $q1->select('supplier_id')
                    ->from('tx_payment_vouchers')
                    ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]).'\'')
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
                    'period' => $period,
                    'bank_id' => $bank_id,
                    'cell_values' => strtoupper($qS->supplier_code.' - '.$qS->name),
                    'f_color' => '#000000',
                    'b_color' => '#ffe699',
                    'font_size' => '12',
                    'font_weight' => '300',
                    'font_style' => 'normal',
                    'text_align' => 'left',
                ]);
                $dayToValidateMonth = $periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]);

                // total actual bayar bulan ini
                $sumPembayaranSupplierBulanIni = Tx_payment_voucher::where('supplier_id', '=', $qS->id)
                ->where('coa_id', '=', $bank_id)
                ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$dayToValidateMonth.'\'')
                ->whereRaw('approved_by IS NOT NULL')
                ->where('is_draft', '=', 'N')
                ->where('active', '=', 'Y')
                ->sum('payment_total_after_vat');
                // total actual bayar bulan ini

                $totalPerRow = 0;
                $lastCol = 0;
                for ($iDay=1;$iDay<=$this->monthDays;$iDay++){
                    $dayToValidate = $periodArr[0].'-'.(strlen($periodArr[1])==1?'0'.$periodArr[1]:$periodArr[1]).'-'.(strlen($iDay)==1?'0'.$iDay:$iDay);

                    $sumTagihanSupplierPerDay = Tx_tagihan_supplier::whereRaw('DATE_FORMAT(tagihan_supplier_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                    ->where('supplier_id', '=', $qS->id)
                    ->where('bank_id', '=', $bank_id)
                    ->where('active', '=', 'Y')
                    ->sum('grandtotal_price');

                    $sumPembayaranSupplierThisDay = $sumTagihanSupplierPerDay-$sumPembayaranSupplierBulanIni;
                    if ($sumPembayaranSupplierThisDay<0){
                        // plan tagihan hari ini < actual tagihan bulan ini
                        $sumPembayaranSupplierThisDay = 0;
                        $sumPembayaranSupplierBulanIni = $sumPembayaranSupplierBulanIni-$sumTagihanSupplierPerDay;
                    }else{
                        // plan tagihan hari ini >= actual tagihan bulan ini
                        $sumPembayaranSupplierBulanIni = 0;
                    }

                    $sumPembayaranSupplierPerDay = Tx_payment_voucher::where('supplier_id', '=', $qS->id)
                    ->where('coa_id', '=', $bank_id)
                    ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m-%d")=\''.$dayToValidate.'\'')
                    ->whereRaw('approved_by IS NOT NULL')
                    ->where('is_draft', '=', 'N')
                    ->where('active', '=', 'Y')
                    ->sum('payment_total_after_vat');

                    $insRptCashFlow = Tx_cash_flow_2026::create([
                        'report_code' => $randomString,
                        'row_number' => $rowInXls,
                        'col_number' => 2+$iDay,
                        'period' => $period,
                        'bank_id' => $bank_id,
                        'cell_values' => $sumPembayaranSupplierPerDay>0?number_format($sumPembayaranSupplierPerDay*-1,0,"",""):number_format($sumPembayaranSupplierThisDay*-1,0,"",""),
                        'f_color' => '#000000',
                        'b_color' => $sumPembayaranSupplierPerDay>0?'#8ea9db':'#ffffff',
                        'font_size' => '12',
                        'font_weight' => '300',
                        'font_style' => 'normal',
                        'text_align' => 'right',
                    ]);

                    $totalPerRow += ($sumPembayaranSupplierPerDay>0?($sumPembayaranSupplierPerDay*-1):($sumPembayaranSupplierThisDay*-1));
                    $lastCol = 2+$iDay;
                }

                $insRptCashFlow = Tx_cash_flow_2026::create([
                    'report_code' => $randomString,
                    'row_number' => $rowInXls,
                    'col_number' => $lastCol+1,
                    'period' => $period,
                    'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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
                'period' => $period,
                'bank_id' => $bank_id,
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
                    'period' => $period,
                    'bank_id' => $bank_id,
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
                    'period' => $period,
                    'bank_id' => $bank_id,
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
                'created_by' => 1,
            ]);
            // update report opener - end
        }
    }

    private function genRandomString(){
        $strRnd = Str::random(6);
        $isNewstrRnd = false;
        while (!$isNewstrRnd){
            $qCashFlow = Tx_cash_flow_2026::where('report_code', '=', $strRnd)
            ->first();
            if ($qCashFlow){
                $isNewstrRnd = false;
                $strRnd = Str::random(6);
            }else{
                $isNewstrRnd = true;
            }

        }
        return $strRnd;
    }

    private function isLeapYear($year) {
        if (($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0)) {
            return true;
        } else {
            return false;
        }
    }
}
