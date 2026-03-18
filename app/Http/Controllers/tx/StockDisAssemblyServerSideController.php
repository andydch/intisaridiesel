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
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use App\Helpers\GlobalFuncHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tx_stock_disassembly;
use Illuminate\Support\Facades\Auth;
use App\Helpers\OutstandingSoSjHelper;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Tx_stock_disassembly_part;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StockDisAssemblyServerSideController extends Controller
{
    protected $title = 'Stock Disassembly';
    protected $folder = 'stock-disassembly';

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
            $query = Tx_stock_disassembly::leftJoin('userdetails AS usr','tx_stock_disassemblies.created_by','=','usr.user_id')
            ->select(
                'tx_stock_disassemblies.id as tx_id',
                'tx_stock_disassemblies.stock_disassembly_no',
                'tx_stock_disassemblies.stock_disassembly_date',
                'tx_stock_disassemblies.active as sa_active',
                'tx_stock_disassemblies.created_by as createdby',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
            )
            ->where('tx_stock_disassemblies.active','=','Y')
            ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_stock_disassemblies.stock_disassembly_date', 'DESC')
            ->orderBy('tx_stock_disassemblies.created_at', 'DESC');

            return DataTables::of($query)
            ->filterColumn('stock_disassembly_date', function($q, $keyword) {
                $q->whereRaw('DATE_FORMAT(tx_stock_disassemblies.stock_disassembly_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('stock_disassembly_date', function ($query) {
                return date_format(date_create($query->stock_disassembly_date),"d/m/Y");
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y')){
                    if ($query->sa_active=='Y' && strpos($query->stock_disassembly_no,'Draft')>0){
                        $links .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-disassembly/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |';
                    }
                    $links .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-disassembly/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                    if ($query->sa_active=='Y' && strpos($query->stock_disassembly_no,'Draft')==0) {
                        $links .= '| <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-disassembly-print/'.$query->tx_id).'" target="_new" style="text-decoration: underline;">Print</a> |
                            <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-disassembly-print/'.$query->tx_id).'" target="_new" style="text-decoration: underline;">Download</a>';
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-disassembly/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
            })
            ->rawColumns(['stock_disassembly_date','action'])
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

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        // $parts = Mst_part::where([
        //     'active' => 'Y'
        // ])
        // ->orderBy('part_number', 'ASC')
        // ->get();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $oldParts = [];
        if(old('part_id')){
            $oldParts = Mst_part::where([
                'id' => old('part_id'),
                'active' => 'Y'
            ])
            ->first();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            // 'qParts' => $parts,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'branch_id' => (old('branch_id')?old('branch_id'):$userLogin->branch_id),
            'userLogin' => $userLogin,
            'oldParts' => $oldParts,
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
            'menu_id' => 46,
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
            'disasm_part_qty' => ['required','numeric',new ValidateQty($request->part_id,(isset($request->branch_id)?$request->branch_id:$userLogin->branch_id))],
            'avg_cost_part_to_be_disassembly_val' => 'same:total_cost_val',
        ];
        $errMsg = [
            'part_id.numeric' => 'Please select a valid part',
            'disasm_part_qty.required' => 'The qty field is required',
            'disasm_part_qty.numeric' => 'The qty field is must be numeric',
            'avg_cost_part_to_be_disassembly_val.same' => 'AVG Cost and Total Cost must match',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i<$request->totalRow; $i++) {
                if ($request['part_no_'.$i]) {
                    $validatePartInput = [
                        'part_no_'.$i => 'required|numeric|different:part_id',
                        'qty'.$i => 'required|numeric',
                        'new_cost'.$i => ['required',new NumericCustom('Cost')],
                        // 'new_cost'.$i => 'required|numeric',
                    ];
                    $errPartMsg = [
                        'part_no_'.$i.'.numeric' => 'Please select a valid part',
                        'part_no_'.$i.'.different' => 'This To Be Part number and To Be Disassembled Part Number must be different.',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                        'new_cost'.$i.'.required' => 'The cost field is required',
                        'new_cost'.$i.'.numeric' => 'The cost field is must be numeric',
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
        for ($i = 0; $i<$request->totalRow; $i++) {
            if ($request['part_no_'.$i]) {
                $iRow += 1;
            }
        }
        if($iRow==0){
            return back()->withErrors([
                'totalRow' => 'Please select the parts to be disassembled.',
            ])->onlyInput('totalRow');
        }
        // ---

        // Start transaction!
        DB::beginTransaction();

        try {

            $draft_at = null;
            $draft_to_created_at = null;
            $identityName = 'tx_stock_disassemblies-draft';
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
                $stock_no = env('P_STOCK_DISASSEMBLY').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_stock_disassemblies';
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
                for ($i = 0; $i<(5 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $stock_no = env('P_STOCK_DISASSEMBLY').date('y').'-'.$zero.strval($newInc);
            }

            $mst_parts = Mst_part::where('id','=',$request->part_id)
            ->first();
            $stock_disassembly_date = date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string(ENV("WAKTU_ID")." HOURS")),"Y-m-d");

            $ins = Tx_stock_disassembly::create([
                'stock_disassembly_no' => $stock_no,
                'stock_disassembly_date' => $stock_disassembly_date,
                'part_id' => $request->part_id,
                'qty' => $request->disasm_part_qty,
                'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                'avg_cost' => GlobalFuncHelper::moneyValidate($mst_parts->avg_cost),
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
                for ($i = 0; $i<$request->totalRow; $i++) {
                    $avgCost = 0;
                    if ($request['part_no_'.$i]) {
                        $parts = Mst_part::leftJoin('tx_qty_parts AS txQty','mst_parts.id','=','txQty.part_id')
                        ->select(
                            'mst_parts.*',
                            'txQty.qty',
                        )
                        ->addSelect([
                            'qty_total' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                            ->limit(1)
                        ])
                        ->where([
                            'mst_parts.id' => $request['part_no_'.$i],
                            'txQty.branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                        ])
                        ->first();
                        if($parts){
                            // outstanding SO/Sj qty
                            $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($request['part_no_'.$i]);
                            $freeOH = ($parts->qty_total-$qtySoSj>0?$parts->qty_total-$qtySoSj:0);

                            $newCost = GlobalFuncHelper::moneyValidate($request['new_cost'.$i]);
                            $avgCost = (($parts->avg_cost*$freeOH)+(($newCost*$request['qty'.$i])))/($freeOH+$request['qty'.$i]);
                            // $avgCost = (($parts->avg_cost*$parts->qty_total)+(($newCost*$request['qty'.$i])))/($parts->qty_total+$request['qty'.$i]);

                            $insPart = Tx_stock_disassembly_part::create([
                                'stock_disassembly_id' => $maxId,
                                'part_id' => $request['part_no_'.$i],
                                'qty' => $request['qty'.$i],
                                'cost' => $newCost,
                                'final_cost' => ($newCost*$request['qty'.$i]),
                                'avg_cost' => $avgCost,
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }
                    }
                }

                if(!strpos($stock_no,"Draft")){
                    $parts = Mst_part::leftJoin('tx_qty_parts AS txQty','mst_parts.id','=','txQty.part_id')
                    ->select(
                        'mst_parts.*',
                        'txQty.qty'
                    )
                    ->where([
                        'mst_parts.id' => $request->part_id,
                        'txQty.branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                    ])
                    ->first();
                    if($parts){
                        // update avg_cost di table disassemby
                        $updDisAsm = Tx_stock_disassembly::where('id','=',$maxId)
                        ->update([
                            'avg_cost' => $parts->avg_cost,
                            'updated_by' => Auth::user()->id,
                        ]);

                        //update qty setelah dikurangi part yang dibongkar (disassembly)
                        $updQty = Tx_qty_part::where([
                            'part_id' => $request->part_id,
                            'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                        ])
                        ->update([
                            'qty' => (int)$parts->qty-(int)$request->disasm_part_qty,
                            'updated_by' => Auth::user()->id,
                        ]);

                        // update qty, avg cost & final cost setelah ditambah part hasil bongkar
                        for ($i = 0; $i<$request->totalRow; $i++) {
                            $avgCost = 0;
                            if ($request['part_no_'.$i]) {
                                $yieldParts = Mst_part::leftJoin('tx_qty_parts AS txQty','mst_parts.id','=','txQty.part_id')
                                ->select(
                                    'mst_parts.*',
                                    'txQty.qty',
                                )
                                ->addSelect([
                                    'qty_total' => Tx_qty_part::selectRaw('SUM(qty)')
                                    ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                                    ->limit(1)
                                ])
                                ->where([
                                    'mst_parts.id' => $request['part_no_'.$i],
                                    'txQty.branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                                ])
                                ->first();
                                if($yieldParts){
                                    // update qty
                                    $updQty = Tx_qty_part::where([
                                        'part_id' => $request['part_no_'.$i],
                                        'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                                    ])
                                    ->update([
                                        'qty' => (int)$yieldParts->qty+(int)$request['qty'.$i],
                                        'updated_by' => Auth::user()->id,
                                    ]);

                                    // outstanding SO/Sj qty
                                    $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($request['part_no_'.$i]);
                                    $freeOH = ($yieldParts->qty_total-$qtySoSj>0?$yieldParts->qty_total-$qtySoSj:0);

                                    //update avg cost & final cost
                                    $newCost = GlobalFuncHelper::moneyValidate($request['new_cost'.$i]);
                                    $avgCost = (($yieldParts->avg_cost*$freeOH)+(($newCost*$request['qty'.$i])))/($freeOH+$request['qty'.$i]);
                                    // $avgCost = (($yieldParts->avg_cost*$yieldParts->qty_total)+(($newCost*$request['qty'.$i])))/($yieldParts->qty_total+$request['qty'.$i]);
                                    $updMstPart = Mst_part::where('id','=',$request['part_no_'.$i])
                                    ->update([
                                        'avg_cost' => $avgCost,
                                        'final_cost' => $newCost*$request['qty'.$i],
                                        'updated_by' => Auth::user()->id,
                                    ]);
                                }
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
     * @param  \App\Models\Tx_stock_disassembly  $tx_sales_order
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

        $query = Tx_stock_disassembly::where('id', '=', $id)
        ->first();
        if ($query) {
            $parts = Mst_part::where([
                'active' => 'Y'
            ])
            ->orderBy('part_name', 'ASC')
            ->get();

            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $oldParts = [];
            if(old('part_id')){
                $oldParts = Mst_part::where([
                    'id' => old('part_id'),
                    'active' => 'Y'
                ])
                ->first();
            }

            $queryPart = Tx_stock_disassembly_part::where([
                'stock_disassembly_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $queryPartCount = Tx_stock_disassembly_part::where([
                'stock_disassembly_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'qParts' => $parts,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPartCount),
                'branch_id' => $query->branch_id,
                'userLogin' => $userLogin,
                'stockDisAsm' => $query,
                'stockDisAsmPart' => $queryPart,
                'oldParts' => $oldParts,
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
     * @param  \App\Models\Tx_stock_disassembly  $tx_sales_order
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

        $query = Tx_stock_disassembly::where('id', '=', $id)->first();
        if ($query) {
            // $parts = Mst_part::where([
            //     'active' => 'Y'
            // ])
            // ->orderBy('part_number', 'ASC')
            // ->get();

            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $oldParts = [];
            if(old('part_id')){
                $oldParts = Mst_part::where([
                    'id' => old('part_id'),
                    'active' => 'Y'
                ])
                ->first();
            }

            $queryPart = Tx_stock_disassembly_part::where([
                'stock_disassembly_id' => $query->id,
                'active' => 'Y'
            ]);

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                // 'qParts' => $parts,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPart->count()),
                'branch_id' => (old('branch_id')?old('branch_id'):$query->branch_id),
                'userLogin' => $userLogin,
                'stockDisAsm' => $query,
                'stockDisAsmPart' => $queryPart->get(),
                'oldParts' => $oldParts,
                'qCurrency' => $qCurrency,
                'branches' => $branches,
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
     * @param  \App\Models\Tx_stock_disassembly  $tx_sales_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 46,
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
            'disasm_part_qty' => ['required','numeric',new ValidateQty($request->part_id,isset($request->branch_id)?$request->branch_id:$userLogin->branch_id)],
            'avg_cost_part_to_be_disassembly_val' => 'same:total_cost_val',
        ];
        $errMsg = [
            'part_id.numeric' => 'Please select a valid part',
            'disasm_part_qty.required' => 'The qty field is required',
            'disasm_part_qty.numeric' => 'The qty field is must be numeric',
            'avg_cost_part_to_be_disassembly_val.same' => 'AVG Cost and Total Cost must match',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i<$request->totalRow; $i++) {
                if ($request['part_no_'.$i]) {
                    $validatePartInput = [
                        'part_no_'.$i => 'required|numeric|different:part_id',
                        'qty'.$i => 'required|numeric',
                        'new_cost'.$i => ['required',new NumericCustom('Cost')],
                        // 'new_cost'.$i => 'required|numeric',
                    ];
                    $errPartMsg = [
                        'part_no_'.$i.'.numeric' => 'Please select a valid part',
                        'part_no_'.$i.'.different' => 'This To Be Part number and To Be Disassembled Part Number must be different.',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                        'new_cost'.$i.'.required' => 'The cost field is required',
                        'new_cost'.$i.'.numeric' => 'The cost field is must be numeric',
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
        for ($i = 0; $i<$request->totalRow; $i++) {
            if ($request['part_no_'.$i]) {
                $iRow += 1;
            }
        }
        if($iRow==0){
            return back()->withErrors([
                'totalRow' => 'Please select the parts to be disassembled.',
            ])->onlyInput('totalRow');
        }
        // ---

        // Start transaction!
        DB::beginTransaction();

        try {
            $stock_disassembly_date = date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string(ENV("WAKTU_ID")." HOURS")),"Y-m-d");

            $stock_no = '';
            $draft = false;
            $stocks = Tx_stock_disassembly::where('id', '=', $id)
            ->where('stock_disassembly_no','LIKE','%Draft%')
            ->first();
            if($stocks){
                // looking for draft order no
                $draft = true;
                $stock_no = $stocks->stock_disassembly_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_stock_disassemblies';
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
                for ($i = 0; $i<(5 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $stock_no = env('P_STOCK_DISASSEMBLY').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_stock_disassembly::where('id', '=', $id)
                ->update([
                    'stock_disassembly_no' => $stock_no,
                    'stock_disassembly_date' => $stock_disassembly_date,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_stock_disassembly::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $ins = Tx_stock_disassembly::where('id','=',$id)
            ->update([
                'part_id' => $request->part_id,
                'qty' => $request->disasm_part_qty,
                'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                'avg_cost' => 0,
                'remark' => $request->remark_txt,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            if ($request->totalRow > 0) {
                // netralkan part yang dihapus
                $updPart = Tx_stock_disassembly_part::where('stock_disassembly_id','=',$id)
                ->update([
                    'active' => 'N',
                    'updated_by' => Auth::user()->id,
                ]);

                for ($i = 0; $i<$request->totalRow; $i++) {
                    $avgCost = 0;
                    if ($request['part_no_'.$i]) {
                        $parts = Mst_part::leftJoin('tx_qty_parts AS txQty','mst_parts.id','=','txQty.part_id')
                        ->select(
                            'mst_parts.*',
                            'txQty.qty'
                        )
                        ->addSelect([
                            'qty_total' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                            ->limit(1)
                        ])
                        ->where([
                            'mst_parts.id' => $request['part_no_'.$i],
                            'txQty.branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                        ])
                        ->first();
                        if($parts){
                            // outstanding SO/Sj qty
                            $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($request['sd_part_id'.$i]);
                            $freeOH = ($parts->qty_total-$qtySoSj>0?$parts->qty_total-$qtySoSj:0);

                            $newCost = GlobalFuncHelper::moneyValidate($request['new_cost'.$i]);
                            $avgCost = (($parts->avg_cost*$freeOH)+(($newCost*$request['qty'.$i])))/($freeOH+$request['qty'.$i]);
                            // $avgCost = (($parts->avg_cost*$parts->qty_total)+(($newCost*$request['qty'.$i])))/($parts->qty_total+$request['qty'.$i]);

                            $qPart = Tx_stock_disassembly_part::where('id','=',$request['sd_part_id'.$i])
                            ->first();
                            if($qPart){
                                $updPart = Tx_stock_disassembly_part::where('id','=',$request['sd_part_id'.$i])
                                ->update([
                                    'stock_disassembly_id' => $id,
                                    'part_id' => $request['part_no_'.$i],
                                    'qty' => $request['qty'.$i],
                                    'cost' => $newCost,
                                    'final_cost' => ($newCost*$request['qty'.$i]),
                                    'avg_cost' => $avgCost,
                                    'active' => 'Y',
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }else{
                                $insPart = Tx_stock_disassembly_part::create([
                                    'stock_disassembly_id' => $id,
                                    'part_id' => $request['part_no_'.$i],
                                    'qty' => $request['qty'.$i],
                                    'cost' => $newCost,
                                    'final_cost' => ($newCost*$request['qty'.$i]),
                                    'avg_cost' => $avgCost,
                                    'active' => 'Y',
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }

                        }
                    }
                }

                if(!strpos($stock_no,"Draft")){
                    $parts = Mst_part::leftJoin('tx_qty_parts AS txQty','mst_parts.id','=','txQty.part_id')
                    ->select(
                        'mst_parts.*',
                        'txQty.qty'
                    )
                    ->where([
                        'mst_parts.id' => $request->part_id,
                        'txQty.branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                    ])
                    ->first();
                    if($parts){
                        // update avg_cost di table disassemby
                        $updDisAsm = Tx_stock_disassembly::where('id','=',$id)
                        ->update([
                            'avg_cost' => $parts->avg_cost,
                            'updated_by' => Auth::user()->id,
                        ]);

                        //update qty setelah dikurangi part yang dibongkar (disassembly)
                        $updQty = Tx_qty_part::where([
                            'part_id' => $request->part_id,
                            'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                        ])
                        ->update([
                            'qty' => $parts->qty-$request->disasm_part_qty,
                            'updated_by' => Auth::user()->id,
                        ]);

                        // update qty, avg cost & final cost setelah ditambah part hasil bongkar
                        for ($i = 0; $i<$request->totalRow; $i++) {
                            $avgCost = 0;
                            if ($request['part_no_'.$i]) {
                                $yieldParts = Mst_part::leftJoin('tx_qty_parts AS txQty','mst_parts.id','=','txQty.part_id')
                                ->select(
                                    'mst_parts.*',
                                    'txQty.qty',
                                )
                                ->addSelect([
                                    'qty_total' => Tx_qty_part::selectRaw('SUM(qty)')
                                    ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                                    ->limit(1)
                                ])
                                ->where([
                                    'mst_parts.id' => $request['part_no_'.$i],
                                    'txQty.branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                                ])
                                ->first();
                                if($yieldParts){
                                    // update qty
                                    $updQty = Tx_qty_part::where([
                                        'part_id' => $request['part_no_'.$i],
                                        'branch_id' => (isset($request->branch_id)?$request->branch_id:$userLogin->branch_id),
                                    ])
                                    ->update([
                                        'qty' => (int)$yieldParts->qty+(int)$request['qty'.$i],
                                        'updated_by' => Auth::user()->id,
                                    ]);

                                    // outstanding SO/Sj qty
                                    $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($request['part_no_'.$i]);
                                    $freeOH = ($yieldParts->qty_total-$qtySoSj>0?$yieldParts->qty_total-$qtySoSj:0);

                                    //update avg cost & final cost
                                    $newCost = GlobalFuncHelper::moneyValidate($request['new_cost'.$i]);
                                    $avgCost = (($yieldParts->avg_cost*$freeOH)+(($newCost*$request['qty'.$i])))/($freeOH+$request['qty'.$i]);
                                    // $avgCost = (($yieldParts->avg_cost*$yieldParts->qty_total)+(($newCost*$request['qty'.$i])))/($yieldParts->qty_total+$request['qty'.$i]);
                                    $updMstPartDetail = Mst_part::where('id','=',$request['part_no_'.$i])
                                    ->update([
                                        'final_cost' => $newCost*$request['qty'.$i],
                                        'avg_cost' => $avgCost,
                                        'updated_by' => Auth::user()->id,
                                    ]);
                                }
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
     * @param  \App\Models\Tx_stock_disassembly  $tx_sales_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_stock_disassembly $tx_sales_order)
    {
        //
    }
}
