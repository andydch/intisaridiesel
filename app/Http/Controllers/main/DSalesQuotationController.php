<?php

namespace App\Http\Controllers\main;

use Exception;
use Illuminate\Http\Request;
use App\Models\Tx_sales_quotation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use App\Models\Tx_sales_quotation_part;
use Illuminate\Validation\ValidationException;

class DSalesQuotationController extends Controller
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
                    $del = Tx_sales_quotation::where([
                        'id' => $Ids[$i],
                    ])
                    ->whereNotIn('id', function (Builder $query) {
                        $query->select('sales_quotation_id')
                            ->from('tx_sales_orders')
                            ->where('sales_quotation_id','<>',null);
                    })
                    ->update([
                        'active'=>'N',
                        'cancel_by' => Auth::user()->id,
                        'cancel_time' => now(),
                        'updated_by' => Auth::user()->id
                    ]);

                    // $del = Tx_sales_quotation_part::where([
                    //     'sales_quotation_id' => $Ids[$i],
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

        session()->flash('status', 'Quotation number has been canceled.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME') . '/sales-quotation');
    }
}
