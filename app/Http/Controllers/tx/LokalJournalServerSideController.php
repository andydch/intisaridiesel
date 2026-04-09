<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_coa;
use App\Models\Auto_inc;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
// use App\Rules\IsLJoApproved;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Models\Tx_lokal_journal;
use App\Models\Tx_receipt_order;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_delivery_order;
use App\Models\Tx_stock_transfer;
use Illuminate\Support\Facades\DB;
use App\Models\Tx_stock_adjustment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_lokal_journal_detail;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Tx_delivery_order_non_tax;
use App\Models\Tx_purchase_retur;
use App\Models\Mst_menu_user;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LokalJournalServerSideController extends Controller
{
    protected $title = 'Lokal Journal';
    protected $folder = 'lokal-journal';
    protected $idQ;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        if ($request->ajax()){
            $query = Tx_lokal_journal::leftJoin('userdetails AS usr','tx_lokal_journals.created_by','=','usr.user_id')
            ->select(
                'tx_lokal_journals.id as tx_id',
                'tx_lokal_journals.general_journal_no',
                'tx_lokal_journals.general_journal_date',
                'tx_lokal_journals.total_debit',
                'tx_lokal_journals.total_kredit',
                'tx_lokal_journals.module_no',
                'tx_lokal_journals.is_wt_for_appr',
                'tx_lokal_journals.status_appr',
                'tx_lokal_journals.active as gj_active',
                'tx_lokal_journals.created_by as createdby',
                'tx_lokal_journals.created_at as createdat',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head'
            )
            ->selectRaw('(CASE 
                WHEN tx_lokal_journals.is_draft=\'Y\' THEN \'Draft\'
                WHEN tx_lokal_journals.is_wt_for_appr=\'Y\' AND tx_lokal_journals.is_draft=\'N\' THEN \'Waiting for Approval\'
                WHEN tx_lokal_journals.is_wt_for_appr=\'N\' AND tx_lokal_journals.status_appr IS NULL THEN \'Create\'
                WHEN tx_lokal_journals.status_appr=\'Y\' THEN \'Create\'
                WHEN tx_lokal_journals.status_appr=\'N\' THEN \'Rejected\'
                ELSE \'\'
                END) AS doc_status')
            ->when($request->date_begin!='' && $request->date_ending!='',
            function($q) use($request) {
                $q->whereRaw('tx_lokal_journals.general_journal_date>=STR_TO_DATE("'.urldecode($request->date_begin).'", "%d/%m/%Y")');
                $q->whereRaw('tx_lokal_journals.general_journal_date<=STR_TO_DATE("'.urldecode($request->date_ending).'", "%d/%m/%Y")');
            })
            ->when($request->branch_id!=='#' && $request->branch_id!==null, function($q) use($request, $userLogin){
                $q->whereIn('tx_lokal_journals.id', function($q1) use($request, $userLogin){
                    $q1->select('tx_ljd.lokal_journal_id')
                    ->from('tx_lokal_journal_details AS tx_ljd')
                    ->leftJoin('mst_coas AS mst_c', 'tx_ljd.coa_id', '=', 'mst_c.id')
                    ->where('tx_ljd.active', 'Y')
                    ->when($userLogin->is_director!='Y', function($q2) use($userLogin){
                        $q2->where('mst_c.branch_id', $userLogin->branch_id);
                    })
                    ->when($userLogin->is_director=='Y', function($q2) use($request){
                        $q2->where('mst_c.branch_id', (int)$request->branch_id);
                    })
                    ->where('mst_c.active', 'Y');
                });
            })
            ->when($request->branch_id=='#' || $request->branch_id==null, function($q) use($request, $userLogin){
                // Log::info('test 3: '.$userLogin->branch_id);
                $q->whereIn('tx_lokal_journals.id', function($q1) use($request, $userLogin){
                    $q1->select('tx_ljd.lokal_journal_id')
                    ->from('tx_lokal_journal_details AS tx_ljd')
                    ->leftJoin('mst_coas AS mst_c', 'tx_ljd.coa_id', '=', 'mst_c.id')
                    ->where('tx_ljd.active', 'Y')
                    ->when($userLogin->is_director!='Y', function($q2) use($userLogin){
                        $q2->where('mst_c.branch_id', $userLogin->branch_id);
                    })
                    ->where('mst_c.active', 'Y');
                });
            })
            // ->when(request()->has('branch_id') && request()->branch_id!='#', function($q) use($request) {
            //     $q->where('usr.branch_id','=',request()->branch_id);
            // })
            ->where('tx_lokal_journals.active','=','Y')
            // ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
            //     $q->where('usr.branch_id','=',$userLogin->branch_id);
            // })
            ->orderBy('tx_lokal_journals.created_at','DESC');

            return DataTables::of($query)
            ->filterColumn('journal_no_with_coa', function($query, $keyword) {
                $query->where(function($q) use($query, $keyword){
                    $q->whereRaw('tx_lokal_journals.general_journal_no LIKE ?', ["%{$keyword}%"])
                    ->orWhere(function($q1) use($query, $keyword){
                        $q1->whereRaw('(SELECT CONCAT(mcoa.coa_code_complete, mcoa.coa_name) 
                            FROM tx_lokal_journal_details AS tx_ljd 
                            LEFT JOIN mst_coas AS mcoa ON tx_ljd.coa_id=mcoa.id 
                            WHERE tx_ljd.lokal_journal_id=tx_lokal_journals.id 
                            AND tx_ljd.active=\'Y\' 
                            ORDER BY tx_ljd.id ASC LIMIT 1) LIKE ?', ["%{$keyword}%"]);
                    });
                });
            })
            ->editColumn('journal_no_with_coa', function ($query) {
                $qCoa = Tx_lokal_journal_detail::leftJoin('mst_coas AS mcoa', 'tx_lokal_journal_details.coa_id', '=', 'mcoa.id')
                ->select(
                    'mcoa.coa_code_complete AS coa_code_complete',
                    'mcoa.coa_name AS coa_name',
                )
                ->where([
                    'tx_lokal_journal_details.lokal_journal_id' => $query->tx_id,
                    'tx_lokal_journal_details.active' => 'Y',
                ])
                ->orderBy('tx_lokal_journal_details.id', 'ASC')
                ->first();
                if ($qCoa){
                    return $query->general_journal_no.'<br/>COA: '.$qCoa->coa_code_complete.' - '.$qCoa->coa_name;
                }
                return '';
            })
            ->filterColumn('general_journal_date_wformat', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_lokal_journals.general_journal_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('general_journal_date_wformat', function ($query) {
                $general_journal_date_wformat=date_create($query->general_journal_date);
                return date_format($general_journal_date_wformat,"d/m/Y");
            })
            ->addColumn('createdat_wformat', function ($query) {
                $createdat_wformat=date_create($query->createdat);
                date_add($createdat_wformat,date_interval_create_from_date_string(env('WAKTU_ID')." hours"));
                return date_format($createdat_wformat,"d/m/Y H:i:s");
            })
            ->filterColumn('viewDoc', function($query, $keyword) {
                $query->whereRaw('tx_lokal_journals.module_no LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('viewDoc', function ($query) {
                $url = url(ENV('TRANSACTION_FOLDER_NAME').'/lokal-journal/view-doc?moduleno='.urlencode($query->module_no));
                $links = '<a href="'.$url.'" target="_new_win" style="text-decoration: underline;">'.$query->module_no.'</a>';
                return $links;
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y') && $query->gj_active=='Y'){
                    if ((strpos($query->general_journal_no,"Draft")>0 || $query->is_wt_for_appr=='Y' || $userLogin->is_director=='Y' || Auth::user()->id==1) && $query->module_no==null){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/lokal-journal/'.urlencode($query->general_journal_no).'/edit').'" style="text-decoration: underline;">Edit</a>';
                    }
                    if ($links!=''){
                        $links .= ' | ';
                    }
                    $links .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/lokal-journal/'.urlencode($query->general_journal_no)).'" style="text-decoration: underline;">View</a>';
                    if ($userLogin->is_director=='Y' && $query->is_wt_for_appr=='Y' && strpos($query->general_journal_no,"Draft")==0){
                        $links .= ' | <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/lokal-journal-approval/'.urlencode($query->general_journal_no)).'" style="text-decoration: underline;">Approve</a>';
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/lokal-journal/'.urlencode($query->general_journal_no)).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
            })
            // ->addColumn('status', function ($query) {
            //     if (strpos($query->general_journal_no,"Draft")>0 && $query->gj_active=='Y'){
            //         return 'Draft';
            //     }else{
            //         if ($query->is_wt_for_appr=='Y'){return 'Waiting for Approval';}
            //         if ($query->is_wt_for_appr=='N' && $query->status_appr==null){return 'Create';}
            //         if ($query->status_appr=='Y'){return 'Create';}
            //         if ($query->status_appr=='N'){return 'Rejected';}
            //         return '';
            //     }
            // })
            ->filterColumn('doc_status', function ($query, $keyword) {
                $query->whereRaw('(CASE 
                    WHEN tx_lokal_journals.is_draft=\'Y\' THEN \'Draft\'
                    WHEN tx_lokal_journals.is_wt_for_appr=\'Y\' AND tx_lokal_journals.is_draft=\'N\' THEN \'Waiting for Approval\'
                    WHEN tx_lokal_journals.is_wt_for_appr=\'N\' AND tx_lokal_journals.status_appr IS NULL THEN \'Create\'
                    WHEN tx_lokal_journals.status_appr=\'Y\' THEN \'Create\'
                    WHEN tx_lokal_journals.status_appr=\'N\' THEN \'Rejected\'
                    ELSE \'\'
                    END) LIKE \'%'.$keyword.'%\'');
            })
            ->editColumn('doc_status', function ($query) {
                return $query->doc_status;
            })
            ->rawColumns(['journal_no_with_coa','general_journal_date_wformat','createdat_wformat','viewDoc','action','doc_status'])
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

        return view('tx.'.$this->folder.'.index-server-side', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $coas = Mst_coa::where('is_master_coa','<>','Y')
        ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
            $q->where('branch_id','=',$userLogin->branch_id);
        })
        ->where(function ($query) {
            $query->where('local','=','N')
            ->orWhere('local','=','A');
        })
        ->where('active','=','Y')
        ->orderBy('coa_level','ASC')
        ->get();

        $data = [
            'title'=>$this->title,
            'folder'=>$this->folder,
            'coas'=>$coas,
            'totalRow'=>(old('totalRow') ? old('totalRow') : 0),
            'qCurrency'=>$qCurrency
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
            'menu_id' => 103,
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

        $validateInput = [
            'journal_date'=>'required',
            'totalCredit'=>'same:totalDebet'
        ];
        $errMsg = [];
        $validateCoaCode = '';
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['coa_code_'.$i]) {
                    $validateCoaCode = '';
                    for ($j = 0; $j < $request->totalRow; $j++) {
                        if ($i!=$j){
                            $validateCoaCode .= '|different:'.'coa_code_'.$j;
                        }
                    }

                    $validateShipmentInput = [
                        'coa_code_'.$i=>'required|numeric',
                        // 'coa_code_'.$i=>'required|numeric'.$validateCoaCode,     // --- validasi duplikasi
                        'debet_amount'.$i=>['required_without:credit_amount'.$i,new NumericCustom('Debet Amount')],
                        'credit_amount'.$i=>['required_without:debet_amount'.$i,new NumericCustom('Credit Amount')],
                    ];
                    $errShipmentMsg = [
                        'coa_code_'.$i.'.numeric'=>'Please select a valid COA code',
                        'coa_code_'.$i.'.different'=>'There cannot be the same COA code',
                        'debet_amount'.$i.'.required_without'=>'The debet amount field is required.',
                        'debet_amount'.$i.'.numeric'=>'The debet amount must be numeric.',
                        'credit_amount'.$i.'.required_without'=>'The credit amount field is required.',
                        'credit_amount'.$i.'.numeric'=>'The credit amount must be numeric.',
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
            $identityName = 'Tx_lokal_journal-draft';
            if($request->is_draft=='Y'){
                $draft_at = now();
                $autoInc = Auto_inc::where([
                    'identity_name'=>$identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "n");
                    if ((int)date("n") > (int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name'=>$identityName
                        ])
                        ->update([
                            'id_auto_inc'=>1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                        $updInc = Auto_inc::where([
                            'identity_name'=>$identityName
                        ])
                        ->update([
                            'id_auto_inc'=>$newInc
                        ]);
                    }
                } else {
                    $insInc = Auto_inc::create([
                        'identity_name'=>$identityName,
                        'id_auto_inc'=>$newInc
                    ]);
                }
                $journal_no = env('P_LOKAL_JURNAL').date('y').date('m').'Draft'.strval($newInc);
            }

            $identityName = 'Tx_lokal_journal';
            $journal_date_new = explode("/", $request->journal_date);
            if($request->is_draft!='Y'){
                $yearTemp = substr($journal_date_new[2], 2, 2);
                $monthTemp = $journal_date_new[1];
                $ymTemp = $yearTemp.$monthTemp;
                $zero = '';
                $YearMonth = '';
                $newInc = 1;

                $draft_to_created_at = now();
                $autoInc = Auto_inc::where([
                    'identity_name'=>$identityName
                ])
                ->first();
                if ($autoInc) {
                    // jika counter sudah terbentuk

                    $date = date_format(date_create($autoInc->updated_at), "n");
                    $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                    $dateNow = date("ym");
                    if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                        // jika bulan di server berbeda dengan bulan jurnal yg dipilih

                        // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                        // untuk menghindari duplikasi
                        $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                        ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                        ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                        ->where([
                            'active'=>'Y',
                        ])
                        ->orderBy('general_journal_no', 'DESC')
                        ->first();
                        if ($lastCounterIfAny){
                            // ambil no urut terakhir dan ditambahkan 1

                            $newInc = $lastCounterIfAny->lastCounter+1;
                        }
                    } else {
                        // jika bulan di server sama dengan bulan jurnal yg dipilih

                        $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => $newInc
                        ]);
                    }

                    $YearMonth = $yearTemp.$monthTemp;
                } else {
                    // jika counter belum pernah terbentuk

                    $dateNow = date("ym");
                    // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                    // untuk menghindari duplikasi
                    $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                    ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                    ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                    ->where([
                        'active'=>'Y',
                    ])
                    ->orderBy('general_journal_no', 'DESC')
                    ->first();
                    if ($lastCounterIfAny){
                        // ambil no urut terakhir dan ditambahkan 1

                        $newInc = $lastCounterIfAny->lastCounter+1;
                    }

                    $insInc = Auto_inc::create([
                        'identity_name'=>$identityName,
                        'id_auto_inc'=>$newInc
                    ]);

                    $YearMonth = date('y').date('m');
                }

                $zero = '';
                for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);
            }

            $journal_date = explode("/",$request->journal_date);

            // Creates DateTime objects
            $dtNow = date_create(date("Y-m-d"));
            $dtJournal = date_create($journal_date[2].'-'.$journal_date[1].'-'.$journal_date[0]);
            // Calculates the difference between DateTime objects
            // $interval = date_diff($dtNow, $dtJournal); // echo $interval->format('%R%y years %m months');
            // $diff_months = ($interval->format('%y')*12)+$interval->format('%m');
            // $needApproval = 'N';
            // if ($diff_months>=1){$needApproval = 'Y';}

            $ins = Tx_lokal_journal::create([
                'general_journal_no'=>$journal_no,
                'general_journal_date'=>$journal_date[2].'-'.$journal_date[1].'-'.$journal_date[0],
                'total_debit'=>$request->totalDebet,
                'total_kredit'=>$request->totalCredit,
                'is_draft'=>$request->is_draft,
                'draft_at'=>$draft_at,
                'draft_to_created_at'=>$draft_to_created_at,
                'is_wt_for_appr'=>'Y',
                // 'who_appr',
                // 'status_appr',
                // 'general_journal_date_old',
                // 'total_debit_old',
                // 'total_kredit_old',
                // 'total_debit_new',
                // 'total_kredit_new',
                'active'=>'Y',
                'created_by'=>Auth::user()->id,
                'updated_by'=>Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            if ($request->totalRow > 0) {
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['coa_code_'.$i]) {
                        $insGJdtl = Tx_lokal_journal_detail::create([
                            'lokal_journal_id'=>$maxId,
                            'coa_id'=>$request['coa_code_'.$i],
                            'description'=>$request['desc_part'.$i],
                            'debit'=>GlobalFuncHelper::moneyValidate((!is_null($request['debet_amount'.$i])?$request['debet_amount'.$i]:0)),
                            'kredit'=>GlobalFuncHelper::moneyValidate((!is_null($request['credit_amount'.$i])?$request['credit_amount'.$i]:0)),
                            // 'debit_old',
                            // 'kredit_old',
                            // 'debit_new',
                            // 'kredit_new',
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);
                    }
                }
            }

            if ($request->is_draft!='Y'){
                // auto approved jika bukan DRAFT
                $updAppr = Tx_lokal_journal::where('id', '=', $maxId)
                ->update([
                    'is_wt_for_appr' => 'N',
                    'who_appr' => 4,
                    'approved_at' => date("Y-m-d H:i:s"),
                    'status_appr' => 'Y',
                ]);
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
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
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

        $query = Tx_lokal_journal::where([
            'general_journal_no' => urldecode($journal_no),
            'active' => 'Y',
            ])
        ->first();
        if ($query) {
            $queryLJ = Tx_lokal_journal_detail::where([
                'lokal_journal_id'=>$query->id,
                'active'=>'Y'
            ]);
            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                'coas'=>$coas,
                'totalRow'=>(old('totalRow') ? old('totalRow') : $queryLJ->count()),
                'journals'=>$query,
                'journaldtls'=>$queryLJ->get(),
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
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function edit($journal_no)
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $coas = Mst_coa::where('is_master_coa','<>','Y')
        ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
            $q->where('branch_id','=',$userLogin->branch_id);
        })
        ->where(function ($query) {
            $query->where('local','=','N')
            ->orWhere('local','=','A');
        })
        ->where('active','=','Y')
        ->orderBy('coa_level','ASC')
        ->get();

        $query = Tx_lokal_journal::where([
            'general_journal_no' => urldecode($journal_no),
            'active' => 'Y',
        ])
        ->first();
        if ($query) {
            $queryLJ = Tx_lokal_journal_detail::where([
                'lokal_journal_id'=>$query->id,
                'active'=>'Y'
            ]);
            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                'coas'=>$coas,
                'totalRow'=>(old('totalRow') ? old('totalRow') : $queryLJ->count()),
                'journals'=>$query,
                'journaldtls'=>$queryLJ->get(),
                'qCurrency'=>$qCurrency
            ];
            return view('tx.'.$this->folder.'.edit', $data);
        } else {
            $data = [
                'errNotif'=>'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $journal_no)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 103,
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
        
        $general_journal_date_old = null;
        $total_debit_old = 0;
        $total_kredit_old = 0;
        $gjOld = Tx_lokal_journal::where('general_journal_no','=',urldecode($journal_no))
        ->first();
        if($gjOld){
            $general_journal_date_old = $gjOld->general_journal_date;
            $total_debit_old = $gjOld->total_debit;
            $total_kredit_old = $gjOld->total_kredit;
        }

        $validateInput = [
            // 'general_journal_no_ori'=>[new IsLJoApproved],
            'journal_date'=>'required',
            'totalCredit'=>'same:totalDebet'
        ];
        $errMsg = [];
        $validateCoaCode = '';
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['coa_code_'.$i]) {

                    $validateCoaCode = '';
                    for ($j = 0; $j < $request->totalRow; $j++) {
                        if ($i!=$j){
                            $validateCoaCode .= '|different:'.'coa_code_'.$j;
                        }
                    }

                    $validateShipmentInput = [
                        'coa_code_'.$i=>'required|numeric',
                        // 'coa_code_'.$i=>'required|numeric'.$validateCoaCode,     // --- validasi duplikasi
                        'debet_amount'.$i=>['required_without:credit_amount'.$i,new NumericCustom('Debet Amount')],
                        'credit_amount'.$i=>['required_without:debet_amount'.$i,new NumericCustom('Credit Amount')],
                    ];
                    $errShipmentMsg = [
                        'coa_code_'.$i.'.numeric'=>'Please select a valid COA code',
                        'coa_code_'.$i.'.different'=>'There cannot be the same COA code',
                        'debet_amount'.$i.'.required_without'=>'The debet amount field is required.',
                        'debet_amount'.$i.'.numeric'=>'The debet amount must be numeric.',
                        'credit_amount'.$i.'.required_without'=>'The credit amount field is required.',
                        'credit_amount'.$i.'.numeric'=>'The credit amount must be numeric.',
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

            $gj = Tx_lokal_journal::where('general_journal_no','=',urldecode($journal_no))
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

            $journal_date_new = explode("/", $request->journal_date);
            if($request->is_draft!='Y' && $draft){
                // promote to created
                $yearTemp = substr($journal_date_new[2], 2, 2);
                $monthTemp = $journal_date_new[1];
                $ymTemp = $yearTemp.$monthTemp;
                $zero = '';
                $YearMonth = '';
                $newInc = 1;

                $identityName = 'Tx_lokal_journal';
                $autoInc = Auto_inc::where([
                    'identity_name'=>$identityName
                ])
                ->first();
                if ($autoInc) {
                    // jika counter sudah terbentuk

                    $date = date_format(date_create($autoInc->updated_at), "n");
                    $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                    $dateNow = date("ym");
                    if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                        // jika bulan di server berbeda dengan bulan jurnal yg dipilih

                        // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                        // untuk menghindari duplikasi
                        $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                        ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                        ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                        ->where([
                            'active'=>'Y',
                        ])
                        ->orderBy('general_journal_no', 'DESC')
                        ->first();
                        if ($lastCounterIfAny){
                            // ambil no urut terakhir dan ditambahkan 1

                            $newInc = $lastCounterIfAny->lastCounter+1;
                        }
                    } else {
                        // jika bulan di server sama dengan bulan jurnal yg dipilih

                        $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => $newInc
                        ]);
                    }

                    $YearMonth = $yearTemp.$monthTemp;
                } else {
                    // jika counter belum pernah terbentuk

                    $dateNow = date("ym");
                    // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                    // untuk menghindari duplikasi
                    $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                    ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                    ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                    ->where([
                        'active'=>'Y',
                    ])
                    ->orderBy('general_journal_no', 'DESC')
                    ->first();
                    if ($lastCounterIfAny){
                        // ambil no urut terakhir dan ditambahkan 1

                        $newInc = $lastCounterIfAny->lastCounter+1;
                    }

                    $insInc = Auto_inc::create([
                        'identity_name'=>$identityName,
                        'id_auto_inc'=>$newInc
                    ]);

                    $YearMonth = date('y').date('m');
                }

                $zero = '';
                for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);

                $upd = Tx_lokal_journal::where('id','=',$gjOld->id)
                ->update([
                    'general_journal_no'=>$journal_no,
                    // 'general_journal_date'=>date("Y-m-d"),
                    'is_draft'=>$request->is_draft,
                    'draft_to_created_at'=>now(),
                    'updated_by'=>Auth::user()->id,
                    // auto approved
                    'is_wt_for_appr' => 'N',
                    'who_appr' => 4,
                    'approved_at' => date("Y-m-d H:i:s"),
                    'status_appr' => 'Y',
                    // auto approved
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_lokal_journal::where('id','=',$gjOld->id)
                ->update([
                    'is_draft'=>$request->is_draft,
                    'draft_to_created_at'=>now(),
                    'updated_by'=>Auth::user()->id
                ]);
            }

            $journal_date = explode("/",$request->journal_date);

            // Creates DateTime objects
            $dtNow = date_create(date("Y-m-d"));
            $dtJournal = date_create($journal_date[2].'-'.$journal_date[1].'-'.$journal_date[0]);
            // Calculates the difference between DateTime objects
            // $interval = date_diff($dtNow, $dtJournal); // echo $interval->format('%R%y years %m months');
            // $diff_months = ($interval->format('%y')*12)+$interval->format('%m');
            // $needApproval = 'N';
            // if ($diff_months>=1){$needApproval = 'Y';}

            $upd = Tx_lokal_journal::where('id','=',$gjOld->id)
            ->update([
                'general_journal_date'=>$journal_date[2].'-'.$journal_date[1].'-'.$journal_date[0],
                'total_debit'=>$request->totalDebet,
                'total_kredit'=>$request->totalCredit,
                // auto approved
                'is_wt_for_appr' => 'N',
                'who_appr' => 4,
                'approved_at' => date("Y-m-d H:i:s"),
                'status_appr' => 'Y',
                // auto approved
                'general_journal_date_old'=>$general_journal_date_old,
                'total_debit_old'=>$total_debit_old,
                'total_kredit_old'=>$total_kredit_old,
                'total_debit_new'=>$request->totalDebet,
                'total_kredit_new'=>$request->totalCredit,
                'active'=>'Y',
                'updated_by'=>Auth::user()->id,
            ]);

            // set not active
            $updPart = Tx_lokal_journal_detail::where([
                'lokal_journal_id'=>$gjOld->id
            ])
            ->update([
                'active'=>'N'
            ]);

            if ($request->totalRow > 0) {
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['coa_code_'.$i]) {
                        if ($request['gj_dtl_id'.$i] > 0) {
                            $qPart = Tx_lokal_journal_detail::where('id','=',$request['gj_dtl_id'.$i])
                            ->first();
                            if ($qPart){
                                $updPart = Tx_lokal_journal_detail::where('id','=',$request['gj_dtl_id'.$i])
                                ->update([
                                    'lokal_journal_id'=>$gjOld->id,
                                    'coa_id'=>$request['coa_code_'.$i],
                                    'description'=>$request['desc_part'.$i],
                                    'debit'=>GlobalFuncHelper::moneyValidate((!is_null($request['debet_amount'.$i])?$request['debet_amount'.$i]:0)),
                                    'kredit'=>GlobalFuncHelper::moneyValidate((!is_null($request['credit_amount'.$i])?$request['credit_amount'.$i]:0)),
                                    'debit_old'=>$qPart->debit,
                                    'kredit_old'=>$qPart->kredit,
                                    'debit_new'=>GlobalFuncHelper::moneyValidate((!is_null($request['debet_amount'.$i])?$request['debet_amount'.$i]:0)),
                                    'kredit_new'=>GlobalFuncHelper::moneyValidate((!is_null($request['credit_amount'.$i])?$request['credit_amount'.$i]:0)),
                                    'active'=>'Y',
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                        } else {
                            $insPart = Tx_lokal_journal_detail::create([
                                'lokal_journal_id'=>$gjOld->id,
                                'coa_id'=>$request['coa_code_'.$i],
                                'description'=>$request['desc_part'.$i],
                                'debit'=>GlobalFuncHelper::moneyValidate((!is_null($request['debet_amount'.$i])?$request['debet_amount'.$i]:0)),
                                'kredit'=>GlobalFuncHelper::moneyValidate((!is_null($request['credit_amount'.$i])?$request['credit_amount'.$i]:0)),
                                // 'debit_old',
                                // 'kredit_old',
                                // 'debit_new',
                                // 'kredit_new',
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
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
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_lokal_journal $Tx_lokal_journal)
    {
        //
    }

    public function view_doc(Request $request){
        $moduleno = 'module-'.urldecode($request->moduleno);

        if (strpos($moduleno,env('P_RECEIPT_ORDER'))>0){
            $qRO = Tx_receipt_order::where([
                'receipt_no'=>urldecode($request->moduleno),
                'active'=>'Y',
            ])
            ->first();
            if ($qRO){
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$qRO->id);
            }else{
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/0');
            }
        }
        if (strpos($moduleno,env('P_FAKTUR'))>0){
            $qFK = Tx_delivery_order::where([
                'delivery_order_no'=>urldecode($request->moduleno),
                'active'=>'Y',
            ])
            ->first();
            if ($qFK){
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/faktur/'.$qFK->id);
            }else{
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/faktur/128000');
            }
        }
        if (strpos($moduleno,env('P_NOTA_RETUR'))>0){
            return redirect(ENV('TRANSACTION_FOLDER_NAME').'/nota-retur/'.urlencode($request->moduleno));
        }
        if (strpos($moduleno,env('P_NOTA_PENJUALAN'))>0){
            $qFK = Tx_delivery_order_non_tax::where([
                'delivery_order_no'=>urldecode($request->moduleno),
                'active'=>'Y',
            ])
            ->first();
            if ($qFK){
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order-local/'.$qFK->id);
            }else{
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order-local/0');
            }
        }
        if (strpos($moduleno,env('P_RETUR'))>0){
            return redirect(ENV('TRANSACTION_FOLDER_NAME').'/retur/'.urlencode($request->moduleno));
        }
        if (strpos($moduleno,env('P_STOCK_TRANSFER'))>0){
            $qFK = Tx_stock_transfer::where([
                'stock_transfer_no'=>urldecode($request->moduleno),
                'active'=>'Y',
            ])
            ->first();
            if ($qFK){
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer/'.$qFK->id);
            }else{
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer/0');
            }
        }
        if (strpos($moduleno,env('P_STOCK_ADJUSTMENT'))>0){
            $qFK = Tx_stock_adjustment::where([
                'stock_adj_no'=>urldecode($request->moduleno),
                'active'=>'Y',
            ])
            ->first();
            if ($qFK){
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/stock-adjustment/'.$qFK->id);
            }else{
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/stock-adjustment/0');
            }
        }
        if (strpos($moduleno,env('P_PAYMENT_VOUCHER'))>0){
            return redirect(ENV('TRANSACTION_FOLDER_NAME').'/payment-voucher/'.urlencode($request->moduleno));
        }
        if (strpos($moduleno,env('P_PAYMENT_RECEIPT'))>0){
            return redirect(ENV('TRANSACTION_FOLDER_NAME').'/payment-receipt/'.urlencode($request->moduleno));
        }
        if (strpos($moduleno,env('P_PURCHASE_RETUR'))>0){
            $qPR = Tx_purchase_retur::where([
                'purchase_retur_no'=>urldecode($request->moduleno),
                'active'=>'Y',
            ])
            ->first();
            return redirect(ENV('TRANSACTION_FOLDER_NAME').'/purchase-retur/'.($qPR?$qPR->id:0));
        }
    }
}
