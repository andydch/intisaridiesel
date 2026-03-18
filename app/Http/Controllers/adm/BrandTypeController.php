<?php

namespace App\Http\Controllers\adm;

use App\Models\Mst_brand_type;
use App\Models\Mst_global;
use App\Models\Mst_menu_user;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BrandTypeController extends Controller
{
    protected $title = 'Brand Type';
    protected $dataCat = 'brand-type';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_brand_type::where('active','=','Y')
        ->with('brand')
        ->orderBy('brand_type', 'ASC')
        ->get();
        $queryCount = Mst_brand_type::where('active','=','Y')
        ->with('brand')
        ->count();
        $data = [
            'brand_types' => $query,
            'title' => $this->title,
            'uri' => $this->dataCat,
            'rowCount' => $queryCount
        ];

        return view('adm.brand-type.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $brands = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y'
        ])
        ->orderBy('title_ind', 'ASC')
        ->get();
        $data = [
            'brands' => $brands,
            'title' => $this->title,
            'uri' => $this->dataCat
        ];
        return view('adm.brand-type.create', $data);
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
            'menu_id' => 28,
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
            'brand_id' => 'required|numeric',
            'brand_type_name' => 'required|max:512|unique:App\Models\Mst_brand_type,brand_type',
        ], [
            'brand_id.numeric' => 'Please select a valid brand',
            'brand_type_name.required' => $this->title.' Name is required',
            'brand_type_name.unique' => $this->title.' Name has already been taken.',
        ])
        ->validate();

        $ins = Mst_brand_type::create([
            'brand_id' => $request->brand_id,
            'brand_type' => $request->brand_type_name,
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/brand-type');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_brand_type  $mst_brand_type
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $brands = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y'
        ])
        ->orderBy('title_ind', 'ASC')
        ->get();

        $query = Mst_brand_type::where('id', '=', $id)
            ->first();
        if ($query) {
            $data = [
                'brands' => $brands,
                'brandtypes' => $query,
                'title' => $this->title,
                'uri' => $this->dataCat
            ];

            return view('adm.brand-type.show', $data);
        } else {
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_brand_type  $mst_brand_type
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $brands = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y'
        ])
        ->orderBy('title_ind', 'ASC')
        ->get();

        $query = Mst_brand_type::where('id', '=', $id)
            ->first();
        if ($query) {
            $data = [
                'brands' => $brands,
                'brandtypes' => $query,
                'title' => $this->title,
                'uri' => $this->dataCat
            ];

            return view('adm.brand-type.edit', $data);
        } else {
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_brand_type  $mst_brand_type
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 28,
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
            'brand_id' => 'required|numeric',
            'brand_type_name' => 'required|max:512|unique:App\Models\Mst_brand_type,brand_type',
        ], [
            'brand_id.numeric' => 'Please select a valid brand',
            'brand_type_name.required' => $this->title.' Name is required',
            'brand_type_name.unique' => $this->title.' Name has already been taken.',
        ])
        ->validate();

        $ins = Mst_brand_type::where('id','=',$id)
        ->update([
            'brand_id' => $request->brand_id,
            'brand_type' => $request->brand_type_name,
            'active' => 'Y',
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/brand-type');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_brand_type  $mst_brand_type
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_brand_type $mst_brand_type)
    {
        //
    }
}
