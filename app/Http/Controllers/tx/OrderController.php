<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Rules\PQnumUnique;
use App\Models\Tx_qty_part;
use App\Models\Mst_supplier;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_purchase_order;
use App\Rules\ApprovalCheckingPO;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Mst_courier;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_quotation;
use App\Models\Tx_purchase_order_part;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;
use App\Models\Mst_supplier_bank_information;
use App\Models\Tx_purchase_order_oo_oh_part;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected $title = 'Purchase Order';
    protected $folder = 'order';
    protected $idQ;

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

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        $query = Tx_purchase_order::leftJoin('userdetails AS usr','tx_purchase_orders.created_by','=','usr.user_id')
        ->select('tx_purchase_orders.*')
        ->addSelect(['total_price' => Tx_purchase_order_part::selectRaw('SUM(qty*price)')
            ->whereColumn('tx_purchase_order_parts.order_id','tx_purchase_orders.id')
            ->where('tx_purchase_order_parts.active','=','Y')
        ])
        ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
            $q->where('usr.branch_id','=',$userLogin->branch_id);
        })
        ->orderBy('tx_purchase_orders.purchase_no', 'DESC')
        ->orderBy('tx_purchase_orders.created_at', 'DESC');

        $data = [
            'orders' => $query->get(),
            'ordersCount' => $query->count(),
            'title' => $this->title,
            'folder' => $this->folder,
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
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $suppliers = Mst_supplier::where([
            'active' => 'Y'
        ])
        ->orderBy('name', 'ASC')
        ->get();
        $branches = Mst_branch::where([
            'active' => 'Y'
        ])
        ->orderBy('name', 'ASC')
        ->get();
        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_number', 'ASC')
        ->get();
        $couriers = Mst_courier::where([
            'active' => 'Y'
        ])
        ->orderBy('name', 'ASC')
        ->get();

        $supplierPic = [];
        $currency = [];
        $quotation = [];
        if (old('supplier_id')) {
            $supplierPic = Mst_supplier::where([
                'id' => old('supplier_id'),
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();

            $quotation = Tx_purchase_quotation::leftJoin('userdetails AS usr','tx_purchase_quotations.created_by','=','usr.user_id')
            ->select(
                'tx_purchase_quotations.id AS pq_id',
                'tx_purchase_quotations.quotation_no'
            )
            ->whereNotIn('tx_purchase_quotations.id', function (Builder $queryQ) {
                $queryQ->select('tx_order.quotation_id')
                ->from('tx_purchase_orders as tx_order')
                ->where('tx_order.quotation_id','<>',null)
                ->where('tx_order.active','=','Y');
                // ->where('tx_order.id','<>',$this->idQ);
            })
            ->where([
                'tx_purchase_quotations.supplier_id' => old('supplier_id'),
                'tx_purchase_quotations.is_draft' => 'N',
                'tx_purchase_quotations.active' => 'Y',
                'usr.branch_id' => $userLogin->branch_id,
            ])
            ->orderBy('tx_purchase_quotations.created_at','DESC')
            ->get();
            // ->toSql();dd($quotation);

            $currency = Mst_supplier_bank_information::where([
                'supplier_id' => old('supplier_id'),
                'active' => 'Y'
            ])
            ->get();
        }
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'suppliers' => $suppliers,
            'supplierPics' => $supplierPic,
            'branches' => $branches,
            'parts' => $parts,
            'currency' => $currency,
            'quotations' => $quotation,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'userLogin' => $userLogin,
            'qCurrency' => $qCurrency,
            'couriers' => $couriers
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
            'supplier_id' => 'required|numeric',
            'supplier_pic' => 'required|numeric',
            'branch_id' => 'required|numeric',
            'currency_id' => 'required|numeric',
            'est_supply_date' => 'nullable',
            'quotation_id' => ['nullable', new PQnumUnique(0)],
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'supplier_pic.numeric' => 'Please select a valid supplier pic',
            'branch_id.numeric' => 'Please select a valid branch',
            'currency_id.numeric' => 'Please select a valid currency',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        'qty'.$i => 'required|numeric',
                        'price_part'.$i => ['required',new NumericCustom('Price')],
                        // 'price_part'.$i => 'required|numeric',
                        // 'price_part'.$i => 'required|regex:/^[0-9]{1,3}(.[0-9]{0})*\,[0-9]+$/',
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.numeric' => 'The qty field is required',
                        'price_part'.$i.'.required' => 'The price field is required.',
                        'price_part'.$i.'.numeric' => 'The price must be numeric.',
                        'price_part'.$i.'.regex' => 'Must have exacly 2 decimal places (9,99)',
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
            $identityName = 'tx_purchase_orders-draft';
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
                $order_no = ENV('P_PURCHASE_ORDER').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_purchase_orders';
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
                $order_no = ENV('P_PURCHASE_ORDER').date('y').'-'.$zero.strval($newInc);
            }

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)->first();
            $qBranch = Mst_branch::where('id', '=', $request->branch_id)->first();
            // get user info
            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $is_vat = 'N';
            if ($request->vat == 'on') {
                $is_vat = 'Y';
            }

            $est_supply_date = [];
            if($request->est_supply_date!=''){
                $est_supply_date = explode("/",$request->est_supply_date);
            }
            $ins = Tx_purchase_order::create([
                'purchase_no' => $order_no,
                'quotation_id' => ($request->quotation_id=='#'?null:$request->quotation_id),
                'purchase_date' => date("Y-m-d"),
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'supplier_office_address' => $qSupplier->office_address,
                'supplier_country_id' => $qSupplier->country_id,
                'supplier_province_id' => $qSupplier->province_id,
                'supplier_city_id' => $qSupplier->city_id,
                'supplier_district_id' => $qSupplier->district_id,
                'supplier_sub_district_id' => $qSupplier->sub_district_id,
                'supplier_post_code' => $qSupplier->post_code,
                'pic_idx' => $request->supplier_pic,
                'currency_id' => $request->currency_id,
                'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                // 'branch_id' => $request->branch_id,
                'branch_address' => $qBranch->address.', ' .
                    ucwords(strtolower($qBranch->subdistrict->sub_district_name)).', ' .
                    $qBranch->district->district_name.'<br/>' .
                    $qBranch->city->city_name.'<br/>' .
                    $qBranch->province->province_name.' ' .
                    $qBranch->post_code,
                'est_supply_date' => ($request->est_supply_date!=''?$est_supply_date[2].'-'.$est_supply_date[1].'-'.$est_supply_date[0]:null),
                'courier_id' => ($request->courier_id=='#'?null:$request->courier_id),
                'is_draft' => $request->is_draft,
                'is_vat' => $is_vat,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            // get last ID
            $maxId = $ins->id;

            // get active VAT
            $vat = ENV('VAT');
            $qVat = Mst_global::where([
                'data_cat' => 'vat',
                'active' => 'Y'
            ])
            ->first();
            if ($qVat) {
                $vat = $qVat->numeric_val;
            }

            if ($request->totalRow > 0) {
                $totalQty = 0;
                $totalPriceBeforeVAT = 0;
                $totalPriceAfterVAT = 0;
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if($request['part_id'.$i]){
                        $insPart = Tx_purchase_order_part::create([
                            'order_id' => $maxId,
                            'part_id' => $request['part_id'.$i],
                            'qty' => $request['qty'.$i],
                            'price' => $request['price_part'.$i] == '' ? null : GlobalFuncHelper::moneyValidate($request['price_part'.$i]),
                            'description' => $request['desc_part'.$i],
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);

                        $totalQty += $request['qty'.$i];
                        $totalPriceBeforeVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price_part'.$i]));
                        $totalPriceAfterVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price_part'.$i])) +
                            ((($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price_part'.$i])) * $vat) / 100);

                        $queryQty = Tx_qty_part::where([
                            'part_id' => $request['part_id'.$i],
                            'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                            // 'branch_id' => $userLogin->branch_id,
                        ])
                        ->first();
                        if(!$queryQty){
                            $insQty = Tx_qty_part::create([
                                'part_id' => $request['part_id'.$i],
                                'qty' => 0,
                                'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                // 'branch_id' => $userLogin->branch_id,
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }

                        if(strpos($order_no,"Draft")==0){
                            // simpan OH dan OO jika bukan draft
                            $qOhOo = Tx_purchase_order_oo_oh_part::where([
                                'purchase_order_id' => $maxId,
                                'purchase_order_part_id' => $insPart->id,
                                'part_id' => $request['part_id'.$i],
                                'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                // 'branch_id' => $userLogin->branch_id,
                            ])
                            ->first();
                            if(!$qOhOo){
                                $insOhOo = Tx_purchase_order_oo_oh_part::create([
                                    'purchase_order_id' => $maxId,
                                    'purchase_order_part_id' => $insPart->id,
                                    'part_id' => $request['part_id'.$i],
                                    'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                    // 'branch_id' => $userLogin->branch_id,
                                    'last_OO_PO_created' => $request['oo_'.$i.'_tmp'],
                                    'last_OH_PO_created' => $request['oh_'.$i.'_tmp'],
                                    'last_OO_OH_PO_created' => now(),
                                    'active' => 'Y',
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }else{
                                $insOhOo = Tx_purchase_order_oo_oh_part::where([
                                    'purchase_order_id' => $maxId,
                                    'purchase_order_part_id' => $insPart->id,
                                    'part_id' => $request['part_id'.$i],
                                    'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                    // 'branch_id' => $userLogin->branch_id,
                                ])
                                ->update([
                                    'purchase_order_id' => $maxId,
                                    'purchase_order_part_id' => $insPart->id,
                                    'part_id' => $request['part_id'.$i],
                                    'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                    // 'branch_id' => $userLogin->branch_id,
                                    'last_OO_PO_created' => $request['oo_'.$i.'_tmp'],
                                    'last_OH_PO_created' => $request['oh_'.$i.'_tmp'],
                                    'last_OO_OH_PO_created' => now(),
                                    'active' => 'Y',
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }

                        }
                    }
                }

                $upd = Tx_purchase_order::where('id', '=', $maxId)
                ->update([
                    'total_qty' => $totalQty,
                    'total_before_vat' => $totalPriceBeforeVAT,
                    'total_after_vat' => $totalPriceAfterVAT,
                ]);
            }

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error','Something Went Wrong!');
        } catch(Exception $e){
            DB::rollback();
            // throw $e;

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error','Something Went Wrong!');
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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
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

        $query = Tx_purchase_order::where('id', '=', $id)->first();
        if ($query) {
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $suppliers = Mst_supplier::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $branches = Mst_branch::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $parts = Mst_part::where([
                'active' => 'Y'
            ])
            ->orderBy('part_number', 'ASC')
            ->get();
            if (old('supplier_id')) {
                $supplierPic = Mst_supplier::where([
                    'id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
                $currency = Mst_supplier_bank_information::where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->get();
            } else {
                $supplierPic = Mst_supplier::where([
                    'id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
                $currency = Mst_supplier_bank_information::where([
                    'supplier_id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->get();
            }
            $queryOrderPart = Tx_purchase_order_part::where([
                'order_id' => $query->id,
                'active' => 'Y'
            ]);
            $data = [
                'orders' => $query,
                // 'quotations' => $quotation,
                'orderParts' => $queryOrderPart->get(),
                'title' => $this->title,
                'folder' => $this->folder,
                'suppliers' => $suppliers,
                'supplierPics' => $supplierPic,
                'branches' => $branches,
                'parts' => $parts,
                'currency' => $currency,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryOrderPart->count()),
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
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

        $query = Tx_purchase_order::where('id', '=', $id)
        ->first();
        if ($query) {
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $this->idQ = $query->quotation_id;
            $quotation = Tx_purchase_quotation::leftJoin('userdetails AS usr','tx_purchase_quotations.created_by','=','usr.user_id')
            ->select(
                'tx_purchase_quotations.id AS pq_id',
                'tx_purchase_quotations.quotation_no'
            )
            ->whereNotIn('tx_purchase_quotations.id', function (Builder $queryQ) {
                $queryQ->select('tx_order.quotation_id')->from('tx_purchase_orders as tx_order')
                    // ->where('tx_order.id','<>',$this->idQ)
                    ->where('tx_order.quotation_id','<>',$this->idQ)
                    ->where('tx_order.active','=','Y');
                })
            ->where([
                'tx_purchase_quotations.is_draft' => 'N',
                'tx_purchase_quotations.active' => 'Y',
                'usr.branch_id' => $userLogin->branch_id,
            ])
            ->orderBy('tx_purchase_quotations.created_at','DESC')
            ->get();

            $suppliers = Mst_supplier::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $branches = Mst_branch::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $parts = Mst_part::where([
                'active' => 'Y'
            ])
            ->orderBy('part_number', 'ASC')
            ->get();
            $couriers = Mst_courier::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();

            if (old('supplier_id')) {
                $supplierPic = Mst_supplier::where([
                    'id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
                $currency = Mst_supplier_bank_information::where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->get();
            } else {
                $supplierPic = Mst_supplier::where([
                    'id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
                $currency = Mst_supplier_bank_information::where([
                    'supplier_id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->get();
            }
            $queryOrderPart = Tx_purchase_order_part::where([
                'order_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $queryOrderPartCount = Tx_purchase_order_part::where([
                'order_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();
            $data = [
                'orders' => $query,
                'quotations' => $quotation,
                'orderParts' => $queryOrderPart,
                'title' => $this->title,
                'folder' => $this->folder,
                'suppliers' => $suppliers,
                'supplierPics' => $supplierPic,
                'branches' => $branches,
                'parts' => $parts,
                'currency' => $currency,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryOrderPartCount),
                'userLogin' => $userLogin,
                'qCurrency' => $qCurrency,
                'couriers' => $couriers
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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validateInput = [
            'supplier_id' => 'required|numeric',
            'supplier_pic' => 'required|numeric',
            'branch_id' => 'required|numeric',
            'currency_id' => 'required|numeric',
            'est_supply_date' => 'nullable',
            'quotation_id' => ['nullable', new PQnumUnique($id)],
            'order_no' => [new ApprovalCheckingPO],
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'supplier_pic.numeric' => 'Please select a valid supplier pic',
            'branch_id.numeric' => 'Please select a valid branch',
            'currency_id.numeric' => 'Please select a valid currency',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        'qty'.$i => 'required|numeric',
                        'price_part'.$i => ['required',new NumericCustom('Price')],
                        // 'price_part'.$i => 'required|numeric',
                        // 'price_part'.$i => 'required|regex:/^[0-9]{1,3}(.[0-9]{0})*\,[0-9]+$/',
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.numeric' => 'The qty field is required',
                        'price_part'.$i.'.required' => 'The price field is required.',
                        'price_part'.$i.'.numeric' => 'The price must be numeric.',
                        'price_part'.$i.'.regex' => 'Must have exacly 2 decimal places (9,99)',
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

        // validasi jika ORDER sudah diapproved oleh kacab/direksi
        // ketika di sisi sales masih waiting for approval
        $qApproveVal = Tx_purchase_order::where('id', '=', $id)
        ->where('approved_by','IS NOT',null)
        ->first();
        if($qApproveVal){
            return redirect()
            ->back()
            ->withInput()
            ->with('status-error','You cannot change data if the Purchase Order has been approved or rejected.');
        }

        // Start transaction!
        DB::beginTransaction();

        try {
            $order_no = '';
            $draft = false;
            $orders = Tx_purchase_order::where('id', '=', $id)
            ->where('purchase_no','LIKE','%Draft%')
            ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $order_no = $orders->order_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_purchase_orders';
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
                $order_no = ENV('P_PURCHASE_ORDER').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_purchase_order::where('id', '=', $id)
                ->update([
                    'purchase_no' => $order_no,
                    'purchase_date' => date("Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);

            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_purchase_order::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $is_vat = 'N';
            if ($request->vat == 'on') {
                $is_vat = 'Y';
            }

            // get active VAT
            $vat = ENV('VAT');
            $qVat = Mst_global::where([
                'data_cat' => 'vat',
                'active' => 'Y'
            ])
                ->first();
            if ($qVat) {
                $vat = $qVat->numeric_val;
            }

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)->first();
            $qBranch = Mst_branch::where('id', '=', $request->branch_id)->first();

            // get user info
            $qO = Tx_purchase_order::where('id','=',$id)
            ->first();
            $userLogin = Userdetail::where('user_id','=',$qO->created_by)
            ->first();

            $est_supply_date = [];
            if($request->est_supply_date!=''){
                $est_supply_date = explode("/",$request->est_supply_date);
            }
            $upd = Tx_purchase_order::where('id', '=', $id)
            ->update([
                'purchase_date' => date("Y-m-d"),
                'quotation_id' => ($request->quotation_id=='#'?null:$request->quotation_id),
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'supplier_office_address' => $qSupplier->office_address,
                'supplier_country_id' => $qSupplier->country_id,
                'supplier_province_id' => $qSupplier->province_id,
                'supplier_city_id' => $qSupplier->city_id,
                'supplier_district_id' => $qSupplier->district_id,
                'supplier_sub_district_id' => $qSupplier->sub_district_id,
                'supplier_post_code' => $qSupplier->post_code,
                'pic_idx' => $request->supplier_pic,
                'currency_id' => $request->currency_id,
                'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                // 'branch_id' => $request->branch_id,
                'branch_address' => $qBranch->address.', ' .
                    ucwords(strtolower($qBranch->subdistrict->sub_district_name)).', ' .
                    $qBranch->district->district_name.'<br/>' .
                    $qBranch->city->city_name.'<br/>' .
                    $qBranch->province->province_name.' ' .
                    $qBranch->post_code,
                'est_supply_date' => ($request->est_supply_date!=''?$est_supply_date[2].'-'.$est_supply_date[1].'-'.$est_supply_date[0]:null),
                'courier_id' => ($request->courier_id=='#'?null:$request->courier_id),
                'is_vat' => $is_vat,
                // 'active' => $active,
                'updated_by' => Auth::user()->id
            ]);

            // set not active
            $updPart = Tx_purchase_order_part::where([
                'order_id' => $id,
                'active' => 'Y'
            ])
            ->update([
                'active' => 'N'
            ]);

            // get active VAT
            $vat = ENV('VAT');
            $qVat = Mst_global::where([
                'data_cat' => 'vat',
                'active' => 'Y'
            ])
            ->first();
            if ($qVat) {
                $vat = $qVat->numeric_val;
            }

            if ($request->totalRow > 0) {
                $totalQty = 0;
                $totalPriceBeforeVAT = 0;
                $totalPriceAfterVAT = 0;
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_id'.$i]) {
                        if ($request['order_part_id_'.$i] > 0) {
                            $insPart = Tx_purchase_order_part::where('id', '=', $request['order_part_id_'.$i])
                            ->update([
                                'part_id' => $request['part_id'.$i],
                                'qty' => $request['qty'.$i],
                                'price' => $request['price_part'.$i] == '' ? null : GlobalFuncHelper::moneyValidate($request['price_part'.$i]),
                                'description' => $request['desc_part'.$i],
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id
                            ]);
                        } else {
                            $insPart = Tx_purchase_order_part::create([
                                'order_id' => $id,
                                'part_id' => $request['part_id'.$i],
                                'qty' => $request['qty'.$i],
                                'price' => $request['price_part'.$i] == '' ? null : GlobalFuncHelper::moneyValidate($request['price_part'.$i]),
                                'description' => $request['desc_part'.$i],
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id
                            ]);
                        }
                        // $qPart = Mst_part::where('id', '=', $request['part_id'.$i])->first();
                        $totalQty += $request['qty'.$i];
                        $totalPriceBeforeVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price_part'.$i]));
                        $totalPriceAfterVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price_part'.$i])) +
                            ((($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price_part'.$i])) * $vat) / 100);

                        $queryQty = Tx_qty_part::where([
                            'part_id' => $request['part_id'.$i],
                            'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                            // 'branch_id' => $userLogin->branch_id,
                        ])
                        ->first();
                        if(!$queryQty){
                            $insQty = Tx_qty_part::create([
                                'part_id' => $request['part_id'.$i],
                                'qty' => 0,
                                'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                // 'branch_id' => $userLogin->branch_id,
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }

                        if(strpos($order_no,"Draft")==0){
                            // simpan OH dan OO jika bukan draft
                            $qOhOo = Tx_purchase_order_oo_oh_part::where([
                                'purchase_order_id' => $id,
                                'purchase_order_part_id' => (isset($insPart->id)?$insPart->id:$request['order_part_id_'.$i]),
                                'part_id' => $request['part_id'.$i],
                                'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                // 'branch_id' => $userLogin->branch_id,
                            ])
                            ->first();
                            if(!$qOhOo){
                                $insOhOo = Tx_purchase_order_oo_oh_part::create([
                                    'purchase_order_id' => $id,
                                    'purchase_order_part_id' => (isset($insPart->id)?$insPart->id:$request['order_part_id_'.$i]),
                                    'part_id' => $request['part_id'.$i],
                                    'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                    // 'branch_id' => $userLogin->branch_id,
                                    'last_OO_PO_created' => $request['oo_'.$i.'_tmp'],
                                    'last_OH_PO_created' => $request['oh_'.$i.'_tmp'],
                                    'last_OO_OH_PO_created' => now(),
                                    'active' => 'Y',
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }else{
                                $updOhOo = Tx_purchase_order_oo_oh_part::where([
                                    'purchase_order_id' => $id,
                                    'purchase_order_part_id' => (isset($insPart->id)?$insPart->id:$request['order_part_id_'.$i]),
                                    'part_id' => $request['part_id'.$i],
                                    'branch_id' => $userLogin->branch_id,
                                ])
                                ->update([
                                    'purchase_order_id' => $id,
                                    'purchase_order_part_id' => (isset($insPart->id)?$insPart->id:$request['order_part_id_'.$i]),
                                    'part_id' => $request['part_id'.$i],
                                    'branch_id' => ($userLogin->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                    // 'branch_id' => $userLogin->branch_id,
                                    'last_OO_PO_created' => $request['oo_'.$i.'_tmp'],
                                    'last_OH_PO_created' => $request['oh_'.$i.'_tmp'],
                                    'last_OO_OH_PO_created' => now(),
                                    'active' => 'Y',
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }

                        }
                    }
                }

                $upd = Tx_purchase_order::where('id', '=', $id)
                ->update([
                    'total_qty' => $totalQty,
                    'total_before_vat' => $totalPriceBeforeVAT,
                    'total_after_vat' => $totalPriceAfterVAT,
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

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_order $tx_purchase_order)
    {
        //
    }
}
