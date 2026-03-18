<?php

namespace App\Http\Controllers\dbg;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// use Illuminate\Support\Facades\Auth;

class RememberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // // tidak berfungsi
        // if (Auth::viaRemember()) {
        //     echo 'konek';
        // } else {
        //     echo 'ga konek';
        // }
        // echo $request->u;

        // Creates DateTime objects
        $datetime1 = date_create(date("Y-m-d"));
        // $datetime1 = date_create('2018-10-10');
        echo date_format($datetime1,"Y-m-d").'<br/>';
        $datetime2 = date_create('2018-09-10');
        echo date_format($datetime2,"Y-m-d").'<br/>';

        // Calculates the difference between DateTime objects
        $interval = date_diff($datetime1, $datetime2);

        // Printing result in years & months format
        // echo $interval->format('%R%y years %m months');

        $diff_months = $interval->format('%R%y years %m months %d days');
        echo $diff_months;
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
