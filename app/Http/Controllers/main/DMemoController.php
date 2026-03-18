<?php

namespace App\Http\Controllers\main;

use Exception;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_memo;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_memo_part;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Database\Query\Builder;

class DMemoController extends Controller
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

            $Ids = explode(',',$request->memoId);
            for($i=0;$i<count($Ids);$i++){
                if($Ids[$i]!==''){
                    $del = Tx_purchase_memo::where('id','=',$Ids[$i])
                    ->whereNotIn('memo_no', function (Builder $query) {
                        $query->select('po_mo_no')
                        ->from('tx_receipt_order_parts');
                    })
                    ->update([
                        'active'=>'N',
                        'canceled_by' => Auth::user()->id,
                        'canceled_at' => now(),
                        'updated_by' => Auth::user()->id
                    ]);

                    // $del = Tx_purchase_memo_part::where('memo_id','=',$Ids[$i])
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

        session()->flash('status', 'Memo number has been canceled.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/memo');
    }
}
