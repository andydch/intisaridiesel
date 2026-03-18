<?php

namespace App\Http\Controllers\tx;

use PDF;
use DateTime;
use DateTimeZone;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_company;
use App\Models\Tx_kwitansi;
use Illuminate\Http\Request;
use App\Models\Tx_kwitansi_detail;
use App\Http\Controllers\Controller;
use App\Models\Tx_delivery_order_non_tax;
use App\Models\Mst_company_bank_information;

class KwitansiPrintController extends Controller
{
    protected $title = 'Proses Tagihan';
    protected $folder = 'kwitansi';

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

        $timezone = new DateTimeZone('Asia/Jakarta');
        $date_local = new DateTime();
        $date_local->setTimeZone($timezone);

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $companyName = '';
        $company_bank_info = [];
        
        $queryKwi = Tx_kwitansi::where('id','=',$request->inv)
        ->first();
        if($queryKwi){
            $userLogin = Userdetail::where('user_id','=',$queryKwi->created_by)
            ->first();

            $company = Mst_company::where('id','=',2)
            ->where('active','=','Y')
            ->first();
            if($company){
                $companyName = $company->name;
                $npwpNo = $company->npwp_no;
    
                $company_bank_info = Mst_company_bank_information::where([
                    'company_id' => $company->id,
                    'coa_id' => $queryKwi->payment_to_id,
                    'active' => 'Y',
                ])
                ->orderBy('id','DESC')
                ->first();
            }

            $delivery_order = Tx_delivery_order_non_tax::where('customer_id','=',$queryKwi->customer_id)
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('active','=','Y')
            ->get();

            $all_selected_NP_from_db = '';
            $all_selected_NP_count_from_db = 0;
            $invdtls = Tx_kwitansi_detail::where([
                'kwitansi_id' => $queryKwi->id,
                'active' => 'Y',
            ])
            ->orderBy('nota_penjualan_no','ASC');
            if($invdtls->get()){
                foreach($invdtls->get() as $invdtl){
                    $all_selected_NP_from_db .= ','.$invdtl->nota_penjualan_no;
                }
                $all_selected_NP_count_from_db = $invdtls->count();
                if(substr($all_selected_NP_from_db,0,1)==','){
                    $all_selected_NP_from_db = substr($all_selected_NP_from_db,1,strlen($all_selected_NP_from_db));
                }
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'companyName' => $companyName,
                'company' => $company,
                'company_bank_info' => $company_bank_info,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'deliveryOrders' => $delivery_order,
                'qKwi' => $queryKwi,
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
                'all_selected_NP_from_db' => $all_selected_NP_from_db,
                'all_selected_NP_count_from_db' => $all_selected_NP_count_from_db,
                'date_local' => $date_local,
            ];
            if($request->doc==1){
                $pdf = PDF::loadView('tx.'.$this->folder.'.permohonan-pembayaran-pdf', $data);
            }
            if($request->doc==2){
                $pdf = PDF::loadView('tx.'.$this->folder.'.tanda-terima-pdf', $data);
            }
            if($request->doc==3){
                $pdf = PDF::loadView('tx.'.$this->folder.'.kwitansi-non-tax-pdf', $data);
            }
            // $pdf->debug = true;
            return $pdf->stream('document-so-'.$queryKwi->sales_order_no.'.pdf');

        }else{
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }
}
