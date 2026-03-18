<?php

namespace App\Http\Controllers\tx;

use App\Http\Controllers\Controller;
use App\Models\Tx_receipt_order;
use App\Models\Tx_receipt_order_part;
use App\Models\Mst_global;
use App\Models\Mst_company;
use Illuminate\Http\Request;
use PDF;

class ReceiptOrderPrintController extends Controller
{
    protected $title = 'Receipt Order';
    protected $folder = 'receipt-order';

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
     * @param  \App\Models\Tx_receipt_order  $tx_purchase_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '512M');

        $query = Tx_receipt_order::where('id', '=', $id)
        ->first();
        if ($query) {
            $queryPart = Tx_receipt_order_part::where([
                'receipt_order_id' => $query->id,
                'active' => 'Y'
            ])
            ->orderBy('po_mo_no','ASC')
            ->get();
            $queryPartCount = Tx_receipt_order_part::where([
                'receipt_order_id' => $query->id,
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

            $qCurrency = Mst_global::where([
                'id' => 3,
                'data_cat' => 'currency',
                'active' => 'Y'
            ])
            ->first();

            // supplier type
            // 10 : internasional
            // 11 : lokal
            $filePDF = '';
            if($query->supplier_type_id==10){
                $filePDF = 'tx.'.$this->folder.'.receipt-order-internasional-pdf';
            }
            if($query->supplier_type_id==11){
                $filePDF = 'tx.'.$this->folder.'.receipt-order-lokal-pdf';
            }

            $data = [
                'receipt_orders' => $query,
                'parts' => $queryPart,
                'partsCount' => $queryPartCount,
                'vat' => $vat,
                'companyName' => $companyName,
                'qCurrency' => $qCurrency
            ];
            $pdf = PDF::loadView($filePDF, $data);
            // $pdf->debug = true;
            return $pdf->stream('document-receipt_order-'.$query->purchase_receipt_order_no.'.pdf');
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
     * @param  \App\Models\Tx_receipt_order  $tx_purchase_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function edit(Tx_receipt_order $tx_purchase_receipt_order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_receipt_order  $tx_purchase_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tx_receipt_order $tx_purchase_receipt_order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_purchase_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_receipt_order $tx_purchase_receipt_order)
    {
        //
    }
}
