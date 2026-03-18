<?php

namespace App\Http\Controllers\main;

use Exception;
use Illuminate\Http\Request;
use App\Models\Tx_stock_transfer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_stock_transfer_part;
use Illuminate\Validation\ValidationException;

class DStockTransferController extends Controller
{
    protected $title = 'Stock Transfer';
    protected $folder = 'stock-transfer';

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
                    $del = Tx_stock_transfer::where('id','=',$Ids[$i])
                    ->where([
                        'approved_by' => null
                    ])
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id
                    ]);
                    // if($del){
                    //     $delPart = Tx_stock_transfer_part::where('stock_transfer_id','=',$Ids[$i])
                    //     ->update([
                    //         'active'=>'N',
                    //         'updated_by' => Auth::user()->id
                    //     ]);
                    // }
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

        session()->flash('status', $this->title.' Number has been canceled.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }
}
