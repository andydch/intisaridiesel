<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HutangPiutangImport implements WithMultipleSheets
{
    public HutangSheetImport $hutang;
    public PiutangSheetImport $piutang;

    public function __construct()
    {
        $this->hutang = new HutangSheetImport();
        $this->piutang = new PiutangSheetImport();
    }

    public function sheets(): array
    {
        return [
            'kartu-hutang'  => $this->hutang,
            'kartu-piutang' => $this->piutang,
        ];
    }
}
