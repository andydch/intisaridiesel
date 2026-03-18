<?php

namespace App\Http\Controllers\adm;

use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Rules\PartNoUnique;
use Illuminate\Http\Request;
use App\Models\Mst_brand_type;
use App\Helpers\GlobalFuncHelper;
use App\Models\Mst_part_brand_type;
use App\Http\Controllers\Controller;
use App\Models\Mst_part_subtitution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class PartController extends Controller
{
    protected $title = 'Part';
    protected $folder = 'part';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_part::orderBy('part_name', 'ASC')
            ->get();
        $data = [
            'parts' => $query,
            'title' => $this->title,
            'folder' => $this->folder
        ];

        return view('adm.' . $this->folder . '.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
        $queryOtherPart = Mst_part::where('active', '=', 'Y')
            ->orderBy('part_name', 'ASC')
            ->get();
        $queryOtherPartCount = Mst_part::where('active', '=', 'Y')
            ->orderBy('part_name', 'ASC')
            ->count();
        $queryBrandType = [];
        if (old('brand_id')) {
            $queryBrandType = Mst_brand_type::where([
                'brand_id' => old('brand_id'),
                'active' => 'Y'
            ])
                ->orderBy('brand_type', 'ASC')
                ->get();
        }

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
            'folder' => $this->folder
        ];

        return view('adm.' . $this->folder . '.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'part_no' => ['required', 'max:255', new PartNoUnique(0)],
            'partName' => 'required|max:255',
            'partType_id' => 'required|numeric',
            'partCategory_id' => 'required|numeric',
            'brand_id' => 'required|numeric',
            'weight' => 'required|numeric',
            'weight_id' => 'required|numeric',
            'quantity_id' => 'required|numeric',
            'max_stock' => 'numeric|nullable',
            'safety_stock' => 'required|numeric',
            'price_list' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{0})*\,[0-9]+$/',
            // 'final_price' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'avg_cost' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'initial_cost' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'final_cost' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'total_cost' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'total_sales' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
        ], [
            'part_type_id.numeric' => 'Please select a valid part type',
            'part_category_id.numeric' => 'Please select a valid part category',
            'brand_id.numeric' => 'Please select a valid brand',
            'weight_id.numeric' => 'Please select a valid weight type',
            'quantity_type_id.numeric' => 'Please select a valid quantity type',
            'price_list.regex' => 'Must have exacly 2 decimal places (9,99)',
            // 'final_price.regex' => 'The final price format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'avg_cost.regex' => 'The avg cost format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'initial_cost.regex' => 'The initial cost format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'final_cost.regex' => 'The final cost format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'total_cost.regex' => 'The total cost format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'total_sales.regex' => 'The total sales format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
        ])->validate();

        $active = 'N';
        if ($request->active == 'on') {
            $active = 'Y';
        }
        $ins = Mst_part::create([
            'part_number' => substr($request->part_no,0,5).''.substr($request->part_no,6,strlen($request->part_no)),
            'part_name' => $request->partName,
            'part_type_id' => $request->partType_id,
            'part_category_id' => $request->partCategory_id,
            'brand_id' => $request->brand_id,
            'weight' => $request->weight,
            'weight_id' => $request->weight_id,
            'quantity_type_id' => $request->quantity_id,
            'max_stock' => $request->max_stock,
            'safety_stock' => $request->safety_stock,
            'price_list' => $request->price_list == '' ? null : GlobalFuncHelper::moneyValidate($request->price_list),
            // 'final_price' => $request->final_price == '' ? null : GlobalFuncHelper::moneyValidate($request->final_price),
            // 'avg_cost' => $request->avg_cost == '' ? null : GlobalFuncHelper::moneyValidate($request->avg_cost),
            // 'initial_cost' => $request->initial_cost == '' ? null : GlobalFuncHelper::moneyValidate($request->initial_cost),
            // 'final_cost' => $request->final_cost == '' ? null : GlobalFuncHelper::moneyValidate($request->final_cost),
            // 'total_cost' => $request->total_cost == '' ? null : GlobalFuncHelper::moneyValidate($request->total_cost),
            // 'total_sales' => $request->total_sales == '' ? null : GlobalFuncHelper::moneyValidate($request->total_sales),
            'active' => $active,
            'updated_by' => Auth::user()->id,
            'created_by' => Auth::user()->id,
        ]);

        // get last ID
        $maxId = Mst_part::max('id');

        // part - brand - brand type
        for ($i = 0; $i < $request->brand_type_count; $i++) {
            if (!empty($request['brand_type_' . $i])) {
                $ins = Mst_part_brand_type::create([
                    'part_id' => $maxId,
                    'brand_id' => $request->brand_id,
                    'brand_type_id' => $request['brand_type_' . $i],
                    'active' => 'Y',
                    'updated_by' => Auth::user()->id,
                    'created_by' => Auth::user()->id,
                ]);
            }
        }

        // part - subtitution
        for ($i = 0; $i < $request->part_count; $i++) {
            if (!empty($request['part_sub' . $i])) {
                $ins = Mst_part_subtitution::create([
                    'part_id' => $maxId,
                    'part_other_id' => $request['part_sub' . $i],
                    'active' => 'Y',
                    'updated_by' => Auth::user()->id,
                    'created_by' => Auth::user()->id,
                ]);
            }
        }

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/' . $this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function show(Mst_part $mst_part)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = Mst_part::where('id', '=', $id)->first();
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
            $queryOtherPart = Mst_part::where('id', '<>', $query->id)
                ->where('active', '=', 'Y')
                ->orderBy('part_name', 'ASC')
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
                $queryBrandType =
                    Mst_brand_type::where([
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

            $data = [
                'partType' => $queryPartType,
                'partCategory' => $queryPartCategory,
                'brand' => $queryBrand,
                'weightType' => $queryWeightType,
                'quantityType' => $queryQuantityType,
                'queryOtherPart' => $queryOtherPart,
                'queryOtherPartCount' => $queryOtherPartCount,
                'queryBrandType' => $queryBrandType,
                'queryBrandTypecount' => $queryBrandTypecount,
                'title' => $this->title,
                'folder' => $this->folder,
                'parts' => $query,
            ];
            return view('adm.' . $this->folder . '.edit', $data);
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
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Validator::make($request->all(), [
            'part_no' => ['required', 'max:255', new PartNoUnique($id)],
            'partName' => 'required|max:255',
            'partType_id' => 'required|numeric',
            'partCategory_id' => 'required|numeric',
            'brand_id' => 'required|numeric',
            'weight' => 'required|numeric',
            'weight_id' => 'required|numeric',
            'quantity_id' => 'required|numeric',
            'max_stock' => 'numeric|nullable',
            'safety_stock' => 'required|numeric',
            'price_list' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{0})*\,[0-9]+$/',
            // 'final_price' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'avg_cost' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'initial_cost' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'final_cost' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'total_cost' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'total_sales' => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
        ], [
            'part_type_id.numeric' => 'Please select a valid part type',
            'part_category_id.numeric' => 'Please select a valid part category',
            'brand_id.numeric' => 'Please select a valid brand',
            'weight_id.numeric' => 'Please select a valid weight type',
            'quantity_type_id.numeric' => 'Please select a valid quantity type',
            'price_list.regex' => 'Must have exacly 2 decimal places (9,99)',
            // 'final_price.regex' => 'The final price format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'avg_cost.regex' => 'The avg cost format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'initial_cost.regex' => 'The initial cost format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'final_cost.regex' => 'The final cost format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'total_cost.regex' => 'The total cost format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'total_sales.regex' => 'The total sales format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
        ])->validate();

        $active = 'N';
        if ($request->active == 'on') {
            $active = 'Y';
        }
        $ins = Mst_part::where('id', '=', $id)
            ->update([
                'part_number' => substr($request->part_no,0,5).''.substr($request->part_no,6,strlen($request->part_no)),
                'part_name' => $request->partName,
                'slug' => SlugService::createSlug(Mst_part::class, 'slug', $request->partName),
                'part_type_id' => $request->partType_id,
                'part_category_id' => $request->partCategory_id,
                'brand_id' => $request->brand_id,
                'weight' => $request->weight,
                'weight_id' => $request->weight_id,
                'quantity_type_id' => $request->quantity_id,
                'max_stock' => $request->max_stock,
                'safety_stock' => $request->safety_stock,
                'price_list' => $request->price_list == '' ? null : GlobalFuncHelper::moneyValidate($request->price_list),
                // 'final_price' => $request->final_price == '' ? null : GlobalFuncHelper::moneyValidate($request->final_price),
                // 'avg_cost' => $request->avg_cost == '' ? null : GlobalFuncHelper::moneyValidate($request->avg_cost),
                // 'initial_cost' => $request->initial_cost == '' ? null : GlobalFuncHelper::moneyValidate($request->initial_cost),
                // 'final_cost' => $request->final_cost == '' ? null : GlobalFuncHelper::moneyValidate($request->final_cost),
                // 'total_cost' => $request->total_cost == '' ? null : GlobalFuncHelper::moneyValidate($request->total_cost),
                // 'total_sales' => $request->total_sales == '' ? null : GlobalFuncHelper::moneyValidate($request->total_sales),
                'active' => $active,
                'updated_by' => Auth::user()->id,
            ]);

        // part - brand - brand type
        $upd = Mst_part_brand_type::where([
            'part_id' => $id,
        ])
            ->update([
                'active' => 'N',
                'updated_by' => Auth::user()->id,
            ]);
        for ($i = 0; $i < $request->brand_type_count; $i++) {
            if (!empty($request['brand_type_' . $i])) {
                $q = Mst_part_brand_type::where([
                    'part_id' => $id,
                    'brand_id' => $request->brand_id,
                    'brand_type_id' => $request['brand_type_' . $i],
                ])
                    ->first();
                if ($q) {
                    $upd = Mst_part_brand_type::where([
                        'part_id' => $id,
                        'brand_id' => $request->brand_id,
                        'brand_type_id' => $request['brand_type_' . $i],
                    ])
                        ->update([
                            'active' => 'Y',
                            'updated_by' => Auth::user()->id,
                        ]);
                } else {
                    $ins = Mst_part_brand_type::create([
                        'part_id' => $id,
                        'brand_id' => $request->brand_id,
                        'brand_type_id' => $request['brand_type_' . $i],
                        'active' => 'Y',
                        'updated_by' => Auth::user()->id,
                        'created_by' => Auth::user()->id,
                    ]);
                }
            }
        }

        // part - subtitution
        $upd = Mst_part_subtitution::where([
            'part_id' => $id,
        ])
            ->update([
                'active' => 'N',
                'updated_by' => Auth::user()->id,
            ]);
        for ($i = 0; $i < $request->part_count; $i++) {
            if (!empty($request['part_sub' . $i])) {
                $q = Mst_part_subtitution::where([
                    'part_id' => $id,
                    'part_other_id' => $request['part_sub' . $i],
                ])
                    ->first();
                if ($q) {
                    $upd = Mst_part_subtitution::where([
                        'part_id' => $id,
                        'part_other_id' => $request['part_sub' . $i],
                    ])
                        ->update([
                            'active' => 'Y',
                            'updated_by' => Auth::user()->id,
                        ]);
                } else {
                    $ins = Mst_part_subtitution::create([
                        'part_id' => $id,
                        'part_other_id' => $request['part_sub' . $i],
                        'active' => 'Y',
                        'updated_by' => Auth::user()->id,
                        'created_by' => Auth::user()->id,
                    ]);
                }
            }
        }

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/' . $this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_part $mst_part)
    {
        //
    }
}
