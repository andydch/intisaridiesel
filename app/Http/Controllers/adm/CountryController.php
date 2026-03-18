<?php

namespace App\Http\Controllers\adm;

use App\Models\Mst_country;
use App\Models\Mst_menu_user;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    protected $title = 'Country';
    protected $dataCat = 'country';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_country::where('active','=','Y')
        ->orderBy('country_name', 'ASC')
        ->get();
        $queryCount = Mst_country::count();
        $data = [
            'country' => $query,
            'title' => $this->title,
            'uri' => $this->dataCat,
            'rowCount' => $queryCount
        ];

        return view('adm.country.index', $data);
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
            'uri' => $this->dataCat,
        ];
        return view('adm.country.create',$data);
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
            'menu_id' => 1,
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
            'countryName' => 'required|max:128',
        ])->validate();

        $ins = Mst_country::create([
            'country_name' => $request->countryName,
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/country');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_country  $Mst_country
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $query = Mst_country::where('id', '=', $id)->first();
        if ($query) {
            $data = [
                'title' => $this->title,
                'uri' => $this->dataCat,
                'country' => $query
            ];

            return view('adm.country.show', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_country  $Mst_country
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = Mst_country::where('id', '=', $id)->first();
        if ($query) {
            $data = [
                'title' => $this->title,
                'uri' => $this->dataCat,
                'country' => $query
            ];

            return view('adm.country.edit', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_country  $Mst_country
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 1,
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
            'countryName' => 'required|max:128',
        ])->validate();

        $ins = Mst_country::where('id', '=', $id)->update([
            'country_name' => $request->countryName,
            'active' => 'Y',
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/country');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_country  $Mst_country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_country $Mst_country)
    {
        //
    }
}
