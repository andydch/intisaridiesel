<?php

namespace App\Http\Controllers\tx;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\PhpWord;
use App\Models\Tx_sales_quotation;
use App\Models\Tx_sales_quotation_part;
use App\Models\Mst_company;

class SalesQuotationPrintController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $querySQ = Tx_sales_quotation::where([
            'id' => $request->pq
        ])
        ->first();
        if($querySQ){

            $querySQpart = Tx_sales_quotation_part::where([
                'sales_quotation_id' => $querySQ->id,
                'active' => 'Y'
            ])
            ->get();

            $fontFamily = 'font-family:Arial;';
            $fontSize = 'font-size:13px;';
            $fontSmallSize = 'font-size:11px;';

            $pic = !is_null($querySQ->customer)?$querySQ->customer->pic1_name:'';
            if($querySQ->pic_idx==2){
                $pic = !is_null($querySQ->customer)?$querySQ->customer->pic2_name:'';
            }

            $custInfo = '';
            if(!is_null($querySQ->customer)){
                $custInfo = (!is_null($querySQ->customer->entity_type)?$querySQ->customer->entity_type->title_ind:'').' '.$querySQ->customer->name.'<br/>'.
                $querySQ->customer->office_address.'<br/>'.
                $querySQ->customer->subdistrict->sub_district_name.', '.$querySQ->customer->district->district_name.',<br/>'.
                $querySQ->customer->city->city_name.', '.$querySQ->customer->province->province_name.',<br/>'.
                $querySQ->customer->province->country->country_name.' '.$querySQ->customer->post_code;
            }

            $html01 = '<table style="width:100%;'.$fontFamily.$fontSize.'">'.
                '<tr>'.
                '<td style="width:50%;text-align:left;">Quotation : SO No '.$querySQ->sales_quotation_no.'</td>'.
                '<td style="width:50%;text-align:right;">Jakarta, '.date_format(date_create($querySQ->sales_quotation_date), 'd/m/Y').'</td>'.
                '</tr>'.
                '<tr>'.
                '<td style="width:50%;text-align:left;">To   : '.$custInfo.'</td>'.
                '<td style="width:50%;text-align:right;"></td>'.
                '</tr>'.
                '<tr>'.
                '<td style="width:50%;text-align:left;">PIC : '.$pic.'</td>'.
                '<td style="width:50%;text-align:right;"></td>'.
                '</tr>'.
                '</table>';

            $html02 = $querySQ->header;

            $html03a = '';
            $i = 1;
            foreach($querySQpart as $qPart){
                $partNumber = $qPart->part->part_number;
                if(strlen($partNumber)<11){
                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                }else{
                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                }
                $html03a .= '<tr>'.
                    '<td cellpadding="15" style="width:5%;padding:13;text-align:right;">'.$i.'.</td>'.
                    '<td style="width:20%;">'.$partNumber.'</td>'.
                    '<td style="width:20%;">'.$qPart->part->part_name.'</td>'.
                    '<td style="width:10%;">'.$qPart->part->part_type->title_ind.'</td>'.
                    '<td style="width:5%;text-align:center;">'.$qPart->qty.'</td>'.
                    '<td style="width:5%;text-align:center;">'.(!is_null($qPart->part->quantity_type)?$qPart->part->quantity_type->title_ind:'').'</td>'.
                    '<td style="width:10%;">'.$qPart->price_part.'</td>'.
                    '<td style="width:10%;">'.($qPart->price_part*$qPart->qty).'</td>'.
                    '<td style="width:10%;">'.$qPart->description.'</td>'.
                    '</tr>';
                $i += 1;
            }
            $html03 = '<table cellpadding="15" style="width:100%;border:1px solid black;'.$fontFamily.$fontSize.'">'.
                '<tr>'.
                '<td style="width:5%;text-align:center;font-weight:bold;">No.</td>'.
                '<td style="width:20%;text-align:center;font-weight:bold;">Part Number</td>'.
                '<td style="width:20%;text-align:center;font-weight:bold;">Part Name</td>'.
                '<td style="width:10%;text-align:center;font-weight:bold;">Part Type</td>'.
                '<td colspan="2" style="width:10%;text-align:center;font-weight:bold;">Qty</td>'.
                '<td style="width:5%;text-align:center;font-weight:bold;">Price</td>'.
                '<td style="width:5%;text-align:center;font-weight:bold;">Total</td>'.
                '<td style="width:10%;text-align:center;font-weight:bold;">Remarks</td>'.
                '</tr>'.
                $html03a.
                '</table>';

            $html04 = $querySQ->footer;

            $companyName = '';
            $company = Mst_company::where('active','=','Y')
            ->first();
            if($company){
                $companyName = $company->name;
            }
            // $html05 = '<p></p><p style="'.$fontFamily.$fontSize.'"><span style="text-decoration:underline;">'.$querySQ->createdBy->name.'</span><br/>'.
            //     '<span style="'.$fontSmallSize.'">'.$companyName.'</span></p><p></p>';

            $html05 = '<table style="width:100%;'.$fontFamily.$fontSize.'">'.
                '<tr>'.
                '<td style="width:50%;text-align:left;"><span style="text-decoration:underline;">'.$pic.'</span><br/><span style="'.$fontSmallSize.'">'.(!is_null($querySQ->customer)?$querySQ->customer->entity_type->title_ind:'').' '.(!is_null($querySQ->customer)?$querySQ->customer->name:'').'</span></td>'.
                '<td style="width:50%;text-align:right;"><span style="text-decoration:underline;">'.$querySQ->createdBy->name.'</span><br/><span style="'.$fontSmallSize.'">'.$companyName.'</span></td>'.
                '</tr>'.
                '</table>';

            $PidPageSettings = array(
                'headerHeight'=> \PhpOffice\PhpWord\Shared\Converter::inchToTwip(1.3),
                // 'headerHeight'=> \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.2),
                'footerHeight'=> \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.2),
                'marginLeft'  => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.75),
                'marginRight' => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.75),
                'marginTop'   => 0,
                'marginBottom'=> 0,
            );

            $phpWord = new PhpWord();
            $section = $phpWord->addSection($PidPageSettings);

            // add header
            $subsequent = $section->addHeader();
            $subsequent->addText("");

            // add footer
            $subsequentFooter = $section->addFooter();
            $subsequentFooter->addText("");

            Html::addHtml($section, $html01.$html02.$html03.$html04.$html05);

            $docname = $querySQ->sales_quotation_no.'-'.date("dmYHis").'.docx';
            $phpWord->save($docname, 'Word2007');
            return response()->download($docname)->deleteFileAfterSend(true);

        }
    }

    // public function __invoke(Request $request)
    // {
    //     $querySQ = Tx_sales_quotation::where([
    //         'id' => $request->pq
    //     ])->first();
    //     if($querySQ){
    //         $querySQpart = Tx_sales_quotation_part::where([
    //             'sales_quotation_id' => $querySQ->id,
    //             'active' => 'Y'
    //         ])->get();

    //         $templateProcessor = new TemplateProcessor('templates/SQ-Templates.docx');

    //         $templateProcessor->setValue('no_pq', $querySQ->sales_quotation_no);
    //         $templateProcessor->setValue('customer_name', $querySQ->customer->name);
    //         $templateProcessor->setValue('address', $querySQ->city->city_name.', '.$querySQ->province->province_name);

    //         $pic = $querySQ->customer->pic1_name;
    //         if($querySQ->pic_idx==2){
    //             $pic = $querySQ->customer->pic2_name;
    //         }
    //         $templateProcessor->setValue('pic', $pic);

    //         $templateProcessor->cloneRow('part_name', count($querySQpart));
    //         $i = 1;
    //         foreach($querySQpart as $qPart){
    //             $templateProcessor->setValue('no#' . $i, $i);
    //             $templateProcessor->setValue('part_no#' . $i, $qPart->part->part_number);
    //             $templateProcessor->setValue('part_name#' . $i, $qPart->part->part_name);
    //             $templateProcessor->setValue('qty#' . $i, $qPart->qty);
    //             $templateProcessor->setValue('set#' . $i, $qPart->part->quantity_type->title_ind);
    //             $templateProcessor->setValue('desc#' . $i, $qPart->description);

    //             $i++;
    //         }

    //         $uniqueNm = date('YmdHis');
    //         $templateProcessor->saveAs('SQ'.$uniqueNm.'.docx');
    //         return response()->download('SQ'.$uniqueNm.'.docx')->deleteFileAfterSend(true);
    //     }
    // }
}
