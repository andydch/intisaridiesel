<?php

namespace App\Http\Controllers\adm;

use App\Http\Controllers\Controller;
use App\Models\Mst_global;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class CourierTypeController extends Controller
{
    protected $title = 'Courier Type';
    protected $dataCat = 'courier-type';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_global::where([
            'data_cat' => $this->dataCat,
            'active' => 'Y'
        ])
        ->orderBy('title_ind', 'ASC')
        ->get();
        $queryCount = Mst_global::where([
            'data_cat' => $this->dataCat,
            'active' => 'Y'
        ])
        ->count();
        $data = [
            'globals' => $query,
            'title' => $this->title,
            'uri' => $this->dataCat,
            'rowCount' => $queryCount
        ];

        return view('adm.global.courier-type-index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            'title' => $this->title,
            'uri' => $this->dataCat
        ];
        return view('adm.global.courier-type-create', $data);
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
            'title_ind' => 'required|max:512',
            // 'value_string' => 'max:32|required',
        ], [
            'title_ind.required' => 'Courier Type is required',
            'value_string.required' => 'Gender Symbol is required'
        ])->validate();

        $ins = Mst_global::create([
            'data_cat' => $this->dataCat,
            'title_ind' => $request->title_ind,
            'title_eng' => $request->title_ind,
            'slug' => SlugService::createSlug(Mst_global::class, 'slug', $request->title_ind),
            'order_no' => 1,
            'notes' => null,
            'small_desc_ind' => null,
            'small_desc_eng' => null,
            'long_desc_ind' => null,
            'long_desc_eng' => null,
            'string_val' => strtoupper(substr($request->title_ind,0,3)),
            'numeric_val' => 0,
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->dataCat);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_global  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $query = Mst_global::where([
            'slug' => urldecode($slug)
        ])
            ->first();
        $data = [
            'globals' => $query,
            'title' => $this->title,
            'uri' => $this->dataCat
        ];
        return view('adm.global.courier-type-show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_global  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $query = Mst_global::where([
            'slug' => urldecode($slug)
        ])
            ->first();
        $data = [
            'globals' => $query,
            'title' => $this->title,
            'uri' => $this->dataCat
        ];
        return view('adm.global.courier-type-edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_global  $mst_global
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        Validator::make($request->all(), [
            'title_ind' => 'required|max:512',
            // 'value_string' => 'max:32|required',
        ], [
            'title_ind.required' => 'Courier Type is required',
            'value_string.required' => 'Gender Symbol is required'
        ])->validate();

        $upd = Mst_global::where([
            'slug' => urldecode($slug)
        ])->update([
            'data_cat' => $this->dataCat,
            'title_ind' => $request->title_ind,
            'title_eng' => $request->title_ind,
            'slug' => SlugService::createSlug(Mst_global::class, 'slug', $request->title_ind),
            'order_no' => 1,
            'notes' => null,
            'small_desc_ind' => null,
            'small_desc_eng' => null,
            'long_desc_ind' => null,
            'long_desc_eng' => null,
            'string_val' => strtoupper(substr($request->title_ind,0,3)),
            'numeric_val' => 0,
            'active' => 'Y',
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->dataCat);
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
