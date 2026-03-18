<?php

namespace App\Http\Controllers\dbg;

use App\Http\Controllers\Controller;
use App\Models\Mst_customer;
use App\Models\Auto_inc;
use Illuminate\Http\Request;

class WhereController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $query = Mst_customer::orWhere([
        //     ['name', 'like', '%cust%'],
        //     ['name', 'like', '%sumber%'],
        // ])->get();
        // dd($query);

        $identityName = 'tx_supplier_order';
        $autoInc = Auto_inc::where([
            'identity_name' => $identityName,
        ])
            ->first();
        // dd($autoInc);
        echo $autoInc->updated_at . '<br/>';
        $date = date_create($autoInc->updated_at);
        echo date_format($date, "Y");
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
     * @param  \App\Models\Mst_customer  $mst_customer
     * @return \Illuminate\Http\Response
     */
    public function show(Mst_customer $mst_customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_customer  $mst_customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Mst_customer $mst_customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_customer  $mst_customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mst_customer $mst_customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_customer  $mst_customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_customer $mst_customer)
    {
        //
    }
}
