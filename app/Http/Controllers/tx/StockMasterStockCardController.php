<?php

namespace App\Http\Controllers\tx;

use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\V_stock_card;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StockMasterStockCardController extends Controller
{
    protected $title = 'Stock Master - Stock Card';
    protected $folder = 'stock-master';
    protected $uri_folder = 'stock-master-stock-card';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->to(url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master'));
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
            'from_date' => 'required',
            'to_date' => 'required',
        ];
        $errMsg = [];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )->validate();

        $queryPart = Mst_part::where('id','=',$request->part_idx)
        ->first();

        $queryBranch = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $queryBranchBeginningBalance = Mst_branch::where('active','=','Y')
        ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
            $q->where('id','=', $request->branch_id);
        })
        ->get();

        // old format : YYYY-MM-DD
        // new format : DD/MM/YYYY
        $from_date = explode("/",$request->from_date);
        $to_date = explode("/",$request->to_date);
        $queryStockCard = V_stock_card::where([
            'part_id' => $request->part_idx
        ])
        ->when(request()->has('branch_id') && request()->branch_id<>'',
            function($q) use($request) {
            $q->where('branch_id','=', $request->branch_id);
        })
        ->where('doc_no','NOT LIKE','%Draft%')
        ->where('tx_date','>=',$from_date[2].'-'.$from_date[1].'-'.$from_date[0])
        ->where('tx_date','<=',$to_date[2].'-'.$to_date[1].'-'.$to_date[0])
        // ->where('updated_at','>=',$from_date[2].'-'.$from_date[1].'-'.$from_date[0].' 00:00:00')
        // ->where('updated_at','<=',$to_date[2].'-'.$to_date[1].'-'.$to_date[0].' 23:59:59')
        ->orderBy('tx_date','ASC')
        ->orderBy('updated_at','ASC')
        ->orderBy('doc_no','ASC')
        ->orderBy('status','DESC');

        $data = [
            'stockcards_part' => $queryStockCard->get(),
            'stockcards_part_first' => $queryStockCard->first(),
            'title' => $this->title,
            'folder' => $this->folder,
            'uri_folder' => $this->uri_folder,
            'queryPart' => $queryPart,
            'queryBranch' => $queryBranch,
            'queryBranchBeginningBalance' => $queryBranchBeginningBalance,
            'request' => $request
        ];

        return view('tx.'.$this->folder.'.index-stock-card', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $queryPart = Mst_part::where('id','=',$id)->first();
        $queryBranch = Mst_branch::where('active','=','Y')
            ->orderBy('name','ASC')
            ->get();
        $data = [
            'stockcards_qty' => [],
            'stockcards_part' => [],
            'title' => $this->title,
            'folder' => $this->folder,
            'uri_folder' => $this->uri_folder,
            'queryPart' => $queryPart,
            'queryBranch' => $queryBranch,
            'queryBranchBeginningBalance' => [],
        ];

        return view('tx.'.$this->folder.'.index-stock-card', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_order $tx_purchase_order)
    {
        //
    }
}
