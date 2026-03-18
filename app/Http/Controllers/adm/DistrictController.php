<?php

namespace App\Http\Controllers\adm;

use Illuminate\Http\Request;
use App\Models\Mst_city;
use App\Models\Mst_province;
use App\Models\Mst_country;
use App\Models\Mst_district;
use App\Models\Mst_menu_user;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DistrictController extends Controller
{
    protected $title = 'District';
    protected $dataCat = 'district';

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
        $country_id = 9999;
        $province_id = '';
        $city_id = '';
        $district_id = '';
        $district_name = '';
        $districtCount = 0;

        if (!is_null($request->province_id)) {
            // $country_id = $request->country_id;
            $province_id = $request->province_id;
            $city_id = $request->city_id;
            $district_id = $request->district_id;
            $district_name = $request->district;

            if ($request->district == '') {
                Validator::make($request->all(), [
                    'city_id' => 'required_without:sub_district|numeric',
                    'province_id' => 'required_without:sub_district|numeric',
                    // 'country_id' => 'required_without:sub_district|numeric',
                ], [
                    'city_id.numeric' => 'Please select a valid city',
                    'province_id.numeric' => 'Please select a valid province',
                    // 'country_id.numeric' => 'Please select a valid country',
                ])
                    ->validate();
            }else{
                Validator::make($request->all(), [
                    'district' => 'required_unless:city_id,#,province_id,#',
                ])
                    ->validate();
            }

            $city = Mst_city::where([
                'province_id' => $request->province_id,
                'active' => 'Y'
            ])
                ->orderBy('city_name', 'ASC')
                ->get();

            if ($city_id != '#' && trim($district_name) == '') {
                $district = Mst_district::where([
                    'city_id' => $city_id,
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
                $districtCount = Mst_district::where([
                    'city_id' => $city_id,
                    'active' => 'Y'
                ])
                ->count();
            } else {
                $district = Mst_district::with('city')
                ->where('district_name', 'LIKE', '%' . $district_name . '%')
                ->where('active','=','Y')
                ->orderBy('district_name', 'ASC')
                ->get();
                $districtCount = Mst_district::with('city')
                ->where('district_name', 'LIKE', '%' . $district_name . '%')
                ->where('active','=','Y')
                ->count();
            }
        } else {
            if ($request->district != '') {
                $district = Mst_district::with('city')
                ->where('district_name', 'LIKE', '%' . $district_name . '%')
                ->where('active','=','Y')
                ->orderBy('district_name', 'ASC')
                ->get();
                $districtCount = Mst_district::with('city')
                ->where('district_name', 'LIKE', '%' . $district_name . '%')
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
            // 'district' => $query,
            // 'country' => $country,
            'province' => $province,
            'city' => $city,
            'district' => $district,
            'country_id' => $country_id,
            'province_id' => $province_id,
            'city_id' => $city_id,
            'district_id' => $district_id,
            'district_name' => $district_name,
            'title' => $this->title,
            'uri' => $this->dataCat,
            'rowCount' => $districtCount
        ];

        return view('adm.district.index', $data);
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
        $data = [
            'country' => $country,
            'province' => $province,
            'city' => $city,
            'title' => $this->title,
            'uri' => $this->dataCat,
        ];
        return view('adm.district.create', $data);
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
            'menu_id' => 4,
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
            'districtName' => 'required|max:128',
            'city_id' => 'required|numeric',
            'province_id' => 'required|numeric',
            'country_id' => 'required|numeric',
        ], [
            'city_id.numeric' => 'Please select a valid city',
            'province_id.numeric' => 'Please select a valid province',
            'country_id.numeric' => 'Please select a valid country'
        ])->validate();

        $ins = Mst_district::create([
            'district_name' => $request->districtName,
            'city_id' => $request->city_id,
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/district');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_city  $mst_city
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $query = Mst_district::with('city')
        ->where('id', '=', $id)->first();
        if ($query) {
            $country = Mst_country::where('active', '=', 'Y')
            ->orderBy('country_name', 'ASC')
            ->get();
            $province = Mst_province::where([
                'country_id' => $query->city->country_id,
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
                'province_id' => $query->city->province_id,
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
            $data = [
                'district' => $query,
                'city' => $city,
                'province' => $province,
                'country' => $country,
                'title' => $this->title,
                'uri' => $this->dataCat,
            ];

            return view('adm.district.show', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_city  $mst_city
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = Mst_district::with('city')
        ->where('id', '=', $id)->first();
        if ($query) {
            $country = Mst_country::where('active', '=', 'Y')
            ->orderBy('country_name', 'ASC')
            ->get();
            $province = Mst_province::where([
                'country_id' => $query->city->country_id,
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
                'province_id' => $query->city->province_id,
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
            $data = [
                'district' => $query,
                'city' => $city,
                'province' => $province,
                'country' => $country,
                'title' => $this->title,
                'uri' => $this->dataCat,
            ];

            return view('adm.district.edit', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_city  $mst_city
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 4,
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
            'districtName' => 'required|max:128',
            'city_id' => 'required|numeric',
            'province_id' => 'required|numeric',
            'country_id' => 'required|numeric',
        ], [
            'city_id.numeric' => 'Please select a valid city',
            'province_id.numeric' => 'Please select a valid province',
            'country_id.numeric' => 'Please select a valid country'
        ])->validate();

        $ins = Mst_district::where('id', '=', $id)->update([
            'district_name' => $request->districtName,
            'city_id' => $request->city_id,
            'active' => 'Y',
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/district');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_city  $mst_city
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_city $mst_city)
    {
        //
    }
}
