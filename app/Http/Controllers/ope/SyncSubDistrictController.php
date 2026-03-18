<?php

namespace App\Http\Controllers\ope;

use App\Http\Controllers\Controller;
use App\Models\Mst_sub_district;
use App\Models\Mst_city;
use Illuminate\Http\Request;

class SyncSubDistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        ini_set('max_execution_time', 2400);

        $queryCity = Mst_city::get();
        foreach ($queryCity as $val) {

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => env('RAJAONGKIR_URL') . 'subdistrict?city=' . $val['id'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'key: ' . env('RAJAONGKIR_API')
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $data = json_decode($response, true);
            foreach ($data['rajaongkir']['results'] as $key) {
                $subdistrict_id = $key['subdistrict_id'];
                $city_id = $key['city_id'];
                $subdistrict_name = $key['subdistrict_name'];

                if (Mst_sub_district::where('id', '=', $subdistrict_id)->exists()) {
                    Mst_sub_district::where('id', '=', $subdistrict_id)
                        ->update([
                            'sub_district_name' => $subdistrict_name,
                            'city_id' => $city_id,
                            'updated_by' => 'autosync'
                        ]);
                } else {
                    Mst_sub_district::create([
                        'id' => $subdistrict_id,
                        'sub_district_name' => $subdistrict_name,
                        'city_id' => $city_id,
                        'created_by' => 'autosync',
                        'updated_by' => 'autosync'
                    ]);
                }
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_sub_district  $mst_sub_district
     * @return \Illuminate\Http\Response
     */
    public function show(Mst_sub_district $mst_sub_district)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_sub_district  $mst_sub_district
     * @return \Illuminate\Http\Response
     */
    public function edit(Mst_sub_district $mst_sub_district)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_sub_district  $mst_sub_district
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mst_sub_district $mst_sub_district)
    {
        //
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
