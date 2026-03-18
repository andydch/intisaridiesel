<?php

namespace App\Http\Controllers\adm;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Mst_automatic_journal;
use App\Models\Mst_automatic_journal_detail;
use App\Models\Mst_automatic_journal_detail_ext;
use App\Models\Mst_branch;
use App\Models\Mst_coa;
use App\Models\Mst_menu_user;
use App\Rules\IsCoaMaster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AutomaticJournalController extends Controller
{
    protected $title = 'Automatic Journal';
    protected $folder = 'automatic-journal';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $qAutomaticJournal = Mst_automatic_journal::where('active','=','Y')
        ->orderBy('order_no','ASC');
        $data = [
            'qAutomaticJournal' => $qAutomaticJournal->get(),
            'qAutomaticJournalCount' => $qAutomaticJournal->count(),
            'title' => $this->title,
            'folder' => $this->folder,
        ];

        return view('adm.'.$this->folder.'.index', $data);
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
     * @param  \App\Models\Tx_tax_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $qAutomaticJournal = Mst_automatic_journal::where([
            'id'=>$id,
            'active'=>'Y'
        ])
        ->first();
        $qAutomaticJournalDtl = [];
        $qBranch = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        if ($qAutomaticJournal) {
            $qAutomaticJournalDtl = Mst_automatic_journal_detail::where([
                'auto_journal_id'=>$id,
                'active'=>'Y'
            ])
            ->when($id!=12 && $request->branch_id, function($q) use($request){
                $q->where([
                    'branch_id'=>$request->branch_id,
                ]);
            })
            ->when($id==12, function($q) use($request){
                $q->when($request->branch_id, function($q1) use($request){
                    $q1->where([
                        'branch_id'=>$request->branch_id,
                    ]);
                });
                $q->when($request->branch_in_id, function($q1) use($request){
                    $q1->where([
                        'branch_in_id'=>$request->branch_in_id,
                    ]);
                });
            })
            ->get();

            $isPpn = 'N';
            $isNonPpn = 'N';
            if ($id==1 || $id==2 || $id==5 || $id==7 || $id==8 || $id==11 || $id==12 || $id==15){
                $isPpn = 'Y';
            }
            if ($id==3 || $id==4 || $id==6 || $id==13 || $id==14 || $id==16){
                $isNonPpn = 'Y';
            }

            $qCoas = Mst_coa::when($request->branch_id, function($q) use($request){
                $q->where([
                    'branch_id'=>$request->branch_id
                ]);
            })
            ->when($isPpn=='Y', function($q){
                $q->whereIn('local',['P','A']);
            })
            ->when($isNonPpn=='Y', function($q){
                $q->whereIn('local',['N','A']);
            })
            ->where('is_master_coa','<>','Y')
            ->where('active','=','Y')
            ->orderBy('coa_code_complete','ASC')
            ->orderBy('coa_name','ASC')
            ->get();

            $data = [
                'qAutomaticJournal' => $qAutomaticJournal,
                'qAutomaticJournalDtl' => $qAutomaticJournalDtl,
                'qBranch' => $qBranch,
                'method_id' => $request->method_id,
                'branch_in_id' => $request->branch_in_id,
                'branch_id' => $request->branch_id,
                'title' => $this->title,
                'folder' => $this->folder,
            ];

            return view('adm.'.$this->folder.'.show', $data);
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
     * @param  \App\Models\Tx_tax_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $qAutomaticJournal = Mst_automatic_journal::where([
            'id'=>$id,
            'active'=>'Y'
        ])
        ->first();
        $qAutomaticJournalDtl = [];
        if ($qAutomaticJournal) {
            $qBranch = Mst_branch::where('active','=','Y')
            ->orderBy('name','ASC')
            ->get();

            $qAutomaticJournalDtl = Mst_automatic_journal_detail::where([
                'auto_journal_id'=>$id,
                'active'=>'Y'
            ])
            ->when($id!=12 && $request->branch_id, function($q) use($request){
                $q->where([
                    'branch_id'=>$request->branch_id,
                ]);
            })
            ->when($id==12, function($q) use($request){
                $q->when($request->branch_id, function($q1) use($request){
                    $q1->where([
                        'branch_id'=>$request->branch_id,
                    ]);
                });
                $q->when($request->branch_in_id, function($q1) use($request){
                    $q1->where([
                        'branch_in_id'=>$request->branch_in_id,
                    ]);
                });
            })
            ->get();

            $isPpn = 'N';
            $isNonPpn = 'N';
            if ($id==1 || $id==2 || $id==5 || $id==7 || $id==8 || $id==11 || $id==12){
                $isPpn = 'Y';
            }
            if ($id==3 || $id==4 || $id==6 || $id==13 || $id==14){
                $isNonPpn = 'Y';
            }

            $qCoas = Mst_coa::when($request->branch_id, function($q) use($request){
                $q->where([
                    'branch_id'=>$request->branch_id
                ]);
            })
            ->when($isPpn=='Y', function($q){
                $q->whereIn('local',['P','A']);
            })
            ->when($isNonPpn=='Y', function($q){
                $q->whereIn('local',['N','A']);
            })
            ->where('is_master_coa','<>','Y')
            ->where('active','=','Y')
            ->orderBy('coa_code_complete','ASC')
            ->orderBy('coa_name','ASC')
            ->get();

            $data = [
                'qAutomaticJournal' => $qAutomaticJournal,
                'qAutomaticJournalDtl' => $qAutomaticJournalDtl,
                'qCoas' => $qCoas,
                'qBranch' => $qBranch,
                'method_id' => $request->method_id,
                'branch_in_id' => $request->branch_in_id,
                'branch_id' => $request->branch_id,
                'title' => $this->title,
                'folder' => $this->folder,
            ];

            return view('adm.'.$this->folder.'.edit', $data);
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
     * @param  \App\Models\Tx_tax_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 101,
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

        if ($id==12){
            $validateInput = [
                'branchId' => 'required|numeric',
                'branch_in_id' => 'required|numeric|different:branchId',
            ];
            $errMsg = [
                'branchId.required' => 'Please select a valid branch',
                'branchId.numeric' => 'Please select a valid branch',
                'branch_in_id.required' => 'Please select a valid branch',
                'branch_in_id.numeric' => 'Please select a valid branch',
                'branch_in_id.different' => 'There cannot be the same branch',
            ];
        }else{
            $validateInput = [
                'branchId' => 'required|numeric',
            ];
            $errMsg = [
                'branchId.required' => 'Please select a valid branch',
                'branchId.numeric' => 'Please select a valid branch',
            ];
        }

        for($iRow=1;$iRow<=$request['coa_row_count'];$iRow++){
            if ($request['coa_id_'.$iRow]) {
                $validateCoaCode = '';
                // for ($jRow=1; $jRow<=$request['coa_row_count'];$jRow++) {
                //     if ($iRow!=$jRow){
                //         $validateCoaCode .= '|different:'.'coa_id_'.$jRow;
                //     }
                // }
                // if ($id==8){
                //     for ($jRow=1; $jRow<=$request['totalCoaRow'];$jRow++) {
                //         $validateCoaCode .= '|different:'.'coa_id_'.$jRow.'_add';
                //     }
                // }
                $validateCoaInput = [
                    'coa_id_'.$iRow => ['required','numeric',new IsCoaMaster()],
                    // 'coa_id_'.$iRow => 'required|numeric'.$validateCoaCode,
                    'order_no_'.$iRow => 'required|numeric',
                    'desc_'.$iRow => 'required',
                    'debet_or_credit_'.$iRow => 'required',
                    'coa_dtl_id_'.$iRow => 'required|numeric',
                ];
                $errCoaMsg = [
                    'coa_id_'.$iRow.'.required' => 'Please select a valid COA Code',
                    'coa_id_'.$iRow.'.numeric' => 'Please select a valid COA Code',
                    // 'coa_id_'.$iRow.'.different' => 'There cannot be the same COA code',
                ];
                $validateInput = array_merge($validateInput, $validateCoaInput);
                $errMsg = array_merge($errMsg, $errCoaMsg);
            }
        }
        if ($id==8){
            $validateInput2 = [
                'methodId' => 'required|numeric',
            ];
            $errMsg2 = [
                'methodId.required' => 'Please select a valid method',
                'methodId.numeric' => 'Please select a valid method',
            ];
            $validateInput = array_merge($validateInput, $validateInput2);
            $errMsg = array_merge($errMsg, $errMsg2);

            for($iRow=1;$iRow<=$request['totalCoaRow'];$iRow++){
                if ($request['coa_id_'.$iRow.'_add']) {
                    $validateCoaCode = '';
                    for ($jRow=1; $jRow<=$request['coa_row_count'];$jRow++) {
                        $validateCoaCode .= '|different:'.'coa_id_'.$jRow;
                    }
                    for ($jRow=1; $jRow<=$request['totalCoaRow'];$jRow++) {
                        if ($iRow!=$jRow){
                            $validateCoaCode .= '|different:'.'coa_id_'.$jRow.'_add';
                        }
                    }
                    $validateCoaInput = [
                        'coa_id_'.$iRow.'_add' => 'required|numeric'.$validateCoaCode,
                        'order_no_'.$iRow.'_add' => 'required|numeric',
                        'desc_'.$iRow.'_add' => 'required',
                        'debet_or_credit_'.$iRow.'_add' => 'required',
                        'coa_dtl_id_'.$iRow.'_add' => 'required|numeric',
                    ];
                    $errCoaMsg = [
                        'coa_id_'.$iRow.'_add.required' => 'Please select a valid COA Code',
                        'coa_id_'.$iRow.'_add.numeric' => 'Please select a valid COA Code',
                        'coa_id_'.$iRow.'_add.different' => 'There cannot be the same COA code',
                    ];
                    $validateInput = array_merge($validateInput, $validateCoaInput);
                    $errMsg = array_merge($errMsg, $errCoaMsg);
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
            for($iRow=1;$iRow<=$request['coa_row_count'];$iRow++){
                $qIsExists = Mst_automatic_journal_detail::where([
                    'id'=>$request['coa_dtl_id_'.$iRow],
                ])
                ->first();
                if($qIsExists){
                    $upd = Mst_automatic_journal_detail::where('id','=',$request['coa_dtl_id_'.$iRow])
                    ->update([
                        'auto_journal_id'=>$id,
                        'method_id'=>$request['methodId'],
                        'branch_id'=>$request['branchId'],
                        'branch_in_id'=>($id==12?$request['branch_in_id']:null),
                        'coa_code_id'=>$request['coa_id_'.$iRow],
                        'desc'=>$request['desc_'.$iRow],
                        'debet_or_credit'=>$request['debet_or_credit_'.$iRow],
                        'order_no'=>$request['order_no_'.$iRow],
                        'active'=>'Y',
                        'updated_by' => Auth::user()->id,
                    ]);
                }else{
                    $ins = Mst_automatic_journal_detail::create([
                        'auto_journal_id'=>$id,
                        'method_id'=>$request['methodId'],
                        'branch_id'=>$request['branchId'],
                        'branch_in_id'=>($id==12?$request['branch_in_id']:null),
                        'coa_code_id'=>$request['coa_id_'.$iRow],
                        'desc'=>$request['desc_'.$iRow],
                        'debet_or_credit'=>$request['debet_or_credit_'.$iRow],
                        'order_no'=>$request['order_no_'.$iRow],
                        'active'=>'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                }
            }

            // set not active
            $updNotActive = Mst_automatic_journal_detail_ext::where([
                'auto_journal_id'=>$id,
                'branch_id'=>$request['branchId'],
                'active'=>'Y',
            ])
            ->update([
                'active'=>'N',
            ]);
            for($iRow=1;$iRow<=$request['totalCoaRow'];$iRow++){
                if ($request['coa_id_'.$iRow.'_add']){
                    $qIsExists = Mst_automatic_journal_detail_ext::where([
                        'id'=>$request['coa_dtl_id_'.$iRow.'_add'],
                    ])
                    ->first();
                    if ($qIsExists){
                        $upd = Mst_automatic_journal_detail_ext::where('id','=',$request['coa_dtl_id_'.$iRow.'_add'])
                        ->update([
                            'auto_journal_id'=>$id,
                            'method_id'=>$request['methodId'],
                            'branch_id'=>$request['branchId'],
                            'coa_code_id'=>$request['coa_id_'.$iRow.'_add'],
                            'desc'=>$request['desc_'.$iRow.'_add'],
                            'debet_or_credit'=>$request['debet_or_credit_'.$iRow.'_add'],
                            'order_no'=>$request['order_no_'.$iRow.'_add'],
                            'active'=>'Y',
                            'updated_by' => Auth::user()->id,
                        ]);
                    }else{
                        $ins = Mst_automatic_journal_detail_ext::create([
                            'auto_journal_id'=>$id,
                            'method_id'=>$request['methodId'],
                            'branch_id'=>$request['branchId'],
                            'coa_code_id'=>$request['coa_id_'.$iRow.'_add'],
                            'desc'=>$request['desc_'.$iRow.'_add'],
                            'debet_or_credit'=>$request['debet_or_credit_'.$iRow.'_add'],
                            'order_no'=>$request['order_no_'.$iRow.'_add'],
                            'active'=>'Y',
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

        session()->flash('status', 'Data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder.'/'.$id.'/edit?'.
            'method_id='.$request['methodId'].
            '&branch_id='.$request['branchId'].
            '&branch_in_id='.$request['branch_in_id']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_tax_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
