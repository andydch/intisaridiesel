<?php

namespace App\Http\Controllers\tx;

use PDF;
use App\Models\Mst_company;
use Illuminate\Http\Request;
use App\Models\Tx_stock_disassembly;
use App\Http\Controllers\Controller;
use App\Models\Mst_global;
use App\Models\Tx_stock_disassembly_part;
use App\Models\User;
use App\Models\Userdetail;

class StockDisAssemblyPrintController extends Controller
{
    protected $title = 'Stock Disassembly';
    protected $folder = 'stock-disassembly';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_stock_disassembly  $tx_purchase_retur
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '512M');

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $query = Tx_stock_disassembly::where('id', '=', urldecode($id))
        ->first();
        if ($query) {
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $owner = User::where('email','=','sulian@intimotor.com')
            ->first();

            $queryPart = Tx_stock_disassembly_part::where([
                'stock_disassembly_id' => $query->id,
                'active' => 'Y'
            ]);

            $companyName = '';
            $company = Mst_company::where('active','=','Y')
            ->first();
            if($company){
                $companyName = $company->name;
            }

            $data = [
                'stock_disassemblies' => $query,
                'parts' => $queryPart->get(),
                'partsCount' => $queryPart->count(),
                'companyName' => $companyName,
                'userLogin' => $userLogin,
                'qCurrency' => $qCurrency,
                'owner' => $owner
            ];
            $pdf = PDF::loadView('tx.'.$this->folder.'.stock-disassembly-pdf', $data);
            // $pdf->debug = true;
            return $pdf->stream('document-stock-disassembly-'.$query->stock_disassembly_no.'.pdf');
        } else {
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_stock_disassembly  $tx_purchase_retur
     * @return \Illuminate\Http\Response
     */
    public function edit(Tx_stock_disassembly $tx_purchase_retur)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_stock_disassembly  $tx_purchase_retur
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tx_stock_disassembly $tx_purchase_retur)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_stock_disassembly  $tx_purchase_retur
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_stock_disassembly $tx_purchase_retur)
    {
        //
    }
}
