<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_part;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use Illuminate\Support\Facades\Validator;

class ReportMovementOfPartsController extends Controller
{
    protected $title = 'Parts Movement';
    protected $folder = 'pergerakan-barang';

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
            'title' => $this->title,
            'folder' => $this->folder,
            'query' => [],
            'queryBranchBeginningBalance' => [],
            'queryStockCard' => [],
            'qCurrency' => $qCurrency,
            'branches' => $branches,
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
            'branch_id' => 'required|numeric',
            'date_start' => 'required|date',
            'date_end' => 'required|date',
        ];
        $errMsg = [
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
            'date_start.required' => 'Date Start is required',
            'date_start.date' => 'Date Start must be date',
            'date_end.required' => 'Date End is required',
            'date_end.date' => 'Date End must be date',
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

        $date_start = '';
        $date_end = '';
        if($request->date_start!=''){
            $date_start = $request->date_start;
        }
        if($request->date_end!=''){
            $date_end = $request->date_end;
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'reqs' => $request,
            'branches' => $branches,
        ];
        if($request->view_mode=='V'){
            return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
        }
        if($request->view_mode=='P'){
            return redirect(ENV('REPORT_FOLDER_NAME').'/'.$this->folder.'-xlsx/'.
                $request->branch_id.'/'.urlencode($date_start).'/'.urlencode($date_end));
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
