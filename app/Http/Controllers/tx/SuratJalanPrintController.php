<?php

namespace App\Http\Controllers\tx;

use PDF;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_surat_jalan;
use App\Models\Tx_surat_jalan_part;
use App\Models\Mst_company;
use App\Models\Mst_global;
use App\Models\Userdetail;

class SuratJalanPrintController extends Controller
{
    protected $title = 'Surat Jalan';
    protected $folder = 'surat-jalan';

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

        $querySJ = Tx_surat_jalan::where([
            'id' => $request->sj
        ])
        ->first();
        if($querySJ){
            $userLogin = Userdetail::where('user_id','=',$querySJ->created_by)
            ->first();

            $querySJpart = Tx_surat_jalan_part::where([
                'surat_jalan_id' => $querySJ->id,
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

            // update counter
            $num_of_print = 0;
            if(is_null($querySJ->number_of_prints) || $querySJ->number_of_prints==0){
                $num_of_print = 1;
            }else{
                $num_of_print = (int)$querySJ->number_of_prints+1;
            }
            $updSO = Tx_surat_jalan::where('id','=',$querySJ->id)
            ->update([
                'number_of_prints' => $num_of_print,
            ]);

            $data = [
                'surat_jalans' => $querySJ,
                'parts' => $querySJpart->get(),
                'partsCount' => $querySJpart->count(),
                // 'vat' => $vat,
                'companyName' => $companyName,
                'npwpNo' => $npwpNo,
                'userLogin' => $userLogin,
                'qCurrency' => $qCurrency,
            ];
            $config = [
                // 'format' => [241,140],      // harus di cetak menggunakan adobe reader, actuan size dan orientation P
                'format' => [241,279],
                'margin_left' => 20,
                'margin_right' => 40,
                'margin_top' => 15,
                'margin_bottom' => 15,
                // 'orientation' => 'L',
            ];
            if($request->doc==1){
                $pdf = PDF::loadView('tx.'.$this->folder.'.surat-jalan-with-price-pdf', $data, [], $config);
            }
            if($request->doc==2){
                $pdf = PDF::loadView('tx.'.$this->folder.'.surat-jalan-without-price-pdf', $data, [], $config);
            }
            if($request->doc=='1a'){
                $pdf = PDF::loadView('tx.'.$this->folder.'.surat-jalan-no-number-with-price-pdf', $data, [], $config);
            }
            if($request->doc=='2a'){
                $pdf = PDF::loadView('tx.'.$this->folder.'.surat-jalan-no-number-without-price-pdf', $data, [], $config);
            }
            $config = [
                'format' => 'A4',
            ];
            if($request->doc==3){
                $pdf = PDF::loadView('tx.'.$this->folder.'.surat-jalan-with-price-pdf-download', $data, [], $config);
            }
            if($request->doc==4){
                $pdf = PDF::loadView('tx.'.$this->folder.'.surat-jalan-without-price-pdf-download', $data, [], $config);
            }
            if($request->doc=='3a'){
                $pdf = PDF::loadView('tx.'.$this->folder.'.surat-jalan-no-number-with-price-pdf-download', $data, [], $config);
            }
            if($request->doc=='4a'){
                $pdf = PDF::loadView('tx.'.$this->folder.'.surat-jalan-no-number-without-price-pdf-download', $data, [], $config);
            }
            // $pdf->debug = true;
            return $pdf->stream('document-so-'.$querySJ->surat_jalan_no.'.pdf');

        }else{
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }
}
