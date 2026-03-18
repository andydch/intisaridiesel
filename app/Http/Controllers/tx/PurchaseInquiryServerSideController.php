<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Userdetail;
use App\Models\Mst_supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tx_purchase_inquiry;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_inquiry_part;
use App\Models\Mst_menu_user;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Yajra\DataTables\Facades\DataTables;

class PurchaseInquiryServerSideController extends Controller
{
    protected $title = 'Purchase Inquiry';
    protected $folder = 'purchase-inquiry';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        if ($request->ajax()) {
            $query = Tx_purchase_inquiry::leftJoin('userdetails AS usr','tx_purchase_inquiries.created_by','=','usr.user_id')
            ->leftJoin('mst_suppliers','tx_purchase_inquiries.supplier_id','=','mst_suppliers.id')
            ->leftJoin('mst_globals as ent','mst_suppliers.entity_type_id','=','ent.id')
            ->select(
                'tx_purchase_inquiries.purchase_inquiry_no',
                'tx_purchase_inquiries.slug',
                'tx_purchase_inquiries.purchase_inquiry_date',
                'tx_purchase_inquiries.active as inquiry_active',
                'tx_purchase_inquiries.created_by as created_by',
                'mst_suppliers.name as supplier_name',
                'mst_suppliers.supplier_code',
                'usr.initial as initial',
                'usr.is_director',
                'usr.is_branch_head',
                'ent.title_ind as supplier_entity_type_name',
            )
            ->where(function($q){
                $q->where('tx_purchase_inquiries.active', 'Y')
                ->orWhere(function($s){
                    // tampilkan inquiry yg sudah dihapus tapi bukan DRAFT
                    $s->where('tx_purchase_inquiries.active','N')
                    ->where('tx_purchase_inquiries.purchase_inquiry_no','NOT LIKE','%Draft%');
                });
            })
            ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_purchase_inquiries.purchase_inquiry_no', 'DESC')
            ->orderBy('tx_purchase_inquiries.created_at', 'DESC');

            return DataTables::of($query)
            ->filterColumn('inquiry_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_purchase_inquiries.purchase_inquiry_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('inquiry_date', function ($query) {
                return date_format(date_create($query->purchase_inquiry_date),"d/m/Y");
            })
            ->filterColumn('supplier_name', function($query, $keyword) {
                $query->where(function($q) use($keyword){
                    $q->where('mst_suppliers.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('mst_suppliers.supplier_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('ent.title_ind', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('supplier_name', function ($query) {
                return $query->supplier_code.' - '.$query->supplier_entity_type_name.' '.$query->supplier_name;
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                if(($query->created_by==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y') && $query->inquiry_active=='Y'){
                    if(strpos($query->purchase_inquiry_no,"Draft")==0){
                        return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-inquiry/'.urlencode($query->slug).'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-inquiry/'.urlencode($query->slug)).'" style="text-decoration: underline;">View</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-purchase-inquiry/'.urlencode($query->slug)).'" style="text-decoration: underline;" target="_new">Print</a> |
                            <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-purchase-inquiry/'.urlencode($query->slug)).'" style="text-decoration: underline;">Download</a>';
                    }else{
                        return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-inquiry/'.urlencode($query->slug).'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-inquiry/'.urlencode($query->slug)).'" style="text-decoration: underline;">View</a>';
                    }
                }else{
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-inquiry/'.urlencode($query->slug)).'" style="text-decoration: underline;">View</a>';
                }
            })
            ->addColumn('status', function ($query) {
                if(strpos($query->purchase_inquiry_no,"Draft")>0 && $query->inquiry_active=='Y'){
                    return 'Draft';
                }
                if(strpos($query->purchase_inquiry_no,"Draft")==0 && $query->inquiry_active=='Y'){
                    return 'Created';
                }
                if($query->inquiry_active=='N'){
                    return 'Cancel';
                }
            })
            ->rawColumns(['inquiry_date','supplier_name','action','status'])
            ->toJson();
        }

        $data = [
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
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $suppliers = Mst_supplier::where([
            'supplier_type_id' => 11,
            'active' => 'Y'
        ])
        ->orderBy('name', 'ASC')
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
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'userLogin' => $userLogin,
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
            'menu_id' => 67,
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
            'supplier_id' => 'required|numeric',
            'supplier_pic' => 'required|numeric',
            'is_draft' => 'in:Y,N',
            'header_txt' => 'required|max:2048',
            'footer_txt' => 'required|max:2048',
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'supplier_pic.numeric' => 'Please select a valid supplier pic',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_name_'.$i]) {
                    $validateShipmentInput = [
                        'part_name_'.$i => 'required|max:255',
                        'qty'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'part_name_'.$i.'.numeric' => 'Please select a valid part',
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
            $identityName = 'tx_purchase_inquiries-draft';
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
                $purchase_inquiry_no = ENV('P_PURCHASE_INQUIRY').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_purchase_inquiries';
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
                $purchase_inquiry_no = ENV('P_PURCHASE_INQUIRY').date('y').'-'.$zero.strval($newInc);
            }

            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $qSupplier = Mst_supplier::where('id','=', $request->supplier_id)
            ->first();

            $ins = Tx_purchase_inquiry::create([
                'purchase_inquiry_no' => $purchase_inquiry_no,
                // 'slug' => SlugService::createSlug(Tx_purchase_inquiry::class, 'slug', $purchase_inquiry_no),
                'purchase_inquiry_date' => date("Y-m-d"),
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
                'header' => $request->header_txt,
                'footer' => $request->footer_txt,
                'remark' => $request->remark_txt,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'is_draft' => $request->is_draft,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            if ($request->totalRow > 0) {
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_name_'.$i]) {
                        $insPart = Tx_purchase_inquiry_part::create([
                            'purchase_inquiry_id' => $maxId,
                            'part_name' => $request['part_name_'.$i],
                            'qty' => $request['qty'.$i],
                            'unit' => $request['unit_'.$i],
                            'description' => $request['desc_part'.$i],
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_purchase_inquiry  $tx_purchase_inquiry
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $query = Tx_purchase_inquiry::where('slug','=',urldecode($slug))
        ->first();
        if ($query) {
            $suppliers = Mst_supplier::where([
                'supplier_type_id' => 11,
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $supplierPic = Mst_supplier::where([
                'id' => $query->supplier_id,
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $queryInquiryPart = Tx_purchase_inquiry_part::where([
                'purchase_inquiry_id' => $query->id,
            ]);
            $data = [
                'p_inquiries' => $query,
                'p_inquiryParts' => $queryInquiryPart->get(),
                'title' => $this->title,
                'folder' => $this->folder,
                'suppliers' => $suppliers,
                'supplierPics' => $supplierPic,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryInquiryPart->count()),
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
     * @param  \App\Models\Tx_purchase_inquiry  $tx_purchase_inquiry
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $query = Tx_purchase_inquiry::where('slug','=',urldecode($slug))
        ->first();
        if ($query) {
            $suppliers = Mst_supplier::where([
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
            } else {
                $supplierPic = Mst_supplier::where([
                    'id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
            }
            $queryInquiryPart = Tx_purchase_inquiry_part::where([
                'purchase_inquiry_id' => $query->id,
                'active' => 'Y'
            ]);

            $data = [
                'p_inquiries' => $query,
                'p_inquiryParts' => $queryInquiryPart->get(),
                'title' => $this->title,
                'folder' => $this->folder,
                'suppliers' => $suppliers,
                'supplierPics' => $supplierPic,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryInquiryPart->count()),
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
     * @param  \App\Models\Tx_purchase_inquiry  $tx_purchase_inquiry
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 67,
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
            'supplier_id' => 'required|numeric',
            'supplier_pic' => 'required|numeric',
            'is_draft' => 'in:Y,N',
            'header_txt' => 'required|max:2048',
            'footer_txt' => 'required|max:2048',
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'supplier_pic.numeric' => 'Please select a valid supplier pic',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_name_'.$i]) {
                    $validateShipmentInput = [
                        'part_name_'.$i => 'required|max:255',
                        'qty'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'part_name_'.$i.'.numeric' => 'Please select a valid part',
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

            $qI = Tx_purchase_inquiry::where('slug','=',urldecode($slug))
            ->first();
            if($qI){
                $draft = false;
                $purchase_inquiry_no = $qI->purchase_inquiry_no;
                $inquiries = Tx_purchase_inquiry::where('id','=',$qI->id)
                ->where('purchase_inquiry_no','LIKE','%Draft%')
                ->first();
                if($inquiries){
                    // looking for draft inquiry no
                    $draft = true;
                    $purchase_inquiry_no = $inquiries->purchase_inquiry_no;
                }

                if($request->is_draft=='Y' && $draft){
                    // still save as draft
                    // no action to do here
                }

                if($request->is_draft!='Y' && $draft){
                    // promote to created

                    $identityName = 'tx_purchase_inquiries';
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
                    $purchase_inquiry_no = ENV('P_PURCHASE_INQUIRY').date('y').'-'.$zero.strval($newInc);

                    $upd = Tx_purchase_inquiry::where('id','=',$qI->id)
                    ->update([
                        'purchase_inquiry_no' => $purchase_inquiry_no,
                        'purchase_inquiry_date' => date("Y-m-d"),
                        'is_draft' => $request->is_draft,
                        'draft_to_created_at' => now(),
                        'updated_by' => Auth::user()->id
                    ]);
                }

                if($request->is_draft!='Y' && !$draft){
                    $upd = Tx_purchase_inquiry::where('id','=',$qI->id)
                    ->update([
                        'is_draft' => $request->is_draft,
                        'draft_to_created_at' => now(),
                        'updated_by' => Auth::user()->id
                    ]);
                }

                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $qSupplier = Mst_supplier::where('id','=', $request->supplier_id)
                ->first();

                $upd = Tx_purchase_inquiry::where('id','=',$qI->id)
                ->update([
                    'slug' => SlugService::createSlug(Tx_purchase_inquiry::class, 'slug', $purchase_inquiry_no),
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
                    'header' => $request->header_txt,
                    'footer' => $request->footer_txt,
                    'remark' => $request->remark_txt,
                    'is_draft' => $request->is_draft,
                    'active' => 'Y',
                    'updated_by' => Auth::user()->id,
                ]);

                // set not active
                $updPart = Tx_purchase_inquiry_part::where([
                    'purchase_inquiry_id' => $qI->id
                ])
                ->update([
                    'active' => 'N'
                ]);

                if ($request->totalRow > 0) {
                    for ($i = 0; $i < $request->totalRow; $i++) {
                        if ($request['part_name_'.$i]) {
                            if ((int)$request['part_name_id_'.$i] > 0) {
                                $updPart = Tx_purchase_inquiry_part::where('id','=',$request['part_name_id_'.$i])
                                ->update([
                                    'purchase_inquiry_id' => $qI->id,
                                    'part_name' => $request['part_name_'.$i],
                                    'qty' => $request['qty'.$i],
                                    'unit' => $request['unit_'.$i],
                                    'active' => 'Y',
                                    'description' => $request['desc_part'.$i],
                                    'updated_by' => Auth::user()->id,
                                ]);
                            } else {
                                $insPart = Tx_purchase_inquiry_part::create([
                                    'purchase_inquiry_id' => $qI->id,
                                    'part_name' => $request['part_name_'.$i],
                                    'qty' => $request['qty'.$i],
                                    'unit' => $request['unit_'.$i],
                                    'description' => $request['desc_part'.$i],
                                    'active' => 'Y',
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }
                        }
                    }
                }
            }else{
                $data = [
                    'errNotif' => 'The data you are looking for is not found'
                ];
                return view('error-notif.not-found-notif', $data);
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
     * @param  \App\Models\Tx_purchase_inquiry  $tx_purchase_inquiry
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_inquiry $tx_purchase_inquiry)
    {
        //
    }
}
