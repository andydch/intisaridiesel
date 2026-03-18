<?php

namespace App\Http\Controllers\adm;

use App\Models\Mst_city;
use App\Models\Mst_country;
use App\Models\Mst_province;
use App\Models\Mst_menu_user;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    protected $title = 'City';
    protected $dataCat = 'city';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_city::with('province')
        ->where('active','=','Y')
        ->orderBy('city_name', 'ASC')
        ->get();
        $queryCount = Mst_city::count();
        $data = [
            'city' => $query,
            'title' => $this->title,
            'uri' => $this->dataCat,
            'rowCount' => $queryCount
        ];

        return view('adm.city.index', $data);
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
        $province = [];
        if (old('country_id')) {
            if (old('country_id') == 9999) {
                $province = Mst_province::where([
                    'country_id' => 9999,
                    'active' => 'Y'
                ])
                    ->orderBy('province_name', 'ASC')
                    ->get();
            } else {
                $province = Mst_province::where([
                    'id' => 9999,
                    'active' => 'Y'
                ])
                    ->orderBy('province_name', 'ASC')
                    ->get();
            }
        }
        $data = [
            'country' => $country,
            'province' => $province,
            'title' => $this->title,
            'uri' => $this->dataCat,
        ];
        return view('adm.city.create', $data);
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
            'menu_id' => 3,
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
            'cityName' => 'required|max:128',
            'city_type' => 'required',
            'province_id' => 'required|numeric',
            'country_id' => 'required|numeric',
        ], [
            'province_id.numeric' => 'Please select a valid province',
            'country_id.numeric' => 'Please select a valid country'
        ])->validate();

        $ins = Mst_city::create([
            'city_name' => $request->cityName,
            'province_id' => $request->province_id,
            'country_id' => $request->country_id,
            'city_type' => $request->city_type,
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/city');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_city  $mst_city
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $query = Mst_city::where('id', '=', $id)
        ->first();
        if ($query) {
            $country = Mst_country::where('active', '=', 'Y')
                ->orderBy('country_name', 'ASC')
                ->get();
            if ($query->country_id == 9999) {
                $province = Mst_province::where([
                    'country_id' => 9999,
                    'active' => 'Y'
                ])
                    ->orderBy('province_name', 'ASC')
                    ->get();
            } else {
                $province = Mst_province::where([
                    'id' => 9999,
                    'active' => 'Y'
                ])
                    ->orderBy('province_name', 'ASC')
                    ->get();
            }
            if (old('country_id')) {
                if (old('country_id') == 9999) {
                    $province = Mst_province::where([
                        'country_id' => 9999,
                        'active' => 'Y'
                    ])
                        ->orderBy('province_name', 'ASC')
                        ->get();
                } else {
                    $province = Mst_province::where([
                        'id' => 9999,
                        'active' => 'Y'
                    ])
                        ->orderBy('province_name', 'ASC')
                        ->get();
                }
            }
            $data = [
                'city' => $query,
                'province' => $province,
                'country' => $country,
                'title' => $this->title,
                'uri' => $this->dataCat,
            ];

            return view('adm.city.show', $data);
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
        $query = Mst_city::where('id', '=', $id)
        ->first();
        if ($query) {
            $country = Mst_country::where('active', '=', 'Y')
                ->orderBy('country_name', 'ASC')
                ->get();
            if ($query->country_id == 9999) {
                $province = Mst_province::where([
                    'country_id' => 9999,
                    'active' => 'Y'
                ])
                    ->orderBy('province_name', 'ASC')
                    ->get();
            } else {
                $province = Mst_province::where([
                    'id' => 9999,
                    'active' => 'Y'
                ])
                    ->orderBy('province_name', 'ASC')
                    ->get();
            }
            if (old('country_id')) {
                if (old('country_id') == 9999) {
                    $province = Mst_province::where([
                        'country_id' => 9999,
                        'active' => 'Y'
                    ])
                        ->orderBy('province_name', 'ASC')
                        ->get();
                } else {
                    $province = Mst_province::where([
                        'id' => 9999,
                        'active' => 'Y'
                    ])
                        ->orderBy('province_name', 'ASC')
                        ->get();
                }
            }
            $data = [
                'city' => $query,
                'province' => $province,
                'country' => $country,
                'title' => $this->title,
                'uri' => $this->dataCat,
            ];

            return view('adm.city.edit', $data);
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
            'menu_id' => 3,
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
            'cityName' => 'required|max:128',
            'city_type' => 'required',
            'province_id' => 'required|numeric',
            'country_id' => 'required|numeric',
        ], [
            'province_id.numeric' => 'Please select a valid province',
            'country_id.numeric' => 'Please select a valid country'
        ])->validate();

        $ins = Mst_city::where('id', '=', $id)->update([
            'city_name' => $request->cityName,
            'province_id' => $request->province_id,
            'country_id' => $request->country_id,
            'city_type' => $request->city_type,
            'active' => 'Y',
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/city');
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
