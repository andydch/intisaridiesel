<?php

namespace App\Http\Controllers\tx;

use PDF;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Models\Tx_delivery_order;
// use App\Models\Tx_delivery_order_part;
use App\Models\Mst_company;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Tx_sales_quotation;
use App\Models\Tx_sales_quotation_part;

class SalesQuoPrintController extends Controller
{
    protected $title = 'Sales Quotation';
    protected $folder = 'sales-quotation';

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '512M');

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $querySQ = Tx_sales_quotation::where([
            'id' => $request->pq
        ])
        ->first();
        if($querySQ){
            $userLogin = Userdetail::where('user_id','=',$querySQ->created_by)
            ->first();

            $querySQpart = Tx_sales_quotation_part::where([
                'sales_quotation_id' => $querySQ->id,
                'active' => 'Y'
            ]);

            $companyName = '';
            $npwpNo = '';
            $company = Mst_company::where('active','=','Y')
            ->first();
            if($company){
                $companyName = $company->name;
                $npwpNo = $company->npwp_no;
            }

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

            $pic = !is_null($querySQ->customer)?$querySQ->customer->pic1_name:'';
            if($querySQ->pic_idx==2){
                $pic = !is_null($querySQ->customer)?$querySQ->customer->pic2_name:'';
            }

            $data = [
                'salesQuos' => $querySQ,
                'parts' => $querySQpart->get(),
                'partsCount' => $querySQpart->count(),
                'vat' => $vat,
                'companyName' => $companyName,
                'npwpNo' => $npwpNo,
                'userLogin' => $userLogin,
                'qCurrency' => $qCurrency,
                'pic' => $pic,
            ];
            $pdf = PDF::loadView('tx.'.$this->folder.'.sq-pdf', $data);
            // $pdf->debug = true;
            return $pdf->stream('document-sq-'.$querySQ->sales_quotation_no.'.pdf');

        }else{
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }
}
