<?php

namespace App\Http\Controllers\tx;

use PDF;
use Exception;
use App\Models\Mst_global;
use App\Models\Mst_company;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_retur;
use App\Http\Controllers\Controller;
use App\Models\Tx_purchase_retur_part;
use Illuminate\Validation\ValidationException;

class PurchaseReturPrintController extends Controller
{
    protected $title = 'Purchase Retur';
    protected $folder = 'purchase-retur';

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
     * @param  \App\Models\Tx_purchase_retur  $tx_purchase_retur
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '512M');

        try {

            $query = Tx_purchase_retur::where('id', '=', $id)
            ->first();
            if ($query) {
                $queryPart = Tx_purchase_retur_part::where([
                    'purchase_retur_id' => $query->id,
                    'active' => 'Y'
                ])
                ->get();
                $queryPartCount = Tx_purchase_retur_part::where([
                    'purchase_retur_id' => $query->id,
                    'active' => 'Y'
                ])
                ->count();

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

                $companyName = '';
                $company = Mst_company::where('active','=','Y')
                ->first();
                if($company){
                    $companyName = $company->name;
                }

                $data = [
                    'returs' => $query,
                    'parts' => $queryPart,
                    'partsCount' => $queryPartCount,
                    'vat' => $vat,
                    'companyName' => $companyName
                ];
                $pdf = PDF::loadView('tx.'.$this->folder.'.purchase-retur-pdf', $data);
                // $pdf->debug = true;
                return $pdf->stream('document-retur-'.$query->purchase_retur_no.'.pdf');
            } else {
                $data = [
                    'errNotif' => 'The data you are looking for is not found'
                ];
                return view('error-notif.not-found-notif', $data);
            }

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            // DB::rollback();

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        } catch(Exception $e){
            // DB::rollback();
            // throw $e;

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        }


    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_purchase_retur  $tx_purchase_retur
     * @return \Illuminate\Http\Response
     */
    public function edit(Tx_purchase_retur $tx_purchase_retur)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_purchase_retur  $tx_purchase_retur
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tx_purchase_retur $tx_purchase_retur)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_purchase_retur  $tx_purchase_retur
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_retur $tx_purchase_retur)
    {
        //
    }
}
