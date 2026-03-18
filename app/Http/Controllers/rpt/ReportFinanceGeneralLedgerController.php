<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_global;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use App\Models\Mst_coa;
use Illuminate\Support\Facades\Validator;

class ReportFinanceGeneralLedgerController extends Controller
{
    protected $title = 'General Ledger';
    protected $folder = 'rpt-finance-general-ledger';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $branches = Mst_branch::where([
            'active'=>'Y',
        ])
        ->get();

        $coas = Mst_coa::whereRaw('branch_id IS NOT null')
        ->whereRaw('local IS NOT null')
        ->where([
            'active'=>'Y',
        ])
        ->orderBy('coa_name','ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => ($qCurrency?$qCurrency:[]),
            'branches' => $branches,
            'coas' => $coas,
        ];
        return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validateInput = [
            'coa_id' => 'required|numeric',
            'branch_id' => 'required|numeric',
            'date_start' => 'required|date',
            'date_end' => 'required|date',
        ];
        $errMsg = [
            'coa_id.required' => 'COA is required',
            'coa_id.numeric' => 'COA is required',
            'branch_id.required' => 'Branch is required',
            'branch_id.numeric' => 'Branch is required',
            'date_start.required' => 'Period start date is required',
            'date_start.date' => 'Period start date must be date',
            'date_end.required' => 'Period end date is required',
            'date_end.date' => 'Period end date must be date',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )->validate();

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        if($request->view_mode=='V'){
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'reqs' => $request,
                'qCurrency' => ($qCurrency?$qCurrency:[]),
            ];
            return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
        }
        if($request->view_mode=='P'){
            $coa_id = ($request->coa_id==''?'x':$request->coa_id);
            $branch_id = ($request->branch_id=='' || !is_numeric($request->branch_id)?0:$request->branch_id);
            return redirect(ENV('REPORT_FOLDER_NAME').'/'.$this->folder.'-xlsx/'.$coa_id.'/'.$branch_id.'/'.urlencode($request->date_start).'/'.urlencode($request->date_end));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
