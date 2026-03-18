<?php

namespace App\Http\Controllers\dbg;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Models\Mst_part;

class DocumentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $templateProcessor = new TemplateProcessor('Template.docx');
        $templateProcessor->setValue('firstname', 'Andy');
        $templateProcessor->setValue('lastname', 'DCH');

        $parts = Mst_part::where('active','=','Y')->get();
        $templateProcessor->cloneRow('part_name', count($parts));
        $i = 1;
        foreach($parts as $part){
            $templateProcessor->setValue('no#' . $i, $i);
            $templateProcessor->setValue('part_name#' . $i, $part->part_name);
            $i++;
        }

        $templateProcessor->saveAs('Result.docx');
        return response()->download('Result.docx')->deleteFileAfterSend(true);
    }
}
