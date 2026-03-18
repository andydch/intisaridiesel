<?php

namespace App\Http\Controllers\ope;

use App\Http\Controllers\Controller;
use App\Models\Mst_city;
use Illuminate\Http\Request;

class SyncCityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('RAJAONGKIR_URL') . 'city',
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
            $cityId = $key['city_id'];
            $provinceId = $key['province_id'];
            $cityName = $key['city_name'];
            $cityType = $key['type'];
            $postalCode = $key['postal_code'];

            if (Mst_city::where('id', '=', $cityId)->exists()) {
                Mst_city::where('id', '=', $cityId)
                    ->update([
                        'city_name' => $cityName,
                        'province_id' => $provinceId,
                        'city_type' => $cityType,
                        'postal_code' => $postalCode,
                        'updated_by' => 'autosync'
                    ]);
            } else {
                Mst_city::create([
                    'id' => $cityId,
                    'city_name' => $cityName,
                    'province_id' => $provinceId,
                    'city_type' => $cityType,
                    'postal_code' => $postalCode,
                    'created_by' => 'autosync',
                    'updated_by' => 'autosync'
                ]);
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
     * @param  \App\Models\Mst_city  $mst_city
     * @return \Illuminate\Http\Response
     */
    public function show(Mst_city $mst_city)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_city  $mst_city
     * @return \Illuminate\Http\Response
     */
    public function edit(Mst_city $mst_city)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_city  $mst_city
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mst_city $mst_city)
    {
        //
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
