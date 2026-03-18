<?php

namespace App\Http\Controllers\tx;

use PDF;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_delivery_order_non_tax;
use App\Models\Tx_delivery_order_non_tax_part;
use App\Models\Mst_company;
use App\Models\Mst_global;
use App\Models\Userdetail;

class DeliveryOrderNonTaxPrintController extends Controller
{
    protected $title = 'Delivery Order';
    protected $folder = 'delivery-order-local';
    protected $uri = 'delivery-order-local';

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

        $queryF = Tx_delivery_order_non_tax::where([
            'id' => $request->fk
        ])
        ->first();
        if($queryF){
            $userLogin = Userdetail::where('user_id','=',$queryF->created_by)
            ->first();

            $queryFpart = Tx_delivery_order_non_tax_part::where([
                'delivery_order_id' => $queryF->id,
                'active' => 'Y'
            ]);

            $companyName = '';
            $npwpNo = '';
            $company = Mst_company::where('id','=',2)
            ->where('active','=','Y')
            ->first();
            if($company){
                $companyName = $company->name;
                $npwpNo = $company->npwp_no;
            }

            $data = [
                'dos' => $queryF,
                'parts' => $queryFpart->get(),
                'partsCount' => $queryFpart->count(),
                'companyName' => $companyName,
                'npwpNo' => $npwpNo,
                'userLogin' => $userLogin,
                'qCurrency' => $qCurrency,
            ];
            if ($request->p==1){
                $config = [
                    // 'format' => [241,140],      // harus di cetak menggunakan adobe reader, actuan size dan orientation P
                    'format' => [241,279],
                    'margin_left' => 20,
                    'margin_right' => 40,
                    'margin_top' => 15,
                    'margin_bottom' => 15,
                    // 'orientation' => 'L',
                ];
                $pdf = PDF::loadView('tx.'.$this->folder.'.np-cf-pdf', $data);
            }
            if ($request->p==2){
                $config = [];
                $pdf = PDF::loadView('tx.'.$this->folder.'.np-pdf', $data);
            }
            return $pdf->stream('document-np-'.$queryF->delivery_order_no.'.pdf');

        }else{
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }
}
