<?php

namespace App\Http\Controllers\dbg;

use App\Models\Mst_part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\QueryDataTable;
use Yajra\DataTables\Facades\DataTables;

class TestDatatableController extends Controller
{
    // public function data()
    // {
    //     return DataTables::of(Mst_part::query())->toJson();
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($param=null,Request $request)
    {
        // die($param);
        // die(urldecode($param));
        if ($request->ajax()) {
            $parameter = explode('::',urldecode($param));
            // $sql = Mst_part::query();
            $sql = Mst_part::where('part_name','like','%'.$parameter[0].'%')
            ->where('part_number','like','%'.$parameter[1].'%')
            ->where('active','=','Y');
            return DataTables::of($sql)
            ->addColumn('del_checkbox', function ($sql) {
                $button =   '<div>
                                <input type="checkbox" id="'.$sql->id.'"/>
                            </div>';
                return $button;
            })
            ->rawColumns(['del_checkbox'])
            // ->filter(function ($query) {
            //     if (request()->has('part_name')) {
            //         $query->where('part_name','like',"%".request('part_name')."%");
            //     }

            //     // if (request()->has('email')) {
            //     //     $query->where('email','like',"%".request('email')."%");
            //     // }
            // })
            ->toJson();
        }

        $data = [
            'param' => $param,
        ];
        return view('dbg.datatable-ori',$data);
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
        // echo $request->part_name;
        return redirect(route('part.index').'/'.urlencode($request->part_name.'::'.$request->part_number));
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
    public function update(Request $request,$id)
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
