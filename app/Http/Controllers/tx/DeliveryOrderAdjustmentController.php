<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Rules\ValidateQty;
use App\Models\Mst_courier;
use App\Models\Tx_qty_part;
use App\Models\Mst_customer;
use Illuminate\Http\Request;
use App\Models\Tx_delivery_order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_delivery_order_part;
use Illuminate\Support\Facades\Validator;
use App\Models\Mst_customer_shipment_address;
use Illuminate\Validation\ValidationException;

class DeliveryOrderAdjustmentController extends Controller
{
    protected $title = 'Delivery Order';
    protected $folder = 'delivery-order-adjustment';

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

        $queryCustomer = Mst_customer::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_name', 'ASC')
        ->get();

        $weighttype = Mst_global::where([
            'data_cat' => 'weight-type',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
        ->get();

        $couriers = Mst_courier::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

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

        $sales_order = [];
        $ship_to = [];
        $query = Tx_delivery_order::where('id','=',$id)->first();
        $parts = [];
        $partCount = 0;
        if(old('customer_id')){
            $parts = Tx_delivery_order_part::where([
                'delivery_order_id' => $id,
                'active' => 'Y'
            ])
            ->get();
            $partCount = Tx_delivery_order_part::where([
                'delivery_order_id' => $id,
                'active' => 'Y'
            ])
            ->count();
            $ship_to = Mst_customer_shipment_address::where([
                'customer_id' => old('customer_id'),
                'active' => 'Y'
            ])
            ->get();
        }else{
            if($query){
                $parts = Tx_delivery_order_part::where([
                    'delivery_order_id' => $id,
                    'active' => 'Y'
                ])
                ->get();
                $partCount = Tx_delivery_order_part::where([
                    'delivery_order_id' => $id,
                    'active' => 'Y'
                ])
                ->count();
                $ship_to = Mst_customer_shipment_address::where([
                    'customer_id' => $query->customer_id,
                    'active' => 'Y'
                ])
                ->get();
            }
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCust' => $queryCustomer,
            'parts' => $parts,
            'weighttype' => $weighttype,
            'couriers' => $couriers,
            'totalRow' => (old('totalRow') ? old('totalRow') : $partCount),
            'vat' => $vat,
            'get_sales_order_no' => $sales_order,
            'ship_to' => $ship_to,
            'queryDelivery' => $query,
            'qCurrency' => $qCurrency
        ];

        return view('tx.'.$this->folder.'.edit', $data);
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
        // data user yg login saat ini
        $user = Userdetail::where('user_id','=',Auth::user()->id)->first();

        $validateInput = [];
        $errMsg = [];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['qtyAdjustment'.$i]) {
                    $f = Tx_delivery_order_part::where('id','=',$request['delv_order_part_id'.$i])
                        ->first();
                    $validateAInput = [
                        'qtyAdjustment'.$i => ['required','numeric', new ValidateQty($f->part_id,$user->branch_id)],
                    ];
                    $errAMsg = [
                        'qtyAdjustment'.$i.'.required' => 'The qty adjustment is required',
                        'qtyAdjustment'.$i.'.numeric' => 'The qty adjustment must be a number',
                    ];
                    $validateInput = array_merge($validateInput, $validateAInput);
                    $errMsg = array_merge($errMsg, $errAMsg);
                }
            }
        }
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {

            $draft = false;
            $orders = Tx_delivery_order::where('id', '=', $id)
            ->where('delivery_order_no','LIKE','%Draft%')
            ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $delivery_order_no = $orders->delivery_order_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_delivery_order';
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y") > (int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => $newInc
                        ]);
                    }
                } else {
                    $insInc = Auto_inc::create([
                        'identity_name' => $identityName,
                        'id_auto_inc' => $newInc
                    ]);
                }

                $zero = '';
                for ($i = 0; $i < (4 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $delivery_order_no = 'DO'.date('y').'-'.$zero.strval($newInc);

                $upd = Tx_delivery_order::where('id', '=', $id)
                ->update([
                    'delivery_order_no' => $delivery_order_no,
                    'delivery_order_date' => date("Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_delivery_order::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $upd = Tx_delivery_order::where('id','=',$id)
                ->update([
                    'updated_by' => Auth::user()->id,
                ]);

            if ($request->totalRow > 0) {
                // set not active
                $upd = Tx_delivery_order_part::where('delivery_order_id','=',$id)
                ->update([
                    'active' => 'N',
                    'updated_by' => Auth::user()->id
                ]);

                $totalQty = 0;
                $totalPrice = 0;
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['qtyAdjustment'.$i]) {
                        $f = Tx_delivery_order_part::where('id','=',$request['delv_order_part_id'.$i])
                        ->first();

                        $partialDelivered = 'N';
                        $qtySum = Tx_delivery_order_part::where('id','<>',$request['delv_order_part_id'.$i])
                        ->where([
                            'sales_order_id' => $f->sales_order_id,
                            'active' => 'Y'
                        ])
                            ->sum('qty');
                        if(($f->qty_so+$qtySum)>$request['qtyAdjustment'.$i]){
                            $partialDelivered = 'Y';
                        }

                        $upd = Tx_delivery_order_part::where('id','=',$request['delv_order_part_id'.$i])
                        ->update([
                            'qty' => $request['qtyAdjustment'.$i],
                            'total_price' => $f->final_price*$request['qtyAdjustment'.$i],
                            'is_partial_delivered' => $partialDelivered,
                            'active' => 'Y',
                            'updated_by' => Auth::user()->id,
                        ]);

                        // update stok tersedia
                        $qStock = Tx_qty_part::where('part_id','=',$f->part_id)
                        ->where('branch_id','=',$user->branch_id)
                        ->first();
                        if($qStock){
                            $updStock = Tx_qty_part::where('part_id','=',$f->part_id)
                            ->where('branch_id','=',$user->branch_id)
                            ->update([
                                'qty' => ($qStock->qty+$f->qty)-$request['qtyAdjustment'.$i],
                                'updated_by' => Auth::user()->id,
                            ]);
                        }

                        $totalQty += $request['qtyAdjustment'.$i];
                        $totalPrice += ($request['qtyAdjustment'.$i]*$f->final_price);
                    }
                }

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

                $upd = Tx_delivery_order::where('id','=',$id)
                ->update([
                    'total_qty' => $totalQty,
                    'total_before_vat' => $totalPrice,
                    'total_after_vat' => $totalPrice+($totalPrice*$vat/100),
                    'updated_by' => Auth::user()->id,
                    'adjustment_at' => now(),
                    'adjustment_by' => Auth::user()->id,
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order');
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
