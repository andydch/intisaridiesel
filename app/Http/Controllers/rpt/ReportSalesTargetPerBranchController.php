<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_part;
use App\Models\Mst_global;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ReportSalesTargetPerBranchController extends Controller
{
    protected $title = 'Sales Target Per Branch';
    protected $folder = 'sales-target-per-branch';

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

        $data = [
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
            'period_year' => 'required|numeric',
        ];
        $errMsg = [
            'period_year.required' => 'Tahun is required',
            'period_year.numeric' => 'Tahun must be numeric',
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

        // get active VAT
        $vat = ENV('VAT');
        $qVat = Mst_global::where([
            'data_cat' => 'vat',
            'active' => 'Y'
        ])
        ->first();
        if ($qVat) {
            $vat = $qVat->numeric_val;
        }

        if($request->view_mode=='V'){
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'reqs' => $request,
                'qCurrency' => $qCurrency,
                'vat' => $vat,
            ];
            return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
        }
        if($request->view_mode=='P'){
            return redirect(ENV('REPORT_FOLDER_NAME').'/'.$this->folder.'-xlsx/'.$request->period_year);
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
