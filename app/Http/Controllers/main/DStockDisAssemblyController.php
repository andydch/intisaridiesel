<?php

namespace App\Http\Controllers\main;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use App\Models\Tx_stock_disassembly_part;
use App\Http\Controllers\Controller;
use App\Models\Tx_stock_disassembly;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DStockDisAssemblyController extends Controller
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
                    $del = Tx_stock_disassembly::where('id','=',$Ids[$i])
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id
                    ]);
                    // $delpart = Tx_stock_disassembly_part::where('stock_disassembly_id','=',$Ids[$i])
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

        session()->flash('status', 'Stock Disassembly Number has been canceled.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/stock-assembly');
    }
}
