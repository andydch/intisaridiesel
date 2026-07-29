<?php

namespace App\Exports\report;

use App\Exports\report\TagihanSupplierExport;
use App\Exports\report\TagihanSupplierSummaryExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TagihanSupplierAllExport implements WithMultipleSheets
{
    protected $branch_id;
    protected $date_start;
    protected $date_end;

    public function __construct($branch_id, $date_start, $date_end)
    {
        // ini_set('memory_limit', '64M');
        // ini_set('max_execution_time', 1800);

        $this->branch_id = $branch_id;
        $this->date_start = $date_start;
        $this->date_end = $date_end;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new TagihanSupplierExport($this->branch_id, $this->date_start, $this->date_end),
            new TagihanSupplierSummaryExport($this->branch_id, $this->date_start, $this->date_end),
        ];
    }
}
