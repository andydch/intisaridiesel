<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_part;
use App\Models\Mst_global;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use App\Models\Mst_supplier;
use Illuminate\Support\Facades\Validator;

class ReportPurchasePerSupplierPerBranchController extends Controller
{
    protected $title = 'Purchase Per Supplier Per Branch';
    protected $folder = 'purchase-per-supplier-per-cabang';

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

        $suppliers = Mst_supplier::where('active', '=', 'Y')
        ->orderBy('name', 'ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'branches' => $branches,
            'suppliers' => $suppliers,
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
            'supplier_id' => 'required|numeric',
        ];
        $errMsg = [
            'branch_id.required' => 'Branch is required',
            'branch_id.numeric' => 'Branch is required',
            'supplier_id.required' => 'Supplier is required',
            'supplier_id.numeric' => 'Supplier is required',
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

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        if($request->view_mode=='V'){
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'reqs' => $request,
                'qCurrency' => $qCurrency,
                'vat' => $vat,
                'branches' => $branches,
            ];
            return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
        }
        if($request->view_mode=='P'){
            return redirect(ENV('REPORT_FOLDER_NAME').'/'.$this->folder.'-xlsx/'.$request->branch_id.'/'.$request->date_start.'/'.$request->date_end.'/'.$request->supplier_id);
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
