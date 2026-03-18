<?php

namespace App\Http\Controllers\adm;

use App\Models\Mst_province;
use App\Models\Mst_country;
use App\Models\Mst_menu_user;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProvinceController extends Controller
{
    protected $title = 'Province';
    protected $dataCat = 'province';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_province::where('active','=','Y')
        ->orderBy('province_name', 'ASC')
        ->get();
        $queryCount = Mst_province::count();
        $data = [
            'province' => $query,
            'title' => $this->title,
            'uri' => $this->dataCat,
            'rowCount' => $queryCount
        ];

        return view('adm.province.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $country = Mst_country::where('active', '=', 'Y')
        ->orderBy('country_name', 'ASC')
        ->get();
        $data = [
            'country' => $country,
            'title' => $this->title,
            'uri' => $this->dataCat,
        ];
        return view('adm.province.create', $data);
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
            'menu_id' => 2,
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
            'provinceName' => 'required|max:128',
            'country_id' => 'required|numeric',
        ], [
            'country_id.numeric' => 'Please select a valid country'
        ])->validate();

        $ins = Mst_province::create([
            'province_name' => $request->provinceName,
            'country_id' => $request->country_id,
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/province');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_province  $Mst_province
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $query = Mst_province::where('id', '=', $id)
        ->first();
        if ($query) {
            $country = Mst_country::where('active', '=', 'Y')
            ->orderBy('country_name', 'ASC')
            ->get();
            $data = [
                'province' => $query,
                'country' => $country,
                'title' => $this->title,
                'uri' => $this->dataCat,
            ];

            return view('adm.province.show', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_province  $Mst_province
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = Mst_province::where('id', '=', $id)
        ->first();
        if ($query) {
            $country = Mst_country::where('active', '=', 'Y')
            ->orderBy('country_name', 'ASC')
            ->get();
            $data = [
                'province' => $query,
                'country' => $country,
                'title' => $this->title,
                'uri' => $this->dataCat,
            ];

            return view('adm.province.edit', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_province  $Mst_province
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 2,
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
            'provinceName' => 'required|max:128',
            'country_id' => 'required|numeric',
        ], [
            'country_id.numeric' => 'Please select a valid country'
        ])->validate();

        $ins = Mst_province::where('id', '=', $id)->update([
            'province_name' => $request->provinceName,
            'country_id' => $request->country_id,
            'active' => 'Y',
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/province');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_province  $Mst_province
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_province $Mst_province)
    {
        //
    }
}
