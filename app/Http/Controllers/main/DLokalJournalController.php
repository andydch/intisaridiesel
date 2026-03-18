<?php

namespace App\Http\Controllers\main;

use Exception;
use Illuminate\Http\Request;
use App\Models\Tx_lokal_journal;
// use App\Models\Tx_lokal_journal_detail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DLokalJournalController extends Controller
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

            $Ids = explode(',',$request->generalJournalId);
            for($i=0;$i<count($Ids);$i++){
                if($Ids[$i]!==''){

                    // $del = Tx_lokal_journal::where('id','=',$Ids[$i])
                    // ->update([
                    //     'active'=>'N',
                    //     'updated_by' => Auth::user()->id
                    // ]);

                    // $del = Tx_lokal_journal_detail::where('general_journal_id','=',$Ids[$i])
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

        session()->flash('status', 'General Journal has been canceled.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/general-journal');
    }
}
