<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ReportMasterInventoryController extends Controller
{
    protected $title = 'Master Inventory';
    protected $folder = 'master-inventory';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $brands = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y'
        ])
        ->orderBy('title_ind', 'ASC')
        ->get();
        $data = [
            'qCurrency' => $qCurrency,
            'branches' => $branches,
            'brands' => $brands,
            'title' => $this->title,
            'folder' => $this->folder,
            'rpt' => []
        ];
        return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
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
        $validateInput = [
            'branch_id' => 'required|numeric',
            'brand_id' => 'required|numeric',
        ];
        $errMsg = [
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
            'brand_id.required' => 'Please select a valid brand',
            'brand_id.numeric' => 'Please select a valid brand',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $brands = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y'
        ])
        ->orderBy('title_ind', 'ASC')
        ->get();

        $data = [
            'qCurrency' => $qCurrency,
            'branches' => $branches,
            'brands' => $brands,
            'title' => $this->title,
            'folder' => $this->folder,
            'reqs' => $request,
        ];

        switch ($request->view_mode) {
            case 'P':
                return redirect(ENV('REPORT_FOLDER_NAME').'/'.$this->folder.'-xlsx/'.
                    $request->branch_id.'/'.
                    $request->brand_id.'/'.
                    ($request->oh_is_zero=='on'?'on':'off'));
                break;
            default:
                return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_part  $mst_part
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
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_part $mst_part)
    {
        //
    }
}
