<?php

namespace App\Http\Controllers\tx;

use PDF;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use PhpOffice\PhpWord\Shared\Html;
// use PhpOffice\PhpWord\PhpWord;
use App\Models\Tx_purchase_quotation;
use App\Models\Tx_purchase_quotation_part;
use App\Models\Mst_company;
use App\Models\Mst_global;

class QuotationPrintController extends Controller
{
    protected $title = 'Purchase Quotation';
    protected $folder = 'quotation';

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

        $queryPQ = Tx_purchase_quotation::where([
            'id' => $request->pq
        ])
        ->first();
        if($queryPQ){
            $queryPQpart = Tx_purchase_quotation_part::where([
                'quotation_id' => $queryPQ->id,
                'active' => 'Y'
            ]);

            $companyName = '';
            $company = Mst_company::where('active','=','Y')
            ->first();
            if($company){
                $companyName = $company->name;
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

            $data = [
                'purchase_quotations' => $queryPQ,
                'parts' => $queryPQpart->get(),
                'partsCount' => $queryPQpart->count(),
                'vat' => $vat,
                'companyName' => $companyName,
            ];
            $pdf = PDF::loadView('tx.'.$this->folder.'.purchase-quotation-pdf', $data);
            // $pdf->debug = true;
            return $pdf->stream('document-purchase-quotation-'.$queryPQ->quotation_no.'.pdf');

        }else{
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    // public function __invoke(Request $request)
    // {
    //     $queryPQ = Tx_purchase_quotation::where([
    //         'id' => $request->pq
    //     ])
    //     ->first();
    //     if($queryPQ){
    //         $queryPQpart = Tx_purchase_quotation_part::where([
    //             'quotation_id' => $queryPQ->id,
    //             'active' => 'Y'
    //         ])
    //         ->get();

    //         $fontFamily = 'font-family:Arial;';
    //         $fontSize = 'font-size:13px;';
    //         $fontSmallSize = 'font-size:11px;';

    //         $pic = $queryPQ->supplier->pic1_name;
    //         if($queryPQ->pic_idx==2){
    //             $pic = $queryPQ->supplier->pic2_name;
    //         }

    //         $html01 = '<table style="width:100%;'.$fontFamily.$fontSize.'">'.
    //             '<tr>'.
    //             '<td style="width:50%;text-align:left;">PQ No : '.$queryPQ->quotation_no.'</td>'.
    //             '<td style="width:50%;text-align:left;">To   : '.(!is_null($queryPQ->supplier_entity_type)?$queryPQ->supplier_entity_type->title_ind:'').' '.(!is_null($queryPQ->supplier)?$queryPQ->supplier->name:'').'</td>'.
    //             '</tr>'.
    //             '<tr>'.
    //             '<td style="width:50%;text-align:left;">Jakarta, '.date_format(date_create($queryPQ->quotation_date), 'd/m/Y').'</td>'.
    //             '<td style="width:50%;text-align:left;">PIC : '.$pic.'</td>'.
    //             '</tr>'.
    //             // '<tr>'.
    //             // '<td style="width:50%;text-align:left;">PIC : '.$pic.'</td>'.
    //             // '<td style="width:50%;text-align:right;"></td>'.
    //             // '</tr>'.
    //             '</table>';

    //         $html02 = $queryPQ->header;

    //         $html03a = '';
    //         $i = 1;
    //         foreach($queryPQpart as $qPart){
    //             $partNumber = $qPart->part->part_number;
    //             if(strlen($partNumber)<11){
    //                 $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
    //             }else{
    //                 $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
    //             }
    //             $html03a .= '<tr>'.
    //                 '<td cellpadding="15" style="width:5%;padding:13;text-align:right;">'.$i.'.</td>'.
    //                 '<td style="width:25%;">'.$partNumber.'</td>'.
    //                 '<td style="width:30%;">'.$qPart->part->part_name.'</td>'.
    //                 '<td style="width:5%;text-align:center;">'.$qPart->qty.'</td>'.
    //                 '<td style="width:5%;text-align:center;">'.(!is_null($qPart->part->quantity_type)?$qPart->part->quantity_type->title_ind:'').'</td>'.
    //                 '<td style="width:30%;">'.$qPart->description.'</td>'.
    //                 '</tr>';
    //             $i += 1;
    //         }
    //         $html03 = '<table cellpadding="15" style="width:100%;border:1px solid black;'.$fontFamily.$fontSize.'">'.
    //             '<tr>'.
    //             '<td style="width:5%;text-align:center;font-weight:bold;">No.</td>'.
    //             '<td style="width:25%;text-align:center;font-weight:bold;">Part Number</td>'.
    //             '<td style="width:30%;text-align:center;font-weight:bold;">Part Name</td>'.
    //             '<td colspan="2" style="width:10%;text-align:center;font-weight:bold;">Qty</td>'.
    //             '<td style="width:30%;text-align:center;font-weight:bold;">Remarks</td>'.
    //             '</tr>'.
    //             $html03a.
    //             '</table>';

    //         $html04 = $queryPQ->footer;

    //         $companyName = '';
    //         $company = Mst_company::where('active','=','Y')
    //         ->first();
    //         if($company){
    //             $companyName = $company->name;
    //         }
    //         $html05 = '<p></p><p style="'.$fontFamily.$fontSize.'"><span style="text-decoration:underline;">'.$queryPQ->createdBy->name.'</span><br/>'.
    //             '<span style="'.$fontSmallSize.'">'.$companyName.'</span></p><p></p>';

    //         $PidPageSettings = array(
    //             'headerHeight'=> \PhpOffice\PhpWord\Shared\Converter::inchToTwip(1.3),
    //             // 'headerHeight'=> \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.2),
    //             'footerHeight'=> \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.2),
    //             'marginLeft'  => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.75),
    //             'marginRight' => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.75),
    //             'marginTop'   => 0,
    //             'marginBottom'=> 0,
    //         );

    //         $phpWord = new PhpWord();
    //         $section = $phpWord->addSection($PidPageSettings);

    //         // add header
    //         $subsequent = $section->addHeader();
    //         $subsequent->addText("");

    //         // add footer
    //         $subsequentFooter = $section->addFooter();
    //         $subsequentFooter->addText("");

    //         Html::addHtml($section, $html01.$html02.$html03.$html04.$html05);

    //         $docname = $queryPQ->quotation_no.'-'.date("dmYHis").'.docx';
    //         $phpWord->save($docname, 'Word2007');
    //         return response()->download($docname)->deleteFileAfterSend(true);
    //     }
    // }
}
