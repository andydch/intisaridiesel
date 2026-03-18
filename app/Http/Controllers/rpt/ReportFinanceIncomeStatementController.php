<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_global;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use Illuminate\Support\Facades\Validator;

class ReportFinanceIncomeStatementController extends Controller
{
    protected $title = 'Income Statement';
    protected $folder = 'rpt-finance-income-statement';

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

        $months = 'All,January,February,March,April,May,June,July,August,September,October,Nopember,December';
        $latest_year = date_format(now(),"Y");

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'branches' => $branches,
            'months' => $months,
            'latest_year' => $latest_year,
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
            'lokal_input' => 'nullable',
            'branch_id' => 'required|numeric',
            'month_id' => 'required|numeric',
            'year_id' => 'required|numeric',
        ];
        $errMsg = [
            'branch_id.required' => 'Branch is required',
            'branch_id.numeric' => 'Branch is required',
            'month_id.required' => 'Month is required',
            'month_id.numeric' => 'Month must be numeric',
            'year_id.required' => 'Year is required',
            'year_id.numeric' => 'Year must be numeric',
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
            $lokal_input = ($request->lokal_input==''?'x':$request->lokal_input);
            $branch_id = ($request->branch_id=='' || !is_numeric($request->branch_id)?0:$request->branch_id);
            return redirect(ENV('REPORT_FOLDER_NAME').'/'.$this->folder.'-xlsx/'.$lokal_input.'/'.$branch_id.'/'.urlencode($request->month_id).'/'.urlencode($request->year_id));
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
