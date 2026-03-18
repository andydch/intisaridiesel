<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_part;
// use App\Models\Tx_qty_part;
// use App\Models\V_tx_qty_part;
use Illuminate\Http\Request;
// use App\Models\Tx_delivery_order_part;
// use App\Models\Tx_receipt_order_part;
// use App\Models\Tx_purchase_memo_part;
// use App\Models\Tx_purchase_order_part;
use App\Models\Mst_global;
use App\Models\Mst_branch;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ReportSummaryStockPerGudangPerMerkController extends Controller
{
    protected $title = 'Summary Stock Per Branch Per Merk';
    protected $folder = 'summary-stock-per-gudang-per-merk';

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
        $brands = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y'
        ])
        ->orderBy('title_ind', 'ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'branches' => $branches,
            'brands' => $brands,
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
            'brand_id' => 'required|numeric',
            'date_start' => 'required',
        ];
        $errMsg = [
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
            'brand_id.required' => 'Please select a valid brand',
            'brand_id.numeric' => 'Please select a valid brand',
            'date_start.required' => 'Per Date is required',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $brands = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y'
        ])
        ->orderBy('title_ind', 'ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'branches' => $branches,
            'brands' => $brands,
            'reqs' => $request,
        ];
        switch ($request->view_mode) {
            case 'P':
                return redirect(ENV('REPORT_FOLDER_NAME').'/'.$this->folder.'-xlsx/'.$request->branch_id.'/'.$request->brand_id.'/'.urlencode($request->date_start));
                break;
            default:
                return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
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
