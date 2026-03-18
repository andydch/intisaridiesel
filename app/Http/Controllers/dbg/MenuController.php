<?php

namespace App\Http\Controllers\dbg;

use App\Models\Mst_menu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $title = 'kwitansi';
        // $uri_folder_title = 'kwitansi';

        // $title = 'Tagihan Supplier';
        // $uri_folder_title = 'tx/tagihan-supplier';

        $title = 'Customer Payment Status';
        $uri_folder_title = 'rpt/customer-payment-status';

        $order_no = 124;

        $ins = Mst_menu::create([
            'name' => 'Report '.$title,
            // 'name' => 'transaction '.$title,
            // 'name' => 'transaction approval '.$title,
            'uri' => $uri_folder_title,
            // 'name' => 'admin '.$title,
            // 'uri' => 'adm/'.$uri_folder_title,
            'order_no' => $order_no,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        // echo '[admin '.$title.']['.$order_no.'] done!';
        // echo '[transaction '.$title.']['.$ins->id.'] done!';
        echo '[report '.$title.']['.$ins->id.'] done!';
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
     * @param  \App\Models\Mst_menu  $mst_menu
     * @return \Illuminate\Http\Response
     */
    public function show(Mst_menu $mst_menu)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_menu  $mst_menu
     * @return \Illuminate\Http\Response
     */
    public function edit(Mst_menu $mst_menu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_menu  $mst_menu
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mst_menu $mst_menu)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_menu  $mst_menu
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_menu $mst_menu)
    {
        //
    }
}
