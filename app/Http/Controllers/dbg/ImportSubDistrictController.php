<?php

namespace App\Http\Controllers\dbg;

use App\Http\Controllers\Controller;
use App\Imports\dbg\ImportSubDistrict;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use DateTime;

class ImportSubDistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '2048M');

        $date01 = new DateTime(date('Y-m-d H:i:s'));

        $file = 'excel/mst_sub_districts.xlsx';
        if (file_exists($file)) {
            Excel::import(new ImportSubDistrict(), $file);
        }

        $date02 = new DateTime(date('Y-m-d H:i:s'));
        $interval = $date02->diff($date01);
        echo ':' . $interval->format('%H:%i:%s') . PHP_EOL . PHP_EOL;
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
