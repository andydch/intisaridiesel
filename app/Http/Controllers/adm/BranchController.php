<?php

namespace App\Http\Controllers\adm;

use App\Models\Mst_branch;
use App\Models\Mst_province;
use App\Models\Mst_city;
use App\Models\Mst_district;
use App\Models\Mst_sub_district;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mst_part;
use App\Models\Tx_qty_part;
use App\Models\Mst_menu_user;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class BranchController extends Controller
{
    protected $title = 'Branch';
    protected $dataCat = 'branch';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_branch::where('active','=','Y')
        ->orderBy('name', 'ASC');
        $data = [
            'branches' => $query->get(),
            'title' => $this->title,
            'uri' => $this->dataCat,
            'rowCount' => $query->count(),
        ];

        return view('adm.branch.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $province = Mst_province::where([
            'country_id' => 9999,
            'active' => 'Y'
        ])
        ->orderBy('province_name', 'ASC')
        ->get();
        $city = [];
        if (old('province_id')) {
            $city = Mst_city::where([
                'province_id' => old('province_id'),
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();
        }
        $districts = [];
        if (old('city_id')) {
            $districts = Mst_district::where([
                'city_id' => old('city_id'),
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
        }
        $subdistricts = [];
        if (old('district_id')) {
            $subdistricts = Mst_sub_district::where([
                'district_id' => old('district_id'),
                'active' => 'Y'
            ])
            ->orderBy('sub_district_name', 'ASC')
            ->get();
        }
        $data = [
            'province' => $province,
            'cities' => $city,
            'districts' => $districts,
            'subdistricts' => $subdistricts,
            'title' => $this->title,
            'uri' => $this->dataCat,
        ];
        return view('adm.branch.create', $data);
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
            'menu_id' => 7,
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
            'initial' => 'required|not_regex:/[^A-Z0-9]/i|max:12',
            'branchName' => 'required|max:255',
            'address' => 'required|max:1024',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'subdistrict_id' => 'required|numeric',
            'postcode' => 'required|max:6',
            'phone1' => 'required|max:32',
            'phone2' => 'max:32|nullable',
        ], [
            'initial.not_regex' => 'Branch initial format is invalid.',
            'province_id.numeric' => 'Please select a valid province',
            'city_id.numeric' => 'Please select a valid city',
            'district_id.numeric' => 'Please select a valid district',
            'subdistrict_id.numeric' => 'Please select a valid sub district',
        ])->validate();

        $ins = Mst_branch::create([
            'initial' => $request->initial,
            'name' => $request->branchName,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district_id' => $request->district_id,
            'sub_district_id' => $request->subdistrict_id,
            'address' => $request->address,
            'post_code' => $request->postcode,
            'phone1' => $request->phone1,
            'phone2' => $request->phone2,
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ]);

        $qPart = Mst_part::where('active','=','Y')
        ->get();
        foreach($qPart as $q){
            $qty = Tx_qty_part::create([
                'part_id' => $q->id,
                'qty' => 0,
                'branch_id' => $ins->id,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);
        }

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/branch');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_branch  $mst_branch
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $branch = Mst_branch::where([
            'slug' => urldecode($slug)
        ])
        ->first();
        if ($branch) {
            $province = Mst_province::where([
                'country_id' => 9999,
                'active' => 'Y'
            ])
            ->orderBy('province_name', 'ASC')
            ->get();
            $city = Mst_city::where([
                'province_id' => $branch->province_id,
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();
            if (old('province_id')) {
                $city = Mst_city::where([
                    'province_id' => old('province_id'),
                    'active' => 'Y'
                ])
                ->orderBy('city_name', 'ASC')
                ->get();
            }
            $districts = Mst_district::where([
                'city_id' => $branch->city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
            if (old('city_id')) {
                $districts = Mst_district::where([
                    'city_id' => old('city_id'),
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
            }
            $subdistricts = Mst_sub_district::where([
                'district_id' => $branch->district_id,
                'active' => 'Y'
            ])
            ->orderBy('sub_district_name', 'ASC')
            ->get();
            if (old('district_id')) {
                $subdistricts = Mst_sub_district::where([
                    'district_id' => old('district_id'),
                    'active' => 'Y'
                ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
            }
            $data = [
                'province' => $province,
                'cities' => $city,
                'districts' => $districts,
                'subdistricts' => $subdistricts,
                'branch' => $branch,
                'title' => $this->title,
                'uri' => $this->dataCat,
            ];
            return view('adm.branch.show', $data);
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
     * @param  \App\Models\Mst_branch  $mst_branch
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $branch = Mst_branch::where([
            'slug' => urldecode($slug)
        ])
        ->first();
        if ($branch) {
            $province = Mst_province::where([
                'country_id' => 9999,
                'active' => 'Y'
            ])
            ->orderBy('province_name', 'ASC')
            ->get();
            $city = Mst_city::where([
                'province_id' => $branch->province_id,
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();
            if (old('province_id')) {
                $city = Mst_city::where([
                    'province_id' => old('province_id'),
                    'active' => 'Y'
                ])
                ->orderBy('city_name', 'ASC')
                ->get();
            }
            $districts = Mst_district::where([
                'city_id' => $branch->city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
            if (old('city_id')) {
                $districts = Mst_district::where([
                    'city_id' => old('city_id'),
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
            }
            $subdistricts = Mst_sub_district::where([
                'district_id' => $branch->district_id,
                'active' => 'Y'
            ])
            ->orderBy('sub_district_name', 'ASC')
            ->get();
            if (old('district_id')) {
                $subdistricts = Mst_sub_district::where([
                    'district_id' => old('district_id'),
                    'active' => 'Y'
                ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
            }
            $data = [
                'province' => $province,
                'cities' => $city,
                'districts' => $districts,
                'subdistricts' => $subdistricts,
                'branch' => $branch,
                'title' => $this->title,
                'uri' => $this->dataCat,
            ];
            return view('adm.branch.edit', $data);
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
     * @param  \App\Models\Mst_branch  $mst_branch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 7,
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
            'initial' => 'required|not_regex:/[^A-Z0-9]/i|max:12',
            'branchName' => 'required|max:255',
            'address' => 'required|max:1024',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'subdistrict_id' => 'required|numeric',
            'postcode' => 'required|max:6',
            'phone1' => 'required|max:32',
            'phone2' => 'max:32|nullable',
        ], [
            'initial.not_regex' => 'Branch initial format is invalid.',
            'province_id.numeric' => 'Please select a valid province',
            'city_id.numeric' => 'Please select a valid city',
            'district_id.numeric' => 'Please select a valid district',
            'subdistrict_id.numeric' => 'Please select a valid sub district',
        ])->validate();

        $upd = Mst_branch::where([
            'slug' => urldecode($slug)
        ])
        ->update([
            'initial' => $request->initial,
            'name' => $request->branchName,
            // 'slug' => SlugService::createSlug(Mst_branch::class, 'slug', $request->branchName),
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district_id' => $request->district_id,
            'sub_district_id' => $request->subdistrict_id,
            'address' => $request->address,
            'post_code' => $request->postcode,
            'phone1' => $request->phone1,
            'phone2' => $request->phone2,
            'active' => 'Y',
            'updated_by' => Auth::user()->id
        ]);

        $qBranch = Mst_branch::where([
            'slug' => urldecode($slug)
        ])
        ->first();

        $qPart = Mst_part::where('active','=','Y')
        ->get();
        foreach($qPart as $q){
            $qQty = Tx_qty_part::where([
                'part_id' => $q->id,
                'branch_id' => $qBranch->id,
            ])
            ->first();
            if(!$qQty){
                $qty = Tx_qty_part::create([
                    'part_id' => $q->id,
                    'qty' => 0,
                    'branch_id' => $qBranch->id,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                ]);
            }else{
                $qty = Tx_qty_part::where([
                    'part_id' => $q->id,
                    'branch_id' => $qBranch->id,
                ])
                ->update([
                    'part_id' => $q->id,
                    'qty' => 0,
                    'branch_id' => $qBranch->id,
                    'updated_by' => Auth::user()->id,
                ]);
            }
        }

        session()->flash('status', 'Existing data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/branch');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_branch  $mst_branch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_branch $mst_branch)
    {
        //
    }
}
