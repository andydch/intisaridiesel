<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Tx_qty_part;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Models\Mst_brand_type;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_purchase_order;
use Illuminate\Support\Facades\DB;
use App\Models\Mst_part_brand_type;
use App\Http\Controllers\Controller;
use App\Models\Mst_part_subtitution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\Mst_menu_user;

class StockMasterPartController extends Controller
{
    protected $title = 'Stock Master - Part';
    protected $folder = 'stock-master';
    protected $uri_folder = 'stock-master-part';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $qUser = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $queryPartType = Mst_global::where([
            'data_cat' => 'part-type',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
        ->get();
        $queryPartCategory = Mst_global::where([
            'data_cat' => 'part-category',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
        ->get();
        $queryBrand = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
        ->get();
        $queryWeightType = Mst_global::where([
            'data_cat' => 'weight-type',
            'active' => 'Y'
        ])
        ->get();
        $queryQuantityType = Mst_global::where([
            'data_cat' => 'quantity-type',
            'active' => 'Y'
        ])
        ->get();
        $queryOtherPart = Mst_part::where('active', '=', 'Y')
        ->orderBy('part_name', 'ASC')
        ->get();
        $queryOtherPartSelect = [];
        if(old('part_subtitution')){
            $part_subtitution = substr(old('part_subtitution'),0,strlen(old('part_subtitution')));
            $queryOtherPart = Mst_part::whereNotIn('id', explode(',',$part_subtitution))
            ->where('active', '=', 'Y')
            ->orderBy('part_name', 'ASC')
            ->get();
            $queryOtherPartSelect = Mst_part::whereIn('id', explode(',',$part_subtitution))
            ->where('active', '=', 'Y')
            ->orderBy('part_name', 'ASC')
            ->get();
        }
        $queryOtherPartCount = Mst_part::where('active', '=', 'Y')
        ->orderBy('part_name', 'ASC')
        ->count();
        $queryBrandType = [];
        $qBrandTypes = [];
        if (old('brand_id')) {
            $queryBrandType = Mst_brand_type::where([
                'brand_id' => old('brand_id'),
                'active' => 'Y'
            ])
            ->orderBy('brand_type', 'ASC')
            ->get();
            $qBrandTypes = Mst_brand_type::where([
                'brand_id' => old('brand_id'),
                'active' => 'Y'
            ])
            ->orderBy('brand_type', 'ASC')
            ->get();
        }
        $qParts = Mst_part::where('active','=','Y')
        ->orderBy('part_name','ASC')
        ->get();

        $qMinMaxStock = Mst_branch::select(
            'id as branch_id',
            'name as branch_name',
        )
        ->where('active','=','Y')
        ->orderBy('name','ASC');

        $data = [
            'partType' => $queryPartType,
            'partCategory' => $queryPartCategory,
            'brand' => $queryBrand,
            'weightType' => $queryWeightType,
            'quantityType' => $queryQuantityType,
            'queryOtherPart' => $queryOtherPart,
            'queryOtherPartCount' => $queryOtherPartCount,
            'queryBrandType' => $queryBrandType,
            'title' => $this->title,
            'folder' => $this->folder,
            'uri_folder' => $this->uri_folder,
            'queryOtherPartSelect' => $queryOtherPartSelect,
            'qBrandTypes' => $qBrandTypes,
            'totBrandTypeRow' => old('totalBrandTypeRow')?old('totalBrandTypeRow'):0,
            'totPartSubsRow' => old('totalPartSubsRow')?old('totalPartSubsRow'):0,
            'qParts' => $qParts,
            'qUser' => $qUser,
            'qMinMaxStock' => $qMinMaxStock->get(),
            'qMinMaxStockCount' => $qMinMaxStock->count(),
        ];

        return view('tx.'.$this->folder.'.create-part', $data);
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
            'menu_id' => 41,
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
            'part_no' => 'required|max:255|unique:App\Models\Mst_part,part_number',
            'partName' => 'required|max:255',
            'partType_id' => 'required|numeric',
            'partCategory_id' => 'required|numeric',
            'brand_id' => 'required|numeric',
            'weight' => 'numeric|nullable',
            'weight_id' => 'numeric|nullable',
            'quantity_id' => 'required|numeric',
            // 'max_stock' => 'numeric|nullable',
            // 'safety_stock' => 'required|numeric',
            'price_list' => ['nullable',new NumericCustom('Pricelist')],
        ];
        $errMsg = [
            'part_type_id.numeric' => 'Please select a valid part type',
            'part_category_id.numeric' => 'Please select a valid part category',
            'brand_id.numeric' => 'Please select a valid brand',
            'weight_id.numeric' => 'Please select a valid weight type',
            'quantity_id.numeric' => 'Please select a valid quantity type',
            'price_list.numeric' => 'Pricelist must be numeric',
            // 'price_list.regex' => 'Must have exacly 2 decimal places (9,99)',
        ];
        if ($request->totalBrandTypeRow > 0) {
            for ($i = 0; $i < $request->totalBrandTypeRow; $i++) {
                if ($request['brand_type_id_'.$i]) {
                    $validateShipmentInput = [
                        'brand_type_id_'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'brand_type_id_'.$i.'.numeric' => 'Please select a valid brand type',
                    ];
                    $validateInput = array_merge($validateInput, $validateShipmentInput);
                    $errMsg = array_merge($errMsg, $errShipmentMsg);
                }
            }
        }
        if ($request->totalPartSubsRow > 0) {
            for ($i = 0; $i < $request->totalPartSubsRow; $i++) {
                if ($request['part_no_'.$i]) {
                    $validateShipmentInput = [
                        'part_no_'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'part_no_'.$i.'.numeric' => 'Please select a valid part',
                    ];
                    $validateInput = array_merge($validateInput, $validateShipmentInput);
                    $errMsg = array_merge($errMsg, $errShipmentMsg);
                }
            }
        }
        if ($request->qMinMaxStockCount > 0) {
            for ($i=0; $i<$request->qMinMaxStockCount; $i++) {
                $validateBranchMinMax = [
                    'min_stock'.$i => 'required|numeric',
                    'max_stock'.$i => 'required|numeric',
                ];
                $errBranchMinMax = [
                    'min_stock'.$i.'.required' => 'Min Stock must be numeric',
                    'max_stock'.$i.'.required' => 'Max Stock must be numeric',
                    'min_stock'.$i.'.numeric' => 'Min Stock must be numeric',
                    'max_stock'.$i.'.numeric' => 'Max Stock must be numeric',
                ];
                $validateInput = array_merge($validateInput, $validateBranchMinMax);
                $errMsg = array_merge($errMsg, $errBranchMinMax);
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

            $ins = Mst_part::create([
                'part_number' => $request->part_no,
                'part_name' => $request->partName,
                'part_brand' => null,
                'part_type_id' => $request->partType_id,
                'part_category_id' => $request->partCategory_id,
                'brand_id' => $request->brand_id,
                'weight' => (is_numeric($request->weight)?$request->weight:null),
                'weight_id' => (is_numeric($request->weight_id)?$request->weight_id:null),
                'quantity_type_id' => $request->quantity_id,
                'max_stock' => $request->max_stock,
                'safety_stock' => $request->safety_stock,
                'price_list' => $request->price_list == '' ? null : GlobalFuncHelper::moneyValidate($request->price_list),
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
                'created_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            // update qty, min stock, max stock
            for ($i=0; $i<$request->qMinMaxStockCount; $i++) {
                $ins = Tx_qty_part::create([
                    'part_id' => $maxId,
                    'qty' => 0,
                    'min_qty' => $request['min_stock'.$i],
                    'max_qty' => $request['max_stock'.$i],
                    'branch_id' => $request['branch_id'.$i],
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                ]);
            }
            // update qty, min stock, max stock

            // brand type
            for ($i = 0; $i < $request->totalBrandTypeRow; $i++) {
                if ($request['brand_type_id_'.$i]) {
                    $ins = Mst_part_brand_type::create([
                        'part_id' => $maxId,
                        'brand_id' => $request->brand_id,
                        'brand_type_id' => $request['brand_type_id_'.$i],
                        'active' => 'Y',
                        'updated_by' => Auth::user()->id,
                        'created_by' => Auth::user()->id,
                    ]);
                }
            }

            // part subtitution
            for ($i = 0; $i < $request->totalPartSubsRow; $i++) {
                if ($request['part_no_'.$i]) {
                    $ins = Mst_part_subtitution::create([
                        'part_id' => $maxId,
                        'part_other_id' => $request['part_no_'.$i],
                        'active' => 'Y',
                        'updated_by' => Auth::user()->id,
                        'created_by' => Auth::user()->id,
                    ]);

                    // relasi subtitusi sebaliknya
                    $ins = Mst_part_subtitution::create([
                        'part_id' => $request['part_no_'.$i],
                        'part_other_id' => $maxId,
                        'active' => 'Y',
                        'updated_by' => Auth::user()->id,
                        'created_by' => Auth::user()->id,
                    ]);
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
        return redirect()->to(url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master/'.urlencode('::::::::::::::')));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $slug)
    {
        $query = Mst_part::where('slug', '=', urldecode($slug))
        ->first();
        if ($query) {
            $queryPartType = Mst_global::where([
                'data_cat' => 'part-type',
                'active' => 'Y'
            ])
            ->get();
            $queryPartCategory = Mst_global::where([
                'data_cat' => 'part-category',
                'active' => 'Y'
            ])
            ->get();
            $queryBrand = Mst_global::where([
                'data_cat' => 'brand',
                'active' => 'Y'
            ])
            ->get();
            $queryWeightType = Mst_global::where([
                'data_cat' => 'weight-type',
                'active' => 'Y'
            ])
            ->get();
            $queryQuantityType = Mst_global::where([
                'data_cat' => 'quantity-type',
                'active' => 'Y'
            ])
            ->get();

            $queryOtherPartCount = Mst_part::where('id', '<>', $query->id)
                ->where('active', '=', 'Y')
                ->orderBy('part_name', 'ASC')
                ->count();

            if (old('brand_id')) {
                $queryBrandType = Mst_brand_type::where([
                    'brand_id' => old('brand_id'),
                    'active' => 'Y'
                ])
                ->orderBy('brand_type', 'ASC')
                ->get();
                $queryBrandTypecount = Mst_brand_type::where([
                    'brand_id' => old('brand_id'),
                    'active' => 'Y'
                ])
                ->count();
            } else {
                $queryBrandType = Mst_brand_type::where([
                        'brand_id' => $query->brand_id,
                        'active' => 'Y'
                    ])
                ->orderBy('brand_type', 'ASC')
                ->get();
                $queryBrandTypecount = Mst_brand_type::where([
                    'brand_id' => $query->brand_id,
                    'active' => 'Y'
                ])
                ->count();
            }

            $qPartBrandTypes = Mst_part_brand_type::where([
                'part_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $qPartBrandTypesCount = Mst_part_brand_type::where([
                'part_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();

            $OtherPart = Mst_part_subtitution::where([
                'part_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $OtherPartCount = Mst_part_subtitution::where([
                'part_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();

            $qParts = Mst_part::where('active','=','Y')
            ->orderBy('part_name','ASC')
            ->get();

            $data = [
                'partType' => $queryPartType,
                'partCategory' => $queryPartCategory,
                'brand' => $queryBrand,
                'weightType' => $queryWeightType,
                'quantityType' => $queryQuantityType,
                'queryOtherPartCount' => $queryOtherPartCount,
                'queryBrandType' => $queryBrandType,
                'queryBrandTypecount' => $queryBrandTypecount,
                'title' => $this->title,
                'folder' => $this->folder,
                'parts' => $query,
                'uri_folder' => $this->uri_folder,
                'totBrandTypeRow' => old('totalBrandTypeRow')?old('totalBrandTypeRow'):$qPartBrandTypesCount,
                'OtherPart' => $OtherPart,
                'totPartSubsRow' => old('totalPartSubsRow')?old('totalPartSubsRow'):$OtherPartCount,
                'qPartBrandTypes' => $qPartBrandTypes,
                'qParts' => $qParts,
                'reqs' => $request,
            ];
            return view('tx.'.$this->folder.'.show-part', $data);
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
    public function edit($slug)
    {
        $qUser = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $query = Mst_part::where('slug', '=', urldecode($slug))
        ->first();
        if ($query) {
            $queryPartType = Mst_global::where([
                'data_cat' => 'part-type',
                'active' => 'Y'
            ])
            ->orderBy('title_ind','ASC')
            ->get();
            $queryPartCategory = Mst_global::where([
                'data_cat' => 'part-category',
                'active' => 'Y'
            ])
            ->orderBy('title_ind','ASC')
            ->get();
            $queryBrand = Mst_global::where([
                'data_cat' => 'brand',
                'active' => 'Y'
            ])
            ->orderBy('title_ind','ASC')
            ->get();
            $queryWeightType = Mst_global::where([
                'data_cat' => 'weight-type',
                'active' => 'Y'
            ])
            ->get();
            $queryQuantityType = Mst_global::where([
                'data_cat' => 'quantity-type',
                'active' => 'Y'
            ])
            ->get();

            $queryOtherPartCount = Mst_part::where('id', '<>', $query->id)
            ->where('active', '=', 'Y')
            ->orderBy('part_name', 'ASC')
            ->count();

            if (old('brand_id')) {
                $queryBrandType = Mst_brand_type::where([
                    'brand_id' => old('brand_id'),
                    'active' => 'Y'
                ])
                ->orderBy('brand_type', 'ASC')
                ->get();
                $queryBrandTypecount = Mst_brand_type::where([
                    'brand_id' => old('brand_id'),
                    'active' => 'Y'
                ])
                ->count();
            } else {
                $queryBrandType = Mst_brand_type::where([
                    'brand_id' => $query->brand_id,
                    'active' => 'Y'
                ])
                ->orderBy('brand_type', 'ASC')
                ->get();
                $queryBrandTypecount = Mst_brand_type::where([
                    'brand_id' => $query->brand_id,
                    'active' => 'Y'
                ])
                ->count();
            }

            $qPartBrandTypes = Mst_part_brand_type::where([
                'part_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $qPartBrandTypesCount = Mst_part_brand_type::where([
                'part_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();

            $OtherPart = Mst_part_subtitution::where([
                'part_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $OtherPartCount = Mst_part_subtitution::where([
                'part_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();

            $qParts = Mst_part::where('active','=','Y')
            ->orderBy('part_name','ASC')
            ->get();

            $qMinMaxStock = Tx_qty_part::leftJoin('mst_branches','tx_qty_parts.branch_id','=','mst_branches.id')
            ->select(
                'tx_qty_parts.part_id',
                'tx_qty_parts.branch_id',
                'tx_qty_parts.min_qty',
                'tx_qty_parts.max_qty',
                'mst_branches.name as branch_name',
            )
            ->where([
                'tx_qty_parts.part_id' => $query->id,
                'mst_branches.active' => 'Y',
            ])
            ->orderBy('mst_branches.name','ASC');

            $data = [
                'partType' => $queryPartType,
                'partCategory' => $queryPartCategory,
                'brand' => $queryBrand,
                'weightType' => $queryWeightType,
                'quantityType' => $queryQuantityType,
                'queryOtherPartCount' => $queryOtherPartCount,
                'queryBrandType' => $queryBrandType,
                'queryBrandTypecount' => $queryBrandTypecount,
                'title' => $this->title,
                'folder' => $this->folder,
                'parts' => $query,
                'uri_folder' => $this->uri_folder,
                'totBrandTypeRow' => old('totalBrandTypeRow')?old('totalBrandTypeRow'):$qPartBrandTypesCount,
                'OtherPart' => $OtherPart,
                'totPartSubsRow' => old('totalPartSubsRow')?old('totalPartSubsRow'):$OtherPartCount,
                'qPartBrandTypes' => $qPartBrandTypes,
                'qParts' => $qParts,
                'qUser' => $qUser,
                'qMinMaxStock' => $qMinMaxStock->get(),
                'qMinMaxStockCount' => $qMinMaxStock->count(),
            ];
            return view('tx.'.$this->folder.'.edit-part', $data);
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
    public function update(Request $request, $slug)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 41,
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
        
        $qPart = Mst_part::where('slug', '=', urldecode($slug))
        ->first();
        if ($qPart){
            $validateInput = [
                // 'part_number' => ['required', 'max:255', Rule::unique('mst_parts')->ignore($qPart->id)],
                'partName' => 'required|max:255',
                'partType_id' => 'required|numeric',
                'partCategory_id' => 'required|numeric',
                'brand_id' => 'required|numeric',
                'weight' => 'numeric|nullable',
                'weight_id' => 'numeric|nullable',
                'quantity_id' => 'required|numeric',
                // 'max_stock' => 'numeric|nullable',
                // 'safety_stock' => 'required|numeric',
                'price_list' => ['nullable',new NumericCustom('Pricelist')],
            ];
            $errMsg = [
                'part_type_id.numeric' => 'Please select a valid part type',
                'part_category_id.numeric' => 'Please select a valid part category',
                'brand_id.numeric' => 'Please select a valid brand',
                'weight_id.numeric' => 'Please select a valid weight type',
                'quantity_id.numeric' => 'Please select a valid quantity type',
                'price_list.numeric' => 'Pricelist must be numberic',
                // 'price_list.regex' => 'Must have exacly 2 decimal places (9,99)',
            ];
            if ($request->totalBrandTypeRow > 0) {
                for ($i = 0; $i < $request->totalBrandTypeRow; $i++) {
                    if ($request['brand_type_id_'.$i]) {
                        $validateShipmentInput = [
                            'brand_type_id_'.$i => 'required|numeric',
                        ];
                        $errShipmentMsg = [
                            'brand_type_id_'.$i.'.numeric' => 'Please select a valid brand type',
                        ];
                        $validateInput = array_merge($validateInput, $validateShipmentInput);
                        $errMsg = array_merge($errMsg, $errShipmentMsg);
                    }
                }
            }
            if ($request->totalPartSubsRow > 0) {
                for ($i = 0; $i < $request->totalPartSubsRow; $i++) {
                    if ($request['part_no_'.$i]) {
                        $validateShipmentInput = [
                            'part_no_'.$i => 'required|numeric',
                        ];
                        $errShipmentMsg = [
                            'part_no_'.$i.'.numeric' => 'Please select a valid part',
                        ];
                        $validateInput = array_merge($validateInput, $validateShipmentInput);
                        $errMsg = array_merge($errMsg, $errShipmentMsg);
                    }
                }
            }
            if ($request->qMinMaxStockCount > 0) {
                for ($i=0; $i<$request->qMinMaxStockCount; $i++) {
                    $validateBranchMinMax = [
                        'min_stock'.$i => 'required|numeric',
                        'max_stock'.$i => 'required|numeric',
                    ];
                    $errBranchMinMax = [
                        'min_stock'.$i.'.required' => 'Min Stock must be numeric',
                        'max_stock'.$i.'.required' => 'Max Stock must be numeric',
                        'min_stock'.$i.'.numeric' => 'Min Stock must be numeric',
                        'max_stock'.$i.'.numeric' => 'Max Stock must be numeric',
                    ];
                    $validateInput = array_merge($validateInput, $validateBranchMinMax);
                    $errMsg = array_merge($errMsg, $errBranchMinMax);
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

                $updPart = Mst_part::where('slug', '=', urldecode($slug))
                ->update([
                    'part_name' => $request->partName,
                    // 'part_number' => $request->part_number,
                    'part_brand' => null,
                    'slug' => SlugService::createSlug(Mst_part::class, 'slug', $request->partName),
                    'part_type_id' => $request->partType_id,
                    'part_category_id' => $request->partCategory_id,
                    'brand_id' => $request->brand_id,
                    'weight' => (is_numeric($request->weight)?$request->weight:null),
                    'weight_id' => (is_numeric($request->weight_id)?$request->weight_id:null),
                    'quantity_type_id' => $request->quantity_id,
                    'max_stock' => $request->max_stock,
                    'safety_stock' => $request->safety_stock,
                    'price_list' => $request->price_list == '' ? null : GlobalFuncHelper::moneyValidate($request->price_list),
                    'active' => 'Y',
                    'updated_by' => Auth::user()->id,
                ]);

                // update qty, min stock, max stock
                for ($i=0; $i<$request->qMinMaxStockCount; $i++) {
                    $qQty = Tx_qty_part::where([
                        'part_id' => $qPart->id,
                        'branch_id' => $request['branch_id'.$i],
                    ])
                    ->first();
                    if ($qQty){
                        $upd = Tx_qty_part::where([
                            'part_id' => $qPart->id,
                            'branch_id' => $request['branch_id'.$i],
                        ])
                        ->update([
                            'min_qty' => $request['min_stock'.$i],
                            'max_qty' => $request['max_stock'.$i],
                            'updated_by' => Auth::user()->id,
                        ]);
                    }else{
                        $ins = Tx_qty_part::create([
                            'part_id' => $qPart->id,
                            'qty' => 0,
                            'min_qty' => $request['min_stock'.$i],
                            'max_qty' => $request['max_stock'.$i],
                            'branch_id' => $request['branch_id'.$i],
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
                }
                // update qty, min stock, max stock

                // part - brand - brand type
                $upd = Mst_part_brand_type::where([
                    'part_id' => $qPart->id,
                ])
                ->update([
                    'active' => 'N',
                    'updated_by' => Auth::user()->id,
                ]);

                for ($i = 0; $i < $request->totalBrandTypeRow; $i++) {
                    if ($request['brand_type_id_'.$i]) {
                        if($request['brand_type_id_row'.$i]>0){
                            $upd = Mst_part_brand_type::where('id','=',$request['brand_type_id_row'.$i])
                            ->update([
                                'part_id' => $qPart->id,
                                'brand_id' => $request->brand_id,
                                'brand_type_id' => $request['brand_type_id_'.$i],
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $ins = Mst_part_brand_type::create([
                                'part_id' => $qPart->id,
                                'brand_id' => $request->brand_id,
                                'brand_type_id' => $request['brand_type_id_'.$i],
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id,
                                'created_by' => Auth::user()->id,
                            ]);
                        }
                    }
                }

                // part - subtitution
                $upd = Mst_part_subtitution::where([
                    'part_id' => $qPart->id,
                ])
                ->update([
                    'active' => 'N',
                    'updated_by' => Auth::user()->id,
                ]);

                for ($i = 0; $i < $request->totalPartSubsRow; $i++) {
                    if ($request['part_no_'.$i]) {
                        if($request['part_subs_id_row'.$i]>0){
                            $upd = Mst_part_subtitution::where('id','=',$request['part_subs_id_row'.$i])
                            ->update([
                                'part_id' => $qPart->id,
                                'part_other_id' => $request['part_no_'.$i],
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $ins = Mst_part_subtitution::create([
                                'part_id' => $qPart->id,
                                'part_other_id' => $request['part_no_'.$i],
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id,
                                'created_by' => Auth::user()->id,
                            ]);
                        }

                        $qCek = Mst_part_subtitution::where([
                            'part_id' => $request['part_no_'.$i],
                            'part_other_id' => $qPart->id,
                        ])
                        ->first();
                        if(!$qCek){
                            $ins = Mst_part_subtitution::create([
                                'part_id' => $request['part_no_'.$i],
                                'part_other_id' => $qPart->id,
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id,
                                'created_by' => Auth::user()->id,
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

            session()->flash('status', 'Existing data has been updated successfully.');
            return redirect()->to(url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master/'.urlencode('::::::::::::::')));
        }else{
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
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
