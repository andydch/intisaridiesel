<?php

namespace App\Exports\coretax;

use Exception;
use App\Models\Mst_company;
use App\Models\Tx_delivery_order;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class FakturListExport implements FromView, WithTitle, WithColumnFormatting, ShouldAutoSize
{
    protected $fakturs;

    public function __construct($fakturs)
    {
        $this->fakturs = $fakturs;
    }

    public function view(): View
    {
        // Start transaction!
        DB::beginTransaction();

        try {

            $npwpNo = '';
            $company = Mst_company::select('npwp_no')
            ->where([
                'id'=>1,
            ])
            ->first();
            if ($company){
                $npwpNo = str_replace(".","",$company->npwp_no);
                $npwpNo = str_replace("-","",$npwpNo);
            }

            // update flag faktur_dl_date
            foreach($this->fakturs as $faktur){
                $updFlag = Tx_delivery_order::where([
                    'id'=>$faktur['faktur_id'],
                ])
                ->update([
                    'faktur_dl_date'=>date('Y-m-d H:i:s'),
                    'updated_by'=>Auth::user()->id,
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

        $data = [
            'npwpNo'=>$npwpNo,
            'fakturs'=>$this->fakturs,
        ];
        return view('tx.delivery-order.fakturs', $data);

    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Faktur';
    }

    public function columnFormats(): array
    {
        return [
            'C1' => NumberFormat::FORMAT_TEXT,
            'A' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
            // 'F' => NumberFormat::FORMAT_NUMBER,
            // 'G' => NumberFormat::FORMAT_NUMBER,
            // 'H' => NumberFormat::FORMAT_NUMBER,
            // 'I' => NumberFormat::FORMAT_NUMBER,
            // 'J' => NumberFormat::FORMAT_NUMBER,
            // 'K' => NumberFormat::FORMAT_NUMBER,
            // 'C' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
    }
}
