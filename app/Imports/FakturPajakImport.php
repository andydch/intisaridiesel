<?php

namespace App\Imports;

use App\Models\Tx_tax_invoice;
use App\Models\Tx_delivery_order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;

class FakturPajakImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach($rows as $row){
            $npwp_pembeli = $row[0];
            $no_faktur_pajak = $row[3];
            $no_faktur = $row[14];
            if(strpos("-".$no_faktur,env('P_FAKTUR'))>0){
                // cek apakah faktur sudah ada di database
                $qFP = Tx_tax_invoice::where([
                    'fp_no'=>$no_faktur_pajak,
                ])
                ->first();
                if(!$qFP){
                    // insert no faktur pajak
                    $insFP = Tx_tax_invoice::create([
                        'fp_no'=>$no_faktur_pajak,
                        'prefiks_code'=>null,
                        'active'=>'Y',
                        'updated_by'=>Auth::user()->id,
                        'created_by'=>Auth::user()->id,
                    ]);

                    // update no faktur di masing-2 dokumen faktur
                    $qFPdoc = Tx_delivery_order::whereRaw('tax_invoice_id is null')
                    ->where([
                        'delivery_order_no'=>$no_faktur,
                    ])
                    ->update([
                        'tax_invoice_id'=>$insFP->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }else{
                    // update no faktur di masing-2 dokumen faktur
                    $qFPdoc = Tx_delivery_order::whereRaw('tax_invoice_id is null')
                    ->where([
                        'delivery_order_no'=>$no_faktur,
                    ])
                    ->update([
                        'tax_invoice_id'=>$qFP->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }
            }
        }
    }
}
