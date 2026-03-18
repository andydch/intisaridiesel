<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_global;
use App\Models\Tx_invoice;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_delivery_order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_delivery_order_part;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InvoiceTaxInvController extends Controller
{
    protected $title = 'Invoice';
    protected $folder = 'invoice';

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
    public function show($invoice_no)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $query = Tx_invoice::where('invoice_no','=',urldecode($invoice_no))
        ->first();
        if($query){
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

            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)->first();

            $delivery_order = Tx_delivery_order::where('customer_id','=',$query->customer_id)
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('active','=','Y')
            ->get();

            $delivery_order_per_id = Tx_delivery_order::where('id','=',$query->delivery_order_id)
            ->where('active','=','Y')
            ->first();

            $delivery_order_part = Tx_delivery_order_part::where([
                'delivery_order_id' => $query->delivery_order_id,
                'active' => 'Y'
            ])
            ->get();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'vat' => $vat,
                'deliveryOrders' => $delivery_order,
                'delivery_order_part' => $delivery_order_part,
                'delivery_order_per_id' => $delivery_order_per_id,
                'qInv' => $query,
                'qCurrency' => $qCurrency
            ];
            return view('tx.'.$this->folder.'.show-tax-inv', $data);
        }else{
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function edit($invoice_no)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $query = Tx_invoice::where('invoice_no','=',urldecode($invoice_no))
        ->first();
        if($query){
            $delivery_order = Tx_delivery_order::where('customer_id','=',$query->customer_id)
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('active','=','Y')
            ->get();

            $delivery_order_per_id = Tx_delivery_order::where('id','=',$query->delivery_order_id)
            ->where('active','=','Y')
            ->first();

            $delivery_order_part = Tx_delivery_order_part::where([
                'delivery_order_id' => $query->delivery_order_id,
                'active' => 'Y'
            ])
            ->get();
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'deliveryOrders' => $delivery_order,
                'delivery_order_part' => $delivery_order_part,
                'delivery_order_per_id' => $delivery_order_per_id,
                'qInv' => $query,
                'qCurrency' => $qCurrency
            ];
            return view('tx.'.$this->folder.'.edit-tax-inv', $data);
        }else{
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
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $invoice_no)
    {
        $validateInput = [
            'tax_invoice_no' => 'required|max:64',
            'tax_invoice_date' => 'required|date',
        ];
        $errMsg = [
            // 'customer_id.numeric' => 'Please select a valid supplier',
            // 'delivery_order_id.numeric' => 'Please select a valid delivery order no',
            // 'delivery_order_id.unique' => 'The delivery order number has already been taken.',
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

            $upd = Tx_invoice::where('invoice_no','=',urldecode($invoice_no))
            ->update([
                'tax_invoice_no' => $request->tax_invoice_no,
                'tax_invoice_date' => $request->tax_invoice_date,
                'active' => 'Y',
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
