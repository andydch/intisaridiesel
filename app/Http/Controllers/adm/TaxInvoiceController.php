<?php

namespace App\Http\Controllers\adm;

use Exception;
use App\Models\Userdetail;
use App\Models\Mst_menu_user;
use Illuminate\Http\Request;
use App\Models\Tx_tax_invoice;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TaxInvoiceController extends Controller
{
    protected $title = 'Tax Invoice';
    protected $folder = 'tax-invoice';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $query = Tx_tax_invoice::whereNotIn('id', function($q){
            $q->select('tax_invoice_id')
            ->from('tx_delivery_orders')
            ->where('tax_invoice_id','<>',null);
        })
        ->where('active','=','Y')
        ->orderBy('fp_no', 'ASC');

        // $query = Tx_tax_invoice::where('active','=','Y')
        // ->orderBy('fp_no', 'ASC');

        $data = [
            'fp_nos' => $query->get(),
            'fp_nosCount' => $query->count(),
            'title' => $this->title,
            'folder' => $this->folder,
            'is_director_now' => $userLogin->is_director,
            'is_branch_head_now' => $userLogin->is_branch_head,
        ];

        return view('adm.'.$this->folder.'.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'userLogin' => $userLogin,
        ];

        return view('adm.'.$this->folder.'.create', $data);
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
            'menu_id' => 68,
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

        $validateInput = [
            'fp_no' => 'required|numeric',
            'qty_fp' => 'required|numeric',
        ];
        $errMsg = [
            'fp_no.required' => 'FP No field is required',
            'fp_no.numeric' => 'FP No field must be numeric',
            'qty_fp.required' => 'Qty FP No field is required',
            'qty_fp.numeric' => 'Qty FP No field must be numeric',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {

            $ins = Tx_tax_invoice::create([
                'fp_no' => $request->fp_no,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);
            if($request->qty_fp>0){
                $zeroLen = '';
                for($zero=0;$zero<strlen($request->fp_no);$zero++){
                    if(substr($request->fp_no,$zero,1)=='0'){
                        $zeroLen .= '0';
                    }else{
                        break;
                    }
                }
                for($i=1;$i<$request->qty_fp;$i++){
                    $ins = Tx_tax_invoice::create([
                        'fp_no' => $zeroLen.($request->fp_no+$i),
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                }
            }

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error','Something Went Wrong!');
        } catch(Exception $e){
            DB::rollback();
            // throw $e;

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error','Something Went Wrong!');
        }

        // If we reach here, then
        // data is valid and working.
        // Commit the queries!
        DB::commit();

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_tax_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $query = Tx_tax_invoice::where('id', '=', $id)
        ->first();
        if ($query) {
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'fp_nos' => $query,
            ];

            return view('adm.'.$this->folder.'.show', $data);
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
     * @param  \App\Models\Tx_tax_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = Tx_tax_invoice::where('id', '=', $id)
        ->first();
        if ($query) {
            $data = [
                'fp_nos' => $query,
                'title' => $this->title,
                'folder' => $this->folder,
            ];

            return view('adm.'.$this->folder.'.edit', $data);
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
     * @param  \App\Models\Tx_tax_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 68,
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

        $validateInput = [
            'prefiks_code' => 'required|size:3',
        ];
        $errMsg = [
            'prefiks_code.required' => 'Prefix Code is required',
            'prefiks_code.size' => 'Prefix Code must be 3 characters long',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {
            if ($request->ope=='sv'){
                $upd = Tx_tax_invoice::where([
                    'id' => $id,
                ])
                ->update([
                    'prefiks_code' => $request->prefiks_code,
                    'updated_by' => Auth::user()->id,
                ]);
            }
            if ($request->ope=='rm'){
                $upd = Tx_tax_invoice::where([
                    'id' => $id,
                ])
                ->update([
                    'active' => 'N',
                    'updated_by' => Auth::user()->id,
                ]);
            }

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        } catch(Exception $e){
            DB::rollback();
            // throw $e;

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        }

        // If we reach here, then
        // data is valid and working.
        // Commit the queries!
        DB::commit();

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_tax_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_tax_invoice $tx_purchase_order)
    {
        //
    }

    public function delFPno(Request $request){
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 68,
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

        $allId = substr($request->allId,1,strlen($request->allId));
        $allIdArr = explode(",",$allId);
        for($i=0;$i<count($allIdArr);$i++){
            $del = Tx_tax_invoice::where([
                'id' => $allIdArr[$i],
            ])
            ->update([
                'active'=>'N',
                'updated_by' => Auth::user()->id,
            ]);
        }

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
    }

    public function fpSearch(Request $request){
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 68,
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
        
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $query = Tx_tax_invoice::where('fp_no','LIKE','%'.$request->fp_no_to_search.'%')
        ->where('tx_tax_invoices.active','=','Y')
        ->orderBy('tx_tax_invoices.fp_no', 'ASC');

        $data = [
            'fp_nos' => $query->get(),
            'fp_nosCount' => $query->count(),
            'title' => $this->title,
            'folder' => $this->folder,
            'is_director_now' => $userLogin->is_director,
            'is_branch_head_now' => $userLogin->is_branch_head,
        ];

        return view('adm.'.$this->folder.'.index', $data);
    }
}
