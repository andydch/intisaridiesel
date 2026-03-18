<?php

namespace App\Http\Controllers\tx;

use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_supplier;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_memo;
use App\Models\Tx_receipt_order;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_purchase_order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_receipt_order_part;
use App\Models\Tx_purchase_order_part;
use Illuminate\Support\Facades\Validator;

class ReceiptOrderOldController extends Controller
{
    protected $title = 'Receipt Order';
    protected $folder = 'receipt-order';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Tx_receipt_order::select('tx_receipt_orders.*')
            ->addSelect(['total_price' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty*tx_receipt_order_parts.part_price)')
                ->whereColumn('tx_receipt_order_parts.receipt_order_id','tx_receipt_orders.id')
                ->where('tx_receipt_order_parts.active','=','Y')
            ])
            ->orderBy('created_at', 'DESC')->get();
        $data = [
            'orders' => $query,
            'title' => $this->title,
            'folder' => $this->folder
        ];
        return view('tx.' . $this->folder . '.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $queryPM = Tx_purchase_memo::select('memo_no AS p_no')
            ->whereNotIn('memo_no', function ($query) {
                $query->select('po_or_pm_no')
                    ->from('tx_receipt_orders');
            })
            ->where('memo_no','NOT LIKE','%Draft%')
            ->where('is_received','IS',null)
            ->where([
                'active' => 'Y',
            ])
            ->orderBy('memo_no', 'DESC');
        $queryPurchase = Tx_purchase_order::select('purchase_no AS p_no')
            ->whereNotIn('purchase_no', function ($query) {
                $query->select('po_or_pm_no')
                    ->from('tx_receipt_orders');
            })
            ->where('approved_by','IS NOT',null)
            ->where([
                'active' => 'Y'
            ])
            ->orderBy('purchase_no', 'DESC')
            ->union($queryPM)
            ->get();

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
            ->orderBy('part_name', 'ASC')
            ->get();

        $branches = Mst_branch::where([
            'active' => 'Y'
        ])
            ->orderBy('name', 'ASC')
            ->get();

        $q = [];
        $qPart = [];
        if (old('p_no')) {
            $p_no = old('p_no');
            $q = [];
            switch (substr($p_no, 0, 2)) {
                case 'MP':
                    $q = Tx_purchase_memo::leftJoin('mst_globals AS supplierType', 'tx_purchase_memos.supplier_type_id', '=', 'supplierType.id')
                        ->leftJoin('mst_globals AS entityType', 'tx_purchase_memos.supplier_entity_type_id', '=', 'entityType.id')
                        ->leftJoin('mst_branches AS branch', 'tx_purchase_memos.branch_id', '=', 'branch.id')
                        ->select(
                            'tx_purchase_memos.supplier_name',
                            'tx_purchase_orders.supplier_id',
                            'supplierType.title_ind AS supplier_type_name',
                            'entityType.title_ind AS entity_type_name',
                            'branch.name AS branch_name',
                            'tx_purchase_memos.branch_address',
                        )
                        ->addSelect([
                            'currency_name' => Mst_global::select('title_ind')
                                ->where('id', '=', function ($query) {
                                    $query->select('mst_supplier_bank_information.currency_id')
                                        ->from('mst_supplier_bank_information')
                                        ->whereColumn('mst_supplier_bank_information.supplier_id', 'tx_purchase_memos.supplier_id')
                                        ->where('mst_supplier_bank_information.active', '=', 'Y')
                                        ->limit(1);
                                })
                        ])
                        ->addSelect([
                            'currency_id' => Mst_global::select('id')
                                ->where('id', '=', function ($query) {
                                    $query->select('mst_supplier_bank_information.currency_id')
                                        ->from('mst_supplier_bank_information')
                                        ->whereColumn('mst_supplier_bank_information.supplier_id', 'tx_purchase_memos.supplier_id')
                                        ->where('mst_supplier_bank_information.active', '=', 'Y')
                                        ->limit(1);
                                })
                        ])
                        ->where([
                            'tx_purchase_memos.memo_no' => $p_no
                        ])
                        ->first();

                    $part = Tx_purchase_memo::where('memo_no', '=', $p_no)->first();
                    $qPart = Tx_purchase_memo_part::leftJoin('mst_parts AS parts', 'tx_purchase_memo_parts.part_id', '=', 'parts.id')
                        ->select(
                            'tx_purchase_memo_parts.part_id AS part_id',
                            'parts.part_name',
                            'parts.part_number',
                            'tx_purchase_memo_parts.qty AS part_qty',
                        )
                        ->addSelect(DB::raw("0 as part_price"))
                        ->where('memo_id', '=', $part->id)
                        ->get();

                    break;
                case 'PO':
                    $q = Tx_purchase_order::leftJoin('mst_globals AS supplierType', 'tx_purchase_orders.supplier_type_id', '=', 'supplierType.id')
                        ->leftJoin('mst_globals AS entityType', 'tx_purchase_orders.supplier_entity_type_id', '=', 'entityType.id')
                        ->leftJoin('mst_globals AS curr', 'tx_purchase_orders.currency_id', '=', 'curr.id')
                        ->leftJoin('mst_branches AS branch', 'tx_purchase_orders.branch_id', '=', 'branch.id')
                        ->select(
                            'tx_purchase_orders.supplier_name',
                            'tx_purchase_orders.supplier_id',
                            'curr.title_ind AS currency_name',
                            'curr.id AS currency_id',
                            'supplierType.title_ind AS supplier_type_name',
                            'entityType.title_ind AS entity_type_name',
                            'branch.name AS branch_name',
                            'tx_purchase_orders.branch_address',
                            'tx_purchase_orders.company_id',
                        )
                        ->where([
                            'tx_purchase_orders.purchase_no' => $p_no
                        ])
                        ->first();

                    $part = Tx_purchase_order::where('purchase_no', '=', $p_no)->first();
                    $qPart = Tx_purchase_order_part::leftJoin('mst_parts AS parts', 'tx_purchase_order_parts.part_id', '=', 'parts.id')
                        ->select(
                            'tx_purchase_order_parts.part_id AS part_id',
                            'parts.part_name',
                            'parts.part_number',
                            'tx_purchase_order_parts.qty AS part_qty',
                            'tx_purchase_order_parts.price AS part_price',
                        )
                        ->where('order_id', '=', $part->id)
                        ->get();

                    break;
                default:
                    // code to be executed if n is different from all labels;
            }
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'queryPurchase' => $queryPurchase,
            'parts' => $parts,
            'branches' => $branches,
            'qS' => $q,
            'qPart' => $qPart,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0)
        ];

        return view('tx.' . $this->folder . '.create', $data);
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
            'order_date' => 'required|date',
            'p_no' => 'required',
            'supplier_doc_no' => 'required|max:255',
            'branch_id' => 'required|numeric',
        ];
        $errMsg = [
            'branch_id.numeric' => 'Please select a valid branch',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id' . $i]) {
                    $validateShipmentInput = [
                        'part_id' . $i => 'required|numeric',
                        'qty' . $i => 'required|numeric',
                        'part_price' . $i => 'required|regex:/^[0-9]{1,3}(.[0-9]{0})*\,[0-9]+$/',
                    ];
                    $errShipmentMsg = [
                        'part_id' . $i . '.numeric' => 'Please select a valid part',
                        'qty' . $i . '.numeric' => 'The qty field is required',
                        'part_price' . $i . '.regex' => 'Must have exacly 2 decimal places (9,99)',
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
        )->validate();

        $draft_at = null;
        $draft_to_created_at = null;
        $identityName = 'tx_receipt_orders-draft';
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
            $order_no = 'RO' . date('y') . '-Draft' . strval($newInc);
        }

        $identityName = 'tx_receipt_orders';
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
            for ($i = 0; $i < (4 - strlen(strval($newInc))); $i++) {
                $zero .= '0';
            }
            $order_no = 'RO'.date('y').'-'.$zero.strval($newInc);
        }

        $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)->first();
        $qBranch = Mst_branch::where('id', '=', $request->branch_id)->first();

        // ---

        $active = 'N';
        if ($request->active == 'on') {
            $active = 'Y';
        }

        // $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)->first();
        // $qBranch = Mst_branch::where('id', '=', $request->branch_id)->first();

        // $identityName = 'tx_receipt_orders';
        // $autoInc = Auto_inc::where([
        //     'identity_name' => $identityName
        // ])
        //     ->first();
        // $newInc = 1;
        // if ($autoInc) {
        //     $date = date_format(date_create($autoInc->updated_at), "Y");
        //     if ((int)date("Y") > (int)$date) {
        //         $updInc = Auto_inc::where([
        //             'identity_name' => $identityName
        //         ])
        //             ->update([
        //                 'id_auto_inc' => 1
        //             ]);
        //     } else {
        //         $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
        //         $updInc = Auto_inc::where([
        //             'identity_name' => $identityName
        //         ])
        //             ->update([
        //                 'id_auto_inc' => $newInc
        //             ]);
        //     }
        // } else {
        //     $insInc = Auto_inc::create([
        //         'identity_name' => $identityName,
        //         'id_auto_inc' => $newInc
        //     ]);
        // }
        // $zero = '';
        // $zeroBranch = '';
        // for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
        //     $zero .= '0';
        // }
        // for ($i = 0; $i < (2 - strlen(strval($qBranch->id))); $i++) {
        //     $zeroBranch .= '0';
        // }
        // $order_no = 'RO' . date('Y') . '-' . $zeroBranch . $qBranch->id . '-' . $zero . strval($newInc);

        $po_companyId = null;
        if (substr($request->p_no, 0, 2) == 'PO') {
            $qPO = Tx_purchase_order::where('purchase_no', '=', $request->p_no)->first();
            $po_companyId = $qPO->company_id;
        }

        $ins = Tx_receipt_order::create([
            'receipt_no' => $order_no,
            'receipt_date' => $request->order_date,
            'po_or_pm_no' => $request->p_no,
            'supplier_doc_no' => $request->supplier_doc_no,
            'supplier_id' => $request->supplier_id,
            'supplier_type_id' => $qSupplier->supplier_type_id,
            'supplier_entity_type_id' => $qSupplier->entity_type_id,
            'supplier_name' => $qSupplier->name,
            'currency_id' => $request->currency_id,
            'company_id' => $po_companyId,
            // 'total_qty',
            // 'total_before_vat',
            // 'total_after_vat',
            'branch_id' => $request->branch_id,
            'active' => $active,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        // get last ID
        $maxId = Tx_receipt_order::max('id');


        if ($request->totalRow > 0) {
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

            $totalQty = 0;
            $totalPriceBeforeVAT = 0;
            $totalPriceAfterVAT = 0;
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id' . $i]) {
                    $insPart = Tx_receipt_order_part::create([
                        'receipt_order_id' => $maxId,
                        'part_id' => $request['part_id' . $i],
                        'qty' => $request['qty' . $i],
                        'part_price' => $request['part_price' . $i] == '' ? null : GlobalFuncHelper::moneyValidate($request['part_price' . $i]),
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);

                    $qPart = Mst_part::where('id', '=', $request['part_id' . $i])->first();
                    $totalQty += $request['qty' . $i];
                    $totalPriceBeforeVAT += ($request['qty' . $i] * GlobalFuncHelper::moneyValidate($request['part_price' . $i]));
                    $totalPriceAfterVAT += ($request['qty' . $i] * GlobalFuncHelper::moneyValidate($request['part_price' . $i])) +
                        ((($request['qty' . $i] * GlobalFuncHelper::moneyValidate($request['part_price' . $i])) * $vat) / 100);
                }
            }

            $upd = Tx_receipt_order::where('id', '=', $maxId)
                ->update([
                    'total_qty' => $totalQty,
                    'total_before_vat' => $totalPriceBeforeVAT,
                    'total_after_vat' => $totalPriceAfterVAT,
                ]);
        }

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME') . '/' . $this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function show(Tx_receipt_order $tx_receipt_order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $queryPM = Tx_purchase_memo::select('memo_no AS p_no')
            ->whereNotIn('memo_no', function ($query) {
                $query->select('po_or_pm_no')
                    ->from('tx_receipt_orders');
            })
            ->where([
                'active' => 'Y'
            ])
            ->orderBy('memo_no', 'DESC');
        $queryPurchase = Tx_purchase_order::select('purchase_no AS p_no')
            ->whereNotIn('purchase_no', function ($query) {
                $query->select('po_or_pm_no')
                    ->from('tx_receipt_orders');
            })
            ->where([
                'active' => 'Y'
            ])
            ->orderBy('purchase_no', 'DESC')
            ->union($queryPM)
            ->get();

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
            ->orderBy('part_name', 'ASC')
            ->get();

        $branches = Mst_branch::where([
            'active' => 'Y'
        ])
            ->orderBy('name', 'ASC')
            ->get();

        $q = [];
        $qPart = [];

        $query = Tx_receipt_order::where('id', '=', $id)->first();
        if ($query) {

            $q = [];
            $p_no = $query->po_or_pm_no;
            switch (substr($p_no, 0, 2)) {
                case 'MP':
                    $q = Tx_purchase_memo::leftJoin('mst_globals AS supplierType', 'tx_purchase_memos.supplier_type_id', '=', 'supplierType.id')
                        ->leftJoin('mst_globals AS entityType', 'tx_purchase_memos.supplier_entity_type_id', '=', 'entityType.id')
                        ->leftJoin('mst_branches AS branch', 'tx_purchase_memos.branch_id', '=', 'branch.id')
                        ->select(
                            'tx_purchase_memos.supplier_name',
                            'tx_purchase_orders.supplier_id',
                            'supplierType.title_ind AS supplier_type_name',
                            'entityType.title_ind AS entity_type_name',
                            'branch.name AS branch_name',
                            'tx_purchase_memos.branch_address',
                        )
                        ->addSelect([
                            'currency_name' => Mst_global::select('title_ind')
                                ->where('id', '=', function ($query) {
                                    $query->select('mst_supplier_bank_information.currency_id')
                                        ->from('mst_supplier_bank_information')
                                        ->whereColumn('mst_supplier_bank_information.supplier_id', 'tx_purchase_memos.supplier_id')
                                        ->where('mst_supplier_bank_information.active', '=', 'Y')
                                        ->limit(1);
                                })
                        ])
                        ->addSelect([
                            'currency_id' => Mst_global::select('id')
                                ->where('id', '=', function ($query) {
                                    $query->select('mst_supplier_bank_information.currency_id')
                                        ->from('mst_supplier_bank_information')
                                        ->whereColumn('mst_supplier_bank_information.supplier_id', 'tx_purchase_memos.supplier_id')
                                        ->where('mst_supplier_bank_information.active', '=', 'Y')
                                        ->limit(1);
                                })
                        ])
                        ->where([
                            'tx_purchase_memos.memo_no' => $p_no
                        ])
                        ->first();

                    $part = Tx_purchase_memo::where('memo_no', '=', $p_no)->first();
                    $qPart = Tx_purchase_memo_part::leftJoin('mst_parts AS parts', 'tx_purchase_memo_parts.part_id', '=', 'parts.id')
                        ->select(
                            'tx_purchase_memo_parts.part_id AS part_id',
                            'parts.part_name',
                            'parts.part_number',
                            'tx_purchase_memo_parts.qty AS part_qty',
                        )
                        ->addSelect(DB::raw("0 as part_price"))
                        ->where('memo_id', '=', $part->id)
                        ->get();

                    break;
                case 'PO':
                    $q = Tx_purchase_order::leftJoin('mst_globals AS supplierType', 'tx_purchase_orders.supplier_type_id', '=', 'supplierType.id')
                        ->leftJoin('mst_globals AS entityType', 'tx_purchase_orders.supplier_entity_type_id', '=', 'entityType.id')
                        ->leftJoin('mst_globals AS curr', 'tx_purchase_orders.currency_id', '=', 'curr.id')
                        ->leftJoin('mst_branches AS branch', 'tx_purchase_orders.branch_id', '=', 'branch.id')
                        ->select(
                            'tx_purchase_orders.supplier_name',
                            'tx_purchase_orders.supplier_id',
                            'curr.title_ind AS currency_name',
                            'curr.id AS currency_id',
                            'supplierType.title_ind AS supplier_type_name',
                            'entityType.title_ind AS entity_type_name',
                            'branch.name AS branch_name',
                            'tx_purchase_orders.branch_address',
                            'tx_purchase_orders.company_id',
                        )
                        ->where([
                            'tx_purchase_orders.purchase_no' => $p_no
                        ])
                        ->first();

                    $part = Tx_purchase_order::where('purchase_no', '=', $p_no)->first();
                    $qPart = Tx_purchase_order_part::leftJoin('mst_parts AS parts', 'tx_purchase_order_parts.part_id', '=', 'parts.id')
                        ->select(
                            'tx_purchase_order_parts.part_id AS part_id',
                            'parts.part_name',
                            'parts.part_number',
                            'tx_purchase_order_parts.qty AS part_qty',
                            'tx_purchase_order_parts.price AS part_price',
                        )
                        ->where('order_id', '=', $part->id)
                        ->get();

                    break;
                default:
                    // code to be executed if n is different from all labels;
            }

            $queryPart = Tx_receipt_order_part::where([
                'receipt_order_id' => $query->id,
                'active' => 'Y'
            ])
                ->get();
            $queryPartCount = Tx_receipt_order_part::where([
                'receipt_order_id' => $query->id,
                'active' => 'Y'
            ])
                ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'queryPurchase' => $queryPurchase,
                'parts' => $parts,
                'branches' => $branches,
                'qS' => $q,
                'qPart' => $qPart,
                'receipts' => $query,
                'receiptParts' => $queryPart,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPartCount)
            ];

            return view('tx.' . $this->folder . '.edit', $data);
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
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validateInput = [
            'order_date' => 'required|date',
            'p_no' => 'required',
            'supplier_doc_no' => 'required|max:255',
            'branch_id' => 'required|numeric',
        ];
        $errMsg = [
            'branch_id.numeric' => 'Please select a valid branch',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id' . $i]) {
                    $validateShipmentInput = [
                        'part_id' . $i => 'required|numeric',
                        'qty' . $i => 'required|numeric',
                        'part_price' . $i => 'required|regex:/^[0-9]{1,3}(.[0-9]{0})*\,[0-9]+$/',
                    ];
                    $errShipmentMsg = [
                        'part_id' . $i . '.numeric' => 'Please select a valid part',
                        'qty' . $i . '.required' => 'The qty field is required',
                        'qty' . $i . '.numeric' => 'The qty field must be numeric',
                        'part_price' . $i . '.required' => 'The price field is required',
                        'part_price' . $i . '.regex' => 'Must have exacly 2 decimal places (9,99)',
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
        )->validate();

        $active = 'N';
        if ($request->active == 'on') {
            $active = 'Y';
        }

        $receipts = Tx_receipt_order::where('id', '=', $id)->first();
        $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)->first();
        $qBranch = Mst_branch::where('id', '=', $request->branch_id)->first();

        $zeroBranch = '';
        for ($i = 0; $i < (2 - strlen(strval($qBranch->id))); $i++) {
            $zeroBranch .= '0';
        }
        $receiptArr = explode("-", $receipts->receipt_no);
        $receiptNo = $receiptArr[0] . '-' . $zeroBranch . $qBranch->id . '-' . $receiptArr[2];

        $po_companyId = null;
        if (substr($request->p_no, 0, 2) == 'PO') {
            $qPO = Tx_purchase_order::where('purchase_no', '=', $request->p_no)->first();
            $po_companyId = $qPO->company_id;
        }

        $ins = Tx_receipt_order::where('id', '=', $id)
            ->update([
                'receipt_no' => $receiptNo,
                'receipt_date' => $request->order_date,
                'po_or_pm_no' => $request->p_no,
                'supplier_doc_no' => $request->supplier_doc_no,
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'currency_id' => $request->currency_id,
                'company_id' => $po_companyId,
                // 'total_qty',
                // 'total_before_vat',
                // 'total_after_vat',
                'branch_id' => $request->branch_id,
                'active' => $active,
                'updated_by' => Auth::user()->id
            ]);

        if ($request->totalRow > 0) {
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

            $updPart = Tx_receipt_order_part::where([
                'receipt_order_id' => $id
            ])
                ->update([
                    'active' => 'N',
                    'updated_by' => Auth::user()->id
                ]);

            $totalQty = 0;
            $totalPriceBeforeVAT = 0;
            $totalPriceAfterVAT = 0;
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id' . $i]) {
                    $receiptParts = Tx_receipt_order_part::where([
                        'receipt_order_id' => $id,
                        'part_id' => $request['part_id' . $i]
                    ])
                        ->first();
                    if ($receiptParts) {
                        $updPart = Tx_receipt_order_part::where([
                            'receipt_order_id' => $id,
                            'part_id' => $request['part_id' . $i]
                        ])
                            ->update([
                                'qty' => $request['qty' . $i],
                                'part_price' => $request['part_price' . $i] == '' ? null : GlobalFuncHelper::moneyValidate($request['part_price' . $i]),
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id
                            ]);
                    } else {
                        $insPart = Tx_receipt_order_part::create([
                            'receipt_order_id' => $id,
                            'part_id' => $request['part_id' . $i],
                            'qty' => $request['qty' . $i],
                            'part_price' => $request['part_price' . $i] == '' ? null : GlobalFuncHelper::moneyValidate($request['part_price' . $i]),
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);
                    }

                    $qPart = Mst_part::where('id', '=', $request['part_id' . $i])->first();
                    $totalQty += $request['qty' . $i];
                    $totalPriceBeforeVAT += ($request['qty' . $i] * GlobalFuncHelper::moneyValidate($request['part_price' . $i]));
                    $totalPriceAfterVAT += ($request['qty' . $i] * GlobalFuncHelper::moneyValidate($request['part_price' . $i])) +
                        ((($request['qty' . $i] * GlobalFuncHelper::moneyValidate($request['part_price' . $i])) * $vat) / 100);
                }
            }

            $upd = Tx_receipt_order::where('id', '=', $id)
                ->update([
                    'total_qty' => $totalQty,
                    'total_before_vat' => $totalPriceBeforeVAT,
                    'total_after_vat' => $totalPriceAfterVAT,
                ]);
        }

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME') . '/' . $this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_receipt_order $tx_receipt_order)
    {
        //
    }
}
