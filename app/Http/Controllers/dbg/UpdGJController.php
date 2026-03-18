<?php

namespace App\Http\Controllers\dbg;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tx_stock_transfer;
use App\Models\Tx_stock_transfer_part;
use App\Models\Tx_general_journal_detail;
use Illuminate\Validation\ValidationException;

class UpdGJController extends Controller
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

            $html = '';
            $htmlBody = '';
            $i = 1;
            $htmlHeader = '<tr>
                <td style="padding:5px;font-weight: bold;">#</td>
                <td style="padding:5px;font-weight: bold;">STOCK TRANSFER NO</td>
                <td style="padding:5px;font-weight: bold;">GJ NO</td>
                </tr>';

            $qStockTransfer = Tx_stock_transfer::leftJoin('tx_general_journals AS tx_gj', 'tx_stock_transfers.stock_transfer_no', '=', 'tx_gj.module_no')
            ->select(
                'tx_stock_transfers.id AS tx_id',
                'tx_stock_transfers.stock_transfer_no AS stock_transfer_no',
                'tx_stock_transfers.received_at AS stock_transfer_received_at',
                'tx_stock_transfers.updated_at AS stock_transfer_updated_at',
                'tx_gj.id AS gj_id',
                'tx_gj.general_journal_no AS general_journal_no',
                'tx_gj.created_at AS gj_created_at',
            )
            // ->where('tx_stock_transfers.stock_transfer_no', '=', 'SMM25-00001')
            ->where('tx_stock_transfers.active', '=', 'Y')
            ->whereRaw('tx_gj.module_no IS NOT null')
            ->where('tx_gj.active', '=', 'Y')
            ->orderBy('tx_stock_transfers.stock_transfer_no', 'ASC')
            // ->limit(5)
            ->get();
            foreach($qStockTransfer AS $qST){
                $htmlBody = '<td style="padding:5px;">'.$i.'#</td>
                    <td style="padding:5px;font-weight: bold;">'.$qST->stock_transfer_no.'</td>
                    <td style="padding:5px;font-weight: bold;">'.$qST->general_journal_no.' ('.$qST->gj_created_at.')</td>';
                $html .= '<tr>'.$htmlBody.'</tr>';

                $htmlBody = '';
                $totalDebetKredit = 0;
                $qStockTransferPart = Tx_stock_transfer_part::leftJoin('mst_parts as msp', 'tx_stock_transfer_parts.part_id', '=', 'msp.id')
                ->select(
                    'tx_stock_transfer_parts.qty',
                    'msp.part_name',
                    'msp.part_number',
                    DB::raw('(SELECT avg_cost 
                        FROM v_log_avg_cost 
                        WHERE part_id=tx_stock_transfer_parts.part_id
                        AND updated_at<\''.$qST->gj_created_at.'\' 
                        ORDER BY updated_at DESC, row_id ASC 
                        LIMIT 1) as last_avg_cost'),
                )
                ->where('tx_stock_transfer_parts.stock_transfer_id', '=', $qST->tx_id)
                ->where('tx_stock_transfer_parts.active', '=', 'Y')
                ->where('msp.active', '=', 'Y')
                ->get();
                foreach($qStockTransferPart as $qSTP){
                    $htmlBody .= '<tr>
                        <td style="padding:5px;">'.$qSTP->part_name.' - '.$qSTP->part_number.'</td>
                        <td style="padding:5px;"> Qty: '.$qSTP->qty.'</td>
                        <td style="padding:5px;"> AVG Cost: '.number_format($qSTP->last_avg_cost,0,".",",").'</td>
                        </tr>';
                    $totalDebetKredit += ($qSTP->qty*$qSTP->last_avg_cost);
                }
                $htmlBody = '<table>'.$htmlBody.'</table>';

                $htmlBody2 = '';
                $debitTmp = 0;
                $kreditTmp = 0;
                $qGJdtl = Tx_general_journal_detail::leftJoin('mst_coas as mcoa', 'tx_general_journal_details.coa_id', '=', 'mcoa.id')
                ->select(
                    'tx_general_journal_details.debit',
                    'tx_general_journal_details.kredit',
                    'mcoa.coa_name as coa_name',
                )
                ->where('tx_general_journal_details.general_journal_id', '=',  $qST->gj_id)
                ->where('tx_general_journal_details.active', '=', 'Y')
                ->where('mcoa.active', '=', 'Y')
                ->get();
                foreach($qGJdtl as $qG){
                    $debitTmp = ($qG->debit>0?$qG->debit:$debitTmp);
                    $kreditTmp = ($qG->kredit>0?$qG->kredit:$kreditTmp);
                    $htmlBody2 .= '<tr>
                        <td style="padding:5px;">'.$qG->coa_name.'</td>
                        <td style="padding:5px;">Debet: '.number_format($qG->debit,0,".",",").'</td>
                        <td style="padding:5px;">Kredit: '.number_format($qG->kredit,0,".",",").'</td>
                        </tr>';
                }
                $htmlBody2 .= '<tr>
                    <td colspan="3" style="padding:5px;">Penyesuaian Jurnal</td>
                    </tr>
                    <tr>
                    <td style="padding:5px;">&nbsp;</td>
                    <td style="padding:5px;'.(intval($debitTmp)>intval($totalDebetKredit)?'color: red;':'color: blue;').'font-weight:bold;">Debet: '.number_format($totalDebetKredit,0,".",",").'</td>
                    <td style="padding:5px;'.(intval($kreditTmp)>intval($totalDebetKredit)?'color: red;':'color: blue;').'font-weight:bold;">Kredit: '.number_format($totalDebetKredit,0,".",",").'</td>
                    </tr>';
                $htmlBody2 = '<table>'.$htmlBody2.'</table>';



                $htmlBody = '<td style="padding:5px;">&nbsp;</td>
                    <td style="padding:5px;vertical-align: top;">'.$htmlBody.'</td>
                    <td style="padding:5px;vertical-align: top;">'.$htmlBody2.'</td>';
                $html .= '<tr>'.$htmlBody.'</tr>';
                $i++;
            }

            $html = '<table>'.$htmlHeader.$html.'</table>';
            echo $html;

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();

            // return redirect()
            // ->back()
            // ->withInput()
            // ->with('status-error',ENV('ERR_MSG_01'));
        } catch(Exception $e){
            DB::rollback();
            throw $e;

            // return redirect()
            // ->back()
            // ->withInput()
            // ->with('status-error',ENV('ERR_MSG_01'));
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
