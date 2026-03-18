<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_coa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ReportCashFlow2026Controller extends Controller
{
    protected $title = 'Cash Flow';
    protected $folder = 'cash-flow';
    protected $monthList = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    protected $monthDays = [31,28,31,30,31,30,31,31,30,31,30,31];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $month = old('period_month')?(strlen(old('period_month'))==1?'0'.old('period_month'):old('period_month')):'01';
        $year = date("Y");

        $coas = Mst_coa::whereIn('id', function($q) use($year, $month){
            $q->select('bank_id')
            ->from('tx_payment_plans')
            ->whereRaw('payment_month=\''.$year.'-'.$month.'-01\'')
            ->where([
                'is_draft' => 'N',
                'active' => 'Y',
            ]);
        })
        ->where([
            'active' => 'Y',
        ])
        ->orderBy('coa_name', 'ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'coas' => $coas,
            'monthList'=>$this->monthList,
        ];
        return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index-2026', $data);
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
        $validateInput = [
            'period_month' => 'required|numeric',
            'period_year' => 'required|numeric',
            'bank_id'=>'required|numeric',
        ];
        $errMsg = [
            'period_month.required' => 'Bulan is required',
            'period_month.numeric' => 'Bulan must be numeric',
            'period_year.required' => 'Tahun is required',
            'period_year.numeric' => 'Tahun must be numeric',
            'bank_id.numeric'=>'Please select a valid Bank Account.',
            'bank_id.required'=>'Please select a valid Bank Account.',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )->validate();

        if($request->view_mode=='V'){
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'reqs' => $request,
            ];
            return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
        }
        if($request->view_mode=='P'){
            return redirect('dbg/rpt-'.$this->folder.'-2026-xlsx/'.urlencode($request->period_year.'-'.$request->period_month).'/'.$request->bank_id);
            // return redirect(ENV('REPORT_FOLDER_NAME').'/rpt-'.$this->folder.'-2026-xlsx/'.urlencode($request->period_year.'-'.$request->period_month).'/'.$request->bank_id);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_part  $mst_part
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
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function isLeapYear($year) {
        if (($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0)) {
            return true;
        } else {
            return false;
        }
    }
}
