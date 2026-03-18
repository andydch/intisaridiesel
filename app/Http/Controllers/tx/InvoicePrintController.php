<?php

namespace App\Http\Controllers\tx;

use PDF;
use DateTime;
use DateTimeZone;
use App\Models\Mst_global;
use App\Models\Tx_invoice;
use App\Models\Userdetail;
use App\Models\Mst_company;
use Illuminate\Http\Request;
use App\Models\Tx_delivery_order;
use App\Models\Tx_invoice_detail;
use App\Http\Controllers\Controller;
use App\Models\Mst_company_bank_information;

class InvoicePrintController extends Controller
{
    protected $title = 'Billing Process';
    protected $folder = 'invoice';

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
        $company_bank_info = [];
        
        $queryInv = Tx_invoice::where('id','=',$request->inv)
        ->first();
        if($queryInv){
            $userLogin = Userdetail::where('user_id','=',$queryInv->created_by)
            ->first();

            $company = Mst_company::where([
                'id' => 1,
                'active' => 'Y'
            ])
            ->first();
            if($company){
                $companyName = $company->name;
                $npwpNo = $company->npwp_no;
    
                $company_bank_info = Mst_company_bank_information::where([
                    'company_id' => $company->id,
                    'coa_id' => $queryInv->payment_to_id,
                    'active' => 'Y',
                ])
                ->orderBy('id','DESC')
                ->first();
            }

            $delivery_order = Tx_delivery_order::where('customer_id','=',$queryInv->customer_id)
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('active','=','Y')
            ->get();

            $all_selected_FK_from_db = '';
            $all_selected_FK_count_from_db = 0;
            $invdtls = Tx_invoice_detail::where([
                'invoice_id' => $queryInv->id,
                'active' => 'Y',
            ])
            ->orderBy('delivery_order_no','ASC');
            if($invdtls->get()){
                foreach($invdtls->get() as $invdtl){
                    $all_selected_FK_from_db .= ','.$invdtl->delivery_order_no;
                }
                $all_selected_FK_count_from_db = $invdtls->count();
                if(substr($all_selected_FK_from_db,0,1)==','){
                    $all_selected_FK_from_db = substr($all_selected_FK_from_db,1,strlen($all_selected_FK_from_db));
                }
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'companyName' => $companyName,
                'company' => $company,
                'company_bank_info' => $company_bank_info,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'vat' => $vat,
                'deliveryOrders' => $delivery_order,
                'qInv' => $queryInv,
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
                'all_selected_FK_from_db' => $all_selected_FK_from_db,
                'all_selected_FK_count_from_db' => $all_selected_FK_count_from_db,
                'date_local' => $date_local,
            ];
            if($request->doc==1){
                $pdf = PDF::loadView('tx.'.$this->folder.'.invoice-pdf', $data);
            }
            if($request->doc==2){
                $pdf = PDF::loadView('tx.'.$this->folder.'.tanda-terima-pdf', $data);
            }
            if($request->doc==3){
                $config = [
                    // 'format' => [285,90],
                    'margin_left' => 5,
                    'margin_right' => 5,
                ];
                $pdf = PDF::loadView('tx.'.$this->folder.'.kwitansi-pdf', $data,[],$config);
                $pdf->shrink_tables_to_fit = 1;
            }
            // $pdf->debug = true;
            return $pdf->stream('document-inv-'.$queryInv->invoice_no.'.pdf');

        }else{
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }
}
