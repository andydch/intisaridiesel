<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Rules\ValidateQty;
use App\Models\Tx_qty_part;
use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use App\Models\Tx_stock_assembly;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helpers\OutstandingSoSjHelper;
use App\Models\Tx_stock_assembly_part;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StockAssemblyServerSideController extends Controller
{
    protected $title = 'Stock Assembly';
    protected $folder = 'stock-assembly';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        if ($request->ajax()){
            $query = Tx_stock_assembly::leftJoin('userdetails AS usr','tx_stock_assemblys.created_by','=','usr.user_id')
            ->select(
                'tx_stock_assemblys.id as tx_id',
                'tx_stock_assemblys.stock_assembly_no',
                'tx_stock_assemblys.stock_assembly_date',
                'tx_stock_assemblys.active as sa_active',
                'tx_stock_assemblys.created_by as createdby',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
            )
            ->where('tx_stock_assemblys.active','=','Y')
            ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_stock_assemblys.stock_assembly_date', 'DESC')
            ->orderBy('tx_stock_assemblys.created_at', 'DESC');

            return DataTables::of($query)
            ->filterColumn('stock_assembly_date', function($q, $keyword) {
                $q->whereRaw('DATE_FORMAT(tx_stock_assemblys.stock_assembly_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('stock_assembly_date', function ($query) {
                return date_format(date_create($query->stock_assembly_date),"d/m/Y");
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y')){
                    if ($query->sa_active=='Y' && strpos($query->stock_assembly_no,'Draft')>0){
                        $links .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-assembly/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |';
                    }
                    $links .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-assembly/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                    if ($query->sa_active=='Y' && strpos($query->stock_assembly_no,'Draft')==0) {
                        $links .= '| <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-assembly-print/'.$query->tx_id).'" target="_new" style="text-decoration: underline;">Print</a> |
                            <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-assembly-print/'.$query->tx_id).'" target="_new" style="text-decoration: underline;">Download</a>';
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-assembly/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
            })
            ->rawColumns(['stock_assembly_date','action'])
            ->toJson();
        }

        $data = [
            // 'stocks' => $query->get(),
            // 'stocksCount' => $query->count(),
            'title' => $this->title,
            'folder' => $this->folder,
            'is_director_now' => $userLogin->is_director,
            'is_branch_head_now' => $userLogin->is_branch_head,
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
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        // $parts = Mst_part::where([
        //     'active' => 'Y'
        // ])
        // ->orderBy('part_number', 'ASC')
        // ->get();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            // 'qParts' => $parts,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'branch_id' => (old('branch_id')?old('branch_id'):$userLogin->branch_id),
            'userLogin' => $userLogin,
            'qCurrency' => $qCurrency,
            'branches' => $branches,
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
            'menu_id' => 45,
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

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $validateInput = [
            'part_id' => 'required|numeric',
            'to_be_part_qty' => 'required|numeric',
        ];
        $errMsg = [
            'part_id.numeric' => 'Please select a valid part',
            'to_be_part_qty.required' => 'The qty field is required',
            'to_be_part_qty.numeric' => 'The qty field is must be numeric',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_no_'.$i]) {
                    $validatePartInput = [
                        'part_no_'.$i => 'required|numeric|different:part_id',
                        'qty'.$i => ['required','numeric',new ValidateQty($request['part_no_'.$i],(isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))],
                    ];
                    $errPartMsg = [
                        'part_no_'.$i.'.numeric' => 'Please select a valid part',
                        'part_no_'.$i.'.different' => 'This part number and To Be Part Number must be different.',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                    ];
                    $validateInput = array_merge($validateInput, $validatePartInput);
                    $errMsg = array_merge($errMsg, $errPartMsg);
                }
            }
        }
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // --- cek apakah part detail kosong
        $iRow = 0;
        for ($i = 0; $i < $request->totalRow; $i++) {
            if ($request['part_no_'.$i]) {
                $iRow += 1;
            }
        }
        if($iRow==0){
            return back()->withErrors([
                'totalRow' => 'Please select the parts to be assembled.',
            ])
            ->onlyInput('totalRow');
        }
        // ---

        // Start transaction!
        DB::beginTransaction();

        try {

            $draft_at = null;
            $draft_to_created_at = null;
            $identityName = 'tx_stock_assemblys-draft';
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
                $order_no = env('P_STOCK_ASSEMBLY').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_stock_assemblys';
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
                $order_no = env('P_STOCK_ASSEMBLY').date('y').'-'.$zero.strval($newInc);
            }

            $stock_assembly_date = date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string(ENV("WAKTU_ID")." HOURS")),"Y-m-d");

            $ins = Tx_stock_assembly::create([
                'stock_assembly_no' => $order_no,
                'stock_assembly_date' => $stock_assembly_date,
                'part_id' => $request->part_id,
                'qty' => $request->to_be_part_qty,
                'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                'final_cost' => 0,
                'avg_cost' => 0,
                'remark' => $request->remark_txt,
                'is_draft' => $request->is_draft,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            if ($request->totalRow > 0) {
                $totalAVG = 0;
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_no_'.$i]) {
                        $part = Mst_part::where('id', '=', $request['part_no_'.$i])
                        ->first();

                        $insPart = Tx_stock_assembly_part::create([
                            'stock_assembly_id' => $maxId,
                            'part_id' => $request['part_no_'.$i],
                            'qty' => $request['qty'.$i],
                            'final_cost' => $part->final_cost,   // ambil dari master part data terakhir
                            'avg_cost' => $part->avg_cost,
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);

                        $totalAVG += ($request['qty'.$i]*$part->avg_cost);
                    }
                }

                if(!strpos($order_no,"Draft")){
                    // outstanding SO/Sj qty
                    $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($request->part_id);

                    // OH
                    $sumOH = Tx_qty_part::where('part_id', '=', $request->part_id)
                    ->whereIn('branch_id', function($q) {
                        $q->select('id')
                        ->from('mst_branches')
                        ->where('active', '=', 'Y');
                    })
                    ->sum('qty');
                    $freeOH = ($sumOH-$qtySoSj>0?$sumOH-$qtySoSj:0);

                    // avg cost awal dan qty awal
                    $part = Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                    ->select(
                        'tx_qty_parts.qty',
                        'mst_parts.avg_cost'
                    )
                    ->where('mst_parts.id', '=', $request->part_id)
                    ->where('tx_qty_parts.branch_id', '=', (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))
                    ->first();
                    if($part){
                        // update avg cost & qty terbaru utk part hasil rakitan
                        $updPart = Mst_part::where('id','=',$request->part_id)
                        ->update([
                            'avg_cost' => (($freeOH*$part->avg_cost)+$totalAVG)/($freeOH+$request->to_be_part_qty),
                            // 'avg_cost' => (($part->qty*$part->avg_cost)+$totalAVG)/($part->qty+$request->to_be_part_qty),
                            'final_cost' => $totalAVG,
                            'updated_by' => Auth::user()->id,
                        ]);

                        $qQty = Tx_qty_part::where([
                            'part_id' => $request->part_id,
                            'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                        ])
                        ->first();
                        if($qQty){
                            $updQty = Tx_qty_part::where('part_id', '=', $request->part_id)
                            ->where('branch_id','=',(isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))
                            ->update([
                                'qty' => $part->qty+$request->to_be_part_qty,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $insQty = Tx_qty_part::create([
                                'part_id' => $request->part_id,
                                'qty' => $request->to_be_part_qty,
                                'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                                'updated_by' => Auth::user()->id,
                                'created_by' => Auth::user()->id,
                            ]);
                        }

                        $updAsm = Tx_stock_assembly::where('id','=',$maxId)
                        ->update([
                            'final_cost' => $totalAVG,
                            'avg_cost' => (($freeOH*$part->avg_cost)+$totalAVG)/($freeOH+$request->to_be_part_qty),
                            // 'avg_cost' => (($part->qty*$part->avg_cost)+$totalAVG)/($part->qty+$request->to_be_part_qty),
                            'updated_by' => Auth::user()->id,
                        ]);
                    }else{
                        // update avg cost & qty terbaru utk part hasil rakitan
                        $updPart = Mst_part::where('id','=',$request->part_id)
                        ->update([
                            'avg_cost' => ($totalAVG)/$request->to_be_part_qty,
                            'final_cost' => $totalAVG,
                            'updated_by' => Auth::user()->id,
                        ]);

                        $qQty = Tx_qty_part::where([
                            'part_id' => $request->part_id,
                            'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                        ])
                        ->first();
                        if($qQty){
                            $updQty = Tx_qty_part::where('part_id', '=', $request->part_id)
                            ->where('branch_id','=',(isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))
                            ->update([
                                'qty' => $request->to_be_part_qty,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $insQty = Tx_qty_part::create([
                                'part_id' => $request->part_id,
                                'qty' => $request->to_be_part_qty,
                                'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                                'updated_by' => Auth::user()->id,
                                'created_by' => Auth::user()->id,
                            ]);
                        }

                        $updAsm = Tx_stock_assembly::where('id','=',$maxId)
                        ->update([
                            'final_cost' => $totalAVG,
                            'avg_cost' => $totalAVG/$request->to_be_part_qty,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
                    // update qty utk part yg dirakit
                    for ($i = 0; $i < $request->totalRow; $i++) {
                        if ($request['part_no_'.$i]) {
                            $partAsm = Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                            ->select(
                                'tx_qty_parts.qty',
                                'mst_parts.avg_cost'
                            )
                            ->where('mst_parts.id', '=', $request['part_no_'.$i])
                            ->where('tx_qty_parts.branch_id', '=', (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))
                            ->first();
                            if($partAsm){
                                $updQty = Tx_qty_part::where('part_id', '=', $request['part_no_'.$i])
                                ->where('branch_id','=',(isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))
                                ->update([
                                    'qty' => $partAsm->qty-$request['qty'.$i],
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }
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

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_stock_assembly  $tx_sales_order
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

        $query = Tx_stock_assembly::where('id', '=', $id)->first();
        if ($query) {
            $parts = Mst_part::where([
                'active' => 'Y'
            ])
            ->orderBy('part_name', 'ASC')
            ->get();

            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $queryPart = Tx_stock_assembly_part::where([
                'stock_assembly_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $queryPartCount = Tx_stock_assembly_part::where([
                'stock_assembly_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'qParts' => $parts,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPartCount),
                'branch_id' => $query->branch_id,
                'stockAsm' => $query,
                'stockAsmPart' => $queryPart,
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
     * @param  \App\Models\Tx_stock_assembly  $tx_sales_order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $query = Tx_stock_assembly::where('id', '=', $id)->first();
        if ($query) {
            // $parts = Mst_part::where([
            //     'active' => 'Y'
            // ])
            // ->orderBy('part_number', 'ASC')
            // ->get();

            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $queryPart = Tx_stock_assembly_part::where([
                'stock_assembly_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();

            $queryPartCount = Tx_stock_assembly_part::where([
                'stock_assembly_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                // 'qParts' => $parts,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPartCount),
                'branch_id' => (old('branch_id')?old('branch_id'):$query->branch_id),
                'stockAsm' => $query,
                'stockAsmPart' => $queryPart,
                'qCurrency' => $qCurrency,
                'branches' => $branches,
                'userLogin' => $userLogin,
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
     * @param  \App\Models\Tx_stock_assembly  $tx_sales_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 45,
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
        
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $validateInput = [
            'part_id' => 'required|numeric',
            'to_be_part_qty' => 'required|numeric',
        ];
        $errMsg = [
            'part_id.numeric' => 'Please select a valid part',
            'to_be_part_qty.required' => 'The qty field is required',
            'to_be_part_qty.numeric' => 'The qty field is must be numeric',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_no_'.$i]) {
                    $validatePartInput = [
                        'part_no_'.$i => 'required|numeric|different:part_id',
                        'qty'.$i => ['required','numeric',new ValidateQty($request['part_no_'.$i],(isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))],
                    ];
                    $errPartMsg = [
                        'part_no_'.$i.'.numeric' => 'Please select a valid part',
                        'part_no_'.$i.'.different' => 'This part number and To Be Part Number must be different.',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                    ];
                    $validateInput = array_merge($validateInput, $validatePartInput);
                    $errMsg = array_merge($errMsg, $errPartMsg);
                }
            }
        }
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // --- cek apakah part detail kosong
        $iRow = 0;
        for ($i = 0; $i < $request->totalRow; $i++) {
            if ($request['part_no_'.$i]) {
                $iRow += 1;
            }
        }
        if($iRow==0){
            return back()->withErrors([
                'totalRow' => 'Please select the parts to be assembled.',
            ])->onlyInput('totalRow');
        }
        // ---

        // Start transaction!
        DB::beginTransaction();

        try {
            $stock_assembly_date = date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string(ENV("WAKTU_ID")." HOURS")),"Y-m-d");

            $order_no = '';
            $draft = false;
            $stocks = Tx_stock_assembly::where('id', '=', $id)
            ->where('stock_assembly_no','LIKE','%Draft%')
            ->first();
            if($stocks){
                // looking for draft order no
                $draft = true;
                $order_no = $stocks->stock_assembly_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_stock_assemblys';
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
                $order_no = env('P_STOCK_ASSEMBLY').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_stock_assembly::where('id', '=', $id)
                ->update([
                    'stock_assembly_no' => $order_no,
                    'stock_assembly_date' => $stock_assembly_date,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_stock_assembly::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $ins = Tx_stock_assembly::where('id','=',$id)
            ->update([
                'part_id' => $request->part_id,
                'qty' => $request->to_be_part_qty,
                'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                'final_cost' => 0,
                'avg_cost' => 0,
                'remark' => $request->remark_txt,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            if ($request->totalRow > 0) {
                // netralkan part yang dihapus
                $updPart = Tx_stock_assembly_part::where('stock_assembly_id','=',$id)
                ->update([
                    'active' => 'N',
                    'updated_by' => Auth::user()->id,
                ]);

                $totalAVG = 0;
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_no_'.$i]) {
                        $part = Mst_part::where('id', '=', $request['part_no_'.$i])
                        ->first();

                        $qAsmPart = Tx_stock_assembly_part::where('id','=',$request['asmPartId'.$i])
                        ->first();
                        if($qAsmPart){
                            $updPart = Tx_stock_assembly_part::where('id','=',$request['asmPartId'.$i])
                            ->update([
                                'stock_assembly_id' => $id,
                                'part_id' => $request['part_no_'.$i],
                                'qty' => $request['qty'.$i],
                                'final_cost' => $part->final_cost,   // ambil dari master part data terakhir
                                'avg_cost' => $part->avg_cost,
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $insPart = Tx_stock_assembly_part::create([
                                'stock_assembly_id' => $id,
                                'part_id' => $request['part_no_'.$i],
                                'qty' => $request['qty'.$i],
                                'final_cost' => $part->final_cost,   // ambil dari master part data terakhir
                                'avg_cost' => $part->avg_cost,
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }

                        $totalAVG += ($request['qty'.$i]*$part->avg_cost);
                    }
                }

                if(!strpos($order_no,"Draft")){
                    // outstanding SO/Sj qty
                    $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($request->part_id);

                    // OH
                    $sumOH = Tx_qty_part::where('part_id', '=', $request->part_id)
                    ->whereIn('branch_id', function($q) {
                        $q->select('id')
                        ->from('mst_branches')
                        ->where('active', '=', 'Y');
                    })
                    ->sum('qty');
                    $freeOH = ($sumOH-$qtySoSj>0?$sumOH-$qtySoSj:0);

                    // avg cost awal dan qty awal
                    $part = Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                    ->select(
                        'tx_qty_parts.qty',
                        'mst_parts.avg_cost'
                    )
                    ->where('mst_parts.id', '=', $request->part_id)
                    ->where('tx_qty_parts.branch_id', '=', $userLogin->branch_id)
                    ->first();
                    if($part){
                        // update avg cost & qty terbaru utk part hasil rakitan
                        $updPart = Mst_part::where('id','=',$request->part_id)
                        ->update([
                            'avg_cost' => (($freeOH*$part->avg_cost)+$totalAVG)/($freeOH+$request->to_be_part_qty),
                            // 'avg_cost' => (($part->qty*$part->avg_cost)+$totalAVG)/($part->qty+$request->to_be_part_qty),
                            'final_cost' => $totalAVG,
                            'updated_by' => Auth::user()->id,
                        ]);

                        $qQty = Tx_qty_part::where([
                            'part_id' => $request->part_id,
                            'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                        ])
                        ->first();
                        if($qQty){
                            $updQty = Tx_qty_part::where('part_id', '=', $request->part_id)
                            ->where('branch_id','=',(isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))
                            ->update([
                                'qty' => $part->qty+$request->to_be_part_qty,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $insQty = Tx_qty_part::create([
                                'part_id' => $request->part_id,
                                'qty' => $request->to_be_part_qty,
                                'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                                'updated_by' => Auth::user()->id,
                                'created_by' => Auth::user()->id,
                            ]);
                        }

                        $updAsm = Tx_stock_assembly::where('id','=',$id)
                        ->update([
                            'final_cost' => $totalAVG,
                            'avg_cost' => (($freeOH*$part->avg_cost)+$totalAVG)/($freeOH+$request->to_be_part_qty),
                            // 'avg_cost' => (($part->qty*$part->avg_cost)+$totalAVG)/($part->qty+$request->to_be_part_qty),
                            'updated_by' => Auth::user()->id,
                        ]);
                    }else{
                        // update avg cost & qty terbaru utk part hasil rakitan
                        $updPart = Mst_part::where('id','=',$request->part_id)
                        ->update([
                            'avg_cost' => ($totalAVG)/$request->to_be_part_qty,
                            'final_cost' => $totalAVG,
                            'updated_by' => Auth::user()->id,
                        ]);

                        $qQty = Tx_qty_part::where([
                            'part_id' => $request->part_id,
                            'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id)
                        ])
                        ->first();
                        if($qQty){
                            $updQty = Tx_qty_part::where('part_id', '=', $request->part_id)
                            ->where('branch_id','=',(isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))
                            ->update([
                                'qty' => $request->to_be_part_qty,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $insQty = Tx_qty_part::create([
                                'part_id' => $request->part_id,
                                'qty' => $request->to_be_part_qty,
                                'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                                'updated_by' => Auth::user()->id,
                                'created_by' => Auth::user()->id,
                            ]);
                        }

                        $updAsm = Tx_stock_assembly::where('id','=',$id)
                        ->update([
                            'final_cost' => $totalAVG,
                            'avg_cost' => $totalAVG/$request->to_be_part_qty,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
                    // update qty utk part yg dirakit
                    for ($i = 0; $i < $request->totalRow; $i++) {
                        if ($request['part_no_'.$i]) {
                            $partAsm = Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                            ->select(
                                'tx_qty_parts.qty',
                                'mst_parts.avg_cost'
                            )
                            ->where('mst_parts.id', '=', $request['part_no_'.$i])
                            ->where('tx_qty_parts.branch_id', '=', (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))
                            ->first();
                            if($partAsm){
                                $updQty = Tx_qty_part::where('part_id', '=', $request['part_no_'.$i])
                                ->where('branch_id','=',(isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))
                                ->update([
                                    'qty' => $partAsm->qty-$request['qty'.$i],
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }
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
     * @param  \App\Models\Tx_stock_assembly  $tx_sales_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_stock_assembly $tx_sales_order)
    {
        //
    }
}
