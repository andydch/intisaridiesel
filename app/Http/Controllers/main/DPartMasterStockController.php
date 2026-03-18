<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mst_part;
use App\Models\Mst_part_brand_type;
use App\Models\Mst_part_subtitution;
use Illuminate\Support\Facades\Auth;

class DPartMasterStockController extends Controller
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
                $del = Mst_part::where('id','=',$Ids[$i])
                ->update([
                    'active'=>'N',
                    'updated_by' => Auth::user()->id
                ]);
                $delBrand = Mst_part_brand_type::where('part_id','=',$Ids[$i])
                ->update([
                    'active'=>'N',
                    'updated_by' => Auth::user()->id
                ]);
                $delBrand = Mst_part_subtitution::where('part_id','=',$Ids[$i])
                ->orWhere('part_other_id',$Ids[$i])
                ->update([
                    'active'=>'N',
                    'updated_by' => Auth::user()->id
                ]);
            }
        }

        session()->flash('status', 'Data has been deleted.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/stock-master/'.urlencode('::::::::::::::'));
    }
}
