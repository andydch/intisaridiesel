<?php

namespace App\Http\Controllers\main;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tx_delivery_order_non_tax;
use App\Models\Tx_delivery_order_non_tax_part;
use App\Models\Tx_general_journal;
use Illuminate\Support\Facades\Auth;
use App\Models\Userdetail;
use App\Models\Tx_qty_part;
use Illuminate\Validation\ValidationException;

class DDeliveryOrderNonTaxController extends Controller
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

                    $delPart = Tx_delivery_order_non_tax_part::leftJoin('tx_delivery_order_non_taxes AS tx_do','tx_delivery_order_non_tax_parts.delivery_order_id','=','tx_do.id')
                    ->leftJoin('tx_sales_orders AS tx_so','tx_delivery_order_non_tax_parts.sales_order_id','=','tx_so.id')
                    ->select(
                        'tx_do.created_by as createdBy',
                        'tx_delivery_order_non_tax_parts.part_id',
                        'tx_delivery_order_non_tax_parts.qty',
                        'tx_so.branch_id',
                    )
                    ->where([
                        'tx_delivery_order_non_tax_parts.delivery_order_id' => $Ids[$i],
                        'tx_delivery_order_non_tax_parts.active' => 'Y',
                    ])
                    ->where('tx_do.delivery_order_no','NOT LIKE','%Draft%')
                    ->get();
                    foreach($delPart as $delP){
                        $userDetail = Userdetail::where('user_id','=',$delP->createdBy)
                        ->first();

                        $qtyStock = Tx_qty_part::where('part_id','=',$delP->part_id)
                        ->where('branch_id','=',$delP->branch_id)
                        ->first();
                        if($qtyStock){
                            $updStock = Tx_qty_part::where('part_id','=',$delP->part_id)
                            ->where('branch_id','=',$delP->branch_id)
                            ->update([
                                'qty' => $qtyStock->qty+$delP->qty,
                            ]);
                        }
                    }

                    $del = Tx_delivery_order_non_tax::where('id','=',$Ids[$i])
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id
                    ]);
                    $delPart = Tx_delivery_order_non_tax_part::where('delivery_order_id','=',$Ids[$i])
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id
                    ]);

                    $q = Tx_delivery_order_non_tax::where([
                        'id' => $Ids[$i],
                    ])
                    ->first();
                    if ($q){
                        $updJournals = Tx_general_journal::where([
                            'module_no'=>$q->delivery_order_no,
                            'active'=>'Y',
                        ])
                        ->update([
                            'active'=>'N',
                            'updated_by' => Auth::user()->id
                        ]);
                    }
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

        session()->flash('status', 'Delivery Order Number has been canceled.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order-local');
    }
}
