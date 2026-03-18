<?php

namespace App\Http\Controllers\main;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_quotation;
use Illuminate\Database\Query\Builder;
use App\Models\Tx_purchase_quotation_part;
use Illuminate\Validation\ValidationException;

class DQuotationController extends Controller
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

            $Ids = explode(',',$request->quotationId);
            for($i=0;$i<count($Ids);$i++){
                if($Ids[$i]!=''){
                    $del = Tx_purchase_quotation::where([
                        'id' => $Ids[$i],
                    ])
                    ->whereNotIn('id', function (Builder $query) {
                        $query->select('quotation_id')
                        ->from('tx_purchase_orders')
                        ->where('quotation_id','<>',null);
                    })
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id
                    ]);

                    // $del = Tx_purchase_quotation_part::where([
                    //     'quotation_id' => $Ids[$i],
                    // ])
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

        session()->flash('status', 'Purchase Quotation number has been canceled.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/quotation');
    }
}
