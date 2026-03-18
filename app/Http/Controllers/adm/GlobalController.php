<?php

namespace App\Http\Controllers\adm;

use App\Http\Controllers\Controller;
use App\Models\Mst_global;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class GlobalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_global::orderBy('data_cat', 'ASC')
            ->orderBy('order_no', 'ASC')
            ->get();
        $data = [
            'globals' => $query
        ];

        return view('adm.global.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('adm.global.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'dataCategory' => 'required|max:32',
            'title_ind' => 'required|max:512',
            'title_eng' => 'required|max:512',
            'order_no' => 'required|numeric',
            'notes' => 'max:1000|nullable',
            'small_desc_ind' => 'max:1000|nullable',
            'small_desc_eng' => 'max:1000|nullable',
            'long_desc_ind' => 'max:8000|nullable',
            'long_desc_eng' => 'max:8000|nullable',
            'value_string' => 'required|max:32',
            'value_numeric' => 'required|numeric',
        ])->validate();

        $active = 'N';
        if ($request->active == 'on') {
            $active = 'Y';
        }

        $ins = Mst_global::create([
            'data_cat' => $request->dataCategory,
            'title_ind' => $request->title_ind,
            'title_eng' => $request->title_eng,
            'order_no' => $request->order_no,
            'notes' => $request->notes,
            'small_desc_ind' => $request->small_desc_ind,
            'small_desc_eng' => $request->small_desc_eng,
            'long_desc_ind' => $request->long_desc_ind,
            'long_desc_eng' => $request->long_desc_eng,
            'string_val' => $request->value_string,
            'numeric_val' => $request->value_numeric,
            'active' => $active,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/mst-global');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_global  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function show(Mst_global $mst_global)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_global  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = Mst_global::where([
            'id' => $id
        ])
            ->first();
        $data = [
            'globals' => $query
        ];
        return view('adm.global.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_global  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Validator::make($request->all(), [
            'dataCategory' => 'required|max:32',
            'title_ind' => 'required|max:512',
            'title_eng' => 'required|max:512',
            'order_no' => 'required|numeric',
            'notes' => 'max:1000|nullable',
            'small_desc_ind' => 'max:1000|nullable',
            'small_desc_eng' => 'max:1000|nullable',
            'long_desc_ind' => 'max:8000|nullable',
            'long_desc_eng' => 'max:8000|nullable',
            'value_string' => 'required|max:32',
            'value_numeric' => 'required|numeric',
        ])->validate();

        $active = 'N';
        if ($request->active == 'on') {
            $active = 'Y';
        }

        $ins = Mst_global::where([
            'id' => $id
        ])->update([
            'data_cat' => $request->dataCategory,
            'title_ind' => $request->title_ind,
            'title_eng' => $request->title_eng,
            'slug' => SlugService::createSlug(Mst_global::class, 'slug', $request->title_eng),
            'order_no' => $request->order_no,
            'notes' => $request->notes,
            'small_desc_ind' => $request->small_desc_ind,
            'small_desc_eng' => $request->small_desc_eng,
            'long_desc_ind' => $request->long_desc_ind,
            'long_desc_eng' => $request->long_desc_eng,
            'string_val' => $request->value_string,
            'numeric_val' => $request->value_numeric,
            'active' => $active,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/mst-global');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_global  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_global $mst_global)
    {
        //
    }
}
