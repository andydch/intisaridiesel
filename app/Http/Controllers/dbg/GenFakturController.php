<?php

namespace App\Http\Controllers\dbg;

use Exception;
use App\Models\Auto_inc;
use Illuminate\Http\Request;
use App\Models\Tx_delivery_order;
use App\Models\Tx_general_journal;
use App\Models\Tx_general_journal_detail;
use Illuminate\Support\Facades\DB;
use App\Models\Tx_sales_order_part;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Mst_automatic_journal_detail;
use Illuminate\Validation\ValidationException;

class GenFakturController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Start transaction!
        DB::beginTransaction();

        try {

            $fakturs = Tx_delivery_order::whereNotIn('delivery_order_no', function($q) {
                $q->select('module_no')
                ->from('tx_general_journals')
                ->whereRaw('module_no IS NOT null')
                ->where('active', '=', 'Y');
            })
            ->where('active', '=', 'Y')
            ->get();
            foreach ($fakturs as $faktur) {
                echo $faktur->delivery_order_no . "<br/>";

                $lastBranchID =$faktur->branch_id;
                $faktur_no = $faktur->delivery_order_no;
                $so_date_arr = explode('-', $faktur->delivery_order_date);
                $so_no_arr = explode(',', $faktur->sales_order_no_all);
                $totalPriceBeforeVat = $faktur->total_before_vat;
                $totalPriceAfterVat = $faktur->total_after_vat;
                $totalPerLastAVGcost = 0;

                foreach ($so_no_arr as $so_no) {
                    if ($so_no!=''){
                        $partSOs = Tx_sales_order_part::leftJoin('tx_sales_orders as tx_so','tx_sales_order_parts.order_id','=','tx_so.id')
                        ->select(
                            'tx_sales_order_parts.*',
                            'tx_so.branch_id',
                        )
                        ->where('tx_so.sales_order_no','=', $so_no)
                        ->get();
                        foreach ($partSOs as $partSO){
                            $totalPerLastAVGcost += ($partSO->last_avg_cost*$partSO->qty);
                        }
                    }
                }

                // cek apakah fitur automatic journal faktur sudah tersedia
                $qAutJournal = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>1,
                    'branch_id'=>$lastBranchID,
                    'active'=>'Y',
                ])
                ->first();
                if ($qAutJournal) {
                    // cogs
                    $qAutJournal_cogs = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>1,
                        'branch_id'=>$lastBranchID,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'cogs\'')
                    ->first();
                    // inventory
                    $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>1,
                        'branch_id'=>$lastBranchID,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'inventory\'')
                    ->first();
                    // piutang
                    $qAutJournal_piutang = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>1,
                        'branch_id'=>$lastBranchID,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'piutang\'')
                    ->first();
                    // sales pajak
                    $qAutJournal_sales_pajak = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>1,
                        'branch_id'=>$lastBranchID,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'sales pajak\'')
                    ->first();
                    // ppn keluaran
                    $qAutJournal_ppn_keluaran = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>1,
                        'branch_id'=>$lastBranchID,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'ppn keluaran\'')
                    ->first();

                    // cek apakah module sudah pernah dibuat
                    $insJournal = [];
                    $qJournals = Tx_general_journal::where([
                        'module_no'=>$faktur_no,
                        'automatic_journal_id'=>1,
                        'active'=>'Y',
                    ])
                    ->first();
                    if (!$qJournals){
                        // journal belum pernah dibuat

                        // create journal
                        $yearTemp = substr($so_date_arr[0], 2, 2);
                        $monthTemp = $so_date_arr[1];
                        $ymTemp = $yearTemp.$monthTemp;
                        $zero = '';
                        $YearMonth = '';
                        $newInc = 1;

                        $identityName = 'tx_general_journal';
                        $autoInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->first();
                        if ($autoInc) {
                            // jika counter sudah terbentuk
                            $date = date_format(date_create($autoInc->updated_at), "n");
                            $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                            $dateNow = date("ym");
                            if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                                // jika bulan di server berbeda dengan bulan jurnal yg dipilih

                                // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                // untuk menghindari duplikasi
                                $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                                ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                ->where([
                                    'active'=>'Y',
                                ])
                                ->orderBy('general_journal_no', 'DESC')
                                ->first();
                                if ($lastCounterIfAny){
                                    // ambil no urut terakhir dan ditambahkan 1

                                    $newInc = $lastCounterIfAny->lastCounter+1;
                                }
                            } else {
                                // jika bulan di server sama dengan bulan jurnal yg dipilih

                                $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                                $updInc = Auto_inc::where([
                                    'identity_name' => $identityName
                                ])
                                ->update([
                                    'id_auto_inc' => $newInc
                                ]);
                            }

                            $YearMonth = $yearTemp.$monthTemp;
                        } else {
                            // jika counter belum pernah terbentuk

                            $dateNow = date("ym");
                            // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                            // untuk menghindari duplikasi
                            $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                            ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                            ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('general_journal_no', 'DESC')
                            ->first();
                            if ($lastCounterIfAny){
                                // ambil no urut terakhir dan ditambahkan 1

                                $newInc = $lastCounterIfAny->lastCounter+1;
                            }

                            $insInc = Auto_inc::create([
                                'identity_name'=>$identityName,
                                'id_auto_inc'=>$newInc
                            ]);

                            $YearMonth = date('y').date('m');
                        }
                        $zero = '';
                        for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                            $zero .= '0';
                        }
                        $journal_no = env('P_GENERAL_JURNAL').$YearMonth.$zero.strval($newInc);

                        // buat jurnal
                        $insJournal = Tx_general_journal::create([
                            'general_journal_no'=>$journal_no,
                            'general_journal_date'=>$faktur->delivery_order_date,
                            'total_debit'=>$totalPerLastAVGcost+$totalPriceAfterVat,
                            'total_kredit'=>$totalPerLastAVGcost+$totalPriceAfterVat,
                            'module_no'=>$faktur_no,
                            'automatic_journal_id'=>1,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);
                    }

                    // cogs
                    $ins_cogs = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_cogs->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>$totalPerLastAVGcost,
                        'kredit'=>0,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // inventory
                    $ins_inventory = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_inventory->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>0,
                        'kredit'=>$totalPerLastAVGcost,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // piutang
                    $ins_piutang = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_piutang->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>$totalPriceAfterVat,
                        'kredit'=>0,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // sales pajak
                    $ins_sales_pajak = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_sales_pajak->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>0,
                        'kredit'=>$totalPriceBeforeVat,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // ppn keluaran
                    $ins_ppn_keluaran = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_ppn_keluaran->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>0,
                        'kredit'=>($totalPriceAfterVat-$totalPriceBeforeVat),
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }
            }

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        } catch(Exception $e){
            DB::rollback();
            throw $e;

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        }

        // If we reach here, then
        // data is valid and working.
        // Commit the queries!
        DB::commit();
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
