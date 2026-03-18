<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_quotation;
use Illuminate\Support\Facades\Validator;
use App\Models\Tx_purchase_quotation_part;
use Illuminate\Validation\ValidationException;

class QuotationController extends Controller
{
    protected $title = 'Purchase Quotation';
    protected $folder = 'quotation';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if($userLogin->is_director=='Y'){
            $query = Tx_purchase_quotation::leftJoin('userdetails AS usr','tx_purchase_quotations.created_by','=','usr.user_id')
            ->select(
                'tx_purchase_quotations.*',
                'tx_purchase_quotations.id AS tx_id',
                'usr.is_director',
                'usr.is_branch_head',
            )
            ->where(function($q){
                $q->where('tx_purchase_quotations.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_purchase_quotations.active','N')
                    ->where('tx_purchase_quotations.quotation_no','NOT LIKE','%Draft%');
                });
            })
            ->orderBy('tx_purchase_quotations.quotation_no', 'DESC')
            ->orderBy('tx_purchase_quotations.created_at', 'DESC');
        }else{
            $query = Tx_purchase_quotation::leftJoin('userdetails AS usr','tx_purchase_quotations.created_by','=','usr.user_id')
            ->select(
                'tx_purchase_quotations.*',
                'tx_purchase_quotations.id AS tx_id',
                'usr.is_director',
                'usr.is_branch_head',
            )
            ->where(function($q){
                $q->where('tx_purchase_quotations.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_purchase_quotations.active','N')
                    ->where('tx_purchase_quotations.quotation_no','NOT LIKE','%Draft%');
                });
            })
            ->when($userLogin->is_director=='N', function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_purchase_quotations.quotation_no', 'DESC')
            ->orderBy('tx_purchase_quotations.created_at', 'DESC');
        }

        $data = [
            'quotations' => $query->get(),
            'quotationsCount' => $query->count(),
            'title' => $this->title,
            'folder' => $this->folder,
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
        ini_set('memory_limit', '64M');
        ini_set('max_execution_time', 1800);

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $suppliers = Mst_supplier::where([
            'active' => 'Y'
        ])
        ->orderBy('name', 'ASC')
        ->get();
        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_number', 'ASC')
        ->get();
        $supplierPic = [];
        if (old('supplier_id')) {
            $supplierPic = Mst_supplier::where([
                'id' => old('supplier_id'),
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
        }
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'suppliers' => $suppliers,
            'supplierPics' => $supplierPic,
            // 'companies' => $companies,
            'parts' => $parts,
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
            'supplier_id' => 'required|numeric',
            'supplier_pic' => 'required|numeric',
            'is_draft' => 'in:Y,N',
            'header_txt' => 'required',
            'footer_txt' => 'required',
            // 'remark_txt' => 'required',
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'supplier_pic.numeric' => 'Please select a valid supplier pic',
            'header_txt.required' => 'The header field is required',
            'footer_txt.required' => 'The footer field is required',
            'remark_txt.required' => 'The remark field is required',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        'qty'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field must be numeric',
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
            $identityName = 'tx_purchase_quotation-draft';
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
                $quotation_no = ENV('P_PURCHASE_QUOTATION').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_purchase_quotation';
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
                $quotation_no = ENV('P_PURCHASE_QUOTATION').date('y').'-'.$zero.strval($newInc);
            }

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)
            ->first();

            $ins = Tx_purchase_quotation::create([
                'quotation_no' => $quotation_no,
                'quotation_date' => date("Y-m-d"),
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
                'is_draft' => $request->is_draft,
                'header' => $request->header_txt,
                'footer' => $request->footer_txt,
                'remark' => $request->remark_txt,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            // get last ID
            $maxId = $ins->id;
            // $maxId = Tx_purchase_quotation::max('id');

            $qty = 0;
            if ($request->totalRow > 0) {
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_id'.$i]) {
                        $insPart = Tx_purchase_quotation_part::create([
                            'quotation_id' => $maxId,
                            'part_id' => $request['part_id'.$i],
                            'qty' => $request['qty'.$i],
                            'description' => $request['desc_part'.$i],
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);

                        $qty += $request['qty'.$i];
                    }
                }
            }

            // update total qty
            $upd = Tx_purchase_quotation::where('id', '=', $maxId)
            ->update([
                'total_qty' => $qty,
            ]);

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
     * @param  \App\Models\Tx_purchase_quotation  $tx_purchase_quotation
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

        $query = Tx_purchase_quotation::where('id', '=', $id)
        ->first();
        if ($query) {
            $suppliers = Mst_supplier::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $parts = Mst_part::where([
                'active' => 'Y'
            ])
            ->orderBy('part_name', 'ASC')
            ->get();
            $supplierPic = Mst_supplier::where([
                'id' => $query->supplier_id,
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $queryQuotationPart = Tx_purchase_quotation_part::where([
                    'quotation_id' => $query->id,
                    'active' => 'Y'
                ])
            ->get();
            $queryQuotationPartCount = Tx_purchase_quotation_part::where([
                    'quotation_id' => $query->id,
                    'active' => 'Y'
                ])
            ->count();
            $data = [
                'quotations' => $query,
                'quotationParts' => $queryQuotationPart,
                'title' => $this->title,
                'folder' => $this->folder,
                'suppliers' => $suppliers,
                'supplierPics' => $supplierPic,
                'parts' => $parts,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryQuotationPartCount),
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
     * @param  \App\Models\Tx_purchase_quotation  $tx_purchase_quotation
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

        $query = Tx_purchase_quotation::where('id', '=', $id)
        ->first();
        if ($query) {
            $suppliers = Mst_supplier::where([
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
            } else {
                $supplierPic = Mst_supplier::where([
                    'id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                    ->orderBy('name', 'ASC')
                    ->get();
            }
            $queryQuotationPart = Tx_purchase_quotation_part::where([
                'quotation_id' => $query->id,
                'active' => 'Y'
            ])->get();
            $queryQuotationPartCount = Tx_purchase_quotation_part::where([
                'quotation_id' => $query->id,
                'active' => 'Y'
            ])->count();
            $data = [
                'quotations' => $query,
                'quotationParts' => $queryQuotationPart,
                'title' => $this->title,
                'folder' => $this->folder,
                'suppliers' => $suppliers,
                'supplierPics' => $supplierPic,
                'parts' => $parts,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryQuotationPartCount),
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
     * @param  \App\Models\Tx_purchase_quotation  $tx_purchase_quotation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validateInput = [
            'supplier_id' => 'required|numeric',
            'supplier_pic' => 'required|numeric',
            'is_draft' => 'in:Y,N',
            'header_txt' => 'required',
            'footer_txt' => 'required',
            // 'remark_txt' => 'required',
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'supplier_pic.numeric' => 'Please select a valid supplier pic',
            'header_txt.required' => 'The header field is required',
            'footer_txt.required' => 'The footer field is required',
            'remark_txt.required' => 'The remark field is required',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        'qty'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field must be numeric',
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
            $orders = Tx_purchase_quotation::where('id', '=', $id)
            ->where('quotation_no','LIKE','%Draft%')
            ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $quotation_no = $orders->quotation_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_purchase_quotation';
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
                $quotation_no = ENV('P_PURCHASE_QUOTATION').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_purchase_quotation::where('id', '=', $id)
                ->update([
                    'quotation_no' => $quotation_no,
                    'quotation_date' => date("Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_purchase_quotation::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)
            ->first();

            $upd = Tx_purchase_quotation::where('id', '=', $id)
                ->update([
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
                    'is_draft' => $request->is_draft,
                    'header' => $request->header_txt,
                    'footer' => $request->footer_txt,
                    'remark' => $request->remark_txt,
                    // 'active' => $active,
                    'updated_by' => Auth::user()->id
                ]);

            // set not active
            $updPart = Tx_purchase_quotation_part::where([
                'quotation_id' => $id
            ])->update([
                'active' => 'N'
            ]);

            $qty = 0;
            if ($request->totalRow > 0) {
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_id'.$i]) {
                        $qty += $request['qty'.$i];
                        if ($request['quotation_part_id_'.$i] > 0) {
                            $insPart = Tx_purchase_quotation_part::where('id', '=', $request['quotation_part_id_'.$i])
                                ->update([
                                    'part_id' => $request['part_id'.$i],
                                    'qty' => $request['qty'.$i],
                                    'description' => $request['desc_part'.$i],
                                    'active' => 'Y',
                                    'updated_by' => Auth::user()->id
                                ]);
                        } else {
                            $insPart = Tx_purchase_quotation_part::create([
                                'quotation_id' => $id,
                                'part_id' => $request['part_id'.$i],
                                'qty' => $request['qty'.$i],
                                'description' => $request['desc_part'.$i],
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id
                            ]);
                        }
                    }
                }

                // update total qty
                $upd = Tx_purchase_quotation::where('id', '=', $id)
                ->update([
                    'total_qty' => $qty,
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
     * @param  \App\Models\Tx_purchase_quotation  $tx_purchase_quotation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_quotation $tx_purchase_quotation)
    {
        //
    }
}
