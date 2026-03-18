<?php

namespace App\Http\Controllers\adm;

use App\Http\Controllers\Controller;
use App\Models\Mst_global;
use App\Models\Mst_menu_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class VatController extends Controller
{
    protected $title = 'VAT';
    protected $dataCat = 'vat';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_global::where([
            'data_cat' => $this->dataCat
        ])
        ->orderBy('data_cat', 'ASC')
        ->orderBy('order_no', 'ASC')
        ->get();
        $data = [
            'globals' => $query,
            'title' => $this->title,
            'uri' => $this->dataCat
        ];

        return view('adm.global.vat-index', $data);
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
        return view('adm.global.create-per-category', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 31,
            'user_id' => Auth::user()->id,
            'user_access_read' => 'Y',
        ])
        ->first();
        if (!$qCheckPriv){
            return redirect()
            ->back()
            ->withInput()
            ->with('status-error', ENV('ERR_MSG_02')?ENV('ERR_MSG_02'):'You are not allowed to access this page!');
        }
        
        Validator::make($request->all(), [
            'title_ind' => 'required|max:512',
            'order_no' => 'required|numeric',
            'notes' => 'max:1000|nullable',
            'small_desc_ind' => 'max:1000|nullable',
            'long_desc_ind' => 'max:8000|nullable',
            'value_string' => 'max:32|required_without:value_numeric',
            'value_numeric' => 'required_without:value_string|numeric',
        ], [
            'title_ind.required' => 'Title is required'
        ])
        ->validate();

        $ins = Mst_global::create([
            'data_cat' => $this->dataCat,
            'title_ind' => $request->title_ind,
            'title_eng' => $request->title_ind,
            'order_no' => $request->order_no,
            'notes' => $request->notes,
            'small_desc_ind' => $request->small_desc_ind,
            'small_desc_eng' => $request->small_desc_ind,
            'long_desc_ind' => $request->long_desc_ind,
            'long_desc_eng' => $request->long_desc_ind,
            'string_val' => $request->value_string,
            'numeric_val' => is_numeric($request->value_numeric) ? $request->value_numeric : 0,
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/' . $this->dataCat);
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
        return view('adm.global.vat-show', $data);
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
        return view('adm.global.vat-edit', $data);
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
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 31,
            'user_id' => Auth::user()->id,
            'user_access_read' => 'Y',
        ])
        ->first();
        if (!$qCheckPriv){
            return redirect()
            ->back()
            ->withInput()
            ->with('status-error', ENV('ERR_MSG_02')?ENV('ERR_MSG_02'):'You are not allowed to access this page!');
        }

        Validator::make($request->all(), [
            'title_ind' => 'required|max:512',
            'value_numeric' => 'required|numeric',
        ], [
            'title_ind.required' => 'Vat Title is required',
            'value_numeric.required' => 'VAT Value is required',
            'value_numeric.numeric' => 'VAT Value must be numeric',
        ])
        ->validate();

        $ins = Mst_global::where([
            'slug' => urldecode($slug)
        ])->update([
            'data_cat' => $this->dataCat,
            'title_ind' => $request->title_ind,
            'title_eng' => $request->title_ind,
            'slug' => SlugService::createSlug(Mst_global::class, 'slug', $request->title_ind),
            'order_no' => 1,
            'notes' => '-',
            'small_desc_ind' => '-',
            'small_desc_eng' => '-',
            'long_desc_ind' => '-',
            'long_desc_eng' => '-',
            'string_val' => null,
            'numeric_val' => is_numeric($request->value_numeric) ? $request->value_numeric : 0,
            'active' => 'Y',
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/' . $this->dataCat);
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
