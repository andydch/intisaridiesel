<?php

namespace App\Http\Controllers\main;

use Exception;
use Illuminate\Http\Request;
use App\Models\Tx_payment_receipt;
// use App\Models\Tx_payment_receipt_invoice;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tx_general_journal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DPaymentReceiptController extends Controller
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

            $Ids = explode(',',$request->payment_receiptId);
            for($i=0;$i<count($Ids);$i++){
                if($Ids[$i]!=''){
                    $del = Tx_payment_receipt::where([
                        'id' => $Ids[$i],
                    ])
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id
                    ]);

                    // $delDtl = Tx_payment_receipt_invoice::where([
                    //     'payment_receipt_id' => $Ids[$i],
                    // ])
                    // ->update([
                    //     'active'=>'N',
                    //     'updated_by' => Auth::user()->id
                    // ]);

                    $q = Tx_payment_receipt::where([
                        'id' => $Ids[$i],
                    ])
                    ->first();
                    if ($q){
                        $updJournals = Tx_general_journal::where([
                            'module_no'=>$q->payment_receipt_no,
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

        session()->flash('status', 'Payment Receipt number has been canceled.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/payment-receipt');
    }
}
