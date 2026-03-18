<?php

namespace App\Http\Controllers\tx;

use App\Http\Controllers\Controller;
use App\Models\Tx_purchase_order;
use App\Models\Tx_purchase_order_part;
use App\Models\Mst_global;
use App\Models\Mst_company;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use PDF;

class OrderPrintController extends Controller
{
    protected $title = 'Purchase Order';
    protected $folder = 'order';

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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_memo
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '512M');

        $query = Tx_purchase_order::where('id', '=', $id)
        ->first();
        if ($query) {
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $qIsDirector = Userdetail::where('user_id','=',$query->director_updated_by)
            ->where('is_director','=','Y')
            ->first();

            $queryPart = Tx_purchase_order_part::where([
                'order_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $queryPartCount = Tx_purchase_order_part::where([
                'order_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();

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

            $companyName = '';
            $company = Mst_company::where('active','=','Y')
            ->first();
            if($company){
                $companyName = $company->name;
            }

            $data = [
                'orders' => $query,
                'parts' => $queryPart,
                'partsCount' => $queryPartCount,
                'vat' => $vat,
                'companyName' => $companyName,
                'userLogin' => $userLogin,
                'qIsDirector' => $qIsDirector
            ];

            // $pdf = PDF::loadView('tx.'.$this->folder.'.order-pdf',$data,[],[
            //     'margin_left' => 20,
            //     'margin_right' => 10,
            // ]);
            $pdf = PDF::loadView('tx.'.$this->folder.'.order-pdf', $data);
            $pdf->simpleTables = true;
            // $pdf->debug = true;
            return $pdf->stream('document-order-'.$query->purchase_no.'.pdf');
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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_memo
     * @return \Illuminate\Http\Response
     */
    public function edit(Tx_purchase_order $tx_purchase_memo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_memo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tx_purchase_order $tx_purchase_memo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_memo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_order $tx_purchase_memo)
    {
        //
    }
}
