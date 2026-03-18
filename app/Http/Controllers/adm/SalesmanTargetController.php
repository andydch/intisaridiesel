<?php

namespace App\Http\Controllers\adm;

use Exception;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Mst_menu_user;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Mst_salesman_target;
use App\Rules\UniqueSalesmanTarget;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch_target;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Mst_salesman_target_detail;
use Illuminate\Validation\ValidationException;

class SalesmanTargetController extends Controller
{
    protected $title = 'Salesman Target';
    protected $folder = 'salesman-target';

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

        $query = Mst_salesman_target::orderBy('created_at','DESC');
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'salesman_targets' => $query->get(),
            'salesman_targetCount' => $query->count(),
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
        $years = Mst_branch_target::select('year')
        ->orderBy('year','ASC')
        ->groupBy('year')
        ->get();
        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $salesmans = [];
        if (old('branch_id')){
            $salesmans = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
            ->select(
                'users.id as user_id',
                'users.name',
            )
            ->where('userdetails.branch_id','=',old('branch_id'))
            ->where('userdetails.is_salesman','=','Y')
            ->where('userdetails.active','=','Y')
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'branches' => $branches,
            'years' => $years,
            'salesmans' => $salesmans,
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
            'menu_id' => 78,
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
            // 'target_year' => 'required|numeric|unique:App\Models\Mst_salesman_target,year',
            'target_year' => ['required','numeric',new UniqueSalesmanTarget($request->target_year,$request->branch_id)],
            'branch_id' => 'required|numeric',
            'sales_target' => 'required|numeric|same:total_sales_target_ori',
        ];
        $errMsg = [
            'target_year.required' => 'Please select a valid year',
            'target_year.numeric' => 'Please select a valid year',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
            'target_year.unique' => 'The target year has already been taken',
            'sales_target.required' => 'Sales Target is required',
            'sales_target.numeric' => 'Sales Target must be numeric',
            'sales_target.same' => 'The sales target and total sales target must match',
        ];
        if ($request->totalRow>0) {
            for ($i=0;$i<$request->totalRow;$i++) {
                if ($request['salesman_id'.$i]) {
                    // validasi agar tidak memilih lebih dari 1 cabang yang sama
                    $salesman_id_other='';
                    if ($request->totalRow>1) {
                        for ($j=0;$j<$request->totalRow;$j++) {
                            if ($request['salesman_id'.$j]) {
                                if($j!=$i){
                                    $salesman_id_other.=','.'salesman_id'.$j;
                                }
                            }
                        }
                        if ($salesman_id_other!=''){
                            $salesman_id_other='|different:'.substr($salesman_id_other,1,strlen($salesman_id_other));
                        }
                    }
                    // validasi agar tidak memilih lebih dari 1 cabang yang sama

                    $validateShipmentInput = [
                        'salesman_id'.$i => 'required|numeric'.$salesman_id_other,
                        'sales_target_per_branch'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'salesman_id'.$i.'.required' => 'Please select a valid salesman',
                        'salesman_id'.$i.'.numeric' => 'Please select a valid salesman',
                        'salesman_id'.$i.'.different' => 'The salesman must be different.',
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

            $ins = Mst_salesman_target::create([
                'year' => $request->target_year,
                'branch_id' => $request->branch_id,
                'sales_target' => $request->sales_target,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            if ($request->totalRow>0) {
                for ($i=0;$i<$request->totalRow;$i++) {
                    if ($request['salesman_id'.$i]) {
                        $insdtl = Mst_salesman_target_detail::create([
                            'salesman_target_id' => $ins->id,
                            'salesman_id' => $request['salesman_id'.$i],
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

        $query = Mst_salesman_target::where([
            'id' => urldecode($id)
        ])
        ->first();
        if($query){
            $qDetail = Mst_salesman_target_detail::where([
                'salesman_target_id' => $query->id,
                'active' => 'Y',
            ]);

            $salesmans = [];
            if (old('branch_id')){
                $salesmans = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                ->select(
                    'users.id as user_id',
                    'users.name',
                )
                ->where('userdetails.branch_id','=',old('branch_id'))
                ->where('userdetails.is_salesman','=','Y')
                ->where('userdetails.active','=','Y')
                ->get();
            }else{
                $salesmans = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                ->select(
                    'users.id as user_id',
                    'users.name',
                )
                ->where('userdetails.branch_id','=',$query->branch_id)
                ->where('userdetails.is_salesman','=','Y')
                ->where('userdetails.active','=','Y')
                ->get();
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'salesman_target' => $query,
                'salesman_target_detail' => $qDetail->get(),
                'salesmans' => $salesmans,
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

        $query = Mst_salesman_target::where([
            'id' => urldecode($id)
        ])
        ->first();
        if($query){
            $qDetail = Mst_salesman_target_detail::where([
                'salesman_target_id' => $query->id,
                'active' => 'Y',
            ]);

            $salesmans = [];
            if (old('branch_id')){
                $salesmans = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                ->select(
                    'users.id as user_id',
                    'users.name',
                )
                ->where('userdetails.branch_id','=',old('branch_id'))
                ->where('userdetails.is_salesman','=','Y')
                ->where('userdetails.active','=','Y')
                ->get();
            }else{
                $salesmans = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                ->select(
                    'users.id as user_id',
                    'users.name',
                )
                ->where('userdetails.branch_id','=',$query->branch_id)
                ->where('userdetails.is_salesman','=','Y')
                ->where('userdetails.active','=','Y')
                ->get();
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'salesman_target' => $query,
                'salesman_target_detail' => $qDetail->get(),
                'salesmans' => $salesmans,
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
            'menu_id' => 78,
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
            // 'target_year' => 'required|numeric|unique:App\Models\Mst_salesman_target,year',
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
                if ($request['salesman_id'.$i]) {
                    // validasi agar tidak memilih lebih dari 1 cabang yang sama
                    $salesman_id_other='';
                    if ($request->totalRow>1) {
                        for ($j=0;$j<$request->totalRow;$j++) {
                            if ($request['salesman_id'.$j]) {
                                if($j!=$i){
                                    $salesman_id_other.=','.'salesman_id'.$j;
                                }
                            }
                        }
                        if ($salesman_id_other!=''){
                            $salesman_id_other='|different:'.substr($salesman_id_other,1,strlen($salesman_id_other));
                        }
                    }
                    // validasi agar tidak memilih lebih dari 1 cabang yang sama

                    $validateShipmentInput = [
                        'salesman_id'.$i => 'required|numeric'.$salesman_id_other,
                        'sales_target_per_branch'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'salesman_id'.$i.'.required' => 'Please select a valid salesman',
                        'salesman_id'.$i.'.numeric' => 'Please select a valid salesman',
                        'salesman_id'.$i.'.different' => 'The salesman must be different.',
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

            $upd = Mst_salesman_target::where('id','=',$id)
            ->update([
                'sales_target' => $request->sales_target,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            $setNotActive = Mst_salesman_target_detail::where('salesman_target_id','=',$id)
            ->update([
                'active' => 'N',
                'updated_by' => Auth::user()->id,
            ]);

            if ($request->totalRow>0) {
                for ($i=0;$i<$request->totalRow;$i++) {
                    if ($request['salesman_id'.$i]) {
                        $qDtl = Mst_salesman_target_detail::where('id','=',$request['sa_id'.$i])
                        ->first();
                        if($qDtl){
                            $upddtl = Mst_salesman_target_detail::where('id','=',$request['sa_id'.$i])
                            ->update([
                                'salesman_id' => $request['salesman_id'.$i],
                                'sales_target_per_branch' =>$request['sales_target_per_branch'.$i],
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $q = Mst_salesman_target::where('id','=',$id)
                            ->first();
                            $insdtl = Mst_salesman_target_detail::create([
                                'salesman_target_id' => $id,
                                'salesman_id' => $request['salesman_id'.$i],
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
