<?php

namespace App\Http\Controllers\dbg;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\TemplateProcessor;

class CreateWordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sessionTbl = DB::table('sessions')
        // ->where('last_activity', '<', now()->subMinutes(0)->timestamp)
        ->where('last_activity', '<', now()->subMinutes(ENV("SESSION_LIFETIME"))->timestamp)
        ->get();
        dd($sessionTbl);



        // // $templateProcessor = new TemplateProcessor('templates/Templates.docx');
        // $htmlTemplate = 'ABC<p style="background-color:#FFFF00;color:#FF0000;">Some text</p>123';

        // $fontFamily = 'font-family:Arial;';
        // $fontSize = 'font-size:13px;';
        // $fontSmallSize = 'font-size:11px;';

        // $html01 = '<table style="width:100%;'.$fontFamily.$fontSize.'">'.
        //     '<tr>'.
        //     '<td style="width:50%;text-align:left;">Quotation : PQ No [purchase quotation]</td>'.
        //     '<td style="width:50%;text-align:right;">Jakarta, Date [purchase quotation]</td>'.
        //     '</tr>'.
        //     '<tr>'.
        //     '<td style="width:50%;text-align:left;">To   : Entity Type Supplier Name [purchase quotation]</td>'.
        //     '<td style="width:50%;text-align:right;"></td>'.
        //     '</tr>'.
        //     '<tr>'.
        //     '<td style="width:50%;text-align:left;">PIC : PIC Name [purchase quotation]</td>'.
        //     '<td style="width:50%;text-align:right;"></td>'.
        //     '</tr>'.
        //     '</table>';
        // $html02 = '<p></p><p style="'.$fontFamily.$fontSize.'">Header [purchase quotation]</p><p></p>';
        // $html03a = '';
        // for($i=2;$i<=50;$i++){
        //     $html03a .= '<tr>'.
        //         '<td cellpadding="15" style="width:5%;padding:13;text-align:right;">'.$i.'.</td>'.
        //         '<td style="width:25%;">xxx-123-xxx</td>'.
        //         '<td style="width:30%;">part 12345</td>'.
        //         '<td style="width:5%;text-align:center;">5</td>'.
        //         '<td style="width:5%;text-align:center;">set</td>'.
        //         '<td style="width:30%;">part ini digunakan untuk truk jenis abc12345</td>'.
        //         '</tr>';
        // }
        // $html03 = '<table cellpadding="15" style="width:100%;border:1px solid black;'.$fontFamily.$fontSize.'">'.
        //     '<tr>'.
        //     '<td style="width:5%;text-align:center;font-weight:bold;">No.</td>'.
        //     '<td style="width:25%;text-align:center;font-weight:bold;">Part Number</td>'.
        //     '<td style="width:30%;text-align:center;font-weight:bold;">Part Name</td>'.
        //     '<td colspan="2" style="width:10%;text-align:center;font-weight:bold;">Qty</td>'.
        //     '<td style="width:30%;text-align:center;font-weight:bold;">Remarks</td>'.
        //     '</tr>'.
        //     '<tr>'.
        //     '<td cellpadding="15" style="width:5%;padding:13;text-align:right;">1.</td>'.
        //     '<td style="width:25%;">xxx-123-xxx</td>'.
        //     '<td style="width:30%;">part 12345</td>'.
        //     '<td style="width:5%;text-align:center;">5</td>'.
        //     '<td style="width:5%;text-align:center;">set</td>'.
        //     '<td style="width:30%;">part ini digunakan untuk truk jenis abc12345</td>'.
        //     '</tr>'.$html03a.
        //     '</table>';
        // $html04 = '<p></p><p style="'.$fontFamily.$fontSize.'">Footer [purchase quotation]</p><p></p>';
        // $html05 = '<p></p><p style="'.$fontFamily.$fontSize.'"><span style="text-decoration:underline;">Sales Name_[purchase quotation]</span><br/>'.
        //     '<span style="'.$fontSmallSize.'">Company Name [mst company]</span></p><p></p>';

        // $PidPageSettings = array(
        //     'headerHeight'=> \PhpOffice\PhpWord\Shared\Converter::inchToTwip(1.3),
        //     // 'headerHeight'=> \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.2),
        //     'footerHeight'=> \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.2),
        //     'marginLeft'  => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.75),
        //     'marginRight' => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(.75),
        //     'marginTop'   => 0,
        //     'marginBottom'=> 0,
        // );

        // $phpWord = new PhpWord();
        // $section = $phpWord->addSection($PidPageSettings);

        // // add header
        // $subsequent = $section->addHeader();
        // $subsequent->addText("");

        // // add footer
        // $subsequentFooter = $section->addFooter();
        // $subsequentFooter->addText("");

        // Html::addHtml($section, $html01.$html02.$html03.$html04.$html05);

        // $docname = 'Templates-'.date("dmYHis").'.docx';
        // $phpWord->save($docname, 'Word2007');
        // return response()->download($docname)->deleteFileAfterSend(true);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
