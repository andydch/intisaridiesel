<?php

namespace App\Http\Controllers\ope;

use App\Http\Controllers\Controller;
use App\Models\Mst_province;
use Illuminate\Http\Request;

class SyncProvinceController extends Controller
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
            CURLOPT_URL => env('RAJAONGKIR_URL') . 'province',
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
            $provinceId = $key['province_id'];
            $provinceName = $key['province'];

            if (Mst_province::where('id', '=', $provinceId)->exists()) {
                Mst_province::where('id', '=', $provinceId)
                    ->update([
                        'province_name' => $provinceName,
                        'country_id' => 999,
                        'updated_by' => 'autosync'
                    ]);
            } else {
                Mst_province::create([
                    'id' => $provinceId,
                    'province_name' => $provinceName,
                    'country_id' => 999,
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
     * @param  \App\Models\Mst_province  $mst_province
     * @return \Illuminate\Http\Response
     */
    public function show(Mst_province $mst_province)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_province  $mst_province
     * @return \Illuminate\Http\Response
     */
    public function edit(Mst_province $mst_province)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_province  $mst_province
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mst_province $mst_province)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_province  $mst_province
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_province $mst_province)
    {
        //
    }
}
