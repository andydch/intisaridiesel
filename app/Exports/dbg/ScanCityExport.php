<?php

namespace App\Exports\dbg;

use App\Models\Mst_city;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ScanCityExport implements FromView, WithTitle
{
    protected $fakturs;

    public function __construct($fakturs)
    {
        $this->fakturs = $fakturs;
    }

    public function view(): View
    {
        $qCities = Mst_city::select(
            'city_name as area_name',
        )
        ->get();
        $data = [
            'areas'=>$qCities,
            'fakturs'=>$this->fakturs,
        ];
        return view('dbg.scans', $data);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'City';
    }
}
