<?php

namespace App\Http\Controllers\main;

use Exception;
use App\Models\Tx_invoice;
use Illuminate\Http\Request;
// use App\Models\Tx_invoice_part;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DInvoiceController extends Controller
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
                    $del = Tx_invoice::where('id','=',$Ids[$i])
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id
                    ]);
                    // if($del){
                    //     $delPart = Tx_invoice_part::where('invoice_id','=',$Ids[$i])
                    //         ->update([
                    //             'active'=>'N',
                    //             'updated_by' => Auth::user()->id
                    //         ]);
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

        session()->flash('status', 'Invoice has been canceled.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/invoice');
    }
}
