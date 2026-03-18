<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_tax_invoice;
use App\Models\Tx_delivery_order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_delivery_order_part;
use App\Models\Mst_menu_user;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeliveryOrderFPController extends Controller
{
    protected $title = 'Faktur';
    protected $folder = 'delivery-order';
    protected $uri = 'faktur-fp';
    protected $uriOld = 'faktur';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        // get active VAT
        $vat = ENV('VAT');
        $qVat = Mst_global::where([
            'data_cat' => 'vat',
            'active' => 'Y'
        ])
        ->first();
        if ($qVat) {
            $vat = $qVat->numeric_val;
        }

        $ship_to = [];
        $query = [];

        $query = Tx_delivery_order::where('id','=',$id)
        ->first();
        if($query){
            $qFP = Tx_tax_invoice::where('active','=','Y')
            ->whereNotIn('id', function($q) use($query){
                $q->select('tax_invoice_id')
                ->from('tx_delivery_orders')
                ->where('tax_invoice_id','<>',$query->tax_invoice_id)
                ->where('active','=','Y');
            })
            ->orderBy('fp_no','ASC')
            ->get();

            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $queryFK = Tx_delivery_order::leftJoin('userdetails AS usr','tx_delivery_orders.created_by','=','usr.user_id')
            ->select('tx_delivery_orders.*')
            ->addSelect(['total_price' => Tx_delivery_order_part::selectRaw('SUM(tx_delivery_order_parts.qty_so*tx_delivery_order_parts.final_price)')
                ->whereColumn('tx_delivery_order_parts.delivery_order_id','tx_delivery_orders.id')
                // ->where('tx_delivery_order_parts.active','=','Y')
            ])
            ->where('tx_delivery_orders.id','=',$query->id)
            ->where(function($q){
                $q->where('tx_delivery_orders.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_delivery_orders.active','N')
                    ->where('tx_delivery_orders.delivery_order_no','NOT LIKE','%Draft%');
                });
            })
            // ->where('usr.branch_id','=',$userLogin->branch_id)
            ->first();
        }else{
            $query = [];

            $qFP = Tx_tax_invoice::where('active','=','Y')
            ->whereNotIn('id', function($q){
                $q->select('tax_invoice_id')
                ->from('tx_delivery_orders')
                ->where('active','=','Y');
            })
            ->orderBy('fp_no','ASC')
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
            'uriOld' => $this->uriOld,
            'vat' => $vat,
            'ship_to' => $ship_to,
            'queryDelivery' => $query,
            'qCurrency' => $qCurrency,
            'qFP' => $qFP,
            'queryFK' => $queryFK
        ];

        return view('tx.'.$this->folder.'.edit-fp', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 39,
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
            'fp_no' => 'required|numeric|unique:App\Models\Tx_delivery_order,tax_invoice_id',
        ];
        $errMsg = [
            'fp_no.numeric' => 'Please select a valid fp no',
            'fp_no.unique' => 'Please select a valid fp no',
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

            $upd = Tx_delivery_order::where('id','=',$id)
            ->update([
                'tax_invoice_id' => $request->fp_no,
                'updated_by' => Auth::user()->id,
            ]);

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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uriOld);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_delivery_order $tx_delivery_order)
    {
        //
    }
}
