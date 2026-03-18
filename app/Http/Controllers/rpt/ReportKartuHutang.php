<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportKartuHutang extends Controller
{
    protected $title;
    protected $folder = 'rpt-finance-kartu-hutang';

    public function __construct()
    {
        $this->title = ucwords(strtolower(ENV('KARTU_HUTANG')));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userLogin = Userdetail::where('user_id', '=', Auth::user()->id)
        ->first();

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $suppliers = Mst_supplier::where([
            'active' => 'Y',
        ])
        ->orderBy('name', 'ASC')
        ->get();

        $branches = Mst_branch::where([
            'active' => 'Y',
        ])
        ->orderBy('name', 'ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => ($qCurrency?$qCurrency:[]),
            'suppliers' => $suppliers,
            'branches' => $branches,
            'userLogin' => $userLogin,
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
            'supplier_id' => 'required',
            'date_start' => 'required|date',
            'date_end' => 'required|date',
            'branch_id' => 'required',
        ];
        $errMsg = [
            'supplier_id.required' => 'Supplier is required',
            'date_start.required' => 'Period start date is required',
            'date_start.date' => 'Period start date must be date',
            'date_end.required' => 'Period end date is required',
            'date_end.date' => 'Period end date must be date',
            'branch_id.required' => 'Branch is required',
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

        // if($request->view_mode=='V'){
        //     $data = [
        //         'title' => $this->title,
        //         'folder' => $this->folder,
        //         'reqs' => $request,
        //         'qCurrency' => ($qCurrency?$qCurrency:[]),
        //     ];
        //     return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
        // }
        // if($request->view_mode=='P'){
        //     return redirect(ENV('REPORT_FOLDER_NAME').'/'.$this->folder.'-xlsx/'.urlencode($request->coa_id).'/'.urlencode($request->date_start).'/'.urlencode($request->date_end));
        // }
        return redirect(ENV('REPORT_FOLDER_NAME').'/'.$this->folder.'-xlsx/'.
            urlencode($request->supplier_id).'/'.
            urlencode($request->date_start).'/'.
            urlencode($request->date_end).'/'.
            urlencode($request->branch_id));
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
