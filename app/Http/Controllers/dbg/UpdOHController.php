<?php

namespace App\Http\Controllers\dbg;

use Exception;
use App\Models\V_stock_card;
use Illuminate\Http\Request;
use App\Models\V_tx_qty_part;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\V_oh_fix_stock_transfer;
use App\Models\Log_tx_qty_part;
use App\Models\Tx_qty_part;
use Illuminate\Validation\ValidationException;

class UpdOHController extends Controller
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
            $i = 1;
            $htmlHeader = '<tr>
                <td style="padding:5px;font-weight: bold;">#</td>
                <td style="padding:5px;font-weight: bold;">Part Name</td>
                <td style="padding:5px;font-weight: bold;">Part Number</td>
                <td style="padding:5px;font-weight: bold;">OH BPN</td>
                <td style="padding:5px;font-weight: bold;">OH SMD</td>
                <td style="padding:5px;font-weight: bold;">OH SBY</td>
                <td style="padding:5px;font-weight: bold;">OH BPN (Stock Card)</td>
                <td style="padding:5px;font-weight: bold;">OH SMD (Stock Card)</td>
                <td style="padding:5px;font-weight: bold;">OH SBY (Stock Card)</td>
                </tr>';
            // $qOH = V_oh_fix_stock_transfer::where('part_number', '=', '17801LDE70')->get();
            $qOH = V_oh_fix_stock_transfer::get();
            foreach($qOH as $oh){
                $ohBPN = $this->fOH($oh, 7, '2025-07-01', '2027-01-01');
                $ohSMD = $this->fOH($oh, 8, '2025-07-01', '2027-01-01');
                $ohSBY = $this->fOH($oh, 9, '2025-07-01', '2027-01-01');
                $htmlBody = '<td style="padding:5px;">'.$i.'</td>
                    <td style="padding:5px;">'.$oh->part_name.'</td>
                    <td style="padding:5px;">'.$oh->part_number.'</td>
                    <td style="padding:5px;'.($ohBPN<>$oh->oh_bpn_current?'color: red;font-weight:bold;font-size: larger;':'').'">'.$oh->oh_bpn_current.'</td>
                    <td style="padding:5px;'.($ohSMD<>$oh->oh_smd_current?'color: red;font-weight:bold;font-size: larger;':'').'">'.$oh->oh_smd_current.'</td>
                    <td style="padding:5px;'.($ohSBY<>$oh->oh_sby_current?'color: red;font-weight:bold;font-size: larger;':'').'">'.$oh->oh_sby_current.'</td>
                    <td style="padding:5px;">'.$ohBPN.'</td>
                    <td style="padding:5px;">'.$ohSMD.'</td>
                    <td style="padding:5px;">'.$ohSBY.'</td>';
                    
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
            // throw $e;

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

    public function fOH($oh, $branch_id, $dateFrom, $dateTo){
        $intOH_BPN_stockcard = 0;

        $qBeginningBalanceOth = V_tx_qty_part::whereRaw('part_id=(SELECT id FROM mst_parts WHERE part_number=\''.$oh->part_number.'\')')
        ->where('branch_id','=', $branch_id)
        ->whereRaw('updated_at>\''.$dateFrom.' 00:00:00\' AND updated_at<\''.$dateTo.' 23:59:59\'')
        ->orderBy('updated_at','ASC')
        ->first();
        
        $qStockCardBPNfirst = V_stock_card::whereRaw('part_id=(SELECT id FROM mst_parts WHERE part_number=\''.$oh->part_number.'\')')
        ->where('branch_id','=', $branch_id)
        ->where('doc_no','NOT LIKE','%Draft%')
        ->where('tx_date','>=',$dateFrom)
        ->where('tx_date','<=',$dateTo)
        ->orderBy('tx_date','ASC')
        ->orderBy('updated_at','ASC')
        ->first();
        if ($qStockCardBPNfirst){
            $qBeginningBalance = V_tx_qty_part::whereRaw('part_id=(SELECT id FROM mst_parts WHERE part_number=\''.$oh->part_number.'\')')
            ->where('branch_id','=', $branch_id)
            ->whereRaw('updated_at<\''.$qStockCardBPNfirst->updated_at.'\'')
            ->orderBy('updated_at','ASC')
            ->first();
            if ($qBeginningBalance){
                $intOH_BPN_stockcard = $qBeginningBalance->qty;
            }else{
                if ($qBeginningBalanceOth){
                    $intOH_BPN_stockcard = $qBeginningBalanceOth->qty;
                }
            }
        }else{
            $qBeginningBalance = V_tx_qty_part::whereRaw('part_id=(SELECT id FROM mst_parts WHERE part_number=\''.$oh->part_number.'\')')
            ->where('branch_id','=', $branch_id)
            ->whereRaw('updated_at<\''.$dateFrom.' 00:00:00\'')
            ->where('qty','>',0)
            ->orderBy('updated_at','ASC')
            ->first();
            if ($qBeginningBalance){
                $intOH_BPN_stockcard = $qBeginningBalance->qty;
            }else{
                if ($qBeginningBalanceOth){
                    $intOH_BPN_stockcard = $qBeginningBalanceOth->qty;
                }
            }
        }

        $qStockCardBPNget = V_stock_card::whereRaw('part_id=(SELECT id FROM mst_parts WHERE part_number=\''.$oh->part_number.'\')')
        ->where('branch_id','=', $branch_id)
        ->where('doc_no','NOT LIKE','%Draft%')
        ->where('tx_date','>=',$dateFrom)
        ->where('tx_date','<=',$dateTo)
        ->orderBy('tx_date','ASC')
        ->orderBy('updated_at','ASC')
        ->get();
        foreach($qStockCardBPNget as $qSC){
            // $docs .= '<br/>'.$qSC->doc_no.'::'.$qSC->status.'::'.$qSC->qty;
            if ($qSC->status=='IN'){
                $intOH_BPN_stockcard += $qSC->qty;
            }
            if ($qSC->status=='OUT'){
                $intOH_BPN_stockcard = $intOH_BPN_stockcard-$qSC->qty;
            }
        }

        return $intOH_BPN_stockcard;
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
