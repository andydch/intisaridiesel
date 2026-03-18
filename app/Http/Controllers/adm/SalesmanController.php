<?php

namespace App\Http\Controllers\adm;

use App\Models\Mst_city;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Rules\PhoneNumber;
use App\Models\Mst_district;
use App\Models\Mst_province;
use App\Models\Mst_salesman;
use Illuminate\Http\Request;
use App\Models\Mst_sub_district;
use App\Helpers\GlobalFuncHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class SalesmanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_salesman::orderBy('name', 'ASC')
            ->get();
        $data = [
            'salesmans' => $query,
        ];

        return view('adm.salesman.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $province = Mst_province::where([
            'country_id' => 9999,
            'active' => 'Y'
        ])
            ->orderBy('province_name', 'ASC')
            ->get();
        $city = [];
        if (old('province_id')) {
            $city = Mst_city::where([
                'province_id' => old('province_id'),
                'active' => 'Y'
            ])
                ->orderBy('city_name', 'ASC')
                ->get();
        }
        $districts = [];
        if (old('city_id')) {
            $districts = Mst_district::where([
                'city_id' => old('city_id'),
                'active' => 'Y'
            ])
                ->orderBy('district_name', 'ASC')
                ->get();
        }
        $subdistricts = [];
        if (old('district_id')) {
            $subdistricts = Mst_sub_district::where([
                'district_id' => old('district_id'),
                'active' => 'Y'
            ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
        }
        $branch = Mst_branch::where([
            'active' => 'Y'
        ])
            ->orderBy('name', 'ASC')
            ->get();
        $gender = Mst_global::where([
            'data_cat' => 'gender',
            'active' => 'Y'
        ])
            ->orderBy('order_no', 'ASC')
            ->get();
        $data = [
            'province' => $province,
            'cities' => $city,
            'districts' => $districts,
            'subdistricts' => $subdistricts,
            'branch' => $branch,
            'gender' => $gender
        ];
        return view('adm.salesman.create', $data);
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
            'salesmanName' => 'required|max:255',
            'branch_id' => 'required|numeric',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'subdistrict_id' => 'required|numeric',
            'address' => 'required|max:1024',
            'postcode' => 'required|max:6',
            'idNo' => 'required|max:32',
            'sales_email' => 'required|max:64|email:rfc',
            'gender_id' => 'required|numeric',
            'phone1' => ['required', 'max:32', new PhoneNumber],
            'join_date' => 'required|date',
            'birth_date' => 'required|date',
            'sales_target' => 'required|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
        ], [
            'branch_id.numeric' => 'Please select a valid branch',
            'province_id.numeric' => 'Please select a valid province',
            'city_id.numeric' => 'Please select a valid city',
            'district_id.numeric' => 'Please select a valid district',
            'subdistrict_id.numeric' => 'Please select a valid sub district',
            'gender_id.numeric' => 'Please select a valid gender',
            'sales_target.regex' => 'The sales target format is invalid. ' .
                'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
        ])->validate();

        $active = 'N';
        if ($request->active == 'on') {
            $active = 'Y';
        }

        $ins = Mst_salesman::create([
            'name' => $request->salesmanName,
            'branch_id' => $request->branch_id,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district_id' => $request->district_id,
            'sub_district_id' => $request->subdistrict_id,
            'address' => $request->address,
            'post_code' => $request->postcode,
            'id_no' => $request->idNo,
            'email' => $request->sales_email,
            'gender_id' => $request->gender_id,
            'mobilephone' => $request->phone1,
            'join_date' => $request->join_date,
            'birth_date' => $request->birth_date,
            'sales_target' => GlobalFuncHelper::moneyValidate($request->sales_target),
            'active' => $active,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/salesman');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_salesman  $mst_salesman
     * @return \Illuminate\Http\Response
     */
    public function show(Mst_salesman $mst_salesman)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_salesman  $mst_salesman
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = Mst_salesman::where('id', '=', $id)
            ->first();
        if ($query) {
            $province = Mst_province::where([
                'country_id' => 9999,
                'active' => 'Y'
            ])
                ->orderBy('province_name', 'ASC')
                ->get();
            $city = Mst_city::where([
                'province_id' => $query->province_id,
                'active' => 'Y'
            ])
                ->orderBy('city_name', 'ASC')
                ->get();
            if (old('province_id')) {
                $city = Mst_city::where([
                    'province_id' => old('province_id'),
                    'active' => 'Y'
                ])
                    ->orderBy('city_name', 'ASC')
                    ->get();
            }
            $districts = Mst_district::where([
                'city_id' => $query->city_id,
                'active' => 'Y'
            ])
                ->orderBy('district_name', 'ASC')
                ->get();
            if (old('city_id')) {
                $districts = Mst_district::where([
                    'city_id' => old('city_id'),
                    'active' => 'Y'
                ])
                    ->orderBy('district_name', 'ASC')
                    ->get();
            }
            $subdistricts = Mst_sub_district::where([
                'district_id' => $query->district_id,
                'active' => 'Y'
            ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
            if (old('district_id')) {
                $subdistricts = Mst_sub_district::where([
                    'district_id' => old('district_id'),
                    'active' => 'Y'
                ])
                    ->orderBy('sub_district_name', 'ASC')
                    ->get();
            }
            $branch = Mst_branch::where([
                'active' => 'Y'
            ])
                ->orderBy('name', 'ASC')
                ->get();
            $gender = Mst_global::where([
                'data_cat' => 'gender',
                'active' => 'Y'
            ])
                ->orderBy('order_no', 'ASC')
                ->get();
            $data = [
                'province' => $province,
                'cities' => $city,
                'districts' => $districts,
                'subdistricts' => $subdistricts,
                'branch' => $branch,
                'gender' => $gender,
                'salesman' => $query
            ];
            return view('adm.salesman.edit', $data);
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
     * @param  \App\Models\Mst_salesman  $mst_salesman
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Validator::make($request->all(), [
            'salesmanName' => 'required|max:255',
            'branch_id' => 'required|numeric',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'subdistrict_id' => 'required|numeric',
            'address' => 'required|max:1024',
            'postcode' => 'required|max:6',
            'idNo' => 'required|max:32',
            'sales_email' => 'required|max:64|email:rfc',
            'gender_id' => 'required|numeric',
            'phone1' => ['required', 'max:32', new PhoneNumber],
            'join_date' => 'required|date',
            'birth_date' => 'required|date',
            'sales_target' => 'required|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
        ], [
            'branch_id.numeric' => 'Please select a valid branch',
            'province_id.numeric' => 'Please select a valid province',
            'city_id.numeric' => 'Please select a valid city',
            'district_id.numeric' => 'Please select a valid district',
            'subdistrict_id.numeric' => 'Please select a valid sub district',
            'gender_id.numeric' => 'Please select a valid gender',
            'sales_target.regex' => 'The sales target format is invalid. ' .
                'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
        ])->validate();

        $active = 'N';
        if ($request->active == 'on') {
            $active = 'Y';
        }

        $ins = Mst_salesman::where('id', '=', $id)
            ->update([
                'name' => $request->salesmanName,
                'slug' => SlugService::createSlug(Mst_salesman::class, 'slug', $request->salesmanName),
                'branch_id' => $request->branch_id,
                'province_id' => $request->province_id,
                'city_id' => $request->city_id,
                'district_id' => $request->district_id,
                'sub_district_id' => $request->subdistrict_id,
                'address' => $request->address,
                'post_code' => $request->postcode,
                'id_no' => $request->idNo,
                'email' => $request->sales_email,
                'gender_id' => $request->gender_id,
                'mobilephone' => $request->phone1,
                'join_date' => $request->join_date,
                'birth_date' => $request->birth_date,
                'sales_target' => GlobalFuncHelper::moneyValidate($request->sales_target),
                'active' => $active,
                'updated_by' => Auth::user()->id
            ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/salesman');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_salesman  $mst_salesman
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_salesman $mst_salesman)
    {
        //
    }
}
