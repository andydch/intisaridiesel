<?php

namespace App\Http\Controllers\tx;

use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use App\Imports\FakturPajakImport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class UploadFakturPajakController extends Controller
{
    protected $title = 'Upload Faktur Pajak';
    protected $folder = 'upl-faktur-pajak';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
        ];
        return view('tx.'.$this->folder.'.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 1800);

        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 39,
            'user_id' => Auth::user()->id,
            'user_access_read' => 'Y',
        ])
        ->first();
        if (!$qCheckPriv){
            return redirect()
            ->back()
            ->withInput()
            ->with('status-error', ENV('ERR_MSG_02')?ENV('ERR_MSG_02'):'You are not allowed to access this page!');
        }
        
        Validator::make($request->all(), [
            // 'upl_faktur_pajak' => 'required|file|max:2048',
            'upl_faktur_pajak' => 'required|file|max:2048|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ], [
            'upl_faktur_pajak.required' => 'Please select the file to upload.',
            'upl_faktur_pajak.file' => 'Please select the file to upload.',
            'upl_faktur_pajak.max' => 'The file size is too large. Max 2MB.',
            'upl_faktur_pajak.mimetypes' => 'The file must be a file of type: xlsx.',
        ])
        ->validate();

        $realpath = $_SERVER['DOCUMENT_ROOT'].'/upl/excel/';
        $extension = $request->file('upl_faktur_pajak')->extension();
        $xlsNm = uniqid().'_'.strtotime('now').'.'.$extension;
        $request->file('upl_faktur_pajak')->move($realpath, $xlsNm);

        try {
            Excel::import(new FakturPajakImport, $realpath.$xlsNm);
            session()->flash('status', 'No Faktur Pajak has been uploaded and updated successfully.');
        } catch (\Exception $exception) {
            session()->flash('status-error', 'Make sure the excel file format complies with CoreTax rules.');
            // session()->flash('status-error', $exception->getMessage());
        }

        if (file_exists($realpath.$xlsNm)) {
            // jalankan hapus file
            unlink($realpath.$xlsNm);
        }

        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder.'/create');
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
