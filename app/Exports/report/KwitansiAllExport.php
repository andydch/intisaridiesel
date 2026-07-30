<?php

namespace App\Exports\report;

use App\Exports\report\KwitansiExport;
use App\Exports\report\KwitansiSummaryExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KwitansiAllExport implements WithMultipleSheets
{
    protected $branch_id;
    protected $start_date;
    protected $end_date;

    public function __construct($branch_id, $start_date, $end_date)
    {
        // ini_set('memory_limit', '64M');
        // ini_set('max_execution_time', 1800);

        $this->branch_id = $branch_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new KwitansiExport($this->branch_id, $this->start_date, $this->end_date),
            new KwitansiSummaryExport($this->branch_id, $this->start_date, $this->end_date),
        ];
    }
}
