<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_coa;
use App\Models\Auto_inc;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_general_journal;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_general_journal_detail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GeneralJournalController extends Controller
{
    protected $title = 'General Journal';
    protected $folder = 'general-journal';
    protected $idQ;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if($userLogin->is_director=='Y'){
            $query = Tx_general_journal::leftJoin('userdetails AS usr','tx_general_journals.created_by','=','usr.user_id')
            ->select(
                'tx_general_journals.*'
            )
            ->when(request()->has('date_begin') && request()->has('date_ending') && request()->date_begin!=null && request()->date_ending!=null, function($q) use($request) {
                $q->where('tx_general_journals.general_journal_date','>',request()->date_begin);
            })
            ->when(request()->has('date_begin') && request()->has('date_ending') && request()->date_begin!=null && request()->date_ending!=null, function($q) use($request) {
                $q->where('tx_general_journals.general_journal_date','<',request()->date_ending);
            })
            // ->when(request()->has('branch_id') && request()->branch_id!='#', function($q) use($request) {
            //     $q->where('usr.branch_id','=',request()->branch_id);
            // })
            ->where('tx_general_journals.active','=','Y')
            ->orderBy('tx_general_journals.general_journal_date','DESC');
            // ->toSql();dd($query);
        }else{
            $query = Tx_general_journal::leftJoin('userdetails AS usr','tx_general_journals.created_by','=','usr.user_id')
            ->select(
                'tx_general_journals.*'
            )
            ->when(request()->has('date_begin') && request()->has('date_ending') && request()->date_begin!=null && request()->date_ending!=null, function($q) use($request) {
                $q->where('tx_general_journals.general_journal_date','>',request()->date_begin);
            })
            ->when(request()->has('date_begin') && request()->has('date_ending') && request()->date_begin!=null && request()->date_ending!=null, function($q) use($request) {
                $q->where('tx_general_journals.general_journal_date','<',request()->date_ending);
            })
            // ->when(request()->has('branch_id') && request()->branch_id!='#', function($q) use($request) {
            //     $q->where('usr.branch_id','=',request()->branch_id);
            // })
            ->where('tx_general_journals.active','=','Y')
            ->where('usr.branch_id','=',$userLogin->branch_id)
            ->orderBy('tx_general_journals.general_journal_date','DESC');
            // ->toSql();dd($query);
        }

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $data = [
            'journals' => $query->get(),
            'journalsCount' => $query->count(),
            'title' => $this->title,
            'folder' => $this->folder,
            'requestAll' => $request,
            'branches' => $branches,
            'qCurrency' => $qCurrency,
            'is_director_now' => $userLogin->is_director,
            'is_branch_head_now' => $userLogin->is_branch_head,
        ];

        return view('tx.'.$this->folder.'.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $coas = Mst_coa::where('is_master_coa','=','Y')
        ->where('active','=','Y')
        ->orderBy('coa_name','ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'coas' => $coas,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'qCurrency' => $qCurrency
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
        $validateInput = [
            'journal_date' => 'required|date',
            'totalCredit' => 'same:totalDebet'
        ];
        $errMsg = [];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['coa_name_'.$i]) {
                    $validateShipmentInput = [
                        'coa_code_'.$i => 'required|numeric',
                        'coa_name_'.$i => 'required|numeric',
                        'debet_amount'.$i => ['required',new NumericCustom('Debet Amount')],
                        'credit_amount'.$i => ['required',new NumericCustom('Credit Amount')],
                    ];
                    $errShipmentMsg = [
                        'coa_code_'.$i.'.numeric' => 'Please select a valid COA code',
                        'coa_name_'.$i.'.numeric' => 'Please select a valid COA name',
                        'debet_amount'.$i.'.required' => 'The debet amount field is required.',
                        'debet_amount'.$i.'.numeric' => 'The debet amount must be numeric.',
                        'credit_amount'.$i.'.required' => 'The credit amount field is required.',
                        'credit_amount'.$i.'.numeric' => 'The credit amount must be numeric.',
                    ];
                    $validateInput = array_merge($validateInput, $validateShipmentInput);
                    $errMsg = array_merge($errMsg, $errShipmentMsg);
                }
            }
        }
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {

            $draft_at = null;
            $draft_to_created_at = null;
            $identityName = 'tx_general_journal-draft';
            if($request->is_draft=='Y'){
                $draft_at = now();
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y") > (int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => $newInc
                        ]);
                    }
                } else {
                    $insInc = Auto_inc::create([
                        'identity_name' => $identityName,
                        'id_auto_inc' => $newInc
                    ]);
                }
                $journal_no = env('P_GENERAL_JURNAL').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_general_journal';
            if($request->is_draft!='Y'){
                $draft_to_created_at = now();
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y") > (int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => $newInc
                        ]);
                    }
                } else {
                    $insInc = Auto_inc::create([
                        'identity_name' => $identityName,
                        'id_auto_inc' => $newInc
                    ]);
                }

                $zero = '';
                for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $journal_no = env('P_GENERAL_JURNAL').date('y').'-'.$zero.strval($newInc);
            }

            $ins = Tx_general_journal::create([
                'general_journal_no' => $journal_no,
                'general_journal_date' => $request->journal_date,
                'total_debit' => $request->totalDebet,
                'total_kredit' => $request->totalCredit,
                'is_draft' => $request->is_draft,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;
            // $maxId = Tx_general_journal::max('id');

            if ($request->totalRow > 0) {
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['coa_name_'.$i]) {
                        $insGJdtl = Tx_general_journal_detail::create([
                            'general_journal_id' => $maxId,
                            'coa_id' => $request['coa_code_'.$i],
                            'description' => $request['desc_part'.$i],
                            'debit' => GlobalFuncHelper::moneyValidate($request['debet_amount'.$i]),
                            'kredit' => GlobalFuncHelper::moneyValidate($request['credit_amount'.$i]),
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
                }
            }

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

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
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
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $coas = Mst_coa::where('is_master_coa','=','Y')
        ->where('active','=','Y')
        ->orderBy('coa_name','ASC')
        ->get();

        $query = Tx_general_journal::where('general_journal_no','=',$journal_no)
        ->first();
        if ($query) {
            $queryGJ = Tx_general_journal_detail::where([
                'general_journal_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $queryGJCount = Tx_general_journal_detail::where([
                'general_journal_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'coas' => $coas,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryGJCount),
                'journals' => $query,
                'journaldtls' => $queryGJ,
                'qCurrency' => $qCurrency
            ];
            return view('tx.'.$this->folder.'.show', $data);
        } else {
            $data = [
                'errNotif' => 'The data you are looking for is not found'
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
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $coas = Mst_coa::where('is_master_coa','=','Y')
        ->where('active','=','Y')
        ->orderBy('coa_name','ASC')
        ->get();

        $query = Tx_general_journal::where('general_journal_no','=',$journal_no)
        ->first();
        if ($query) {
            $queryGJ = Tx_general_journal_detail::where([
                'general_journal_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $queryGJCount = Tx_general_journal_detail::where([
                'general_journal_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'coas' => $coas,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryGJCount),
                'journals' => $query,
                'journaldtls' => $queryGJ,
                'qCurrency' => $qCurrency
            ];
            return view('tx.'.$this->folder.'.edit', $data);
        } else {
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_general_journal  $tx_general_journal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $journal_no)
    {
        $gjOld = Tx_general_journal::where('general_journal_no','=',urldecode($journal_no))
        ->first();

        $validateInput = [
            'journal_date' => 'required|date',
            'totalCredit' => 'same:totalDebet'
        ];
        $errMsg = [];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['coa_name_'.$i]) {
                    $validateShipmentInput = [
                        'coa_code_'.$i => 'required|numeric',
                        'coa_name_'.$i => 'required|numeric',
                        'debet_amount'.$i => ['required',new NumericCustom('Debet Amount')],
                        'credit_amount'.$i => ['required',new NumericCustom('Credit Amount')],
                    ];
                    $errShipmentMsg = [
                        'coa_code_'.$i.'.numeric' => 'Please select a valid COA code',
                        'coa_name_'.$i.'.numeric' => 'Please select a valid COA name',
                        'debet_amount'.$i.'.required' => 'The debet amount field is required.',
                        'debet_amount'.$i.'.numeric' => 'The debet amount must be numeric.',
                        'credit_amount'.$i.'.required' => 'The credit amount field is required.',
                        'credit_amount'.$i.'.numeric' => 'The credit amount must be numeric.',
                    ];
                    $validateInput = array_merge($validateInput, $validateShipmentInput);
                    $errMsg = array_merge($errMsg, $errShipmentMsg);
                }
            }
        }
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {

            $draft = false;
            $gj = Tx_general_journal::where('general_journal_no','=',urldecode($journal_no))
            ->where('general_journal_no','LIKE','%Draft%')
            ->first();
            if($gj){
                // looking for draft order no
                $draft = true;
                $journal_no = $gj->order_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_general_journal';
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y") > (int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => $newInc
                        ]);
                    }
                } else {
                    $insInc = Auto_inc::create([
                        'identity_name' => $identityName,
                        'id_auto_inc' => $newInc
                    ]);
                }

                $zero = '';
                for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $journal_no = env('P_GENERAL_JURNAL').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_general_journal::where('id','=',$gjOld->id)
                ->update([
                    'general_journal_no' => $journal_no,
                    'general_journal_date' => date("Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_general_journal::where('id','=',$gjOld->id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $upd = Tx_general_journal::where('id','=',$gjOld->id)
            ->update([
                // 'general_journal_no' => $journal_no,
                // 'general_journal_date' => $request->journal_date,
                'total_debit' => $request->totalDebet,
                'total_kredit' => $request->totalCredit,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            // set not active
            $updPart = Tx_general_journal_detail::where([
                'general_journal_id' => $gjOld->id
            ])
            ->update([
                'active' => 'N'
            ]);

            if ($request->totalRow > 0) {
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['coa_name_'.$i]) {
                        if ($request['gj_dtl_id'.$i] > 0) {
                            $insPart = Tx_general_journal_detail::where('id','=',$request['gj_dtl_id'.$i])
                            ->update([
                                'general_journal_id' => $gjOld->id,
                                'coa_id' => $request['coa_code_'.$i],
                                'description' => $request['desc_part'.$i],
                                'debit' => GlobalFuncHelper::moneyValidate($request['debet_amount'.$i]),
                                'kredit' => GlobalFuncHelper::moneyValidate($request['credit_amount'.$i]),
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id,
                            ]);
                        } else {
                            $insPart = Tx_general_journal_detail::create([
                                'general_journal_id' => $gjOld->id,
                                'coa_id' => $request['coa_code_'.$i],
                                'description' => $request['desc_part'.$i],
                                'debit' => GlobalFuncHelper::moneyValidate($request['debet_amount'.$i]),
                                'kredit' => GlobalFuncHelper::moneyValidate($request['credit_amount'.$i]),
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }
                    }
                }
            }

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
