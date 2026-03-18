<?php

namespace App\Http\Controllers\tx;

use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use App\Rules\ValidateFakturForTax;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Exports\coretax\FakturToCoretaxExport;

class DownloadFakturPajakController extends Controller
{
    protected $title = 'Download List Faktur';
    protected $folder = 'dl-faktur-pajak';

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
        
        $allCustIds = '';
        $totalRows = ($request['totRowCust']=''?0:$request['totRowCust']);
        for($i=0;$i<$totalRows;$i++){
            if ($request['custId'.$i]){
                $allCustIds .= ','.$request['custId'.$i];
            }
        }
        if ($allCustIds!=''){
            $allCustIds = substr($allCustIds,1,strlen($allCustIds));
        }
        if ($request['customer_all']=='on'){
            $allCustIds = 'all';
        }

        $validateInput = [
            'start_date' => 'required',
            'end_date' => 'required',
            'customer_one' => [new ValidateFakturForTax($allCustIds)],
        ];
        $errMsg = [
            'start_date.required' => 'Start Date is required',
            'end_date.required' => 'End Date is required',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        $date_xls = date_create(now());

        return Excel::download(
            new FakturToCoretaxExport(
                $request['start_date'],
                $request['end_date'],
                $request['customer_all'],
                $allCustIds
            ),
            'FakturToCoreTax-'.date_format($date_xls,"YmdHis").'.xlsx');
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
