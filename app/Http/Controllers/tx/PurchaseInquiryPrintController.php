<?php

namespace App\Http\Controllers\tx;

use App\Http\Controllers\Controller;
use App\Models\Tx_purchase_inquiry;
use App\Models\Tx_purchase_inquiry_part;
use App\Models\Mst_company;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use PDF;

class PurchaseInquiryPrintController extends Controller
{
    protected $title = 'Purchase Inquiry';
    protected $folder = 'purchase-inquiry';

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
     * @param  \App\Models\Tx_purchase_inquiry  $tx_purchase_inquiry
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '64M');

        $query = Tx_purchase_inquiry::where('slug','=',urldecode($slug))
        ->first();
        if ($query) {
            $queryPart = Tx_purchase_inquiry_part::where([
                'purchase_inquiry_id' => $query->id,
                'active' => 'Y'
            ]);

            $companyName = '';
            $company = Mst_company::where('active','=','Y')
            ->first();
            if($company){
                $companyName = $company->name;
            }

            $userdetails = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $data = [
                'p_inquiries' => $query,
                'p_inquiries_parts' => $queryPart->get(),
                'companyName' => $companyName,
                'userdetails' => $userdetails
            ];
            $pdf = PDF::loadView('tx.'.$this->folder.'.purchase-inquiry-pdf', $data);
            // $pdf->debug = true;
            return $pdf->stream('document-inquiry-'.$query->purchase_inquiry_no.'.pdf');
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
     * @param  \App\Models\Tx_purchase_inquiry  $tx_purchase_inquiry
     * @return \Illuminate\Http\Response
     */
    public function edit(Tx_purchase_inquiry $tx_purchase_inquiry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_purchase_inquiry  $tx_purchase_inquiry
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tx_purchase_inquiry $tx_purchase_inquiry)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_purchase_inquiry  $tx_purchase_inquiry
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_inquiry $tx_purchase_inquiry)
    {
        //
    }
}
