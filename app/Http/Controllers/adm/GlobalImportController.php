<?php

namespace App\Http\Controllers\adm;

use Illuminate\Http\Request;
use App\Imports\admin\GlobalImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class GlobalImportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        Validator::make($request->all(), [
            'xlsx_file' => 'required|file|max:2048|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->validate();

        $realpath = $_SERVER['DOCUMENT_ROOT'] . '/upl/excel/';
        $extension = $request->file('xlsx_file')->extension();
        $xlsNm = uniqid() . '_' . strtotime('now') . '.' . $extension;
        $request->file('xlsx_file')->move($realpath, $xlsNm);

        try {
            Excel::import(new GlobalImport, $realpath . $xlsNm);
            session()->flash('status', 'Data has been uploaded and updated successfully.');
        } catch (\Exception $exception) {
            session()->flash('status-error', $exception->getMessage());
        }

        if (file_exists($realpath . $xlsNm)) {
            // jalankan hapus file
            unlink($realpath . $xlsNm);
        }
        return redirect(env('ADMIN_FOLDER_NAME') . '/mst-global');
    }
}
