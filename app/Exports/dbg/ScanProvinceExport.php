<?php

namespace App\Exports\dbg;

use App\Models\Mst_province;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ScanProvinceExport implements FromView, WithTitle
{
    protected $fakturs;

    public function __construct($fakturs)
    {
        $this->fakturs = $fakturs;
    }

    public function view(): View
    {
        $qProvinces = Mst_province::select(
            'province_name as area_name'
        )
        ->get();
        $data = [
            'areas'=>$qProvinces,
            'fakturs'=>$this->fakturs,
        ];
        return view('dbg.scans', $data);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Province';
    }
}
