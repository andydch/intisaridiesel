<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ReportStockInventoryAccPerBranchController extends Controller
{
    protected $title = 'Stock Inventory Accuration Per Branch';
    protected $folder = 'stock-inventory-accuration-per-branch';

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

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $data = [
            'branches' => $branches,
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
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
            'year_id' => 'required|numeric',
            'branch_id' => 'required|numeric',
        ];
        $errMsg = [
            'year_id.required' => 'Please select a year',
            'year_id.numeric' => 'Please select a year',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
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

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        if($request->view_mode=='V'){
            $data = [
                'branches' => $branches,
                'title' => $this->title,
                'folder' => $this->folder,
                'reqs' => $request,
                'qCurrency' => $qCurrency,
            ];
            return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
        }
        if($request->view_mode=='P'){
            return redirect(ENV('REPORT_FOLDER_NAME').'/stock-inventory-accuration-per-branch-xlsx/'.$request->branch_id.'/'.$request->year_id);
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
    public function destroy(Mst_part $mst_part)
    {
        //
    }
}
