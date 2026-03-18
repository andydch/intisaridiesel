<?php

namespace App\Http\Controllers\adm;

use Exception;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Mst_menu_user;
use Illuminate\Http\Request;
use App\Models\Mst_branch_target;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch_target_detail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BranchTargetController extends Controller
{
    protected $title = 'Branch Target';
    protected $folder = 'branch-target';

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

        $query = Mst_branch_target::orderBy('created_at','DESC');
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'branch_targets' => $query->get(),
            'branch_targetCount' => $query->count(),
            'qCurrency' => $qCurrency,
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
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();
        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'branches' => $branches,
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
            'menu_id' => 77,
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
            'target_year' => 'required|numeric|unique:App\Models\Mst_branch_target,year',
            'sales_target' => 'required|numeric|same:total_sales_target_ori',
        ];
        $errMsg = [
            'target_year.required' => 'Please select a valid year',
            'target_year.numeric' => 'Please select a valid year',
            'target_year.unique' => 'The target year has already been taken',
            'sales_target.required' => 'Sales Target is required',
            'sales_target.numeric' => 'Sales Target must be numeric',
            'sales_target.same' => 'The sales target and total sales target must match',
        ];
        if ($request->totalRow>0) {
            for ($i=0;$i<$request->totalRow;$i++) {
                if ($request['branch_id'.$i]) {
                    // validasi agar tidak memilih lebih dari 1 cabang yang sama
                    $branch_id_other='';
                    if ($request->totalRow>1) {
                        for ($j=0;$j<$request->totalRow;$j++) {
                            if ($request['branch_id'.$j]) {
                                if($j!=$i){
                                    $branch_id_other.=','.'branch_id'.$j;
                                }
                            }
                        }
                        if ($branch_id_other!=''){
                            $branch_id_other='|different:'.substr($branch_id_other,1,strlen($branch_id_other));
                        }
                    }
                    // validasi agar tidak memilih lebih dari 1 cabang yang sama

                    $validateShipmentInput = [
                        'branch_id'.$i => 'required|numeric'.$branch_id_other,
                        'sales_target_per_branch'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'branch_id'.$i.'.required' => 'Please select a valid branch',
                        'branch_id'.$i.'.numeric' => 'Please select a valid branch',
                        'branch_id'.$i.'.different' => 'The branch must be different.',
                        'sales_target_per_branch'.$i.'.required' => 'Sales Target is required',
                        'sales_target_per_branch'.$i.'.numeric' => 'Sales Target must be numeric',
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

            $ins = Mst_branch_target::create([
                'year' => $request->target_year,
                'sales_target' => $request->sales_target,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            if ($request->totalRow>0) {
                for ($i=0;$i<$request->totalRow;$i++) {
                    if ($request['branch_id'.$i]) {
                        $insdtl = Mst_branch_target_detail::create([
                            'branch_target_id' => $ins->id,
                            'branch_id' => $request['branch_id'.$i],
                            'year_per_branch' => $request->target_year,
                            'sales_target_per_branch' =>$request['sales_target_per_branch'.$i],
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
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_global  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $query = Mst_branch_target::where([
            'id' => urldecode($id)
        ])
        ->first();
        if($query){
            $qDetail = Mst_branch_target_detail::where([
                'branch_target_id' => $query->id,
                'active' => 'Y',
            ]);

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'branch_target' => $query,
                'branch_target_detail' => $qDetail->get(),
                'qCurrency' => $qCurrency,
                'totalRow' => (old('totalRow') ? old('totalRow') : $qDetail->count()),
                'branches' => $branches,
            ];
        }
        return view('adm.'.$this->folder.'.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_global  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $query = Mst_branch_target::where([
            'id' => urldecode($id)
        ])
        ->first();
        if($query){
            $qDetail = Mst_branch_target_detail::where([
                'branch_target_id' => $query->id,
                'active' => 'Y',
            ]);

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'branch_target' => $query,
                'branch_target_detail' => $qDetail->get(),
                'qCurrency' => $qCurrency,
                'totalRow' => (old('totalRow') ? old('totalRow') : $qDetail->count()),
                'branches' => $branches,
            ];
        }

        return view('adm.'.$this->folder.'.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_global  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 77,
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
            // 'target_year' => 'required|numeric|unique:App\Models\Mst_branch_target,year',
            'sales_target' => 'required|numeric|same:total_sales_target_ori',
        ];
        $errMsg = [
            'target_year.required' => 'Please select a valid year',
            'target_year.numeric' => 'Please select a valid year',
            'target_year.unique' => 'The target year has already been taken',
            'sales_target.required' => 'Sales Target is required',
            'sales_target.numeric' => 'Sales Target must be numeric',
            'sales_target.same' => 'The sales target and total sales target must match',
        ];
        if ($request->totalRow>0) {
            for ($i=0;$i<$request->totalRow;$i++) {
                if ($request['branch_id'.$i]) {
                    // validasi agar tidak memilih lebih dari 1 cabang yang sama
                    $branch_id_other='';
                    if ($request->totalRow>1) {
                        for ($j=0;$j<$request->totalRow;$j++) {
                            if ($request['branch_id'.$j]) {
                                if($j!=$i){
                                    $branch_id_other.=','.'branch_id'.$j;
                                }
                            }
                        }
                        if ($branch_id_other!=''){
                            $branch_id_other='|different:'.substr($branch_id_other,1,strlen($branch_id_other));
                        }
                    }
                    // validasi agar tidak memilih lebih dari 1 cabang yang sama

                    $validateShipmentInput = [
                        'branch_id'.$i => 'required|numeric'.$branch_id_other,
                        'sales_target_per_branch'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'branch_id'.$i.'.required' => 'Please select a valid branch',
                        'branch_id'.$i.'.numeric' => 'Please select a valid branch',
                        'branch_id'.$i.'.different' => 'The branch must be different.',
                        'sales_target_per_branch'.$i.'.required' => 'Sales Target is required',
                        'sales_target_per_branch'.$i.'.numeric' => 'Sales Target must be numeric',
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

            $upd = Mst_branch_target::where('id','=',$id)
            ->update([
                'sales_target' => $request->sales_target,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            $setNotActive = Mst_branch_target_detail::where('branch_target_id','=',$id)
            ->update([
                'active' => 'N',
                'updated_by' => Auth::user()->id,
            ]);

            if ($request->totalRow>0) {
                for ($i=0;$i<$request->totalRow;$i++) {
                    if ($request['branch_id'.$i]) {
                        $qDtl = Mst_branch_target_detail::where('id','=',$request['bt_id'.$i])
                        ->first();
                        if($qDtl){
                            $upddtl = Mst_branch_target_detail::where('id','=',$request['bt_id'.$i])
                            ->update([
                                'branch_id' => $request['branch_id'.$i],
                                'sales_target_per_branch' =>$request['sales_target_per_branch'.$i],
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $q = Mst_branch_target::where('id','=',$id)
                            ->first();
                            $insdtl = Mst_branch_target_detail::create([
                                'branch_target_id' => $id,
                                'branch_id' => $request['branch_id'.$i],
                                'year_per_branch' => $q->year,
                                'sales_target_per_branch' =>$request['sales_target_per_branch'.$i],
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
            throw $e;

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
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_global  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_global $mst_global)
    {
        //
    }
}
