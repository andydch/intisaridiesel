<?php

namespace App\Http\Controllers\tx;

use PDF;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_delivery_order;
use App\Models\Tx_delivery_order_part;
use App\Models\Mst_company;
use App\Models\Mst_global;
use App\Models\Userdetail;

class FakturPrintController extends Controller
{
    protected $title = 'Faktur';
    protected $folder = 'delivery-order';
    protected $uri = 'faktur';

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

        $queryF = Tx_delivery_order::where([
            'id' => $request->fk
        ])
        ->first();
        if($queryF){
            $userLogin = Userdetail::where('user_id','=',$queryF->created_by)
            ->first();

            $queryFpart = Tx_delivery_order_part::where([
                'delivery_order_id' => $queryF->id,
                // 'active' => 'Y'
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
            if(is_null($queryF->number_of_prints) || $queryF->number_of_prints==0){
                $num_of_print = 1;
            }else{
                $num_of_print = (int)$queryF->number_of_prints+1;
            }
            $updSO = Tx_delivery_order::where('id','=',$queryF->id)
            ->update([
                'number_of_prints' => $num_of_print,
            ]);

            $data = [
                'fakturs' => $queryF,
                'parts' => $queryFpart->get(),
                'partsCount' => $queryFpart->count(),
                'vat' => $vat,
                'companyName' => $companyName,
                'npwpNo' => $npwpNo,
                'userLogin' => $userLogin,
                'qCurrency' => $qCurrency,
                'num_of_print' => $num_of_print,
            ];
            if ($request->p==1){
                $config = [
                    'format' => [241,279],
                    'margin_left' => 20,
                    'margin_right' => 40,
                    'margin_top' => 5,
                    'margin_bottom' => 15,
                    // 'orientation' => 'L',
                ];
                $pdf = PDF::loadView('tx.'.$this->folder.'.faktur-cf-pdf', $data, [], $config);
                return $pdf->stream('document-fk-cf-'.$queryF->delivery_order_no.'.pdf');
            }
            if ($request->p==2){
                $config = [];
                $pdf = PDF::loadView('tx.'.$this->folder.'.faktur-a4-pdf', $data, [], $config);
                return $pdf->stream('document-fk-a4-'.$queryF->delivery_order_no.'.pdf');
            }
        }else{
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }
}
