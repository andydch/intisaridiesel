<?php

namespace App\Exports\dbg;

use App\Exports\dbg\ScanCityExport;
use App\Exports\dbg\ScanProvinceExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ScanExport implements WithMultipleSheets
{
    protected $fakturs;

    public function __construct($fakturs)
    {
        $this->fakturs = $fakturs;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new ScanCityExport($this->fakturs),
            new ScanProvinceExport($this->fakturs),
        ];
    }
}
