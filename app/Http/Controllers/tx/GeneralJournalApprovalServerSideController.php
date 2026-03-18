<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_coa;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_general_journal;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_general_journal_detail;
use App\Models\Mst_menu_user;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class GeneralJournalApprovalServerSideController extends Controller
{
    protected $title = 'General Journal Approval';
    protected $folder = 'general-journal-approval';
    protected $idQ;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        if ($request->ajax()){
            $query = Tx_general_journal::leftJoin('userdetails AS usr','tx_general_journals.created_by','=','usr.user_id')
            ->select(
                'tx_general_journals.id as tx_id',
                'tx_general_journals.general_journal_no',
                'tx_general_journals.general_journal_date',
                'tx_general_journals.total_debit',
                'tx_general_journals.total_kredit',
                'tx_general_journals.is_wt_for_appr',
                'tx_general_journals.status_appr',
                'tx_general_journals.active as gj_active',
                'tx_general_journals.created_by as createdby',
                'tx_general_journals.created_at as createdat',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
            )
            // ->selectRaw('DATE_FORMAT(tx_general_journals.general_journal_date, "%d/%m/%Y") AS general_journal_date')
            ->when(request()->has('date_begin') && request()->has('date_ending') && request()->date_begin!=null && request()->date_ending!=null,
            function($q) use($request) {
                $date_begin = explode("/",$request->date_begin);
                $q->where('tx_general_journals.general_journal_date','>',$date_begin[2].'-'.$date_begin[1].'-'.$date_begin[0]);
            })
            ->when(request()->has('date_begin') && request()->has('date_ending') && request()->date_begin!=null && request()->date_ending!=null,
            function($q) use($request) {
                $date_ending = explode("/",$request->date_ending);
                $q->where('tx_general_journals.general_journal_date','<',$date_ending[2].'-'.$date_ending[1].'-'.$date_ending[0]);
            })
            ->when(request()->has('branch_id') && request()->branch_id!='#', function($q) use($request) {
                $q->where('usr.branch_id','=',request()->branch_id);
            })
            ->where('tx_general_journals.is_wt_for_appr','=','Y')
            ->where('tx_general_journals.active','=','Y')
            ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_general_journals.general_journal_date','ASC')
            ->orderBy('tx_general_journals.general_journal_no','DESC');
            // ->toSql();

            return DataTables::of($query)
            // ->addColumn('general_journal_date_new', function ($query) {
            //     return '::'.$query->general_journal_date;
            //     // return date_format(date_create($query->general_journal_date),"d/m/Y");
            // })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y') && $query->gj_active=='Y'){
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/general-journal-approval/'.urlencode($query->general_journal_no)).'" style="text-decoration: underline;">View</a>';
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/general-journal-approval/'.urlencode($query->general_journal_no)).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                if (strpos($query->general_journal_no,"Draft")>0 && $query->gj_active=='Y'){
                    return 'Draft';
                }else{
                    if ($query->is_wt_for_appr=='Y'){return 'Waiting for Approval';}
                    if ($query->is_wt_for_appr=='N' && $query->status_appr==null){return 'Created';}
                    if ($query->status_appr=='Y'){return 'Approved';}
                    if ($query->status_appr=='N'){return 'Rejected';}
                    return '';
                }
            })
            ->rawColumns(['action','status'])
            ->toJson();
        }

        $data = [
            'title'=>$this->title,
            'folder'=>$this->folder,
            'requestAll'=>$request,
            'branches'=>$branches,
            'qCurrency'=>$qCurrency,
            'is_director_now'=>$userLogin->is_director,
            'is_branch_head_now'=>$userLogin->is_branch_head,
        ];

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/general-journal');
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
     * @param  \App\Models\Tx_general_journal  $tx_general_journal
     * @return \Illuminate\Http\Response
     */
    public function show($journal_no)
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $coas = Mst_coa::where('is_master_coa','<>','Y')
        ->where('active','=','Y')
        ->orderBy('coa_name','ASC')
        ->get();

        $query = Tx_general_journal::where([
            'general_journal_no' => urldecode($journal_no),
            'active' => 'Y',
        ])
        ->first();
        if ($query) {
            $queryGJ = Tx_general_journal_detail::where([
                'general_journal_id'=>$query->id,
                'active'=>'Y'
            ]);
            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                'coas'=>$coas,
                'totalRow'=>(old('totalRow') ? old('totalRow') : $queryGJ->count()),
                'journals'=>$query,
                'journaldtls'=>$queryGJ->get(),
                'qCurrency'=>$qCurrency
            ];
            return view('tx.'.$this->folder.'.show', $data);
        } else {
            $data = [
                'errNotif'=>'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_general_journal  $tx_general_journal
     * @return \Illuminate\Http\Response
     */
    public function edit($journal_no)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_general_journal  $tx_general_journal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 56,
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
        
        // Start transaction!
        DB::beginTransaction();

        try {

            $upd = Tx_general_journal::where('id','=',$id)
            ->update([
                'is_wt_for_appr'=>'N',
                'who_appr'=>Auth::user()->id,
                'approved_at'=>now(),
                'status_appr'=>$request->journal_appr,
                'draft_to_created_at'=>now(),
                'updated_by'=>Auth::user()->id
            ]);

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        } catch(Exception $e){
            DB::rollback();
            // throw $e;

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        }

        // If we reach here, then
        // data is valid and working.
        // Commit the queries!
        DB::commit();

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_general_journal  $tx_general_journal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_general_journal $tx_general_journal)
    {
        //
    }
}
