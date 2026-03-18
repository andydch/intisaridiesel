<?php

namespace App\Http\Controllers\adm;

use Illuminate\Http\Request;
use App\Models\Mst_city;
use App\Models\Mst_province;
use App\Models\Mst_country;
use App\Models\Mst_district;
use App\Models\Mst_sub_district;
use App\Models\Mst_menu_user;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubDistrictController extends Controller
{
    protected $title = 'Sub District';
    protected $dataCat = 'subdistrict';

    public function  __construct()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 1800);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $city = [];
        $district = [];
        $subdistrict = [];
        $country_id = 9999;
        $province_id = '';
        $city_id = '';
        $district_id = '';
        $sub_district_name = '';
        $subdistrictCount = 0;

        $city = Mst_city::where([
            'province_id' => old('province_id')?old('province_id'):0,
            'active' => 'Y'
        ])
        ->orderBy('city_name', 'ASC')
        ->get();
        $district = Mst_district::where([
            'city_id' => old('city_id')?old('city_id'):0,
            'active' => 'Y'
        ])
        ->orderBy('district_name', 'ASC')
        ->get();

        if (!is_null($request->province_id)) {
            // $country_id = $request->country_id;
            $province_id = $request->province_id;
            $city_id = $request->city_id;
            $district_id = $request->district_id;
            $sub_district_name = $request->sub_district;

            if ($request->sub_district == '') {
                Validator::make($request->all(), [
                    'district_id' => 'required_without:sub_district|numeric',
                    'city_id' => 'required_without:sub_district|numeric',
                    'province_id' => 'required_without:sub_district|numeric',
                    // 'country_id' => 'required_without:sub_district|numeric',
                ], [
                    'district_id.numeric' => 'Please select a valid district',
                    'city_id.numeric' => 'Please select a valid city',
                    'province_id.numeric' => 'Please select a valid province',
                    // 'country_id.numeric' => 'Please select a valid country',
                ])
                ->validate();
            } else {
                Validator::make($request->all(), [
                    'sub_district' => 'required_unless:district_id,#,city_id,#,province_id,#',
                ])
                ->validate();
            }

            $city = Mst_city::where([
                'province_id' => $request->province_id,
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();
            $district = Mst_district::where([
                'city_id' => $city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();

            if ($district_id != '#' && trim($sub_district_name) == '') {
                $subdistrict = Mst_sub_district::with('district')
                ->where([
                    'district_id' => $district_id,
                    'active' => 'Y'
                ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
                $subdistrictCount = Mst_sub_district::with('district')
                ->where([
                    'district_id' => $district_id,
                    'active' => 'Y'
                ])
                ->count();
            } else {
                $subdistrict = Mst_sub_district::with('district')
                ->where('sub_district_name', 'LIKE', '%' . $sub_district_name . '%')
                ->where('active','=','Y')
                ->orderBy('sub_district_name', 'ASC')
                ->get();
                $subdistrictCount = Mst_sub_district::with('district')
                ->where('sub_district_name', 'LIKE', '%' . $sub_district_name . '%')
                ->where('active','=','Y')
                ->count();
            }
        } else {            
            if ($request->sub_district != '') {
                $subdistrict = Mst_sub_district::with('district')
                ->where('sub_district_name', 'LIKE', '%' . $sub_district_name . '%')
                ->where('active','=','Y')
                ->orderBy('sub_district_name', 'ASC')
                ->get();
                $subdistrictCount = Mst_sub_district::with('district')
                ->where('sub_district_name', 'LIKE', '%' . $sub_district_name . '%')
                ->where('active','=','Y')
                ->count();
            }
        }

        $province = Mst_province::where([
            'country_id' => $country_id,
            'active' => 'Y'
        ])
        ->orderBy('province_name', 'ASC')
        ->get();
        $data = [
            'subdistrict' => $subdistrict,
            // 'country' => $country,
            'province' => $province,
            'city' => $city,
            'district' => $district,
            'country_id' => $country_id,
            'province_id' => $province_id,
            'city_id' => $city_id,
            'district_id' => $district_id,
            'sub_district_name' => $sub_district_name,
            'title' => $this->title,
            'uri' => $this->dataCat,
            'rowCount' => $subdistrictCount,
            'reqs' => $request,
        ];

        return view('adm.subdistrict.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $country = Mst_country::where('active', '=', 'Y')
        ->orderBy('country_name', 'ASC')
        ->get();
        $province = Mst_province::where([
            'country_id' => 9999,
            'active' => 'Y'
        ])
        ->orderBy('province_name', 'ASC')
        ->get();
        if (old('country_id')) {
            $province = Mst_province::where([
                'country_id' => old('country_id'),
                'active' => 'Y'
            ])
            ->orderBy('province_name', 'ASC')
            ->get();
        }
        $city = [];
        if (old('province_id')) {
            $city = Mst_city::where([
                'province_id' => old('province_id'),
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();
        }
        $district = [];
        if (old('city_id')) {
            $district = Mst_district::where([
                'city_id' => old('city_id'),
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
        }
        $data = [
            'country' => $country,
            'province' => $province,
            'city' => $city,
            'district' => $district,
            'title' => $this->title,
            'uri' => $this->dataCat,
        ];
        return view('adm.subdistrict.create', $data);
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
            'menu_id' => 5,
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

        Validator::make($request->all(), [
            'subdistrictName' => 'required|max:128',
            'postcode' => 'required|max:6',
            'district_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'province_id' => 'required|numeric',
            'country_id' => 'required|numeric',
        ], [
            'district_id.numeric' => 'Please select a valid district',
            'city_id.numeric' => 'Please select a valid city',
            'province_id.numeric' => 'Please select a valid province',
            'country_id.numeric' => 'Please select a valid country'
        ])->validate();

        $ins = Mst_sub_district::create([
            'sub_district_name' => $request->subdistrictName,
            'post_code' => $request->postcode,
            'district_id' => $request->district_id,
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/subdistrict');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_sub_district  $mst_sub_district
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $query = Mst_sub_district::with('district')
        ->where('id', '=', $id)
        ->first();
        if ($query) {
            $country = Mst_country::where('active', '=', 'Y')
            ->orderBy('country_name', 'ASC')
            ->get();
            $province = Mst_province::where([
                'country_id' => $query->district->city->country_id,
                'active' => 'Y'
            ])
            ->orderBy('province_name', 'ASC')
            ->get();
            if (old('country_id')) {
                $province = Mst_province::where([
                    'country_id' => old('country_id'),
                    'active' => 'Y'
                ])
                ->orderBy('province_name', 'ASC')
                ->get();
            }
            $city = Mst_city::where([
                'province_id' => $query->district->city->province_id,
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
            $district = Mst_district::where([
                'city_id' => $query->district->city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
            if (old('city_id')) {
                $district = Mst_district::where([
                    'city_id' => old('city_id'),
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
            }
            $data = [
                'subdistrict' => $query,
                'district' => $district,
                'city' => $city,
                'province' => $province,
                'country' => $country,
                'title' => $this->title,
            'uri' => $this->dataCat,
            ];

            return view('adm.subdistrict.show', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_sub_district  $mst_sub_district
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = Mst_sub_district::with('district')
        ->where('id', '=', $id)
        ->first();
        if ($query) {
            $country = Mst_country::where('active', '=', 'Y')
            ->orderBy('country_name', 'ASC')
            ->get();
            $province = Mst_province::where([
                'country_id' => $query->district->city->country_id,
                'active' => 'Y'
            ])
            ->orderBy('province_name', 'ASC')
            ->get();
            if (old('country_id')) {
                $province = Mst_province::where([
                    'country_id' => old('country_id'),
                    'active' => 'Y'
                ])
                ->orderBy('province_name', 'ASC')
                ->get();
            }
            $city = Mst_city::where([
                'province_id' => $query->district->city->province_id,
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
            $district = Mst_district::where([
                'city_id' => $query->district->city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
            if (old('city_id')) {
                $district = Mst_district::where([
                    'city_id' => old('city_id'),
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
            }
            $data = [
                'subdistrict' => $query,
                'district' => $district,
                'city' => $city,
                'province' => $province,
                'country' => $country,
                'title' => $this->title,
            'uri' => $this->dataCat,
            ];

            return view('adm.subdistrict.edit', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_sub_district  $mst_sub_district
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 5,
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
        
        Validator::make($request->all(), [
            'subdistrictName' => 'required|max:128',
            'postcode' => 'required|max:6',
            'district_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'province_id' => 'required|numeric',
            'country_id' => 'required|numeric',
        ], [
            'district_id.numeric' => 'Please select a valid district',
            'city_id.numeric' => 'Please select a valid city',
            'province_id.numeric' => 'Please select a valid province',
            'country_id.numeric' => 'Please select a valid country'
        ])->validate();

        $upd = Mst_sub_district::where('id', '=', $id)
        ->update([
            'sub_district_name' => $request->subdistrictName,
            'post_code' => $request->postcode,
            'district_id' => $request->district_id,
            'active' => 'Y',
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/subdistrict');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_sub_district  $mst_sub_district
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_sub_district $mst_sub_district)
    {
        //
    }
}
