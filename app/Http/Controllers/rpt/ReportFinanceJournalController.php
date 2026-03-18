<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_part;
use App\Models\Mst_global;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_general_journal;
use App\Models\Tx_lokal_journal;
use Illuminate\Support\Facades\Validator;

class ReportFinanceJournalController extends Controller
{
    protected $title = 'Journal';
    protected $folder = 'rpt-finance-journal';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $generalJournal = Tx_general_journal::select(
            'general_journal_no as journal_no',
            'general_journal_date as journal_date',
        )
        ->where([
            'active'=>'Y',
        ])
        ->orderBy('general_journal_no','DESC')
        ->get();

        $lokalJournal = Tx_lokal_journal::select(
            'general_journal_no as journal_no',
            'general_journal_date as journal_date',
        )
        ->where([
            'active'=>'Y',
        ])
        ->orderBy('general_journal_no','DESC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => ($qCurrency?$qCurrency:[]),
            'generalJournal' => ($generalJournal?$generalJournal:[]),
            'lokalJournal' => ($lokalJournal?$lokalJournal:[]),
        ];
        return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
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
            'journal_no' => 'required',
        ];
        $errMsg = [
            'journal_no.required' => 'Journal No is required',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )->validate();

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        if($request->view_mode=='V'){
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'reqs' => $request,
                'qCurrency' => ($qCurrency?$qCurrency:[]),
            ];
            return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
        }
        if($request->view_mode=='P'){
            return redirect(ENV('REPORT_FOLDER_NAME').'/'.$this->folder.'-xlsx/'.urlencode($request->journal_no));
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
    public function destroy(Mst_part $mst_part)
    {
        //
    }
}
