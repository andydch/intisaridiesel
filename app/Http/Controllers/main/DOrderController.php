<?php

namespace App\Http\Controllers\main;

use Exception;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
// use App\Models\Tx_purchase_order_part;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\ValidationException;

class DOrderController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // Start transaction!
        DB::beginTransaction();

        try {

            $Ids = explode(',',$request->orderId);
            for($i=0;$i<count($Ids);$i++){
                if($Ids[$i]!==''){
                    $del = Tx_purchase_order::where('id','=',$Ids[$i])
                    ->whereNotIn('purchase_no', function (Builder $query) {
                        $query->select('tx_rop.po_mo_no')
                        ->from('tx_receipt_order_parts AS tx_rop')
                        ->leftJoin('tx_receipt_orders AS tx_ro', 'tx_ro.id', '=', 'tx_rop.receipt_order_id')
                        ->where('tx_rop.active', 'Y')
                        ->where('tx_ro.is_draft', 'N')
                        ->where('tx_ro.active', 'Y');
                    })
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id
                    ]);

                    // $del = Tx_purchase_order_part::where('order_id','=',$Ids[$i])
                    // ->update([
                    //     'active'=>'N',
                    //     'updated_by' => Auth::user()->id
                    // ]);
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
            // throw $e;

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        }

        // If we reach here, then
        // data is valid and working.
        // Commit the queries!
        DB::commit();

        session()->flash('status', 'Order Number has been canceled.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/order');
    }
}
