<?php

namespace App\Http\Controllers\adm;

use App\Models\Mst_global;
use App\Models\Mst_menu_user;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Helpers\GlobalFuncHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class MemoLimitController extends Controller
{
    protected $title = 'Memo Limit';
    protected $dataCat = 'memo-limit';

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
        ->where('active','=','Y')
        ->orderBy('data_cat', 'ASC')
        ->orderBy('order_no', 'ASC')
        ->get();
        $queryCount = Mst_global::where([
            'data_cat' => $this->dataCat
        ])
        ->where('active','=','Y')
        ->count();
        $data = [
            'globals' => $query,
            'globalsCount' => $queryCount,
            'title' => $this->title,
            'uri' => $this->dataCat
        ];

        return view('adm.global.memo-limit-index', $data);
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
        return view('adm.global.memo-limit-create', $data);
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
            'menu_id' => 66,
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
            'memo_limit_val' => ['required','max:1000000000',new NumericCustom('Memo Limit Value')],
        ], [])
        ->validate();

        $ins = Mst_global::create([
            'data_cat' => $this->dataCat,
            'title_ind' => 'Memo Limit',
            'title_eng' => 'Memo Limit',
            'order_no' => 1,
            'notes' => 'max total price di setiap pembuatan memo',
            'small_desc_ind' => null,
            'small_desc_eng' => null,
            'long_desc_ind' => null,
            'long_desc_eng' => null,
            'string_val' => null,
            'numeric_val' => GlobalFuncHelper::moneyValidate($request->memo_limit_val),
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
        return view('adm.global.memo-limit-show', $data);
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
        return view('adm.global.memo-limit-edit', $data);
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
            'menu_id' => 66,
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
            'memo_limit_val' => ['required','max:1000000000',new NumericCustom('Memo Limit Value')],
        ], [])
        ->validate();

        $ins = Mst_global::where([
            'slug' => urldecode($slug)
        ])->update([
            'data_cat' => $this->dataCat,
            'title_ind' => 'Memo Limit',
            'title_eng' => 'Memo Limit',
            'order_no' => 1,
            'notes' => 'max total price di setiap pembuatan memo',
            'small_desc_ind' => null,
            'small_desc_eng' => null,
            'long_desc_ind' => null,
            'long_desc_eng' => null,
            'string_val' => null,
            'numeric_val' => GlobalFuncHelper::moneyValidate($request->memo_limit_val),
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
