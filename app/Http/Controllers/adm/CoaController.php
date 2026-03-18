<?php

namespace App\Http\Controllers\adm;

use App\Models\Mst_coa;
use App\Models\Mst_branch;
use App\Models\Mst_menu_user;
use App\Rules\CoaCheckCode;
use App\Rules\CoaCheckLevel;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Helpers\GlobalFuncHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CoaController extends Controller
{
    protected $title = 'Chart of Account';
    protected $folder = 'coa';
    protected $minLevel = 1;
    protected $maxLevel = 5;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_coa::where([
            'coa_level' => 1,
            'active' => 'Y'
        ])
        ->orderBy('coa_code', 'ASC')
        ->orderBy('coa_level', 'ASC');

        $queryParent0 = Mst_coa::where([
            'coa_parent' => 0,
            'active' => 'Y'
        ])
        ->orderBy('coa_code', 'ASC')
        ->orderBy('coa_level', 'ASC');
        $data = [
            'coas' => $query->get(),
            'coasCount' => $query->count()+$queryParent0->count(),
            'coasParent0' => $queryParent0->get(),
            'title' => $this->title,
            'folder' => $this->folder
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
        $qCoaParent = [];
        if(old('coa_level')){
            $qCoaParent = Mst_coa::where([
                'coa_level' => (old('coa_level')>0)?old('coa_level')-1:0,
                'is_draft' => 'N',
                'active' => 'Y'
            ])
            ->orderBy('coa_name','ASC')
            ->get();
        }
        $qBranches = Mst_branch::where([
            'active'=>'Y',
        ])
        ->orderBy('name','ASC')
        ->get();
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'minLevel' => $this->minLevel,
            'maxLevel' => $this->maxLevel,
            'qCoaParent' => $qCoaParent,
            'qBranches' => $qBranches,
        ];
        return view('adm.'.$this->folder.'.create', $data);
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
            'menu_id' => 32,
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

        $coa_parent = $request->coa_parent;
        $coa_code_complete = '';
        for($i=$request->coa_level-1;$i>=1;$i--){
            $qParent = Mst_coa::where('id','=',$coa_parent)
            ->first();
            if($qParent){
                $coa_code_complete = $qParent->coa_code.$coa_code_complete;
                $coa_parent = $qParent->coa_parent;
            }
        }
        $coa_code_complete = $coa_code_complete.$request->coa_code;

        Validator::make($request->all(), [
            'coa_level' => 'required|numeric',
            'coa_code' => ['required','regex:/^[0-9]+$/'],
            'coa_code' => ['required',new CoaCheckCode($coa_code_complete)],
            'coa_name' => 'required|max:1000',
            'coa_parent' => [new CoaCheckLevel($request->coa_level)],
            'beginning_balance_amount' => [new NumericCustom('Beginning Balance Amount'),'nullable'],
        ])
        ->validate();

        $is_master_coa = 'N';
        if ($request->is_master_coa == 'on') {
            $is_master_coa = 'Y';
        }
        $is_balance_sheet = 'N';
        if ($request->is_balance_sheet == 'on') {
            $is_balance_sheet = 'Y';
        }
        $is_profit_loss = 'N';
        if ($request->is_profit_loss == 'on') {
            $is_profit_loss = 'Y';
        }

        $draft_at = null;
        if($request->is_draft=='Y'){
            $draft_at = now();
        }

        $beginning_balance_date = [];
        if($request->beginning_balance_date!=''){
            $beginning_balance_date = explode("/",$request->beginning_balance_date);
        }

        $ins = Mst_coa::create([
            'coa_level' => $request->coa_level,
            'coa_code' => $request->coa_code,
            'coa_code_complete' => $coa_code_complete,
            'coa_name'=> $request->coa_name,
            'coa_parent' => $request->coa_parent,
            'branch_id' => ($is_master_coa=='Y'?null:$request->branch_id),
            'local' => ($is_master_coa=='Y'?null:$request->local_id),
            'beginning_balance_date' => ($request->beginning_balance_date!=''?$beginning_balance_date[2].'-'.$beginning_balance_date[1].'-'.$beginning_balance_date[0]:null),
            'beginning_balance_amount' => ($request->beginning_balance_amount == '' ? null : GlobalFuncHelper::moneyValidate($request->beginning_balance_amount)),
            'is_master_coa' => $is_master_coa,
            'is_balance_sheet' => $is_balance_sheet,
            'is_profit_loss' => $is_profit_loss,
            'is_draft' => $request->is_draft,
            'draft_at' => $draft_at,
            'draft_to_created_at' => null,
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_coa  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $query = Mst_coa::where([
            'id' => $id
        ])
        ->first();
        $data = [
            'coas' => $query,
            'title' => $this->title,
            'folder' => $this->folder,
            'minLevel' => $this->minLevel,
            'maxLevel' => $this->maxLevel
        ];
        return view('adm.'.$this->folder.'.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_coa  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = Mst_coa::where([
            'id' => $id
        ])
        ->first();
        if(old('coa_level')){
            $qCoaParent = Mst_coa::where('id','<>',$id)
            ->where([
                'coa_level' => (old('coa_level')>0)?old('coa_level')-1:0,
                'is_draft' => 'N',
                'active' => 'Y'
            ])
            ->orderBy('coa_name','ASC')
            ->get();
        }else{
            $qCoaParent = Mst_coa::where([
                'coa_level' => $query->coa_level-1,
                'is_draft' => 'N',
                'active' => 'Y'
            ])
            ->orderBy('coa_name','ASC')
            ->get();
        }
        $qBranches = Mst_branch::where([
            'active'=>'Y',
        ])
        ->orderBy('name','ASC')
        ->get();
        $data = [
            'coas' => $query,
            'title' => $this->title,
            'folder' => $this->folder,
            'minLevel' => $this->minLevel,
            'maxLevel' => $this->maxLevel,
            'qCoaParent' => $qCoaParent,
            'qBranches' => $qBranches,
        ];
        return view('adm.'.$this->folder.'.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_coa  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 32,
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
            'coa_level' => 'required|numeric',
            'coa_code' => ['required','regex:/^[0-9]+$/'],
            // 'coa_code' => ['required','regex:/^[0-9]+$/',new CoaCheckCode($request->coa_level,$id)],
            // 'coa_code' => 'required|regex:/^[0-9]+$/|unique:mst_coas,coa_code',
            'coa_name' => 'required|max:1000',
            'coa_parent' => [new CoaCheckLevel($request->coa_level)],
            // 'local_id' => 'required|size:1',
            'beginning_balance_amount' => [new NumericCustom('Beginning Balance Amount'),'nullable'],
        ])
        ->validate();

        $is_master_coa = 'N';
        if ($request->is_master_coa == 'on') {
            $is_master_coa = 'Y';
        }
        $is_balance_sheet = 'N';
        if ($request->is_balance_sheet == 'on') {
            $is_balance_sheet = 'Y';
        }
        $is_profit_loss = 'N';
        if ($request->is_profit_loss == 'on') {
            $is_profit_loss = 'Y';
        }

        $coa_parent = $request->coa_parent;
        $coa_code_complete = '';
        for($i=$request->coa_level-1;$i>=1;$i--){
            $qParent = Mst_coa::where('id','=',$coa_parent)
            ->first();
            if($qParent){
                $coa_code_complete = $qParent->coa_code.$coa_code_complete;
                $coa_parent = $qParent->coa_parent;
            }
        }
        $coa_code_complete = $coa_code_complete.$request->coa_code;

        $q = Mst_coa::where('id', '=', $id)
        ->first();
        $draft_to_created_at = null;
        $is_draft = $request->is_draft;
        if($q->is_draft=='Y' && $request->is_draft=='N'){
            $draft_to_created_at = now();
        }

        $beginning_balance_date = [];
        if($request->beginning_balance_date!=''){
            $beginning_balance_date = explode("/",$request->beginning_balance_date);
        }

        $upd = Mst_coa::where('id', '=', $id)
        ->update([
            'coa_level' => $request->coa_level,
            'coa_code' => $request->coa_code,
            'coa_code_complete' => $coa_code_complete,
            'coa_name'=> $request->coa_name,
            'coa_parent' => $request->coa_parent,
            'branch_id' => ($is_master_coa=='Y'?null:$request->branch_id),
            'local' => ($is_master_coa=='Y'?null:$request->local_id),
            'beginning_balance_date' => ($request->beginning_balance_date!=''?$beginning_balance_date[2].'-'.$beginning_balance_date[1].'-'.$beginning_balance_date[0]:null),
            'beginning_balance_amount' => ($request->beginning_balance_amount == '' ? null : GlobalFuncHelper::moneyValidate($request->beginning_balance_amount)),
            'is_master_coa' => $is_master_coa,
            'is_balance_sheet' => $is_balance_sheet,
            'is_profit_loss' => $is_profit_loss,
            'is_draft' => $is_draft,
            'draft_to_created_at' => $draft_to_created_at,
            'active' => 'Y',
            'updated_by' => Auth::user()->id,
        ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_coa  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_coa $mst_global)
    {
        //
    }
}
