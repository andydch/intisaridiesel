<?php

namespace App\Http\Controllers\dbg;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_general_journal;
use App\Models\Tx_lokal_journal;
use App\Models\Tx_delivery_order;

class UpdJournalDateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $html = '';
        $i = 0;

        // faktur
        // $generalJournals = Tx_general_journal::leftJoin('tx_delivery_orders as tx_do','tx_general_journals.module_no','=','tx_do.delivery_order_no')
        // ->select(
        //     'tx_general_journals.*',
        //     'tx_general_journals.id as journal_id',
        //     'tx_do.delivery_order_date',
        // )
        // ->whereRaw('tx_general_journals.module_no LIKE \'FK%\'')
        // ->whereRaw('tx_general_journals.general_journal_date<>tx_do.delivery_order_date')
        // // ->whereIn('tx_general_journals.general_journal_no', ['G250100030'])
        // ->get();
        // foreach ($generalJournals as $journal){
        //     $i++;
        //     $html .= $i.'. '.$journal->general_journal_no.' || '.$journal->general_journal_date.' || '.$journal->module_no.' || '.$journal->delivery_order_date.'<br/>';

        //     // update journal date
        //     $updJournal = Tx_general_journal::where('id','=',$journal->journal_id)
        //     ->update([
        //         'general_journal_date'=>$journal->delivery_order_date,
        //     ]);
        // }
        // echo $html;

        // nota penjualan
        // $lokalJournals = Tx_lokal_journal::leftJoin('tx_delivery_order_non_taxes as tx_do','tx_lokal_journals.module_no','=','tx_do.delivery_order_no')
        // ->select(
        //     'tx_lokal_journals.*',
        //     'tx_lokal_journals.id as journal_id',
        //     'tx_do.delivery_order_date',
        // )
        // ->whereRaw('tx_lokal_journals.module_no LIKE \'NP%\'')
        // ->whereRaw('tx_lokal_journals.general_journal_date<>tx_do.delivery_order_date')
        // // ->whereIn('tx_lokal_journals.general_journal_no', ['N250100020'])
        // ->get();
        // foreach ($lokalJournals as $journal){
        //     $i++;
        //     $html .= $i.'. '.$journal->general_journal_no.' || '.$journal->general_journal_date.' || '.$journal->module_no.' || '.$journal->delivery_order_date.'<br/>';

        //     // update journal date
        //     $updJournal = Tx_lokal_journal::where('id','=',$journal->journal_id)
        //     ->update([
        //         'general_journal_date'=>$journal->delivery_order_date,
        //     ]);
        // }
        // echo $html;

        // purchase retur - ppn
        // $generalJournals = Tx_general_journal::leftJoin('tx_purchase_returs as tx_pr','tx_general_journals.module_no','=','tx_pr.purchase_retur_no')
        // ->select(
        //     'tx_general_journals.*',
        //     'tx_general_journals.id as journal_id',
        //     'tx_pr.purchase_retur_date',
        // )
        // ->whereRaw('tx_general_journals.module_no LIKE \'PR%\'')
        // ->whereRaw('tx_general_journals.general_journal_date<>tx_pr.purchase_retur_date')
        // // ->whereIn('tx_general_journals.general_journal_no', ['G250900058 '])
        // ->get();
        // foreach ($generalJournals as $journal){
        //     $i++;
        //     $html .= $i.'. '.$journal->general_journal_no.' || '.$journal->general_journal_date.' || '.$journal->module_no.' || '.$journal->purchase_retur_date.'<br/>';

        //     // update journal date
        //     $updJournal = Tx_general_journal::where('id','=',$journal->journal_id)
        //     ->update([
        //         'general_journal_date'=>$journal->purchase_retur_date,
        //     ]);
        // }
        // echo $html;

        // purchase retur - non ppn
        $lokalJournals = Tx_lokal_journal::leftJoin('tx_purchase_returs as tx_pr','tx_lokal_journals.module_no','=','tx_pr.purchase_retur_no')
        ->select(
            'tx_lokal_journals.*',
            'tx_lokal_journals.id as journal_id',
            'tx_pr.purchase_retur_date',
        )
        ->whereRaw('tx_lokal_journals.module_no LIKE \'PR%\'')
        ->whereRaw('tx_lokal_journals.general_journal_date<>tx_pr.purchase_retur_date')
        // ->whereIn('tx_lokal_journals.general_journal_no', ['N250100034'])
        ->get();
        foreach ($lokalJournals as $journal){
            $i++;
            $html .= $i.'. '.$journal->general_journal_no.' || '.$journal->general_journal_date.' || '.$journal->module_no.' || '.$journal->purchase_retur_date.'<br/>';

            // update journal date
            $updJournal = Tx_lokal_journal::where('id','=',$journal->journal_id)
            ->update([
                'general_journal_date'=>$journal->purchase_retur_date,
            ]);
        }
        echo $html;

        // nota retur - ppn
        // $generalJournals = Tx_general_journal::leftJoin('tx_nota_returs as tx_nr','tx_general_journals.module_no','=','tx_nr.nota_retur_no')
        // ->select(
        //     'tx_general_journals.*',
        //     'tx_general_journals.id as journal_id',
        //     'tx_nr.nota_retur_date',
        // )
        // ->whereRaw('tx_general_journals.module_no LIKE \'NR%\'')
        // ->whereRaw('tx_general_journals.general_journal_date<>tx_nr.nota_retur_date')
        // ->whereIn('tx_general_journals.general_journal_no', ['G250900058 '])
        // ->get();
        // foreach ($generalJournals as $journal){
        //     $i++;
        //     $html .= $i.'. '.$journal->general_journal_no.' || '.$journal->general_journal_date.' || '.$journal->module_no.' || '.$journal->nota_retur_date.'<br/>';

        //     // update journal date
        //     $updJournal = Tx_general_journal::where('id','=',$journal->journal_id)
        //     ->update([
        //         'general_journal_date'=>$journal->nota_retur_date,
        //     ]);
        // }
        // echo $html;

        // nota retur - non ppn
        // $lokalJournals = Tx_lokal_journal::leftJoin('tx_nota_retur_non_taxes as tx_nr','tx_lokal_journals.module_no','=','tx_nr.nota_retur_no')
        // ->select(
        //     'tx_lokal_journals.*',
        //     'tx_lokal_journals.id as journal_id',
        //     'tx_nr.nota_retur_date',
        // )
        // ->whereRaw('tx_lokal_journals.module_no LIKE \'RE%\'')
        // ->whereRaw('tx_lokal_journals.general_journal_date<>tx_nr.nota_retur_date')
        // // ->whereIn('tx_lokal_journals.general_journal_no', ['N250100034'])
        // ->get();
        // foreach ($lokalJournals as $journal){
        //     $i++;
        //     $html .= $i.'. '.$journal->general_journal_no.' || '.$journal->general_journal_date.' || '.$journal->module_no.' || '.$journal->nota_retur_date.'<br/>';

        //     // update journal date
        //     // $updJournal = Tx_lokal_journal::where('id','=',$journal->journal_id)
        //     // ->update([
        //     //     'general_journal_date'=>$journal->nota_retur_date,
        //     // ]);
        // }
        // echo $html;
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
