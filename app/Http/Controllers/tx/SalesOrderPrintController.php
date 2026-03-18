<?php

namespace App\Http\Controllers\tx;

use PDF;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_sales_order;
use App\Models\Tx_sales_order_part;
use App\Models\Mst_company;
use App\Models\Mst_global;
use App\Models\Userdetail;

class SalesOrderPrintController extends Controller
{
    protected $title = 'Sales Order';
    protected $folder = 'sales-order';

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

        $querySO = Tx_sales_order::where([
            'id' => $request->so
        ])
        ->first();
        if($querySO){
            $userLogin = Userdetail::where('user_id','=',$querySO->created_by)
            ->first();

            $querySOpart = Tx_sales_order_part::where([
                'order_id' => $querySO->id,
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

            // update counter
            $num_of_print = 0;
            if(is_null($querySO->number_of_prints) || $querySO->number_of_prints==0){
                $num_of_print = 1;
            }else{
                $num_of_print = (int)$querySO->number_of_prints+1;
            }
            $updSO = Tx_sales_order::where('id','=',$querySO->id)
            ->update([
                'number_of_prints' => $num_of_print,
            ]);

            $data = [
                'sales_orders' => $querySO,
                'parts' => $querySOpart->get(),
                'partsCount' => $querySOpart->count(),
                'vat' => $vat,
                'companyName' => $companyName,
                'npwpNo' => $npwpNo,
                'userLogin' => $userLogin,
                'qCurrency' => $qCurrency,
            ];
            $config = [
                // 'format' => [210,148],      // separuh A4
                // 'format' => [241,140],      // harus di cetak menggunakan adobe reader, actuan size dan orientation P
                'format' => [241,279],
                'margin_left' => 20,
                'margin_right' => 40,
                'margin_top' => 15,
                'margin_bottom' => 15,
            ];
            if($request->doc==1){
                $pdf = PDF::loadView('tx.'.$this->folder.'.sales-order-with-price-pdf', $data, [], $config);
            }
            if($request->doc==2){
                $pdf = PDF::loadView('tx.'.$this->folder.'.sales-order-without-price-pdf', $data, [], $config);
            }
            if($request->doc=='1a'){
                $pdf = PDF::loadView('tx.'.$this->folder.'.sales-order-no-number-with-price-pdf', $data, [], $config);
            }
            if($request->doc=='2a'){
                $pdf = PDF::loadView('tx.'.$this->folder.'.sales-order-no-number-without-price-pdf', $data, [], $config);
            }
            $config =[
                'format' => 'A4',
            ];
            if($request->doc==3){
                $pdf = PDF::loadView('tx.'.$this->folder.'.sales-order-with-price-pdf-download', $data, [], $config);
            }
            if($request->doc==4){
                $pdf = PDF::loadView('tx.'.$this->folder.'.sales-order-without-price-pdf-download', $data, [], $config);
            }
            if($request->doc=='3a'){
                $pdf = PDF::loadView('tx.'.$this->folder.'.sales-order-no-number-with-price-pdf-download', $data, [], $config);
            }
            if($request->doc=='4a'){
                $pdf = PDF::loadView('tx.'.$this->folder.'.sales-order-no-number-without-price-pdf-download', $data, [], $config);
            }
            // $pdf->debug = true;
            return $pdf->stream('document-so-'.$querySO->sales_order_no.'.pdf');

        }else{
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }
}
