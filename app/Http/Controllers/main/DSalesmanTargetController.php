<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mst_salesman_target;
use Illuminate\Support\Facades\Auth;

class DSalesmanTargetController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $Ids = explode(',',$request->all_ids);
        for($i=0;$i<count($Ids);$i++){
            if($Ids[$i]!==''){
                $del = Mst_salesman_target::where('id','=',$Ids[$i])
                ->update([
                    'active'=>'N',
                    'updated_by' => Auth::user()->id
                ]);
            }
        }

        session()->flash('status', 'Data has been deleted.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$request->next_uri);
    }
}
